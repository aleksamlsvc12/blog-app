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

// Decode JSON request body
$input = json_decode(file_get_contents("php://input"), true);

// Sanitize and validate user input to prevent SQL injection and empty fields
$title = mysqli_real_escape_string($conn, trim($input['title'] ?? ''));
$content = mysqli_real_escape_string($conn, trim($input['content'] ?? ''));
$fk_category = intval($input['fk_category'] ?? 0);
$fk_user = intval($input['fk_user'] ?? 0);

// Validate that all required fields are present and non-empty
if (empty($title) || empty($content) || !$fk_category || !$fk_user) {
  echo json_encode([
    "status" => "error",
    "message" => "All fields are required."
  ]);
  exit;
}

// Insert new post into the database with current timestamp
$sql = "INSERT INTO posts (title, content, created_at, fk_user, fk_category)
        VALUES ('$title', '$content', NOW(), '$fk_user', '$fk_category')";

// Execute query and send appropriate JSON response
if (mysqli_query($conn, $sql)) {
  echo json_encode([
    "status" => "success",
    "message" => "Post created successfully."
  ]);
} else {
  // Return error message including database error for debugging
  echo json_encode([
    "status" => "error",
    "message" => "Database error: " . mysqli_error($conn)
  ]);
}
?>
