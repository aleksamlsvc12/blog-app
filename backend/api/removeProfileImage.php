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

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
  echo json_encode(['ok' => false, 'message' => 'Invalid user ID']);
  exit;
}

$stmt = $conn->prepare("SELECT profile_img FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($oldImage);
$stmt->fetch();
$stmt->close();

if (!empty($oldImage) && file_exists("../" . $oldImage)) {
  unlink("../" . $oldImage);
}

$update = $conn->prepare("UPDATE users SET profile_img = NULL WHERE id = ?");
$update->bind_param("i", $id);
$success = $update->execute();
$update->close();

$conn->close();

if ($success) {
  echo json_encode(['ok' => true, 'message' => 'Profile image removed successfully.']);
} else {
  echo json_encode(['ok' => false, 'message' => 'Failed to remove profile image.']);
}
?>