<?php
// ===================================
// api/middleware/auth.php
// ===================================
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../utils/token.php';

function authenticate() {
    $headers = getallheaders();
    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    if (!$auth_header) {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Token não fornecido."
        ));
        exit();
    }
    
    $token = str_replace('Bearer ', '', $auth_header);
    $user_id = Token::validate($token);
    
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Token inválido ou expirado."
        ));
        exit();
    }
    
    return $user_id;
}