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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$id = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$surname = trim($data['surname'] ?? '');
$email = trim($data['email'] ?? '');
$password = (string)($data['password'] ?? '');
$title = trim($data['title'] ?? '');
$bio = trim($data['bio'] ?? '');

if ($id <= 0) {
  http_response_code(400);
  echo json_encode([
    'ok' => false,
    'message' => 'Invalid or missing user ID.'
  ]);
  exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'message' => 'Invalid email format.'
  ]);
  exit;
}

if ($password !== '' && strlen($password) < 8) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'message' => 'Password must have at least 8 characters.'
  ]);
  exit;
}

if ($email !== '') {
  $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
  $check->bind_param("si", $email, $id);
  $check->execute();
  $check->store_result();
  if ($check->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
      'ok' => false,
      'message' => 'This email is already in use.'
    ]);
    exit;
  }
  $check->close();
}

$fields = [];
$params = [];
$types = "";

if ($name !== '') {
  $fields[] = "name = ?";
  $params[] = $name;
  $types .= "s";
}

if ($surname !== '') {
  $fields[] = "surname = ?";
  $params[] = $surname;
  $types .= "s";
}

if ($email !== '') {
  $fields[] = "email = ?";
  $params[] = $email;
  $types .= "s";
}

if ($password !== '') {
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $fields[] = "password_hash = ?";
  $params[] = $hash;
  $types .= "s";
}

if ($title !== '') {
  $fields[] = "title = ?";
  $params[] = $title;
  $types .= "s";
}

if ($bio !== '') {
  $fields[] = "bio = ?";
  $params[] = $bio;
  $types .= "s";
}

if (empty($fields)) {
  echo json_encode([
    'ok' => true,
    'message' => 'No fields to update.'
  ]);
  exit;
}

$params[] = $id;
$types .= "i";

$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
  echo json_encode([
    'ok' => true,
    'message' => 'Profile updated successfully.'
  ]);
} else {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'message' => 'Database error: ' . $stmt->error
  ]);
}

$stmt->close();
$conn->close();
