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

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT name, surname, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
  echo json_encode([
    "success" => true,
    "name" => $user['name'],
    "surname" => $user["surname"],
    "created_at" => $user["created_at"],
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "User not found"
  ]);
}
