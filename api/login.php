<?php
// ===================================
// api/login.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'models/User.php';
include_once 'utils/token.php';

header("Content-Type: application/json; charset=UTF-8");

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->email) &&
    !empty($data->password)
) {
    // Initialize database
    $database = new Database();
    $db = $database->getConnection();

    // Initialize user object
    $user = new User($db);

    // Set user property values
    $user->email = $data->email;
    $email_exists = $user->emailExists();

    // Check if email exists and password is correct
    if($email_exists && $data->password == $user->password) {
        
        // Generate token
        $token = Token::generate($user->id);
        
        // Set response code - 200 OK
        http_response_code(200);

        // Response
        echo json_encode(array(
            "success" => true,
            "message" => "Login realizado com sucesso.",
            "token" => $token,
            "userId" => "user-" . $user->id,
            "userName" => $user->first_name . " " . $user->last_name,
            "userInitials" => $user->initials,
            "userColor" => $user->color
        ));
    }
    else {
        // Set response code - 401 Unauthorized
        http_response_code(401);

        // Tell the user login failed
        echo json_encode(array(
            "success" => false,
            "message" => "E-mail ou senha incorretos."
        ));
    }
}
else {
    // Set response code - 400 bad request
    http_response_code(400);

    // Tell the user data is incomplete
    echo json_encode(array(
        "success" => false,
        "message" => "Por favor, preencha todos os campos."
    ));
}
?>