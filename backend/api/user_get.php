<?php
require "../db/db.php";

$user_id = $_GET["id"] ?? 0;
if (!$user_id) {
    http_response_code(400);
    exit("Invalid user ID");
}

/* BASE USER */
$stmt = $conn->prepare("
    SELECT id, username, role, status
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    exit("User not found");
}

/* STUDENT */
if ($user["role"] === "student") {
    $stmt = $conn->prepare("
        SELECT 
            s.id AS student_db_id,
            s.student_id,
            s.full_name,
            s.email,

            g.full_name AS guardian_name,
            g.email AS guardian_email,
            g.contact_no,

            r.card_uid,
            r.status AS card_status
        FROM students s
        LEFT JOIN guardians g ON g.student_id = s.id
        LEFT JOIN rfid_cards r ON r.student_id = s.id
        WHERE s.user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user["student"] = $stmt->get_result()->fetch_assoc();
}

/* TEACHER */
if ($user["role"] === "teacher") {
    $stmt = $conn->prepare("
        SELECT teacher_id, full_name, email
        FROM teachers
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user["teacher"] = $stmt->get_result()->fetch_assoc();
}

header("Content-Type: application/json");
echo json_encode($user);
