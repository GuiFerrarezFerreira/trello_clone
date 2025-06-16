<?php
// ===================================
// api/search.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['boardId']) && isset($_GET['q'])) {
    $board_id = str_replace('board-', '', $_GET['boardId']);
    $search = $_GET['q'];
    
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
    
    // Search cards
    $query = "SELECT c.*, l.title as list_title,
             GROUP_CONCAT(DISTINCT cl.label) as labels,
             GROUP_CONCAT(DISTINCT ct.tag) as tags
             FROM cards c
             JOIN lists l ON c.list_id = l.id
             LEFT JOIN card_labels cl ON c.id = cl.card_id
             LEFT JOIN card_tags ct ON c.id = ct.card_id
             WHERE l.board_id = ? AND (c.title LIKE ? OR c.description LIKE ?)
             GROUP BY c.id
             ORDER BY c.updated_at DESC
             LIMIT 20";
    
    $stmt = $db->prepare($query);
    $search_param = "%{$search}%";
    $stmt->bindParam(1, $board_id);
    $stmt->bindParam(2, $search_param);
    $stmt->bindParam(3, $search_param);
    $stmt->execute();
    
    $cards = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cards[] = array(
            "id" => "card-" . $row['id'],
            "title" => $row['title'],
            "description" => $row['description'],
            "listTitle" => $row['list_title'],
            "labels" => $row['labels'] ? explode(',', $row['labels']) : array(),
            "tags" => $row['tags'] ? explode(',', $row['tags']) : array()
        );
    }
    
    echo json_encode(array(
        "success" => true,
        "cards" => $cards
    ));
}
?>