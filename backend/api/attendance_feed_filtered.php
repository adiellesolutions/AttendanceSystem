<?php
require "../db/db.php";
header("Content-Type: application/json");

$where = [];
$params = [];
$types = "";

/* Filters */
if (!empty($_GET['student'])) {
    $where[] = "(s.full_name LIKE ? OR s.student_id LIKE ?)";
    $params[] = "%" . $_GET['student'] . "%";
    $params[] = "%" . $_GET['student'] . "%";
    $types .= "ss";
}

if (!empty($_GET['from'])) {
    $where[] = "a.scan_time >= ?";
    $params[] = $_GET['from'] . " 00:00:00";
    $types .= "s";
}

if (!empty($_GET['to'])) {
    $where[] = "a.scan_time <= ?";
    $params[] = $_GET['to'] . " 23:59:59";
    $types .= "s";
}

$sql = "
SELECT
  a.scan_time,
  a.scan_type,
  s.full_name,
  s.student_id
FROM attendance_logs a
JOIN students s ON a.student_id = s.id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY a.scan_time DESC LIMIT 20";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

echo json_encode($rows);
