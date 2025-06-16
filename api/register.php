<?php
// ===================================
// api/register.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'models/User.php';

header("Content-Type: application/json; charset=UTF-8");

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->firstName) &&
    !empty($data->lastName) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    // Initialize database
    $database = new Database();
    $db = $database->getConnection();

    // Initialize user object
    $user = new User($db);

    // Set user property values
    $user->first_name = $data->firstName;
    $user->last_name = $data->lastName;
    $user->email = $data->email;
    $user->password = $data->password;

    // Check if email already exists
    if($user->emailExists()) {
        // Set response code - 409 Conflict
        http_response_code(409);

        echo json_encode(array(
            "success" => false,
            "message" => "Este e-mail já está cadastrado."
        ));
    }
    else {
        // Create the user
        if($user->create()) {
            // Set response code - 201 created
            http_response_code(201);

            echo json_encode(array(
                "success" => true,
                "message" => "Usuário cadastrado com sucesso.",
                "userId" => "user-" . $user->id
            ));
        }
        else {
            // Set response code - 503 service unavailable
            http_response_code(503);

            echo json_encode(array(
                "success" => false,
                "message" => "Não foi possível cadastrar o usuário. Tente novamente."
            ));
        }
    }
}
else {
    // Set response code - 400 bad request
    http_response_code(400);

    echo json_encode(array(
        "success" => false,
        "message" => "Por favor, preencha todos os campos obrigatórios."
    ));
}
?>