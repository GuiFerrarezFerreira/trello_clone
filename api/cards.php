<?php
// ===================================
// api/cards.php
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
        if (isset($_GET['id'])) {
            $card_id = str_replace('card-', '', $_GET['id']);
            
            // Check if user has access
            if (!checkCardAccess($db, $card_id, $user_id)) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Acesso negado."
                ));
                exit();
            }
            
            // Get card details
            $query = "SELECT c.*, 
                     GROUP_CONCAT(DISTINCT cl.label) as labels,
                     GROUP_CONCAT(DISTINCT ct.tag) as tags
                     FROM cards c
                     LEFT JOIN card_labels cl ON c.id = cl.card_id
                     LEFT JOIN card_tags ct ON c.id = ct.card_id
                     WHERE c.id = ?
                     GROUP BY c.id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->execute();
            
            $card = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$card) {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Cartão não encontrado."
                ));
                exit();
            }
            
            // Get card members
            $query = "SELECT u.id, u.first_name, u.last_name, u.initials, u.color
                     FROM users u
                     JOIN card_members cm ON u.id = cm.user_id
                     WHERE cm.card_id = ?";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->execute();
            
            $members = array();
            while ($member = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $members[] = array(
                    "id" => "user-" . $member['id'],
                    "name" => $member['first_name'] . " " . $member['last_name'],
                    "initials" => $member['initials'],
                    "color" => $member['color']
                );
            }
            
            echo json_encode(array(
                "success" => true,
                "card" => array(
                    "id" => "card-" . $card['id'],
                    "title" => $card['title'],
                    "description" => $card['description'],
                    "labels" => $card['labels'] ? explode(',', $card['labels']) : array(),
                    "tags" => $card['tags'] ? explode(',', $card['tags']) : array(),
                    "members" => $members,
                    "dueDate" => $card['due_date'],
                    "position" => $card['position']
                )
            ));
        }
        break;
        
    case 'POST':
        // Create new card
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->listId) && !empty($data->title)) {
            $list_id = str_replace('list-', '', $data->listId);
            
            // Check if user has edit access to board
            $query = "SELECT b.id FROM lists l 
                     JOIN boards b ON l.board_id = b.id
                     JOIN board_members bm ON b.id = bm.board_id
                     WHERE l.id = ? AND bm.user_id = ? AND bm.role IN ('admin', 'editor')";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $list_id);
            $stmt->bindParam(2, $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para criar cartões."
                ));
                exit();
            }
            
            // Get next position
            $query = "SELECT MAX(position) as max_pos FROM cards WHERE list_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $list_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $position = $result['max_pos'] ? $result['max_pos'] + 1 : 0;
            
            // Create card
            $query = "INSERT INTO cards (list_id, title, position) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $list_id);
            $stmt->bindParam(2, $data->title);
            $stmt->bindParam(3, $position);
            
            if ($stmt->execute()) {
                $card_id = $db->lastInsertId();
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Cartão criado com sucesso.",
                    "cardId" => "card-" . $card_id
                ));
            }
        }
        break;
        
    case 'PUT':
        if (isset($_GET['id'])) {
            $card_id = str_replace('card-', '', $_GET['id']);
            $data = json_decode(file_get_contents("php://input"));
            
            // Check if user has edit access
            if (!checkCardAccess($db, $card_id, $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para editar cartões."
                ));
                exit();
            }
            
            // Handle move operation
            if (strpos($_SERVER['REQUEST_URI'], '/move') !== false) {
                $list_id = str_replace('list-', '', $data->listId);
                
                $query = "UPDATE cards SET list_id = ?, position = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $list_id);
                $stmt->bindParam(2, $data->position);
                $stmt->bindParam(3, $card_id);
            } else {
                // Update card details
                $updates = array();
                $params = array();
                
                if (isset($data->title)) {
                    $updates[] = "title = ?";
                    $params[] = $data->title;
                }
                
                if (isset($data->description)) {
                    $updates[] = "description = ?";
                    $params[] = $data->description;
                }
                
                if (isset($data->dueDate)) {
                    $updates[] = "due_date = ?";
                    $params[] = $data->dueDate;
                }
                
                if (empty($updates)) {
                    http_response_code(400);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Nenhum dado para atualizar."
                    ));
                    exit();
                }
                
                $params[] = $card_id;
                $query = "UPDATE cards SET " . implode(", ", $updates) . " WHERE id = ?";
                $stmt = $db->prepare($query);
                
                foreach ($params as $i => $param) {
                    $stmt->bindValue($i + 1, $param);
                }
            }
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Cartão atualizado com sucesso."
                ));
            }
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            $card_id = str_replace('card-', '', $_GET['id']);
            
            // Check if user has edit access
            if (!checkCardAccess($db, $card_id, $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para excluir cartões."
                ));
                exit();
            }
            
            $query = "DELETE FROM cards WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Cartão excluído com sucesso."
                ));
            }
        }
        break;
}
?>