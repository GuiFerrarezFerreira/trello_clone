<?php
// ===================================
// api/toggle-board-permission.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

// Check if user is system admin
$query = "SELECT is_admin FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $user_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result || $result['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(array(
        "success" => false,
        "message" => "Acesso negado. Apenas administradores podem gerenciar permissões."
    ));
    exit();
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->userId) && isset($data->canCreateBoards)) {
    $target_user_id = str_replace('user-', '', $data->userId);
    
    // Update permission
    $query = "UPDATE users SET can_create_boards = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $data->canCreateBoards, PDO::PARAM_BOOL);
    $stmt->bindParam(2, $target_user_id);
    
    if ($stmt->execute()) {
        echo json_encode(array(
            "success" => true,
            "message" => $data->canCreateBoards ? 
                "Permissão para criar quadros concedida." : 
                "Permissão para criar quadros removida."
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Erro ao atualizar permissão."
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Dados inválidos."
    ));
}
?>