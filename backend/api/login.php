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

// Parse and validate incoming JSON data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$email = trim($data['email'] ?? '');
$password = (string) ($data['password'] ?? '');

// Validation checks
$errors = [];
if ($email === '' || $password === '') {
  $errors['fields'] = 'All fields are mandatory';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors['email'] = 'Invalid email format';
}

if ($errors) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'stage' => 'validation',
    'errors' => $errors
  ]);
  exit;
}

// Fetch user record for authentication
$sql = "SELECT id, password_hash, fk_user_type FROM users WHERE email = ? LIMIT 1";
$select = mysqli_prepare($conn, $sql);

if (!$select) {
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

if (mysqli_stmt_num_rows($select) !== 1) {
  mysqli_stmt_close($select);
  http_response_code(401);
  echo json_encode([
    'ok' => false,
    'stage' => 'auth',
    'message' => 'Invalid email or password'
  ]);
  exit;
}

// Bind the results
mysqli_stmt_bind_result($select, $userId, $passwordHash, $userType);
mysqli_stmt_fetch($select);
mysqli_stmt_close($select);

// Verify password
if (!password_verify($password, $passwordHash)) {
  http_response_code(401);
  echo json_encode([
    'ok' => false,
    'stage' => 'auth',
    'message' => 'Invalid email or password'
  ]);
  exit;
}

// Successful authentication
echo json_encode([
  'ok' => true,
  'user' => [
    'id' => $userId,
    'email' => $email,
    'fk_user_type' => (int) $userType
  ]
]);
?>
