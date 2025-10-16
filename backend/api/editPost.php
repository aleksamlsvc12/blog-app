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

$action = null;
$post_id = null;

if (in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'PUT'])) {
  $raw = file_get_contents("php://input");
  $data = json_decode($raw, true);
  $action = $data['action'] ?? null;
  $post_id = intval($data['post_id'] ?? 0);
} else {
  $action = $_POST['action'] ?? null;
  $post_id = intval($_POST['post_id'] ?? 0);
}

if (!$action) {
  echo json_encode(["status" => "error", "message" => "No action provided"]);
  exit;
}


//DELETE POST — remove thumbnail file too

if ($action === 'delete') {
  if ($post_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid post ID"]);
    exit;
  }

  // get old image
  $stmt = $conn->prepare("SELECT image FROM posts WHERE id = ?");
  $stmt->bind_param("i", $post_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $imagePath = $result->fetch_assoc()['image'] ?? null;
  $stmt->close();

  // delete post
  $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
  $stmt->bind_param("i", $post_id);
  $success = $stmt->execute();
  $stmt->close();

  if ($success) {
    if ($imagePath && file_exists("../" . $imagePath)) {
      unlink("../" . $imagePath);
    }
    echo json_encode(["status" => "success", "message" => "Post deleted with image"]);
  } else {
    echo json_encode(["status" => "error", "message" => "Failed to delete post"]);
  }

  $conn->close();
  exit;
}


//EDIT POST — can update text, replace or remove image
if ($action === 'edit') {
  $title = trim($_POST['title'] ?? '');
  $fk_category = intval($_POST['category'] ?? 0);
  $content = trim($_POST['content'] ?? '');
  $remove_image = filter_var($_POST['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN);

  if ($post_id <= 0 || $title === '' || $content === '' || $fk_category <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid or missing fields"]);
    exit;
  }

  // get current image
  $stmt = $conn->prepare("SELECT image FROM posts WHERE id = ?");
  $stmt->bind_param("i", $post_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $oldImage = $result->fetch_assoc()['image'] ?? null;
  $stmt->close();

  $newImagePath = $oldImage;

  // if user uploaded a new file
  if (!empty($_FILES['thumbnail']['name'])) {
    $targetDir = "../uploads/thumbnails/";
    if (!file_exists($targetDir))
      mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($_FILES["thumbnail"]["name"]);
    $targetFile = $targetDir . $fileName;
    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png"];

    if (!in_array($ext, $allowed)) {
      echo json_encode(["status" => "error", "message" => "Invalid image type"]);
      exit;
    }

    if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $targetFile)) {
      $newImagePath = "uploads/thumbnails/" . $fileName;
      if ($oldImage && file_exists("../" . $oldImage))
        unlink("../" . $oldImage);
    }
  } elseif ($remove_image) {
    if ($oldImage && file_exists("../" . $oldImage))
      unlink("../" . $oldImage);
    $newImagePath = null;
  }

  // update DB
  $stmt = $conn->prepare("UPDATE posts SET title=?, content=?, fk_category=?, image=? WHERE id=?");
  $stmt->bind_param("ssisi", $title, $content, $fk_category, $newImagePath, $post_id);
  $ok = $stmt->execute();
  $stmt->close();

  if ($ok) {
    echo json_encode(["status" => "success", "message" => "Post updated successfully"]);
  } else {
    echo json_encode(["status" => "error", "message" => "Update failed"]);
  }

  $conn->close();
  exit;
}

echo json_encode(["status" => "error", "message" => "Invalid action"]);
exit;
?>