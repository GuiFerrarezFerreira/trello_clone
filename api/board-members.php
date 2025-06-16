<?php
// ===================================
// api/board-members.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        // Add member to board
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->boardId) && !empty($data->userId) && !empty($data->role)) {
            $board_id = str_replace('board-', '', $data->boardId);
            $new_user_id = str_replace('user-', '', $data->userId);
            
            // Check if current user is admin
            $query = "SELECT role FROM board_members WHERE board_id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->bindParam(2, $user_id);
            $stmt->execute();
            
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member || $member['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Apenas administradores podem adicionar membros."
                ));
                exit();
            }
            
            // Check if user already member
            $query = "SELECT * FROM board_members WHERE board_id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->bindParam(2, $new_user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Usuário já é membro do quadro."
                ));
                exit();
            }
            
            // Add member
            $query = "INSERT INTO board_members (board_id, user_id, role) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->bindParam(2, $new_user_id);
            $stmt->bindParam(3, $data->role);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Membro adicionado com sucesso."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao adicionar membro."
                ));
            }
        }
        break;
        
    case 'PUT':
        // Update member role
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->boardId) && !empty($data->userId) && !empty($data->role)) {
            $board_id = str_replace('board-', '', $data->boardId);
            $target_user_id = str_replace('user-', '', $data->userId);
            
            // Check if current user is admin
            $query = "SELECT role FROM board_members WHERE board_id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->bindParam(2, $user_id);
            $stmt->execute();
            
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member || $member['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Apenas administradores podem alterar roles."
                ));
                exit();
            }
            
            // Prevent self demotion
            if ($target_user_id == $user_id) {
                http_response_code(400);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Você não pode alterar seu próprio role."
                ));
                exit();
            }
            
            $query = "UPDATE board_members SET role = ? WHERE board_id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->role);
            $stmt->bindParam(2, $board_id);
            $stmt->bindParam(3, $target_user_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Role atualizado com sucesso."
                ));
            }
        }
        break;
        
    case 'DELETE':
        // Remove member from board
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->boardId) && !empty($data->userId)) {
            $board_id = str_replace('board-', '', $data->boardId);
            $target_user_id = str_replace('user-', '', $data->userId);
            
            // Check if current user is admin
            $query = "SELECT role FROM board_members WHERE board_id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->bindParam(2, $user_id);
            $stmt->execute();
            
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member || $member['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Apenas administradores podem remover membros."
                ));
                exit();
            }
            
            // Prevent self removal
            if ($target_user_id == $user_id) {
                http_response_code(400);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Você não pode remover a si mesmo."
                ));
                exit();
            }
            
            $query = "DELETE FROM board_members WHERE board_id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->bindParam(2, $target_user_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Membro removido com sucesso."
                ));
            }
        }
        break;
}
?>