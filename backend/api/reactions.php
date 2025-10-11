<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once '../data/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$fk_post = intval($data['fk_post'] ?? 0);
$fk_user = intval($data['fk_user'] ?? 0);
$type = $data['type'] ?? null;

if (!$fk_post || !$fk_user) {
  echo json_encode(["status" => "error", "message" => "Invalid input."]);
  exit;
}

if ($type === "remove") {
  $stmt = $conn->prepare("DELETE FROM reactions WHERE fk_post = ? AND fk_user = ?");
  $stmt->bind_param("ii", $fk_post, $fk_user);
  $ok = $stmt->execute();
  $stmt->close();

  if (!$ok) {
    echo json_encode(["status" => "error", "message" => "Failed to remove reaction."]);
    exit;
  }
} 
elseif ($type === 0 || $type === 1 || $type === "0" || $type === "1") {
  $type = intval($type);

  $check = $conn->prepare("SELECT id, type FROM reactions WHERE fk_post = ? AND fk_user = ?");
  $check->bind_param("ii", $fk_post, $fk_user);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    if ((int)$row['type'] === $type) {
      $stmt = $conn->prepare("DELETE FROM reactions WHERE id = ?");
      $stmt->bind_param("i", $row['id']);
      $stmt->execute();
      $stmt->close();
    } else {
      $stmt = $conn->prepare("UPDATE reactions SET type = ? WHERE id = ?");
      $stmt->bind_param("ii", $type, $row['id']);
      $stmt->execute();
      $stmt->close();
    }
  } else {
    $stmt = $conn->prepare("INSERT INTO reactions (type, fk_post, fk_user) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $type, $fk_post, $fk_user);
    $stmt->execute();
    $stmt->close();
  }

  $check->close();
} 
else {
  echo json_encode(["status" => "error", "message" => "Invalid reaction type."]);
  exit;
}

$count = $conn->prepare("
  SELECT 
    SUM(type = 1) AS likes, 
    SUM(type = 0) AS dislikes
  FROM reactions
  WHERE fk_post = ?
");
$count->bind_param("i", $fk_post);
$count->execute();
$result = $count->get_result()->fetch_assoc();
$count->close();

echo json_encode([
  "status" => "success",
  "likes" => intval($result['likes']),
  "dislikes" => intval($result['dislikes'])
]);

$conn->close();
?>
