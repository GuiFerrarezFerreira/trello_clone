<?php
// ===================================
// api/boards.php
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
    case 'GET':
        if (isset($_GET['id'])) {
            // Get specific board with all its data
            $board_id = $_GET['id'];
            
            // Check if user has access
            $query = "SELECT b.*, bm.role 
                     FROM boards b
                     JOIN board_members bm ON b.id = bm.board_id
                     WHERE b.id = ? AND bm.user_id = ?";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->bindParam(2, $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Acesso negado a este quadro."
                ));
                exit();
            }
            
            $board = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get board members
            $query = "SELECT u.id, u.first_name, u.last_name, u.email, u.initials, u.color, bm.role
                     FROM users u
                     JOIN board_members bm ON u.id = bm.user_id
                     WHERE bm.board_id = ?";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->execute();
            
            $members = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $members[] = array(
                    "userId" => "user-" . $row['id'],
                    "name" => $row['first_name'] . " " . $row['last_name'],
                    "email" => $row['email'],
                    "initials" => $row['initials'],
                    "color" => $row['color'],
                    "role" => $row['role']
                );
            }
            
            // Get lists with cards
            $query = "SELECT * FROM lists WHERE board_id = ? ORDER BY position";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            $stmt->execute();
            
            $lists = array();
            while ($list = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Get cards for this list
                $card_query = "SELECT c.*, 
                              GROUP_CONCAT(DISTINCT cl.label) as labels,
                              GROUP_CONCAT(DISTINCT ct.tag) as tags,
                              (SELECT COUNT(*) FROM card_images WHERE card_id = c.id) as image_count
                              FROM cards c
                              LEFT JOIN card_labels cl ON c.id = cl.card_id
                              LEFT JOIN card_tags ct ON c.id = ct.card_id
                              WHERE c.list_id = ?
                              GROUP BY c.id
                              ORDER BY c.position";
                
                $card_stmt = $db->prepare($card_query);
                $card_stmt->bindParam(1, $list['id']);
                $card_stmt->execute();
                
                $cards = array();
                while ($card = $card_stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Get card members with full information
                    $member_query = "SELECT u.id, u.first_name, u.last_name, u.initials, u.color 
                                   FROM users u
                                   JOIN card_members cm ON u.id = cm.user_id
                                   WHERE cm.card_id = ?";
                    $member_stmt = $db->prepare($member_query);
                    $member_stmt->bindParam(1, $card['id']);
                    $member_stmt->execute();
                    
                    $card_members = array();
                    while ($member = $member_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $card_members[] = array(
                            "id" => "user-" . $member['id'],
                            "name" => $member['first_name'] . " " . $member['last_name'],
                            "initials" => $member['initials'],
                            "color" => $member['color']
                        );
                    }
                    
                    $cards[] = array(
                        "id" => "card-" . $card['id'],
                        "title" => $card['title'],
                        "description" => $card['description'],
                        "labels" => $card['labels'] ? explode(',', $card['labels']) : array(),
                        "tags" => $card['tags'] ? explode(',', $card['tags']) : array(),
                        "members" => $card_members, // Agora com informações completas
                        "dueDate" => $card['due_date'],
                        "position" => $card['position']
                    );
                }
                
                $lists[] = array(
                    "id" => "list-" . $list['id'],
                    "title" => $list['title'],
                    "position" => $list['position'],
                    "cards" => $cards
                );
            }
            
            echo json_encode(array(
                "success" => true,
                "board" => array(
                    "id" => $board['id'],
                    "title" => $board['title'],
                    "color" => $board['color'],
                    "role" => $board['role'],
                    "members" => $members,
                    "lists" => $lists
                )
            ));
            
        } else {
            // Get all boards for user
            $query = "SELECT b.*, bm.role, 
                     (SELECT COUNT(*) FROM lists WHERE board_id = b.id) as list_count,
                     (SELECT COUNT(*) FROM cards c JOIN lists l ON c.list_id = l.id WHERE l.board_id = b.id) as card_count
                     FROM boards b
                     JOIN board_members bm ON b.id = bm.board_id
                     WHERE bm.user_id = ?
                     ORDER BY b.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->execute();
            
            $boards = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $boards[] = array(
                    "id" => $row['id'],
                    "title" => $row['title'],
                    "color" => $row['color'],
                    "role" => $row['role'],
                    "listCount" => $row['list_count'],
                    "cardCount" => $row['card_count']
                );
            }
            
            echo json_encode(array(
                "success" => true,
                "boards" => $boards
            ));
        }
        break;
        
    case 'POST':
        // Create new board
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->title) && !empty($data->color)) {
            // Check if user has permission to create boards
            $checkPermQuery = "SELECT can_create_boards FROM users WHERE id = ?";
            $checkPermStmt = $db->prepare($checkPermQuery);
            $checkPermStmt->bindParam(1, $user_id);
            $checkPermStmt->execute();
            $userPerm = $checkPermStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userPerm || $userPerm['can_create_boards'] != 1) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Você não tem permissão para criar quadros. Entre em contato com o administrador."
                ));
                exit();
            }
            
            $db->beginTransaction();
            
            try {
                // Create board
                $query = "INSERT INTO boards (title, color, created_by) VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $data->title);
                $stmt->bindParam(2, $data->color);
                $stmt->bindParam(3, $user_id);
                $stmt->execute();
                
                $board_id = $db->lastInsertId();
                
                // Add creator as admin
                $query = "INSERT INTO board_members (board_id, user_id, role) VALUES (?, ?, 'admin')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $board_id);
                $stmt->bindParam(2, $user_id);
                $stmt->execute();
                
                $db->commit();
                
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Quadro criado com sucesso.",
                    "boardId" => $board_id
                ));
            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao criar quadro."
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
        // Update board
        if (isset($_GET['id'])) {
            $board_id = $_GET['id'];
            $data = json_decode(file_get_contents("php://input"));
            
            // Check if user is admin
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
                    "message" => "Apenas administradores podem editar o quadro."
                ));
                exit();
            }
            
            $query = "UPDATE boards SET title = ?, color = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->title);
            $stmt->bindParam(2, $data->color);
            $stmt->bindParam(3, $board_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Quadro atualizado com sucesso."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao atualizar quadro."
                ));
            }
        }
        break;
        
    case 'DELETE':
        // Delete board
        if (isset($_GET['id'])) {
            $board_id = $_GET['id'];
            
            // Check if user is admin
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
                    "message" => "Apenas administradores podem excluir o quadro."
                ));
                exit();
            }
            
            $query = "DELETE FROM boards WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $board_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Quadro excluído com sucesso."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao excluir quadro."
                ));
            }
        }
        break;
}
?>