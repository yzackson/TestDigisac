<?php

// Função para fazer a requisição HTTP GET
function getRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

// Verifica se o parâmetro 'message' foi passado na URI
if (isset($_GET['message'])) {
    // Obtém o valor do parâmetro 'message'
    $messageID = $_GET['message'];
    
    // Faz a requisição HTTP GET para a outra API com o 'messageID'
    $response = getRequest("https://camisetasparana.digisac.biz/api/v1/messages/{$messageID}");

    /*
    // Verifica se a requisição foi bem-sucedida
    if ($response !== false) {
        // Converte a resposta da outra API para um array associativo
        $data = json_decode($response, true);

        // Cria uma página HTML com os dados recebidos da outra API
        echo '<!DOCTYPE html>
              <html>
              <head>
                  <title>Dados da Mensagem</title>
              </head>
              <body>
                  <h1>Dados da Mensagem</h1>
                  <ul>
                      <li>ID: ' . $data['id'] . '</li>
                      <li>Título: ' . $data['title'] . '</li>
                      <li>Conteúdo: ' . $data['content'] . '</li>
                      <!-- Adicione mais campos conforme necessário -->
                  </ul>
              </body>
              </html>';
    } else {
        // Em caso de falha na requisição, imprime uma mensagem de erro
        echo '<!DOCTYPE html>
              <html>
              <head>
                  <title>Erro</title>
              </head>
              <body>
                  <h1>Erro ao obter os dados do servidor.</h1>
              </body>
              </html>';
    }
    */
    // Verifica se a requisição foi bem-sucedida
    if ($response !== false) {
        // Imprime a resposta da outra API
        echo $response;
    } else {
        // Em caso de falha na requisição, imprime uma mensagem de erro
        echo json_encode(['error' => 'Erro ao obter os dados do servidor.']);
    }
} else {
    // Se o parâmetro 'message' não foi passado, imprime uma mensagem de erro
    echo '<!DOCTYPE html>
          <html>
          <head>
              <title>Erro</title>
          </head>
          <body>
              <h1>Parâmetro "message" não fornecido.</h1>
          </body>
          </html>';
}