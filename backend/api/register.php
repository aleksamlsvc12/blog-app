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

$name = trim($data['name'] ?? '');
$surname = trim($data['surname'] ?? '');
$email = trim($data['email'] ?? '');
$password = (string) ($data['password'] ?? '');

$errors = [];
if ($name === '')
  $errors['name'] = 'Name is mandatory.';
if ($surname === '')
  $errors['surname'] = 'Surname is mandatory.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
  $errors['email'] = 'Email invalid.';
if (strlen($password) < 8)
  $errors['password'] = 'Password must have 8 or more characters.';

if ($errors) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'stage' => 'validation',
    'errors' => $errors
  ]);
  exit;
}

$select = mysqli_prepare(
  $conn,
  "SELECT id FROM users WHERE email = ? LIMIT 1"
);

if (!$select) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'stage' => 'prepare-select',
    'error' => mysqli_error($conn)
  ]);
  exit;
}

mysqli_stmt_bind_param(
  $select,
  "s",
  $email
);

mysqli_stmt_execute($select);
mysqli_stmt_store_result($select);

if (mysqli_stmt_num_rows($select) > 0) {
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

$hash = password_hash($password, PASSWORD_DEFAULT);
if (!$hash) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'stage' => 'hash',
    'message' => 'Hash not generated'
  ]);
  exit;
}

$insert = mysqli_prepare(
  $conn,
  "INSERT INTO users (name, surname, email, password_hash) VALUES (?, ?, ?, ?)"
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

mysqli_stmt_bind_param(
  $insert,
  "ssss",
  $name,
  $surname,
  $email,
  $hash
);

$ok = mysqli_stmt_execute($insert);
$insertErr = mysqli_error($conn);
mysqli_stmt_close($insert);

if ($ok) {
  http_response_code(201);
  echo json_encode([
    'ok' => true,
    'stage' => 'insert-true',
    'message' => 'Registration successful'
  ]);
} else {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'stage' => 'insert-fail',
    'error' => $insertErr
  ]);
}