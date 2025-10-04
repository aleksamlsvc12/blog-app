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

$sql = "SELECT id, name FROM categories ORDER BY name ASC";
$result = mysqli_query($conn, $sql);

$categories = [];

if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
  }

  echo json_encode([
    "status" => "success",
    "categories" => $categories
  ]);
} else {
  echo json_encode([
    "status" => "empty",
    "categories" => []
  ]);
}
?>
