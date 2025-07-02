<?php
// ===================================
// api/card-cover.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Helper function to check card access
function checkCardAccess($db, $card_id, $user_id, $min_role = 'reader') {
    $query = "SELECT b.id, bm.role 
             FROM cards c
             JOIN lists l ON c.list_id = l.id
             JOIN boards b ON l.board_id = b.id
             JOIN board_members bm ON b.id = bm.board_id
             WHERE c.id = ? AND bm.user_id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $card_id);
    $stmt->bindParam(2, $user_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) return false;
    
    if ($min_role === 'editor') {
        return $result['role'] === 'admin' || $result['role'] === 'editor';
    }
    
    return true;
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->cardId) && isset($data->coverImageId)) {
        $card_id = str_replace('card-', '', $data->cardId);
        $cover_image_id = $data->coverImageId;
        
        // Check if user has edit access
        if (!checkCardAccess($db, $card_id, $user_id, 'editor')) {
            http_response_code(403);
            echo json_encode(array(
                "success" => false,
                "message" => "Sem permissão para editar cartão."
            ));
            exit();
        }
        
        // First, remove current cover status from all images of this card
        $query = "UPDATE card_images SET is_cover = 0 WHERE card_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $card_id);
        $stmt->execute();
        
        // If coverImageId is not null, set the new cover
        if ($cover_image_id !== null) {
            $query = "UPDATE card_images SET is_cover = 1 WHERE id = ? AND card_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $cover_image_id);
            $stmt->bindParam(2, $card_id);
            $stmt->execute();
        }
        
        // Log activity
        $activity_query = "INSERT INTO activities (board_id, user_id, action, description) 
                         SELECT l.board_id, ?, 'cover_changed', ?
                         FROM cards c
                         JOIN lists l ON c.list_id = l.id
                         WHERE c.id = ?";
        
        $activity_stmt = $db->prepare($activity_query);
        $activity_desc = $cover_image_id ? "definiu uma imagem de capa" : "removeu a imagem de capa";
        $activity_stmt->bindParam(1, $user_id);
        $activity_stmt->bindParam(2, $activity_desc);
        $activity_stmt->bindParam(3, $card_id);
        $activity_stmt->execute();
        
        echo json_encode(array(
            "success" => true,
            "message" => "Imagem de capa atualizada com sucesso."
        ));
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Dados incompletos."
        ));
    }
}
?>