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

// Read and parse incoming JSON data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

// Extract and sanitize user input
$name = trim($data['name'] ?? '');
$surname = trim($data['surname'] ?? '');
$email = trim($data['email'] ?? '');
$password = (string) ($data['password'] ?? '');

// Validation of input fields
$errors = [];
if ($name === '')
  $errors['name'] = 'Name is mandatory.';
if ($surname === '')
  $errors['surname'] = 'Surname is mandatory.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
  $errors['email'] = 'Email invalid.';
if (strlen($password) < 8)
  $errors['password'] = 'Password must have 8 or more characters.';

// If validation fails, return error with details
if ($errors) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'stage' => 'validation',
    'errors' => $errors
  ]);
  exit;
}

// Check if email is already registered
$select = mysqli_prepare(
  $conn,
  "SELECT id FROM users WHERE email = ? LIMIT 1"
);

if (!$select) {
  // Handle DB prepare statement error
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'stage' => 'prepare-select',
    'error' => mysqli_error($conn)
  ]);
  exit;
}

mysqli_stmt_bind_param($select, "s", $email);
mysqli_stmt_execute($select);
mysqli_stmt_store_result($select);

if (mysqli_stmt_num_rows($select) > 0) {
  // Email already exists in database
  mysqli_stmt_close($select);
  http_response_code(409);
  echo json_encode([
    'ok' => false,
    'stage' => 'email-exists',
    'message' => 'Existing email'
  ]);
  exit;
}
mysqli_stmt_close($select);

// Secure password hashing before storing
$hash = password_hash($password, PASSWORD_DEFAULT);
if (!$hash) {
  // If hashing fails, return server error
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'stage' => 'hash',
    'message' => 'Hash not generated'
  ]);
  exit;
}

// Insert new user into database
$insert = mysqli_prepare(
  $conn,
  "INSERT INTO users (name, surname, email, password_hash, fk_user_type) VALUES (?, ?, ?, ?, 3)"
);

if (!$insert) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'stage' => 'prepare-insert',
    'error' => mysqli_error($conn)
  ]);
  exit;
}

mysqli_stmt_bind_param($insert, "ssss", $name, $surname, $email, $hash);
$ok = mysqli_stmt_execute($insert);
$insertErr = mysqli_error($conn);
mysqli_stmt_close($insert);

// Response
if ($ok) {
  // Successful registration
  http_response_code(201);
  echo json_encode([
    'ok' => true,
    'stage' => 'insert-true',
    'message' => 'Registration successful'
  ]);
} else {
  // Failed to insert user (DB issue)
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'stage' => 'insert-fail',
    'error' => $insertErr
  ]);
}
?>