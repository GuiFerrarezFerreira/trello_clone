<?php
// ===================================
// api/card-comments.php
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

switch($method) {
    case 'GET':
        // Get comments for a card
        if (isset($_GET['cardId'])) {
            $card_id = str_replace('card-', '', $_GET['cardId']);
            
            // Check if user has access
            if (!checkCardAccess($db, $card_id, $user_id)) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Acesso negado."
                ));
                exit();
            }
            
            $query = "SELECT cc.*, u.first_name, u.last_name, u.initials, u.color
                     FROM card_comments cc
                     JOIN users u ON cc.user_id = u.id
                     WHERE cc.card_id = ?
                     ORDER BY cc.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->execute();
            
            $comments = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comments[] = array(
                    "id" => $row['id'],
                    "comment" => $row['comment'],
                    "user" => array(
                        "id" => "user-" . $row['user_id'],
                        "name" => $row['first_name'] . " " . $row['last_name'],
                        "initials" => $row['initials'],
                        "color" => $row['color']
                    ),
                    "createdAt" => $row['created_at'],
                    "updatedAt" => $row['updated_at'],
                    "isOwner" => $row['user_id'] == $user_id
                );
            }
            
            echo json_encode(array(
                "success" => true,
                "comments" => $comments
            ));
        }
        break;
        
    case 'POST':
        // Add new comment
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->cardId) && !empty($data->comment)) {
            $card_id = str_replace('card-', '', $data->cardId);
            
            // Check if user has access
            if (!checkCardAccess($db, $card_id, $user_id)) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Acesso negado."
                ));
                exit();
            }
            
            // Insert comment
            $query = "INSERT INTO card_comments (card_id, user_id, comment) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $user_id);
            $stmt->bindParam(3, $data->comment);
            
            if ($stmt->execute()) {
                $comment_id = $db->lastInsertId();
                
                // Log activity
                $activity_query = "INSERT INTO activities (board_id, user_id, action, description) 
                                 SELECT l.board_id, ?, 'comment_added', ?
                                 FROM cards c
                                 JOIN lists l ON c.list_id = l.id
                                 WHERE c.id = ?";
                
                $activity_stmt = $db->prepare($activity_query);
                $activity_desc = "comentou no cartão";
                $activity_stmt->bindParam(1, $user_id);
                $activity_stmt->bindParam(2, $activity_desc);
                $activity_stmt->bindParam(3, $card_id);
                $activity_stmt->execute();
                
                // Return the new comment
                $query = "SELECT cc.*, u.first_name, u.last_name, u.initials, u.color
                         FROM card_comments cc
                         JOIN users u ON cc.user_id = u.id
                         WHERE cc.id = ?";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $comment_id);
                $stmt->execute();
                
                $new_comment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Comentário adicionado com sucesso.",
                    "comment" => array(
                        "id" => $new_comment['id'],
                        "comment" => $new_comment['comment'],
                        "user" => array(
                            "id" => "user-" . $new_comment['user_id'],
                            "name" => $new_comment['first_name'] . " " . $new_comment['last_name'],
                            "initials" => $new_comment['initials'],
                            "color" => $new_comment['color']
                        ),
                        "createdAt" => $new_comment['created_at'],
                        "updatedAt" => $new_comment['updated_at'],
                        "isOwner" => true
                    )
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao adicionar comentário."
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Dados incompletos."
            ));
        }
        break;
        
    case 'PUT':
        // Update comment
        if (isset($_GET['id'])) {
            $comment_id = $_GET['id'];
            $data = json_decode(file_get_contents("php://input"));
            
            // Check if user owns the comment
            $query = "SELECT cc.*, c.id as card_id
                     FROM card_comments cc
                     JOIN cards c ON cc.card_id = c.id
                     WHERE cc.id = ? AND cc.user_id = ?";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $comment_id);
            $stmt->bindParam(2, $user_id);
            $stmt->execute();
            
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$comment) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Você não pode editar este comentário."
                ));
                exit();
            }
            
            // Update comment
            $query = "UPDATE card_comments SET comment = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->comment);
            $stmt->bindParam(2, $comment_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Comentário atualizado com sucesso."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao atualizar comentário."
                ));
            }
        }
        break;
        
    case 'DELETE':
        // Delete comment
        if (isset($_GET['id'])) {
            $comment_id = $_GET['id'];
            
            // Check if user owns the comment or is board admin
            $query = "SELECT cc.*, c.id as card_id, bm.role
                     FROM card_comments cc
                     JOIN cards c ON cc.card_id = c.id
                     JOIN lists l ON c.list_id = l.id
                     JOIN boards b ON l.board_id = b.id
                     JOIN board_members bm ON b.id = bm.board_id AND bm.user_id = ?
                     WHERE cc.id = ?";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $comment_id);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Acesso negado."
                ));
                exit();
            }
            
            // Check if user can delete (owns comment or is admin)
            $query = "SELECT user_id FROM card_comments WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $comment_id);
            $stmt->execute();
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($comment['user_id'] != $user_id && $result['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Você não pode excluir este comentário."
                ));
                exit();
            }
            
            // Delete comment
            $query = "DELETE FROM card_comments WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $comment_id);
            
            if ($stmt->execute()) {
                // Log activity
                $activity_query = "INSERT INTO activities (board_id, user_id, action, description) 
                                 SELECT l.board_id, ?, 'comment_removed', ?
                                 FROM cards c
                                 JOIN lists l ON c.list_id = l.id
                                 WHERE c.id = ?";
                
                $activity_stmt = $db->prepare($activity_query);
                $activity_desc = "removeu um comentário";
                $activity_stmt->bindParam(1, $user_id);
                $activity_stmt->bindParam(2, $activity_desc);
                $activity_stmt->bindParam(3, $result['card_id']);
                $activity_stmt->execute();
                
                echo json_encode(array(
                    "success" => true,
                    "message" => "Comentário excluído com sucesso."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao excluir comentário."
                ));
            }
        }
        break;
}
?>