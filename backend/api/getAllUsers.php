<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once '../data/db.php';

$sql = "SELECT id, name, surname, email, created_at, fk_user_type 
        FROM users 
        WHERE fk_user_type != 1
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => mysqli_error($conn)
  ]);
  exit;
}

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
  $users[] = [
    'id' => (int) $row['id'],
    'name' => $row['name'],
    'surname' => $row['surname'],
    'email' => $row['email'],
    'created_at' => $row['created_at'],
    'fk_user_type' => (int) $row['fk_user_type']
  ];
}

echo json_encode([
  'ok' => true,
  'users' => $users
]);
?>