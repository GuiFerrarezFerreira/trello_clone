<?php
// ===================================
// api/card-labels.php
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
        // Add label to card
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->cardId) && !empty($data->label)) {
            $card_id = str_replace('card-', '', $data->cardId);
            
            // Check edit access
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
                    "message" => "Sem permissão."
                ));
                exit();
            }
            
            $query = "INSERT IGNORE INTO card_labels (card_id, label) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $data->label);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Label adicionada."
                ));
            }
        }
        break;
        
    case 'DELETE':
        // Remove label from card
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->cardId) && !empty($data->label)) {
            $card_id = str_replace('card-', '', $data->cardId);
            
            // Check edit access
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
                    "message" => "Sem permissão."
                ));
                exit();
            }
            
            $query = "DELETE FROM card_labels WHERE card_id = ? AND label = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $data->label);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Label removida."
                ));
            }
        }
        break;
}
?>