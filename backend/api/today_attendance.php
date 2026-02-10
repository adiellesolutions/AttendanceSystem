<?php
require "../db/db.php";
header("Content-Type: application/json");

$conn->query("SET time_zone = '+08:00'");

$date   = $_GET["date"] ?? date("Y-m-d");
$search = trim($_GET["search"] ?? "");
$page   = max(1, (int)($_GET["page"] ?? 1));
$limit  = max(1, (int)($_GET["limit"] ?? 8));
$offset = ($page - 1) * $limit;

$like = "%" . $search . "%";

/* ---------------- TOTAL COUNT ---------------- */
if ($search !== "") {
  $countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM students
    WHERE full_name LIKE ? OR student_id LIKE ?
  ");
  $countStmt->bind_param("ss", $like, $like);
} else {
  $countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM students
  ");
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()["total"];

/* ---------------- DATA QUERY ---------------- */
if ($search !== "") {
  $stmt = $conn->prepare("
    SELECT
      s.id AS student_db_id,
      s.student_id,
      s.full_name,
      u.profile_photo,

      MIN(CASE WHEN al.scan_type='entry' THEN al.scan_time END) AS time_in,
      MAX(CASE WHEN al.scan_type='exit'  THEN al.scan_time END) AS time_out,

      CASE
        WHEN COUNT(al.id) = 0 THEN 'absent'
        WHEN SUM(al.status='late') > 0 THEN 'late'
        ELSE 'present'
      END AS day_status

    FROM students s
    JOIN users u ON u.id = s.user_id
    LEFT JOIN attendance_logs al
      ON al.student_id = s.id
     AND DATE(al.scan_time) = ?

    WHERE s.full_name LIKE ? OR s.student_id LIKE ?
    GROUP BY s.id
    ORDER BY s.full_name
    LIMIT ? OFFSET ?
  ");
  $stmt->bind_param("sssii", $date, $like, $like, $limit, $offset);
} else {
  $stmt = $conn->prepare("
    SELECT
      s.id AS student_db_id,
      s.student_id,
      s.full_name,
      u.profile_photo,

      MIN(CASE WHEN al.scan_type='entry' THEN al.scan_time END) AS time_in,
      MAX(CASE WHEN al.scan_type='exit'  THEN al.scan_time END) AS time_out,

      CASE
        WHEN COUNT(al.id) = 0 THEN 'absent'
        WHEN SUM(al.status='late') > 0 THEN 'late'
        ELSE 'present'
      END AS day_status

    FROM students s
    JOIN users u ON u.id = s.user_id
    LEFT JOIN attendance_logs al
      ON al.student_id = s.id
     AND DATE(al.scan_time) = ?

    GROUP BY s.id
    ORDER BY s.full_name
    LIMIT ? OFFSET ?
  ");
  $stmt->bind_param("sii", $date, $limit, $offset);
}

$stmt->execute();

$records = [];
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $records[] = $row;
}

echo json_encode([
  "success" => true,
  "records" => $records,
  "pagination" => [
    "total" => (int)$total,
    "page" => $page,
    "limit" => $limit,
    "pages" => ceil($total / $limit)
  ]
]);
