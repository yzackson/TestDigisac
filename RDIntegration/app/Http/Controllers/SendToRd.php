<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendToRd extends Controller
{
    public function Send(Request $request) {
        try {
          $contact_id = $request["contactId"];

          $full_contact = json_decode($this->GetDigiSacContact($contact_id));
          $full_contact_data_number = $full_contact->data;

          $organization_id = DB::select('SELECT organization_id FROM contact_organization WHERE name = ? and number = ?', [$full_contact->name, $full_contact_data_number->number]);
        } catch (\Throwable $th) {
          return "Ocorreu um erro. Contacto o mantenedor: " . $th->__toString();
        }

        if($organization_id != null) {
          $response = $this->UpdateCompanyOnRD($organization_id[0]->organization_id,$request);  
          if($response != typeOf(1)) {
            Log::info("Cliente atualizado com sucesso: " . $response);
            return "Cliente atualizado com sucesso";
          }
        } else {
          Log::error("Organization ID not found. Contact: " . $full_contact);
          return "Organization ID not found. Contact: " . $full_contact;
        }
    }



    private function GetDigiSacContact($contact_id)
    {
        // Token de autenticação
        $token = env('DIGISACTOKEN');

        // Realiza a requisição HTTP GET com o cabeçalho de autorização
        try {
          $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            ])->get("https://camisetasparana.digisac.biz/api/v1/contacts/{$contact_id}");
        } catch (\Throwable $th) {
          throw $th;
        }

        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna o conteúdo da resposta
            return $response->body();
        } else {
            // Em caso de falha na requisição, retorna uma mensagem de erro
            return $response->status();
        }
    }


    private function UpdateCompanyOnRD($organization_id, $request)
    {
        // Token de autenticação
        $token = env('RDTOKEN');

        // Realiza a requisição HTTP GET com o cabeçalho de autorização
        try {
          $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
            ])->put("https://crm.rdstation.com/api/v1/organizations/{$organization_id}?token={$token}", [
              'organization' => [
                  'organization_custom_fields' => [
                    [
                      "value" => $request["cpf_cnpj"],
                      "custom_field_id" => "6495b874762aa6000f8ea83d" //CPF/CNPJ
                    ],
                    [
                      "value" => $request["razao_social"],
                      "custom_field_id" => "66216ece48208900114653b8" //Razão Social
                    ],
                    [
                      "value" => $request["ie"],
                      "custom_field_id" => "66216ee4dd3387001bc0edd3" //Inscrição Estadual
                    ],
                    [
                      "value" => $request["cep"],
                      "custom_field_id" => "66216f0aed7897000f9e113f" //CEP
                    ],
                    [
                      "value" => $request["endereco"],
                      "custom_field_id" => "66216f01d700ad000f579068" //Endereço
                    ],
                    [
                      "value" => $request["bairro"],
                      "custom_field_id" => "66216ec9ed7897000f9e10d3" //Bairro
                    ],
                    [
                      "value" => $request["estado"],
                      "custom_field_id" => "66216fcfb15e2e000ef1d45e" //Estado
                    ],
                    [
                      "value" => $request["email"],
                      "custom_field_id" => "66216ec6a2fa4e00153e944a" //Email
                    ]
                  ]
              ]
            ]);
        } catch (\Throwable $th) {
          throw $th;
        }

        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna o conteúdo da resposta
            return $response->body();
        } else {
            // Em caso de falha na requisição, retorna uma mensagem de erro
            return $response->status();
        }
    }
}