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

// Fetch all categories from DB
$sql = "SELECT id, name, description FROM categories ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

// Check if query execution failed
if (!$result) {
  echo json_encode([
    "status" => "error",
    "message" => "Database query failed: " . mysqli_error($conn)
  ]);
  exit;
}

$categories = [];

// Loop through each row and add it to the categories array
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
  }

  // Send success response with category list
  echo json_encode([
    "status" => "success",
    "count" => count($categories),
    "categories" => $categories
  ]);
} else {
  // Handle case where no categories exist in DB
  echo json_encode([
    "status" => "empty",
    "categories" => []
  ]);
}
?>
