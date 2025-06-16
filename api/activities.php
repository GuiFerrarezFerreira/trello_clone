<?php
// ===================================
// api/activities.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['boardId'])) {
    $board_id = str_replace('board-', '', $_GET['boardId']);
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    
    // Check if user has access to board
    $query = "SELECT role FROM board_members WHERE board_id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $board_id);
    $stmt->bindParam(2, $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        echo json_encode(array(
            "success" => false,
            "message" => "Acesso negado."
        ));
        exit();
    }
    
    // Get activities
    $query = "SELECT a.*, u.first_name, u.last_name, u.initials, u.color
             FROM activities a
             JOIN users u ON a.user_id = u.id
             WHERE a.board_id = ?
             ORDER BY a.created_at DESC
             LIMIT ?";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $board_id);
    $stmt->bindParam(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $activities = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = array(
            "id" => $row['id'],
            "action" => $row['action'],
            "description" => $row['description'],
            "user" => array(
                "name" => $row['first_name'] . " " . $row['last_name'],
                "initials" => $row['initials'],
                "color" => $row['color']
            ),
            "createdAt" => $row['created_at']
        );
    }
    
    echo json_encode(array(
        "success" => true,
        "activities" => $activities
    ));
}
?>