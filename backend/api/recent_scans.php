<?php
require "../db/db.php";
header("Content-Type: application/json");

$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 10;
if ($limit <= 0 || $limit > 50) $limit = 10;

$stmt = $conn->prepare("
  SELECT
    al.id,
    al.scan_type,
    al.status,
    al.scan_time,
    al.card_uid,
    s.full_name,
    s.student_id
  FROM attendance_logs al
  INNER JOIN students s ON s.id = al.student_id
  ORDER BY al.scan_time DESC
  LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();

$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
  $rows[] = $row;
}

echo json_encode([
  "success" => true,
  "scans" => $rows
]);
