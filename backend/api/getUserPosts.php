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

// If invalid or missing user ID, return error
if ($user_id <= 0) {
  echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
  exit;
}

// Fetch posts belonging to a specific user
// The query joins the "posts" and "categories" tables to retrieve post details along with category name
$sql = "
  SELECT 
    posts.id,
    posts.title,
    posts.content,
    posts.created_at,
    posts.fk_category,
    categories.name AS category
  FROM posts
  JOIN categories ON posts.fk_category = categories.id
  WHERE posts.fk_user = ?
  ORDER BY posts.created_at DESC
";

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Build JSON response with posts
$posts = [];
while ($row = $result->fetch_assoc()) {
  // Explicitly cast category foreign key to integer
  $row['fk_category'] = (int) $row['fk_category'];
  $posts[] = $row;
}

// Return results depending on whether posts were found
if (count($posts) > 0) {
  echo json_encode(["status" => "success", "posts" => $posts]);
} else {
  echo json_encode(["status" => "empty", "posts" => []]);
}

$stmt->close();
$conn->close();
?>
