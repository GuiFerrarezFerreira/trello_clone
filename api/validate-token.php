<?php
// ===================================
// api/validate-token.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'models/User.php';
include_once 'utils/token.php';

header("Content-Type: application/json; charset=UTF-8");

// Get headers
$headers = getallheaders();
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if($auth_header) {
    // Extract token
    $token = str_replace('Bearer ', '', $auth_header);
    
    // Validate token
    $user_id = Token::validate($token);
    
    if($user_id) {
        // Token is valid, get user info
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, first_name, last_name, email, initials, color 
                  FROM users WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "user" => array(
                    "id" => "user-" . $row['id'],
                    "firstName" => $row['first_name'],
                    "lastName" => $row['last_name'],
                    "email" => $row['email'],
                    "initials" => $row['initials'],
                    "color" => $row['color']
                )
            ));
        } else {
            http_response_code(404);
            echo json_encode(array(
                "success" => false,
                "message" => "Usuário não encontrado."
            ));
        }
    } else {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Token inválido ou expirado."
        ));
    }
} else {
    http_response_code(401);
    echo json_encode(array(
        "success" => false,
        "message" => "Token não fornecido."
    ));
}
?>