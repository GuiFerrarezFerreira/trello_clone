<?php
// ===================================
// api/user-management.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Check if user is system admin
function isSystemAdmin($db, $user_id) {
    $query = "SELECT is_admin FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $user_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result && $result['is_admin'] == 1;
}

switch($method) {
    case 'GET':
        // Check if user is admin
        if (!isSystemAdmin($db, $user_id)) {
            http_response_code(403);
            echo json_encode(array(
                "success" => false,
                "message" => "Acesso negado. Apenas administradores podem acessar esta área."
            ));
            exit();
        }
        
        // Get all users with their board memberships
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $offset = ($page - 1) * $limit;
        
        // Build query
        $countQuery = "SELECT COUNT(*) as total FROM users";
        $query = "SELECT u.*, 
                 (SELECT COUNT(DISTINCT bm.board_id) FROM board_members bm WHERE bm.user_id = u.id) as boards_count,
                 (SELECT COUNT(*) FROM boards b WHERE b.created_by = u.id) as owned_boards_count
                 FROM users u";
        
        $params = array();
        if ($search) {
            $searchCondition = " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $query .= $searchCondition;
            $countQuery .= $searchCondition;
            $searchParam = "%{$search}%";
            $params = array($searchParam, $searchParam, $searchParam);
        }
        
        $query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        
        // Get total count
        $stmt = $db->prepare($countQuery);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $totalResult['total'];
        
        // Get users
        $stmt = $db->prepare($query);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $users = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Get user's board memberships
            $boardsQuery = "SELECT b.id, b.title, b.color, bm.role 
                           FROM boards b
                           JOIN board_members bm ON b.id = bm.board_id
                           WHERE bm.user_id = ?
                           ORDER BY b.created_at DESC
                           LIMIT 5";
            
            $boardsStmt = $db->prepare($boardsQuery);
            $boardsStmt->bindParam(1, $row['id']);
            $boardsStmt->execute();
            
            $boards = array();
            while ($board = $boardsStmt->fetch(PDO::FETCH_ASSOC)) {
                $boards[] = array(
                    "id" => $board['id'],
                    "title" => $board['title'],
                    "color" => $board['color'],
                    "role" => $board['role']
                );
            }
            
            $users[] = array(
                "id" => "user-" . $row['id'],
                "firstName" => $row['first_name'],
                "lastName" => $row['last_name'],
                "email" => $row['email'],
                "initials" => $row['initials'],
                "color" => $row['color'],
                "boardsCount" => $row['boards_count'],
                "ownedBoardsCount" => $row['owned_boards_count'],
                "boards" => $boards,
                "createdAt" => $row['created_at'],
                "isSystemAdmin" => $row['is_admin'] == 1,
                "canCreateBoards" => $row['can_create_boards'] == 1
            );
        }
        
        echo json_encode(array(
            "success" => true,
            "users" => $users,
            "pagination" => array(
                "total" => $total,
                "page" => $page,
                "limit" => $limit,
                "totalPages" => ceil($total / $limit)
            )
        ));
        break;
        
    case 'PUT':
        // Update user
        if (!isSystemAdmin($db, $user_id)) {
            http_response_code(403);
            echo json_encode(array(
                "success" => false,
                "message" => "Acesso negado."
            ));
            exit();
        }
        
        if (isset($_GET['id'])) {
            $target_user_id = str_replace('user-', '', $_GET['id']);
            $data = json_decode(file_get_contents("php://input"));
            
            $updates = array();
            $params = array();
            
            if (isset($data->firstName)) {
                $updates[] = "first_name = ?";
                $params[] = $data->firstName;
            }
            
            if (isset($data->lastName)) {
                $updates[] = "last_name = ?";
                $params[] = $data->lastName;
            }
            
            if (isset($data->email)) {
                // Check if email already exists for another user
                $checkQuery = "SELECT id FROM users WHERE email = ? AND id != ?";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(1, $data->email);
                $checkStmt->bindParam(2, $target_user_id);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() > 0) {
                    http_response_code(409);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Este e-mail já está em uso."
                    ));
                    exit();
                }
                
                $updates[] = "email = ?";
                $params[] = $data->email;
            }
            
            if (isset($data->password) && !empty($data->password)) {
                $updates[] = "password = ?";
                $params[] = password_hash($data->password, PASSWORD_BCRYPT);
            }
            
            // Update admin status (only if current user is admin and not editing themselves)
            if (isset($data->isAdmin) && $target_user_id != $user_id) {
                $updates[] = "is_admin = ?";
                $params[] = $data->isAdmin ? 1 : 0;
            }
            
            // Update board creation permission
            if (isset($data->canCreateBoards)) {
                $updates[] = "can_create_boards = ?";
                $params[] = $data->canCreateBoards ? 1 : 0;
            }
            
            // Update initials if name changed
            if (isset($data->firstName) || isset($data->lastName)) {
                // Get current name if not provided
                if (!isset($data->firstName) || !isset($data->lastName)) {
                    $getNameQuery = "SELECT first_name, last_name FROM users WHERE id = ?";
                    $getNameStmt = $db->prepare($getNameQuery);
                    $getNameStmt->bindParam(1, $target_user_id);
                    $getNameStmt->execute();
                    $currentName = $getNameStmt->fetch(PDO::FETCH_ASSOC);
                }
                
                $firstInitial = isset($data->firstName) ? substr($data->firstName, 0, 1) : substr($currentName['first_name'], 0, 1);
                $lastInitial = isset($data->lastName) ? substr($data->lastName, 0, 1) : substr($currentName['last_name'], 0, 1);
                $updates[] = "initials = ?";
                $params[] = strtoupper($firstInitial . $lastInitial);
            }
            
            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Nenhum dado para atualizar."
                ));
                exit();
            }
            
            $params[] = $target_user_id;
            $query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $db->prepare($query);
            
            foreach ($params as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Usuário atualizado com sucesso."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao atualizar usuário."
                ));
            }
        }
        break;
        
    case 'DELETE':
        // Delete user
        if (!isSystemAdmin($db, $user_id)) {
            http_response_code(403);
            echo json_encode(array(
                "success" => false,
                "message" => "Acesso negado."
            ));
            exit();
        }
        
        if (isset($_GET['id'])) {
            $target_user_id = str_replace('user-', '', $_GET['id']);
            
            // Prevent self deletion
            if ($target_user_id == $user_id) {
                http_response_code(400);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Você não pode excluir sua própria conta."
                ));
                exit();
            }
            
            // Check if user owns any boards
            $checkQuery = "SELECT COUNT(*) as count FROM boards WHERE created_by = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(1, $target_user_id);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                http_response_code(400);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Este usuário possui quadros. Transfira ou exclua os quadros antes de excluir o usuário."
                ));
                exit();
            }
            
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $target_user_id);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Usuário excluído com sucesso."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao excluir usuário."
                ));
            }
        }
        break;
}
?>