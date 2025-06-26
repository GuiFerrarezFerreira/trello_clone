<?php
// ===================================
// api/system-stats.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

// Check if user is system admin
$query = "SELECT is_admin FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $user_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result || $result['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(array(
        "success" => false,
        "message" => "Acesso negado. Apenas administradores podem acessar estas estatísticas."
    ));
    exit();
}

// Get statistics
$stats = array();

// Total users
$query = "SELECT COUNT(*) as total FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['totalUsers'] = $result['total'];

// Total boards
$query = "SELECT COUNT(*) as total FROM boards";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['totalBoards'] = $result['total'];

// Total cards
$query = "SELECT COUNT(*) as total FROM cards";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['totalCards'] = $result['total'];

// Active users (last 30 days - based on activities)
$thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
$query = "SELECT COUNT(DISTINCT user_id) as total FROM activities WHERE created_at >= ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $thirtyDaysAgo);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['activeUsers30Days'] = $result['total'];

// New users (last 30 days)
$query = "SELECT COUNT(*) as total FROM users WHERE created_at >= ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $thirtyDaysAgo);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['newUsers30Days'] = $result['total'];

// Most active boards
$query = "SELECT b.id, b.title, b.color, COUNT(a.id) as activity_count
         FROM boards b
         LEFT JOIN activities a ON b.id = a.board_id AND a.created_at >= ?
         GROUP BY b.id
         ORDER BY activity_count DESC
         LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $thirtyDaysAgo);
$stmt->execute();

$mostActiveBoards = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $mostActiveBoards[] = array(
        "id" => $row['id'],
        "title" => $row['title'],
        "color" => $row['color'],
        "activityCount" => $row['activity_count']
    );
}
$stats['mostActiveBoards'] = $mostActiveBoards;

// Users by role distribution
$query = "SELECT bm.role, COUNT(DISTINCT bm.user_id) as count
         FROM board_members bm
         GROUP BY bm.role";
$stmt = $db->prepare($query);
$stmt->execute();

$roleDistribution = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $roleDistribution[$row['role']] = $row['count'];
}
$stats['roleDistribution'] = $roleDistribution;

// Average boards per user
$query = "SELECT AVG(board_count) as avg_boards FROM (
         SELECT COUNT(*) as board_count
         FROM board_members
         GROUP BY user_id
         ) as user_boards";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['avgBoardsPerUser'] = round($result['avg_boards'], 1);

// Average cards per board
$query = "SELECT AVG(card_count) as avg_cards FROM (
         SELECT b.id, COUNT(c.id) as card_count
         FROM boards b
         LEFT JOIN lists l ON b.id = l.board_id
         LEFT JOIN cards c ON l.id = c.list_id
         GROUP BY b.id
         ) as board_cards";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['avgCardsPerBoard'] = round($result['avg_cards'], 1);

echo json_encode(array(
    "success" => true,
    "stats" => $stats
));
?>