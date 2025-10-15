<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once "../data/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['action']) || !isset($data['user_id'])) {
    echo json_encode(["ok" => false, "message" => "Missing parameters"]);
    exit;
}

$action = $data['action'];
$user_id = intval($data['user_id']);

switch ($action) {
    case 'delete':
        $query = "DELETE FROM users WHERE id = ?";
        break;
    case 'promote':
        $query = "UPDATE users SET fk_user_type = 2 WHERE id = ?";
        break;
    case 'demote':
        $query = "UPDATE users SET fk_user_type = 3 WHERE id = ?";
        break;
    default:
        echo json_encode(["ok" => false, "message" => "Invalid action"]);
        exit;
}

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["ok" => true]);
    } else {
        echo json_encode(["ok" => false, "message" => "No rows affected"]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["ok" => false, "message" => $e->getMessage()]);
}
?>
