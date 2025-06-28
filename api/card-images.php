<?php
// ===================================
// api/card-images.php
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
        // Get images for a card
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
            
            $query = "SELECT ci.*, u.first_name, u.last_name, u.initials, u.color
                     FROM card_images ci
                     JOIN users u ON ci.uploaded_by = u.id
                     WHERE ci.card_id = ?
                     ORDER BY ci.uploaded_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->execute();
            
            $images = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $images[] = array(
                    "id" => $row['id'],
                    "filename" => $row['filename'],
                    "url" => $row['file_path'],
                    "size" => $row['file_size'],
                    "uploadedBy" => array(
                        "name" => $row['first_name'] . " " . $row['last_name'],
                        "initials" => $row['initials'],
                        "color" => $row['color']
                    ),
                    "uploadedAt" => $row['uploaded_at']
                );
            }
            
            echo json_encode(array(
                "success" => true,
                "images" => $images
            ));
        }
        break;
        
    case 'POST':
        // Upload new image
        if (!isset($_FILES['image']) || !isset($_POST['cardId'])) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Dados incompletos."
            ));
            exit();
        }
        
        $card_id = str_replace('card-', '', $_POST['cardId']);
        
        // Check if user has edit access
        if (!checkCardAccess($db, $card_id, $user_id, 'editor')) {
            http_response_code(403);
            echo json_encode(array(
                "success" => false,
                "message" => "Sem permissão para adicionar imagens."
            ));
            exit();
        }
        
        $file = $_FILES['image'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP."
            ));
            exit();
        }
        
        // Check file size (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Arquivo muito grande. Tamanho máximo: 5MB."
            ));
            exit();
        }
        
        // Create upload directory if not exists
        $upload_dir = __DIR__ . '/../uploads/cards/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('card_' . $card_id . '_') . '.' . $extension;
        $file_path = $upload_dir . $filename;
        $url_path = '/trello_clone/uploads/cards/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Save to database
            $query = "INSERT INTO card_images (card_id, filename, file_path, file_size, uploaded_by) 
                     VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $card_id);
            $stmt->bindParam(2, $file['name']);
            $stmt->bindParam(3, $url_path);
            $stmt->bindParam(4, $file['size']);
            $stmt->bindParam(5, $user_id);
            
            if ($stmt->execute()) {
                // Log activity
                $activity_query = "INSERT INTO activities (board_id, user_id, action, description) 
                                 SELECT l.board_id, ?, 'image_added', ?
                                 FROM cards c
                                 JOIN lists l ON c.list_id = l.id
                                 WHERE c.id = ?";
                
                $activity_stmt = $db->prepare($activity_query);
                $activity_desc = "adicionou uma imagem ao cartão";
                $activity_stmt->bindParam(1, $user_id);
                $activity_stmt->bindParam(2, $activity_desc);
                $activity_stmt->bindParam(3, $card_id);
                $activity_stmt->execute();
                
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Imagem enviada com sucesso.",
                    "image" => array(
                        "id" => $db->lastInsertId(),
                        "filename" => $file['name'],
                        "url" => $url_path
                    )
                ));
            } else {
                // Delete uploaded file if database save fails
                unlink($file_path);
                
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao salvar imagem no banco de dados."
                ));
            }
        } else {
            http_response_code(500);
            echo json_encode(array(
                "success" => false,
                "message" => "Erro ao fazer upload da imagem."
            ));
        }
        break;
        
    case 'DELETE':
        // Delete image
        if (isset($_GET['id'])) {
            $image_id = $_GET['id'];
            
            // Get image info
            $query = "SELECT ci.*, c.id as card_id 
                     FROM card_images ci
                     JOIN cards c ON ci.card_id = c.id
                     WHERE ci.id = ?";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $image_id);
            $stmt->execute();
            
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$image) {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Imagem não encontrada."
                ));
                exit();
            }
            
            // Check if user has edit access
            if (!checkCardAccess($db, $image['card_id'], $user_id, 'editor')) {
                http_response_code(403);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Sem permissão para excluir imagens."
                ));
                exit();
            }
            
            // Delete from database
            $query = "DELETE FROM card_images WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $image_id);
            
            if ($stmt->execute()) {
                // Delete file
                $file_path = __DIR__ . '/..' . $image['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                // Log activity
                $activity_query = "INSERT INTO activities (board_id, user_id, action, description) 
                                 SELECT l.board_id, ?, 'image_removed', ?
                                 FROM cards c
                                 JOIN lists l ON c.list_id = l.id
                                 WHERE c.id = ?";
                
                $activity_stmt = $db->prepare($activity_query);
                $activity_desc = "removeu uma imagem do cartão";
                $activity_stmt->bindParam(1, $user_id);
                $activity_stmt->bindParam(2, $activity_desc);
                $activity_stmt->bindParam(3, $image['card_id']);
                $activity_stmt->execute();
                
                echo json_encode(array(
                    "success" => true,
                    "message" => "Imagem excluída com sucesso."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Erro ao excluir imagem."
                ));
            }
        }
        break;
}
?>