<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class CadastraOportunidadeController extends Controller
{
    function Index(Request $request) {
        //Seleciona contactId
        $request_data = $request["data"];
        $contact_id = $request_data["contactId"];

        $full_contact = json_decode($this->GetDigiSacContact($contact_id));
        $full_contact_data_number = $full_contact->data;
        $created_company = json_decode($this->CreateCompanyOnRD($full_contact->name));
        $created_contact = $this->CreateContactOnRD($full_contact->name, $full_contact_data_number->number, $created_company->_id);
        $created_deal = json_decode($this->CreateDealOnRD($full_contact->name, $full_contact_data_number->number, $created_company->_id), true);

        DB::insert('insert into contact_organization (NAME, NUMBER, ORGANIZATION_ID) values (?, ?, ?)', [$full_contact->name, $full_contact_data_number->number, $created_company->_id]);

        return $created_deal;
    }

    private function GetDigiSacContact($contact_id)
    {
        // Token de autenticação
        $token = env('DIGISACTOKEN');

        // Realiza a requisição HTTP GET com o cabeçalho de autorização
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            ])->get("https://camisetasparana.digisac.biz/api/v1/contacts/{$contact_id}");

        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna o conteúdo da resposta
            return $response->body();
        } else {
            // Em caso de falha na requisição, retorna uma mensagem de erro
            return $response->status();
        }
    }


    private function CreateCompanyOnRD($contact_name)
    {
        // Token de autenticação
        $token = env('RDTOKEN');

        // Realiza a requisição HTTP GET com o cabeçalho de autorização
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
            ])->post("https://crm.rdstation.com/api/v1/organizations?token={$token}", [
                'organization' => [
                    'name' => "{$contact_name}"
                ]
            ]);
        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna o conteúdo da resposta
            return $response->body();
        } else {
            // Em caso de falha na requisição, retorna uma mensagem de erro
            return $response->status();
        }
    }

    private function CreateContactOnRD($contact_name, $contact_number, $organization_id)
    {
        // Token de autenticação
        $token = env('RDTOKEN');

        // Realiza a requisição HTTP GET com o cabeçalho de autorização
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
            ])->post("https://crm.rdstation.com/api/v1/contacts?token={$token}", [
                'contact' => [
                    'name' => $contact_name,
                    'organization_id' => $organization_id,
                    'phones' => [
                        [
                            'phone' => $contact_number
                        ]
                    ]
                ]
            ]);

        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna o conteúdo da resposta
            return $response->body();
        } else {
            // Em caso de falha na requisição, retorna uma mensagem de erro
            return $response->status();
        }
    }


    private function CreateDealOnRD($contact_name, $contact_number, $organization_id)
    {
        // Token de autenticação
        $token = env('RDTOKEN');

        // Realiza a requisição HTTP GET com o cabeçalho de autorização
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
            ])->post("https://crm.rdstation.com/api/v1/deals?token={$token}", [
                'deal_source' => [
                    '_id' => '629f76e4f8bd9e0012cea59a' // Fonte: cliente ativo
                ],
                'deal' => [
                    'deal_stage_id' => '629f76e4f8bd9e0012cea59c', // em contato
                    'name' => 'Negociação'
                ],
                'distribution_settings' => [
                    'owner' => [
                        'type' => 'all'
                    ]
                ],
                'organization' => [
                    '_id' => "{$organization_id}"
                ],
                'contacts' => [
                    [
                        'name' => "{$contact_name}",
                        'phones' => [
                            [
                                'phone' => "{$contact_number}"
                            ]
                        ]
                    ]
                ]
            ]);

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
