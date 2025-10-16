<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

include_once "../data/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $sql = "SELECT * FROM categories ORDER BY id DESC";
  $result = $conn->query($sql);

  $categories = [];
  while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
  }

  echo json_encode(["ok" => true, "categories" => $categories]);
  exit;
}

if ($method === 'POST') {
  $data = json_decode(file_get_contents("php://input"), true);

  if (!isset($data['action'])) {
    echo json_encode(["ok" => false, "message" => "Missing action"]);
    exit;
  }

  $action = $data['action'];

  switch ($action) {
    case 'create':
      if (empty($data['name']) || empty($data['description'])) {
        echo json_encode(["ok" => false, "message" => "Missing data"]);
        exit;
      }

      $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
      $stmt->bind_param("ss", $data['name'], $data['description']);
      $stmt->execute();

      echo json_encode(["ok" => true, "message" => "Category created successfully"]);
      break;

    case 'update':
      if (!isset($data['id']) || empty($data['name']) || empty($data['description'])) {
        echo json_encode(["ok" => false, "message" => "Missing data"]);
        exit;
      }

      $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
      $stmt->bind_param("ssi", $data['name'], $data['description'], $data['id']);
      $stmt->execute();

      echo json_encode(["ok" => true, "message" => "Category updated successfully"]);
      break;

    case 'delete':
      if (!isset($data['id'])) {
        echo json_encode(["ok" => false, "message" => "Missing ID"]);
        exit;
      }

      $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
      $stmt->bind_param("i", $data['id']);
      $stmt->execute();

      echo json_encode(["ok" => true, "message" => "Category deleted successfully"]);
      break;

    default:
      echo json_encode(["ok" => false, "message" => "Invalid action"]);
      break;
  }

  $conn->close();
}
?>