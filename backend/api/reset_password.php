<?php
session_start();
require "../db/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode(["success" => false, "message" => "Not logged in"]);
  exit;
}

$new = $_POST["new_password"] ?? "";
$new = trim($new);

if (strlen($new) < 6) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
  exit;
}

$userId = (int)$_SESSION["user_id"];
$hash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ?, must_reset_password = 0 WHERE id = ?");
$stmt->bind_param("si", $hash, $userId);
$stmt->execute();

// return role for redirect
echo json_encode([
  "success" => true,
  "role" => $_SESSION["role"] ?? "student"
]);
