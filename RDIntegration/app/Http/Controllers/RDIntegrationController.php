<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Throwable;

class RDIntegrationController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function UpdateOrganization(Request $request): \Illuminate\Contracts\View\View
    {
        try {
            Log::debug("Requisição para atualizar cliente recebida: $request");
            // Obtém o ID da mensagem passado no parãmetro "message"
            $message = $request->input('message');

            // Extrai somente o conteúdo da mensagem
            $message = $this->getDigisacMessage($message);
            $message_text = json_decode($message);




            $cpfCnpj = "";
            $razaoSocial = "";
            $ie = "";
            $cep = "";
            $endereco = "";
            $bairro = "";
            $estado = "";
            $email = "";
            
            // Retorna os dados em formato JSON
            $json_text = json_decode($this->stringToJson($message_text->text), true);
            foreach ($json_text as $key => $value) {
                if(str_contains(strtoupper($key), "CPF") || str_contains(strtoupper($key), "CNPJ")){
                    $cpfCnpj = $value;
                } 

                if(str_contains(strtoupper($key), "NOME FANTASIA")){
                    $fantasia = $value;
                } else if(str_contains(strtoupper($key), "NOME") || str_contains(strtoupper($key), "RAZÃO SOCIAL")){
                    $razaoSocial = $value;
                }
                
                if(str_contains(strtoupper($key), "IE") || str_contains(strtoupper($key), "I.E.")){
                    $ie = $value;
                } 

                if(str_contains(strtoupper($key), "CEP")){
                    $cep = $value;
                } 

                if(str_contains(strtoupper($key), "ENDERECO") || str_contains(strtoupper($key), "ENDEREÇO")){
                    $endereco = $value;
                } 

                if(str_contains(strtoupper($key), "BAIRRO") || str_contains(strtoupper($key), "BAIRO") || str_contains(strtoupper($key), "BARRO")){
                    $bairro = $value;
                } 
                if(str_contains(strtoupper($key), "ESTADO") || str_contains(strtoupper($key), "UF")){
                    $estado = $value;
                } 

                if(str_contains(strtoupper($key), "EMAIL") || str_contains(strtoupper($key), "E-MAIL") || str_contains(strtoupper($key), "E MAIL")){
                    $email = $value;
                } 
            }

            return view::make('formToValidateData', ['cpfCnpj' => $cpfCnpj,
                'razaoSocial' => $razaoSocial,
                'ie' => $ie,
                'endereco' => $endereco,
                'bairro' => $bairro,
                'estado' => $estado,
                'cep' => $cep,
                'email' => $email,
                'contactId' => $message_text->contactId
            ]);
        } catch (Throwable $ex) {
            Log::error("Something went wrong: " . $ex);
            return view::make("Something went wrong: " . $ex);
        }
    }

    /**
     * @throws ConnectionException
     */
    private function getDigisacMessage($url): int|string
    {
        // Token de autenticação
        $token = env('DIGISACTOKEN');

        // Realiza a requisição HTTP GET com o cabeçalho de autorização
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            ])->get("https://camisetasparana.digisac.biz/api/v1/messages/$url");

        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna o conteúdo da resposta
            return $response->body();
        } else {
            // Em caso de falha na requisição, retorna uma mensagem de erro
            return $response->status();
        }
    }

    private function stringToJson($string): false|string
    {
        // Divide a string em linhas
        $lines = explode("\n", $string);

        // Inicializa um array para armazenar os pares chave-valor
        $dataArray = [];

        // Percorre as linhas
        foreach ($lines as $line) {
            // Separa a linha pelo primeiro ':' encontrado
            $parts = explode(':', $line, 2);

            // Remove espaços em branco do início e do final do nome da chave e do valor
            $key = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            // Adiciona o par chave-valor ao array
            $dataArray[$key] = $value;
        }

        // Converte o array associativo para JSON
        return json_encode($dataArray, JSON_UNESCAPED_UNICODE, true);
    }
}
