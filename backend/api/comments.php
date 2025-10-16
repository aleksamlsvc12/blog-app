<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once '../data/db.php';

// ✅ GET REQUEST — Fetch comments
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (!isset($_GET['fk_post'])) {
    echo json_encode([
      "status" => "error",
      "message" => "Missing fk_post parameter."
    ]);
    exit;
  }

  $fk_post = intval($_GET['fk_post']); // Sanitize post ID

  // ✅ Fetch all comments for a specific post along with user info and profile image
  $sql = "
    SELECT 
      c.id, 
      c.content, 
      c.created_at, 
      u.name, 
      u.surname, 
      u.id AS fk_user,
      u.profile_img
    FROM comments c
    JOIN users u ON c.fk_user = u.id
    WHERE c.fk_post = $fk_post
    ORDER BY c.created_at ASC
  ";

  $result = mysqli_query($conn, $sql);
  if (!$result) {
    echo json_encode([
      "status" => "error",
      "message" => "Query failed: " . mysqli_error($conn)
    ]);
    exit;
  }

  $comments = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $comments[] = [
      "id" => intval($row['id']),
      "content" => $row['content'],
      "created_at" => $row['created_at'],
      "fk_user" => intval($row['fk_user']),
      "name" => $row['name'],
      "surname" => $row['surname'],
      "profile_img" => $row['profile_img']
    ];
  }

  echo json_encode([
    "status" => "success",
    "comments" => $comments,
    "count" => count($comments)
  ]);
  exit;
}

// POST REQUEST — Add a comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents("php://input"), true);

  if (
    !isset($data['fk_user']) ||
    !isset($data['fk_post']) ||
    !isset($data['content'])
  ) {
    echo json_encode([
      "status" => "error",
      "message" => "Missing required fields."
    ]);
    exit;
  }

  $fk_user = intval($data['fk_user']);
  $fk_post = intval($data['fk_post']);
  $content = mysqli_real_escape_string($conn, trim($data['content']));

  $sql_insert = "
    INSERT INTO comments (content, created_at, fk_user, fk_post)
    VALUES ('$content', NOW(), $fk_user, $fk_post)
  ";

  if (mysqli_query($conn, $sql_insert)) {
    echo json_encode([
      "status" => "success",
      "message" => "Comment added successfully."
    ]);
  } else {
    echo json_encode([
      "status" => "error",
      "message" => "Failed to insert comment: " . mysqli_error($conn)
    ]);
  }

  exit;
}

// PUT REQUEST — Edit a comment
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $data = json_decode(file_get_contents("php://input"), true);

  if (!isset($data['id']) || !isset($data['fk_user']) || !isset($data['content'])) {
    echo json_encode([
      "status" => "error",
      "message" => "Missing required fields."
    ]);
    exit;
  }

  $comment_id = intval($data['id']);
  $fk_user = intval($data['fk_user']);
  $content = mysqli_real_escape_string($conn, trim($data['content']));

  $check = mysqli_query($conn, "SELECT fk_user FROM comments WHERE id = $comment_id");
  $row = mysqli_fetch_assoc($check);

  if (!$row) {
    echo json_encode(["status" => "error", "message" => "Comment not found."]);
    exit;
  }

  if ($row['fk_user'] != $fk_user) {
    echo json_encode(["status" => "error", "message" => "Not authorized to edit this comment."]);
    exit;
  }

  $sql_update = "UPDATE comments SET content = '$content' WHERE id = $comment_id";

  if (mysqli_query($conn, $sql_update)) {
    echo json_encode([
      "status" => "success",
      "message" => "Comment updated successfully."
    ]);
  } else {
    echo json_encode([
      "status" => "error",
      "message" => "Failed to update comment: " . mysqli_error($conn)
    ]);
  }
  exit;
}

// DELETE REQUEST — Remove comment
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $data = json_decode(file_get_contents("php://input"), true);

  if (!isset($data['id']) || !isset($data['fk_user'])) {
    echo json_encode([
      "status" => "error",
      "message" => "Missing required fields."
    ]);
    exit;
  }

  $comment_id = intval($data['id']);
  $fk_user = intval($data['fk_user']);

  $check = mysqli_query($conn, "SELECT fk_user FROM comments WHERE id = $comment_id");
  $row = mysqli_fetch_assoc($check);

  if (!$row) {
    echo json_encode(["status" => "error", "message" => "Comment not found."]);
    exit;
  }

  if ($row['fk_user'] != $fk_user) {
    echo json_encode(["status" => "error", "message" => "Not authorized to delete this comment."]);
    exit;
  }

  $sql_delete = "DELETE FROM comments WHERE id = $comment_id";

  if (mysqli_query($conn, $sql_delete)) {
    echo json_encode([
      "status" => "success",
      "message" => "Comment deleted successfully."
    ]);
  } else {
    echo json_encode([
      "status" => "error",
      "message" => "Failed to delete comment: " . mysqli_error($conn)
    ]);
  }
  exit;
}

// Invalid method fallback
echo json_encode([
  "status" => "error",
  "message" => "Invalid request method."
]);
exit;
?>