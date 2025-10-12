<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once '../data/db.php';

// Read and decode raw JSON input from the request body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Validate that JSON is received correctly
if (!$data) {
  echo json_encode(["status" => "error", "message" => "No JSON received"]);
  exit;
}

// Determine requested action (edit or delete)
$action = $data['action'] ?? null;
if (!$action) {
  echo json_encode(["status" => "error", "message" => "No action provided"]);
  exit;
}

// DELETE ACTION — Remove a post
if ($action === 'delete') {
  $post_id = intval($data['post_id'] ?? 0);

  // Validate post ID before executing SQL
  if ($post_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid post ID"]);
    exit;
  }

  // Use prepared statement to safely delete post
  $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
  $stmt->bind_param("i", $post_id);

  // Execute delete query and send response
  if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Post deleted"]);
  } else {
    echo json_encode([
      "status" => "error",
      "message" => "Failed to delete post",
      "sql_error" => $conn->error
    ]);
  }

  $stmt->close();
  $conn->close();
  exit;
}

// EDIT ACTION — Update a post
if ($action === 'edit') {
  $post_id = intval($data['post_id'] ?? 0);
  $title = trim($data['title'] ?? '');
  $fk_category = intval($data['category'] ?? 0);
  $content = trim($data['content'] ?? '');

  // Check all required fields and ensure valid data
  if ($post_id <= 0 || $title === '' || $content === '' || $fk_category <= 0) {
    echo json_encode(["status" => "error", "message" => "Missing or invalid fields"]);
    exit;
  }

  // Use prepared statement to safely update post details
  $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, fk_category = ? WHERE id = ?");
  $stmt->bind_param("ssii", $title, $content, $fk_category, $post_id);

  // Execute update query and return response
  if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Post updated"]);
  } else {
    echo json_encode([
      "status" => "error",
      "message" => "Failed to update post",
      "sql_error" => $conn->error
    ]);
  }

  $stmt->close();
  $conn->close();
  exit;
}

// Invalid or unsupported action
echo json_encode(["status" => "error", "message" => "Invalid action"]);
exit;
?>
