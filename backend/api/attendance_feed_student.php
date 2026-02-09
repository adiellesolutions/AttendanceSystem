<?php
session_start();
require_once "../db/db.php";

// must be logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    exit;
}

/*
    users -> students -> attendance_logs
*/

// get student id using logged-in user
$sql = "SELECT id FROM students WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo json_encode([]);
    exit;
}

$studentId = $student['id'];

// fetch ONLY this student's attendance
$sql = "

SELECT 
    e.id AS entry_id,
    e.scan_time AS time_in,
    (
        SELECT MIN(x.scan_time)
        FROM attendance_logs x
        WHERE 
            x.student_id = e.student_id
            AND x.scan_type = 'exit'
            AND x.scan_time > e.scan_time
    ) AS time_out,
    e.status
FROM attendance_logs e
WHERE 
    e.student_id = ?
    AND e.scan_type = 'entry'
ORDER BY e.scan_time DESC


";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $studentId);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
