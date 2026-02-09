<?php
require "../db/db.php";
header("Content-Type: application/json");

$search = $_GET['search'] ?? '';
$role   = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "
    SELECT 
        u.id,
        u.username,
        u.role,
        u.status,
        u.last_login,
        COALESCE(s.full_name, t.full_name, u.username) AS full_name,
        COALESCE(s.email, t.email, u.username) AS email,
        CASE
            WHEN u.role = 'student' THEN s.student_id
            WHEN u.role = 'teacher' THEN t.teacher_id
            ELSE '—'
        END AS assoc
    FROM users u
    LEFT JOIN students s ON s.user_id = u.id
    LEFT JOIN teachers t ON t.user_id = u.id
    WHERE 1=1
";

if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (u.username LIKE '%$search%' OR s.full_name LIKE '%$search%' OR t.full_name LIKE '%$search%')";
}

if ($role !== '') {
    $role = $conn->real_escape_string($role);
    $sql .= " AND u.role = '$role'";
}

if ($status !== '') {
    $status = $conn->real_escape_string($status);
    $sql .= " AND u.status = '$status'";
}

$result = $conn->query($sql);
$rows = [];

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode([
    "count" => count($rows),
    "users" => $rows
]);
