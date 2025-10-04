<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

require_once '../data/db.php';

$sql = "SELECT id, name FROM categories ORDER BY name ASC";
$result = mysqli_query($conn, $sql);

$categories = [];

if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
  }
}

echo json_encode([
  "status" => "success",
  "categories" => $categories
]);
