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

// --- Ako je zahtev POST sa form-data ---
$title = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
$content = mysqli_real_escape_string($conn, trim($_POST['content'] ?? ''));
$fk_category = intval($_POST['fk_category'] ?? 0);
$fk_user = intval($_POST['fk_user'] ?? 0);

// Validacija
if (empty($title) || empty($content) || !$fk_category || !$fk_user) {
  echo json_encode(["status" => "error", "message" => "All fields are required."]);
  exit;
}

$imagePath = null;

// Ako postoji thumbnail fajl
if (!empty($_FILES['thumbnail']['name'])) {
  $targetDir = "../uploads/thumbnails/";
  if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
  }

  $fileName = time() . "_" . basename($_FILES["thumbnail"]["name"]);
  $targetFile = $targetDir . $fileName;
  $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

  $allowedTypes = ["jpg", "jpeg", "png"];
  if (!in_array($imageFileType, $allowedTypes)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, JPEG, PNG allowed."]);
    exit;
  }

  if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $targetFile)) {
    $imagePath = "uploads/thumbnails/" . $fileName;
  } else {
    echo json_encode(["status" => "error", "message" => "Failed to upload thumbnail."]);
    exit;
  }
}

// Insert u bazu
$sql = "INSERT INTO posts (title, content, image, created_at, fk_user, fk_category)
        VALUES ('$title', '$content', " . ($imagePath ? "'$imagePath'" : "NULL") . ", NOW(), '$fk_user', '$fk_category')";

if (mysqli_query($conn, $sql)) {
  echo json_encode(["status" => "success", "message" => "Post created successfully."]);
} else {
  echo json_encode(["status" => "error", "message" => "Database error: " . mysqli_error($conn)]);
}
?>
