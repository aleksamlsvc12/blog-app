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

// Retrieve and sanitize input parameters
$categoryName = mysqli_real_escape_string($conn, trim($_GET['category']));
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch category ID based on name
$sql_category = "SELECT id FROM categories WHERE LOWER(name) = LOWER('$categoryName')";
$result_category = mysqli_query($conn, $sql_category);

if (!$result_category) {
  // Handle query error
  echo json_encode([
    "status" => "error",
    "message" => "Database error: " . mysqli_error($conn)
  ]);
  exit;
}

if (mysqli_num_rows($result_category) === 0) {
  // No category found with provided name
  echo json_encode([
    "status" => "empty",
    "message" => "Category not found."
  ]);
  exit;
}

// Extract category ID for later use
$category_row = mysqli_fetch_assoc($result_category);
$category_id = intval($category_row['id']);

// Fetch posts that belong to the category
// Includes aggregated like/dislike counts
// and user’s personal reaction
$sql_posts = "
  SELECT 
    p.id, 
    p.title, 
    p.content, 
    p.image, 
    p.created_at, 
    u.name, 
    u.surname, 
    u.id AS fk_user,
    -- Count number of likes (type = 1)
    (SELECT COUNT(*) FROM reactions r WHERE r.fk_post = p.id AND r.type = 1) AS likes,
    -- Count number of dislikes (type = 0)
    (SELECT COUNT(*) FROM reactions r WHERE r.fk_post = p.id AND r.type = 0) AS dislikes,
    -- Get the logged-in user's reaction to this post (if exists)
    (SELECT type FROM reactions r WHERE r.fk_post = p.id AND r.fk_user = $user_id LIMIT 1) AS user_reaction
  FROM posts p
  JOIN users u ON p.fk_user = u.id
  WHERE p.fk_category = '$category_id'
  ORDER BY p.created_at DESC
";

$result_posts = mysqli_query($conn, $sql_posts);

if (!$result_posts) {
  // Handle query execution error
  echo json_encode([
    "status" => "error",
    "message" => "Query failed: " . mysqli_error($conn)
  ]);
  exit;
}

// Format posts for JSON response
if (mysqli_num_rows($result_posts) > 0) {
  $posts = [];

  while ($row = mysqli_fetch_assoc($result_posts)) {
    $posts[] = [
      "id" => $row['id'],
      "title" => $row['title'],
      "content" => $row['content'],
      "image" => $row['image'],
      "created_at" => $row['created_at'],
      "fk_user" => $row["fk_user"],
      "name" => $row["name"],
      "surname" => $row["surname"],
      "likes" => intval($row['likes']),
      "dislikes" => intval($row['dislikes']),
      // If user has no reaction, value is null
      "user_reaction" => isset($row['user_reaction']) ? intval($row['user_reaction']) : null
    ];
  }

  // Successful response with all posts
  echo json_encode([
    "status" => "success",
    "posts" => $posts
  ]);
} else {
  // No posts found in this category
  echo json_encode([
    "status" => "empty",
    "message" => "No posts found for this category."
  ]);
}
?>
