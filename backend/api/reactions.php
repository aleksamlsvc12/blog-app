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
$type = isset($data['type']) ? intval($data['type']) : null;

if (!$fk_post || !$fk_user || ($type !== 0 && $type !== 1)) {
  echo json_encode([
    "status" => "error",
    "message" => "Invalid input."
  ]);
  exit;
}

$sql_check = "SELECT id, type FROM reactions WHERE fk_post = $fk_post AND fk_user = $fk_user";
$result = mysqli_query($conn, $sql_check);

if (!$result) {
  echo json_encode([
    "status" => "error",
    "message" => "Database error: " . mysqli_error($conn)
  ]);
  exit;
}

if (mysqli_num_rows($result) > 0) {
  $row = mysqli_fetch_assoc($result);
  $reaction_id = $row['id'];
  $current_type = $row['type'];

  if ($current_type === $type) {
    $sql_delete = "DELETE FROM reactions WHERE id = $reaction_id";
    $ok = mysqli_query($conn, $sql_delete);
    $status_msg = "Reaction removed.";
  } else {
    $sql_update = "UPDATE reactions SET type = $type WHERE id = $reaction_id";
    $ok = mysqli_query($conn, $sql_update);
    $status_msg = "Reaction updated.";
  }
} else {
  $sql_insert = "INSERT INTO reactions (type, fk_post, fk_user) VALUES ($type, $fk_post, $fk_user)";
  $ok = mysqli_query($conn, $sql_insert);
  $status_msg = "Reaction added.";
}

if (!$ok) {
  echo json_encode([
    "status" => "error",
    "message" => "Query failed: " . mysqli_error($conn)
  ]);
  exit;
}

$sql_count = "
  SELECT 
    SUM(type = 1) AS likes, 
    SUM(type = 0) AS dislikes
  FROM reactions
  WHERE fk_post = $fk_post
";
$count_result = mysqli_query($conn, $sql_count);
$count_data = mysqli_fetch_assoc($count_result);

echo json_encode([
  "status" => "success",
  "message" => $status_msg,
  "likes" => intval($count_data['likes']),
  "dislikes" => intval($count_data['dislikes'])
]);
?>