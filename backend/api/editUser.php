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

// Decode incoming JSON data safely
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

// Extract and sanitize input data
$id = (int) ($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$surname = trim($data['surname'] ?? '');
$email = trim($data['email'] ?? '');
$password = (string) ($data['password'] ?? '');
$title = trim($data['title'] ?? '');
$bio = trim($data['bio'] ?? '');

// Input Validation

// Ensure valid user ID
if ($id <= 0) {
  http_response_code(400);
  echo json_encode([
    'ok' => false,
    'message' => 'Invalid or missing user ID.'
  ]);
  exit;
}

// Validate email format if provided
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'message' => 'Invalid email format.'
  ]);
  exit;
}

// Check minimum password length if password provided
if ($password !== '' && strlen($password) < 8) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'message' => 'Password must have at least 8 characters.'
  ]);
  exit;
}

// Unique Email Check
// Prevent duplicate email usage across different accounts
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

// Dynamic Query Building
// Build SQL query dynamically depending on provided fields
$fields = []; 
$params = [];  
$types = "";

// Only add non-empty fields to the update query
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
  // Securely hash the password before saving to DB
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

// If no fields are provided for update, skip SQL execution
if (empty($fields)) {
  echo json_encode([
    'ok' => true,
    'message' => 'No fields to update.'
  ]);
  exit;
}

// Execute Update Query

// Append user ID for WHERE clause
$params[] = $id;
$types .= "i";

// Construct final SQL dynamically
$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";

// Prepare and bind parameters safely
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

// Execute the query and handle response
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
?>
