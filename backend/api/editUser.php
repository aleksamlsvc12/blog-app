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

$id = 0;
$name = $surname = $email = $password = $title = $bio = "";

if (isset($_SERVER["CONTENT_TYPE"]) && str_starts_with($_SERVER["CONTENT_TYPE"], "application/json")) {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true) ?: [];
  $id = (int)($data['id'] ?? 0);
  $name = trim($data['name'] ?? '');
  $surname = trim($data['surname'] ?? '');
  $email = trim($data['email'] ?? '');
  $password = $data['password'] ?? '';
  $title = trim($data['title'] ?? '');
  $bio = trim($data['bio'] ?? '');
} else {

  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $surname = trim($_POST['surname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $title = trim($_POST['title'] ?? '');
  $bio = trim($_POST['bio'] ?? '');
}


if ($id <= 0) {
  echo json_encode(['ok' => false, 'message' => 'Invalid user ID']);
  exit;
}


$oldImage = null;
$getOld = $conn->prepare("SELECT profile_img FROM users WHERE id = ?");
$getOld->bind_param("i", $id);
$getOld->execute();
$getOld->bind_result($oldImage);
$getOld->fetch();
$getOld->close();

$profileImgPath = null;
if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK && $_FILES['profile_img']['size'] > 0) {
  $uploadDir = '../uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

  $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
  $filename = 'user_' . $id . '_' . time() . '.' . $ext;
  $targetFile = $uploadDir . $filename;

  if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFile)) {
    $profileImgPath = 'uploads/' . $filename;

    if (!empty($oldImage) && file_exists("../" . $oldImage)) {
      unlink("../" . $oldImage);
    }
  }
}

$fields = [];
$params = [];
$types = "";

if ($name !== '') { $fields[] = "name = ?"; $params[] = $name; $types .= "s"; }
if ($surname !== '') { $fields[] = "surname = ?"; $params[] = $surname; $types .= "s"; }
if ($email !== '') { $fields[] = "email = ?"; $params[] = $email; $types .= "s"; }
if ($password !== '') {
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $fields[] = "password_hash = ?";
  $params[] = $hash;
  $types .= "s";
}
if ($title !== '') { $fields[] = "title = ?"; $params[] = $title; $types .= "s"; }
if ($bio !== '') { $fields[] = "bio = ?"; $params[] = $bio; $types .= "s"; }
if ($profileImgPath) { $fields[] = "profile_img = ?"; $params[] = $profileImgPath; $types .= "s"; }

if (empty($fields)) {
  echo json_encode(['ok' => true, 'message' => 'Nothing to update.']);
  exit;
}

$params[] = $id;
$types .= "i";

$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
  echo json_encode(['ok' => true, 'message' => 'Profile updated successfully.']);
} else {
  echo json_encode(['ok' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
