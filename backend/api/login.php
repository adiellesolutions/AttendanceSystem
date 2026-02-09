<?php
session_start();
require "../db/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed");
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    http_response_code(400);
    exit("Missing credentials");
}

$stmt = $conn->prepare("
    SELECT id, username, password, role, status
    FROM users
    WHERE username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(401);
    exit("Invalid credentials");
}

if ($user["status"] !== "active") {
    http_response_code(403);
    exit("Account inactive");
}

if (!password_verify($password, $user["password"])) {
    http_response_code(401);
    exit("Invalid credentials");
}

/* ✅ LOGIN SUCCESS */
$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];

$conn->query("
    UPDATE users 
    SET last_login = NOW() 
    WHERE id = {$user['id']}
");

/* Redirect by role */
echo json_encode([
    "status" => "success",
    "role" => $user["role"]
]);
