<?php
require "../db/db.php";
require_once __DIR__ . "/send_attendance_email.php";

header("Content-Type: application/json");

/* =========================
   INPUT VALIDATION
========================= */
$uid = trim($_POST['uid'] ?? '');

if ($uid === "") {
    echo json_encode(["ok" => false, "error" => "Invalid UID"]);
    exit;
}

/* =========================
   1️⃣ FIND RFID CARD
========================= */
$stmt = $conn->prepare(
    "SELECT 
        r.student_id,
        s.full_name
     FROM rfid_cards r
     JOIN students s ON r.student_id = s.id
     WHERE r.card_uid = ? AND r.status = 'active'"
);
$stmt->bind_param("s", $uid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["ok" => false, "error" => "Card not registered"]);
    exit;
}

$student = $res->fetch_assoc();
$student_id   = (int)$student['student_id'];
$student_name = $student['full_name'];

/* =========================
   2️⃣ DETERMINE IN / OUT
========================= */
$stmt = $conn->prepare(
    "SELECT scan_type
     FROM attendance_logs
     WHERE student_id = ?
     ORDER BY scan_time DESC
     LIMIT 1"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$last = $stmt->get_result()->fetch_assoc();

$scan_type = ($last && $last['scan_type'] === 'entry') ? 'exit' : 'entry';

/* =========================
   3️⃣ DETERMINE STATUS
========================= */
$status = "present";

if ($scan_type === "entry") {
    $currentTime = date("H:i:s");

    // ⏰ Adjust cutoff time here
    if ($currentTime > "08:00:00") {
        $status = "late";
    }
}

/* =========================
   4️⃣ INSERT ATTENDANCE LOG
========================= */
$stmt = $conn->prepare(
    "INSERT INTO attendance_logs
     (student_id, card_uid, scan_type, scan_time, status)
     VALUES (?, ?, ?, NOW(), ?)"
);
$stmt->bind_param("isss", $student_id, $uid, $scan_type, $status);
$stmt->execute();

$scanTime = date("Y-m-d H:i:s");

/* =========================
   5️⃣ FETCH EMAILS
========================= */
$stmt = $conn->prepare(
    "SELECT 
        s.email AS student_email,
        g.email AS guardian_email
     FROM students s
     LEFT JOIN guardians g ON g.student_id = s.id
     WHERE s.id = ?"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$studentEmail   = null;
$guardianEmails = [];

while ($row = $result->fetch_assoc()) {
    if (!$studentEmail && $row['student_email']) {
        $studentEmail = $row['student_email'];
    }
    if (!empty($row['guardian_email'])) {
        $guardianEmails[] = $row['guardian_email'];
    }
}

/* =========================
   6️⃣ SEND EMAIL (SAFE)
========================= */
try {
    sendAttendanceEmail(
        $student_name,
        strtoupper($scan_type),   // IN / OUT
        $scanTime,
        $studentEmail,
        $guardianEmails
    );
} catch (Exception $e) {
    // ❗ Do nothing — attendance must still succeed
}

/* =========================
   7️⃣ RESPONSE TO ESP32
========================= */
echo json_encode([
    "ok"        => true,
    "student"  => $student_name,
    "scan_type"=> $scan_type,
    "status"   => $status,
    "time"     => $scanTime
]);
