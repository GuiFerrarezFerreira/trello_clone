<?php
// ===================================
// api/board-tags.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Helper function to check board access
function checkBoardAccess($db, $board_id, $user_id, $min_role = 'reader') {
    $query = "SELECT role FROM board_members WHERE board_id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $board_id);
    $stmt->bindParam(2, $user_id);
    $stmt->execute();
    
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) return false;
    
    if ($min_role === 'editor') {
        return $member['role'] === 'admin' || $member['role'] === 'editor';
    }
    
    return true;
}

switch($method) {
    case 'GET':
        // Get tags for a board
        if (isset($_GET['boardId'])) {
            $board_id = str_replace('board-', '', $_GET['boardId']);
            
            // Check if user has access
            if (!checkBoardAccess($db, $board_id, $user_id)) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Acesso negado."
                ));
                exit();
            }
            
            $query = "SELECT * FROM board_tags WHERE board_id = ? ORDER BY name";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->execute();
            
            $tags = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tags[] = array(
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "color" => $row['color']
                );
            }
            
            echo json_encode(array(
                "success" => true,
                "tags" => $tags
            ));
        }
        break;
        
    case 'POST':
        // Create new tag configuration
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->boardId) && !empty($data->name) && !empty($data->color)) {
            $board_id = str_replace('board-', '', $data->boardId);
            
            // Check if user has edit access
            if (!checkBoardAccess($db, $board_id, $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para criar tags."
                ));
                exit();
            }
            
            // Check if tag already exists
            $check_query = "SELECT id FROM board_tags WHERE board_id = ? AND name = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(1, $board_id);
            $check_stmt->bindParam(2, $data->name);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                // Update existing tag color
                $update_query = "UPDATE board_tags SET color = ? WHERE board_id = ? AND name = ?";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(1, $data->color);
                $update_stmt->bindParam(2, $board_id);
                $update_stmt->bindParam(3, $data->name);
                
                if ($update_stmt->execute()) {
                    echo json_encode(array(
                        "success" => true,
                        "message" => "Cor da tag atualizada."
                    ));
                }
            } else {
                // Create new tag
                $query = "INSERT INTO board_tags (board_id, name, color) VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $board_id);
                $stmt->bindParam(2, $data->name);
                $stmt->bindParam(3, $data->color);
                
                if ($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode(array(
                        "success" => true,
                        "message" => "Tag criada com sucesso.",
                        "tagId" => $db->lastInsertId()
                    ));
                }
            }
        }
        break;
        
    case 'PUT':
        // Update tag
        if (isset($_GET['id'])) {
            $tag_id = $_GET['id'];
            $data = json_decode(file_get_contents("php://input"));
            
            // Get board_id from tag
            $query = "SELECT board_id FROM board_tags WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $tag_id);
            $stmt->execute();
            $tag = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tag) {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Tag não encontrada."
                ));
                exit();
            }
            
            // Check if user has edit access
            if (!checkBoardAccess($db, $tag['board_id'], $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para editar tags."
                ));
                exit();
            }
            
            $query = "UPDATE board_tags SET color = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->color);
            $stmt->bindParam(2, $tag_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Tag atualizada com sucesso."
                ));
            }
        }
        break;
        
    case 'DELETE':
        // Delete tag
        if (isset($_GET['id'])) {
            $tag_id = $_GET['id'];
            
            // Get board_id from tag
            $query = "SELECT board_id, name FROM board_tags WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $tag_id);
            $stmt->execute();
            $tag = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tag) {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Tag não encontrada."
                ));
                exit();
            }
            
            // Check if user has edit access
            if (!checkBoardAccess($db, $tag['board_id'], $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para excluir tags."
                ));
                exit();
            }
            
            // Delete tag configuration (card_tags entries remain)
            $query = "DELETE FROM board_tags WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $tag_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Tag excluída com sucesso."
                ));
            }
        }
        break;
}
?>