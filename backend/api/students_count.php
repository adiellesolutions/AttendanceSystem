<?php
session_start();
require "../db/db.php"; // ✅ adjust path if needed

header("Content-Type: application/json");

// Optional: restrict to admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

try {
    $result = $conn->query("SELECT COUNT(*) AS total FROM students");

    if (!$result) {
        throw new Exception($conn->error);
    }

    $row = $result->fetch_assoc();

    echo json_encode([
        "total" => (int)$row['total']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
