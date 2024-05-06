<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

class RDIntegrationController extends Controller
{
    public function UpdateOrganization(Request $request) {
        // Obtém o ID da mensagem passado no parãmetro "message"
        $message = $request->input('message');

        // Extrai somente o conteúdo da mensagem
        $message = $this->getDigisacMessage($message);
        $message_text = json_decode($message);

        // Retorna os dados em formato JSON
        $json_text = json_decode($this->stringToJson($message_text->text), true);
        return view::make('formToValidateData', ['cpfCnpj' => $json_text['CPF/CNPJ'],
            'razaoSocial' => $json_text['Razão Social'],
            'ie' => $json_text['Inscrição Estadual'],
            'cep' => $json_text['CEP'],
            'endereco' => $json_text['Endereço'],
            'bairro' => $json_text['Bairro'],
            'estado' => $json_text['Estado'],
            'email' => $json_text['Email'],
            'contactId' => $message_text->contactId
        ]);
    }

    private function getDigisacMessage($url)
    {
        // Token de autenticação
        $token = env('DIGISACTOKEN');

        // Realiza a requisição HTTP GET com o cabeçalho de autorização
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            ])->get("https://camisetasparana.digisac.biz/api/v1/messages/{$url}");

        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna o conteúdo da resposta
            return $response->body();
        } else {
            // Em caso de falha na requisição, retorna uma mensagem de erro
            return $response->status();
        }
    }

    private function stringToJson($string) 
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
        $json = json_encode($dataArray, JSON_UNESCAPED_UNICODE, true);
        
        return $json;
    }
}
