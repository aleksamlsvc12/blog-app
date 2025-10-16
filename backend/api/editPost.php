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

// Read and decode raw JSON input
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
  echo json_encode(["status" => "error", "message" => "No JSON received"]);
  exit;
}

$action = $data['action'] ?? null;
if (!$action) {
  echo json_encode(["status" => "error", "message" => "No action provided"]);
  exit;
}

/* ======================================================
   🗑 DELETE POST — also remove thumbnail file if it exists
   ====================================================== */
if ($action === 'delete') {
  $post_id = intval($data['post_id'] ?? 0);

  if ($post_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid post ID"]);
    exit;
  }

  // 1️⃣ Get image path from database
  $stmt = $conn->prepare("SELECT image FROM posts WHERE id = ?");
  $stmt->bind_param("i", $post_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $imagePath = null;

  if ($row = $result->fetch_assoc()) {
    $imagePath = $row['image'];
  }
  $stmt->close();

  // 2️⃣ Delete post record
  $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
  $stmt->bind_param("i", $post_id);
  $deleteSuccess = $stmt->execute();
  $stmt->close();

  // 3️⃣ If delete succeeded, remove image from disk (if it exists)
  if ($deleteSuccess) {
    if ($imagePath && file_exists("../" . $imagePath)) {
      unlink("../" . $imagePath);
    }

    echo json_encode(["status" => "success", "message" => "Post and image deleted successfully"]);
  } else {
    echo json_encode([
      "status" => "error",
      "message" => "Failed to delete post",
      "sql_error" => $conn->error
    ]);
  }

  $conn->close();
  exit;
}

/* ======================================================
   ✏️ EDIT POST — update text and category (image stays the same)
   ====================================================== */
if ($action === 'edit') {
  $post_id = intval($data['post_id'] ?? 0);
  $title = trim($data['title'] ?? '');
  $fk_category = intval($data['category'] ?? 0);
  $content = trim($data['content'] ?? '');

  if ($post_id <= 0 || $title === '' || $content === '' || $fk_category <= 0) {
    echo json_encode(["status" => "error", "message" => "Missing or invalid fields"]);
    exit;
  }

  $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, fk_category = ? WHERE id = ?");
  $stmt->bind_param("ssii", $title, $content, $fk_category, $post_id);

  if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Post updated successfully"]);
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

// Invalid action
echo json_encode(["status" => "error", "message" => "Invalid action"]);
exit;
?>
