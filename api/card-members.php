<?php
// ===================================
// api/card-members.php
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
        // Add member to card
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->cardId) && !empty($data->userId)) {
            $card_id = str_replace('card-', '', $data->cardId);
            $member_id = str_replace('user-', '', $data->userId);
            
            // Check if user has edit access to the board
            $query = "SELECT b.id FROM cards c
                     JOIN lists l ON c.list_id = l.id
                     JOIN boards b ON l.board_id = b.id
                     JOIN board_members bm ON b.id = bm.board_id
                     WHERE c.id = ? AND bm.user_id = ? AND bm.role IN ('admin', 'editor')";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para adicionar membros."
                ));
                exit();
            }
            
            // Check if member is already assigned
            $query = "SELECT * FROM card_members WHERE card_id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $member_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Membro já atribuído ao cartão."
                ));
                exit();
            }
            
            // Add member
            $query = "INSERT INTO card_members (card_id, user_id) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $member_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Membro adicionado ao cartão."
                ));
            }
        }
        break;
        
    case 'DELETE':
        // Remove member from card
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->cardId) && !empty($data->userId)) {
            $card_id = str_replace('card-', '', $data->cardId);
            $member_id = str_replace('user-', '', $data->userId);
            
            // Check if user has edit access
            $query = "SELECT b.id FROM cards c
                     JOIN lists l ON c.list_id = l.id
                     JOIN boards b ON l.board_id = b.id
                     JOIN board_members bm ON b.id = bm.board_id
                     WHERE c.id = ? AND bm.user_id = ? AND bm.role IN ('admin', 'editor')";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para remover membros."
                ));
                exit();
            }
            
            $query = "DELETE FROM card_members WHERE card_id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $member_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Membro removido do cartão."
                ));
            }
        }
        break;
}
?>