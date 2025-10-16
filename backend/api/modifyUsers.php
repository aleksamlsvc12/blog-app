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

try {
  if ($action === 'delete') {
    $stmt = $conn->prepare("SELECT profile_img FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $imagePath = null;
    if ($row = $result->fetch_assoc()) {
      $imagePath = $row['profile_img'];
    }
    $stmt->close();

    if ($imagePath && file_exists("../" . $imagePath)) {
      unlink("../" . $imagePath);
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
      echo json_encode(["ok" => true, "message" => "User and profile image deleted"]);
    } else {
      echo json_encode(["ok" => false, "message" => "No rows affected"]);
    }

    $stmt->close();
    $conn->close();
    exit;
  }

  // PROMOTE
  if ($action === 'promote') {
    $stmt = $conn->prepare("UPDATE users SET fk_user_type = 2 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    echo json_encode(["ok" => $stmt->affected_rows > 0]);
    $stmt->close();
    $conn->close();
    exit;
  }

  // DEMOTE
  if ($action === 'demote') {
    $stmt = $conn->prepare("UPDATE users SET fk_user_type = 3 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    echo json_encode(["ok" => $stmt->affected_rows > 0]);
    $stmt->close();
    $conn->close();
    exit;
  }

  echo json_encode(["ok" => false, "message" => "Invalid action"]);
} catch (Exception $e) {
  echo json_encode(["ok" => false, "message" => $e->getMessage()]);
}
?>