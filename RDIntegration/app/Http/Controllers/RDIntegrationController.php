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
        $message_text = $this->getDigisacMessageText($message);
        $message_text_value = json_decode($message_text);

        $text_field = $this->stringToJson($message_text_value->text);
        

        // Retorna os dados em formato JSON
        //return $this->stringToJson($message_text_value->text);
        return view::make('formToValidateData', ['cpfCnpj' => $text_field->CEP,
            'razaoSocial' => 'e',
            'ie' => 'um',
            'cep' => 'teste',
            'endereco' => 'de',
            'bairro' => 'envio',
            'estado' => 'de',
            'email' => 'formulario'
        ]);
    }

    private function getDigisacMessageText($url)
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
        $json = json_encode($dataArray, JSON_UNESCAPED_UNICODE);
        
        return $json;
    }
}
