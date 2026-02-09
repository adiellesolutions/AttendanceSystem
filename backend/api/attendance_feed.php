<?php
require "../db/db.php";

header("Content-Type: application/json");

$sql = "
    SELECT 
        a.id,
        a.scan_type,
        a.scan_time,
        a.status,
        s.full_name,
        s.student_id
    FROM attendance_logs a
    JOIN students s ON s.id = a.student_id
    ORDER BY a.scan_time DESC
    LIMIT 10
";

$result = $conn->query($sql);

$feeds = [];

while ($row = $result->fetch_assoc()) {
    $feeds[] = $row;
}

echo json_encode([
    "feeds" => $feeds
]);
