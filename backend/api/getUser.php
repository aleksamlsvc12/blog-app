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

// Retrieve user info by ID

// Get user ID from query string, default to 0 if not provided
$id = $_GET['id'] ?? 0;

// Use a prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT name, surname, title, bio, created_at, profile_img FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Fetch the result of the query
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If a user with the given ID exists, return their data
if ($user) {
  echo json_encode([
    "success" => true,
    "name" => $user['name'],
    "surname" => $user["surname"],
    "created_at" => $user["created_at"],
    "title" => $user["title"],
    "bio" => $user["bio"],
    "image" => $user["profile_img"]
  ]);
} else {
  // If no user found, send an error response
  echo json_encode([
    "success" => false,
    "message" => "User not found"
  ]);
}
?>
