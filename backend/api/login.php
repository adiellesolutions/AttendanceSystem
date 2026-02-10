<?php
session_start();
require "../db/db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing credentials"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, username, password, role, status, must_reset_password
    FROM users
    WHERE username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
    exit;
}

if ($user["status"] !== "active") {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Account inactive"]);
    exit;
}

if (!password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
    exit;
}

/* ✅ LOGIN SUCCESS */
$_SESSION["user_id"] = (int)$user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];

/* ✅ update last_login safely */
$u = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$u->bind_param("i", $_SESSION["user_id"]);
$u->execute();

echo json_encode([
    "success" => true,
    "role" => $user["role"],
    "must_reset" => ((int)$user["must_reset_password"] === 1)
]);
