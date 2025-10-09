<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once '../data/db.php';

$user_id = $_GET['user_id'] ?? 0;

if (!$user_id) {
  echo json_encode([
    "status" => "error",
    "message" => "Missing user_id parameter"
  ]);
  exit;
}

$stmt = $conn->prepare("
  SELECT p.id, p.title, p.content, p.created_at, c.name AS category
  FROM posts p
  LEFT JOIN categories c ON p.fk_category = c.id
  WHERE p.fk_user = ?
  ORDER BY p.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
  $posts[] = $row;
}

if (count($posts) > 0) {
  echo json_encode(["status" => "success", "posts" => $posts]);
} else {
  echo json_encode(["status" => "empty", "posts" => []]);
}

$stmt->close();
$conn->close();
