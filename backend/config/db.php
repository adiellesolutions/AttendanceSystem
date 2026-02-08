<?php
declare(strict_types=1);

// backend/config/db.php
$DB_HOST = "localhost";
$DB_NAME = "rfid_attendance_db";
$DB_USER = "root";
$DB_PASS = ""; // change if you set password

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Throwable $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(["ok" => false, "error" => "DB connection failed"]);
    exit;
}
