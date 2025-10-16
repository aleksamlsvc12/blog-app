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

// Validate user_id parameter
$user_id = intval($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
  echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
  exit;
}

// ✅ Fetch posts with image field
$sql = "
  SELECT 
    p.id,
    p.title,
    p.content,
    p.image,            -- 👈 dodato thumbnail polje
    p.created_at,
    p.fk_category,
    c.name AS category
  FROM posts p
  JOIN categories c ON p.fk_category = c.id
  WHERE p.fk_user = ?
  ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
  $row['fk_category'] = (int) $row['fk_category'];
  $posts[] = $row;
}

if (count($posts) > 0) {
  echo json_encode(["status" => "success", "posts" => $posts]);
} else {
  echo json_encode(["status" => "empty", "posts" => []]);
}

$stmt->close();
$conn->close();
?>
