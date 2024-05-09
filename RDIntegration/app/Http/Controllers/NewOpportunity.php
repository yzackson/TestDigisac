<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Throwable;


class NewOpportunity extends Controller
{
    function Index(Request $request): string
    {
        try {
            Log::debug("Requisição para criar cliente recebida: $request");
            //Selection contactId
            $request_data = $request["data"];
            $contact_id = $request_data["contactId"];
            $request_message = $request_data["message"];

            $full_contact = json_decode($this->GetDigiSacContact($contact_id));
            $full_contact_data_number = $full_contact->data;

            
            $client_name = $request_message["text"] . " (" . $full_contact->name . ")";
            try {
                $organization_id = DB::select('SELECT organization_id FROM contact_organization WHERE name = ? and number = ?', [$client_name, $full_contact_data_number->number]);
            } catch (Throwable $th) {
                Log::debug("Ocorreu um erro. Contacto o mantenedor: " . $th->__toString());
                return "Ocorreu um erro. Contacto o mantenedor: " . $th->__toString();
            }

            if($organization_id == null) {
                $created_company = json_decode($this->CreateCompanyOnRD($client_name));
                if (is_numeric($created_company)) {
                    Log::debug("Error code: ". $created_company);
                    return ("Error code: ". $created_company);
                }
                $created_deal = json_decode($this->CreateDealOnRD($client_name, $full_contact_data_number->number, $created_company->_id), true);

                if(!is_numeric($created_deal)) {
                    Log::debug("Created company: " . json_encode($created_company));
                    Log::debug("Created deal: " . json_encode($created_deal));
                    DB::insert('insert into contact_organization (NAME, NUMBER, ORGANIZATION_ID) values (?, ?, ?)', [$client_name, $full_contact_data_number->number, $created_company->_id]);
                }

                Log::debug("Created deal status: " . json_encode($created_deal));
                return ("Created deal status: " . json_encode($created_deal));
            } else {
                Log::error("Organization already registered");
                return "Organization already registered";
            }
        } catch (Throwable $th) {
            Log::error("Something went wrong: " . $th);
            return ("Something went wrong: " . $th);
        }
    }

    /**
     * @throws Throwable
     * @throws ConnectionException
     */
    private function GetDigiSacContact($contact_id): string
    {
        // Authentication token
        $token = env('DIGISACTOKEN');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            ])->get("https://camisetasparana.digisac.biz/api/v1/contacts/$contact_id");

        // Check if request was successful
        if ($response->successful()) {
            // Return response
            return $response->body();
        } else {
            // Return error code
            return (string)$response->status();
        }
    }


    /**
     * @throws Throwable
     * @throws ConnectionException
     */
    private function CreateCompanyOnRD($contact_name): string
    {
        $token = env('RDTOKEN');
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ])->post("https://crm.rdstation.com/api/v1/organizations?token={$token}", [
            'organization' => [
                'name' => $contact_name
            ]
        ]);

        // Check if request was successful
        if ($response->successful()) {
            // Return response
            return $response->body();
        } else {
            // Return error code
            return (string)$response->status();
        }
    }

    /**
     * @throws ConnectionException
     */
    private function CreateContactOnRD($contact_name, $contact_number, $organization_id): string
    {
        $token = env('RDTOKEN');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
            ])->post("https://crm.rdstation.com/api/v1/contacts?token=$token", [
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

        // Check if request was successful
        if ($response->successful()) {
            // Return response
            return $response->body();
        } else {
            // Return error code
            return (string)$response->status();
        }
    }


    /**
     * @throws Throwable
     * @throws ConnectionException
     */
    private function CreateDealOnRD($contact_name, $contact_number, $organization_id): string
    {
        $token = env('RDTOKEN');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
            ])->post("https://crm.rdstation.com/api/v1/deals?token=$token", [
                'deal_source' => [
                    '_id' => '629f76e4f8bd9e0012cea59a' // Active Client
                ],
                'deal' => [
                    'deal_stage_id' => '629f76e4f8bd9e0012cea59c', // In Contact
                    'name' => 'Negociação'
                ],
                'distribution_settings' => [
                    'owner' => [
                        'type' => 'all'
                    ]
                ],
                'organization' => [
                    '_id' => "$organization_id"
                ],
                'contacts' => [
                    [
                        'name' => "$contact_name",
                        'phones' => [
                            [
                                'phone' => "$contact_number"
                            ]
                        ]
                    ]
                ]
            ]);

        // Check if request was successful
        if ($response->successful()) {
            // Return response
            return $response->body();
        } else {
            // Return error code
            return (string)$response->status();
        }
    }
}
