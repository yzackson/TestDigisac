<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RDIntegrationController extends Controller
{
    public function Index(Request $request) {
        // Obtém o parâmetro "message" da URL GET
        $message = $request->input('message');

        // Chama a função getDigisacMessage com o parâmetro recebido
        $response = $this->getDigisacMessage($message);
        $jObj = json_decode($response);

        

        // Retorna os dados em formato JSON
        return $this->stringToJson($jObj->text);
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




    /*
    private function createOrganizationRD($body)
    {
        // Realiza a requisição HTTP POST para criar uma organização no RD Station
        $response = Http::post('https://crm.rdstation.com/api/v1/organizations', json_decode($body, true));

        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna o conteúdo da resposta
            return $response->body();
        } else {
            // Em caso de falha na requisição, retorna uma mensagem de erro
            return response()->json(['error' => 'Erro ao criar a organização no RD Station.'], $response->status());
        }
    }


*/

    private function stringToJson($string) {
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
