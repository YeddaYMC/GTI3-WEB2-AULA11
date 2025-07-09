<?php

/**
 * Verifica se uma chave de API válida foi enviada no cabeçalho da requisição.
 */
function verificarApiKey()
{
    $apiKeyCorreta = "sua-chave-secreta-aqui-12345"; // Chave de API estática para este exemplo.
    $apiKeyEnviada = null;

    // Tenta obter a chave do cabeçalho 'X-API-KEY'
    $headers = getallheaders();
    $apiKeyEnviada = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? null;

    if ($apiKeyEnviada !== $apiKeyCorreta) {
        // Se a chave estiver incorreta ou ausente, retorna erro 401 Unauthorized e interrompe a execução.
        http_response_code(401);
        echo json_encode(["message" => "Acesso não autorizado. Chave de API (X-API-KEY) inválida ou ausente."]);
        exit();
    }
}
