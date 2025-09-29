<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'blog_db';

$conn = mysqli_connect(
  $host,
  $user,
  $pass,
  $db,
  3306
);

if (!$conn) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode([
    'ok' => false,
    'message' => 'DB connect failed',
    'error' => mysqli_connect_error()
  ]);
  exit;
}