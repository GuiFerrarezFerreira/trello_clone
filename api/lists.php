<?php
// ===================================
// api/lists.php
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
    case 'POST':
        // Create new list
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->boardId) && !empty($data->title)) {
            $board_id = str_replace('board-', '', $data->boardId);
            
            // Check if user has edit access
            if (!checkBoardAccess($db, $board_id, $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para criar listas."
                ));
                exit();
            }
            
            // Get next position
            $query = "SELECT MAX(position) as max_pos FROM lists WHERE board_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $position = $result['max_pos'] ? $result['max_pos'] + 1 : 0;
            
            // Create list
            $query = "INSERT INTO lists (board_id, title, position) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->bindParam(2, $data->title);
            $stmt->bindParam(3, $position);
            
            if ($stmt->execute()) {
                $list_id = $db->lastInsertId();
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Lista criada com sucesso.",
                    "listId" => "list-" . $list_id
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao criar lista."
                ));
            }
        }
        break;
        
    case 'PUT':
        if (isset($_GET['id'])) {
            $list_id = str_replace('list-', '', $_GET['id']);
            $data = json_decode(file_get_contents("php://input"));
            
            // Get board_id from list
            $query = "SELECT board_id FROM lists WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $list_id);
            $stmt->execute();
            $list = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$list) {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Lista não encontrada."
                ));
                exit();
            }
            
            // Check if user has edit access
            if (!checkBoardAccess($db, $list['board_id'], $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para editar listas."
                ));
                exit();
            }
            
            // Handle move operation
            if (strpos($_SERVER['REQUEST_URI'], '/move') !== false) {
                $query = "UPDATE lists SET position = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $data->position);
                $stmt->bindParam(2, $list_id);
            } else {
                // Update title
                $query = "UPDATE lists SET title = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $data->title);
                $stmt->bindParam(2, $list_id);
            }
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Lista atualizada com sucesso."
                ));
            }
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            $list_id = str_replace('list-', '', $_GET['id']);
            
            // Get board_id from list
            $query = "SELECT board_id FROM lists WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $list_id);
            $stmt->execute();
            $list = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$list) {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Lista não encontrada."
                ));
                exit();
            }
            
            // Check if user has edit access
            if (!checkBoardAccess($db, $list['board_id'], $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para excluir listas."
                ));
                exit();
            }
            
            $query = "DELETE FROM lists WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $list_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Lista excluída com sucesso."
                ));
            }
        }
        break;
}
?>