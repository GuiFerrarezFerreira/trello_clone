<?php
// ===================================
// api/users.php
// ===================================
include_once 'config/cors.php';
include_once 'config/database.php';
include_once 'middleware/auth.php';

header("Content-Type: application/json; charset=UTF-8");

$user_id = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search = $_GET['search'];
    
    $query = "SELECT id, first_name, last_name, email, initials, color 
             FROM users 
             WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
             AND id != ?
             LIMIT 10";
    
    $stmt = $db->prepare($query);
    $search_param = "%{$search}%";
    $stmt->bindParam(1, $search_param);
    $stmt->bindParam(2, $search_param);
    $stmt->bindParam(3, $search_param);
    $stmt->bindParam(4, $user_id);
    $stmt->execute();
    
    $users = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = array(
            "id" => "user-" . $row['id'],
            "name" => $row['first_name'] . " " . $row['last_name'],
            "email" => $row['email'],
            "initials" => $row['initials'],
            "color" => $row['color']
        );
    }
    
    echo json_encode(array(
        "success" => true,
        "users" => $users
    ));
}
?>