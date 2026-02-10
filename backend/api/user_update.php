<?php
session_start();
require_once "../db/db.php";

header("Content-Type: application/json");

// 🔒 Admin only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing user ID"]);
    exit;
}

$userId   = (int)($_POST['id']);
$username = trim($_POST['username'] ?? "");
$status   = $_POST['status'] ?? "active";
$role     = $_POST['role'] ?? "";

if ($userId <= 0 || $username === "" || $role === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$password = $_POST['password'] ?? ""; // optional in edit

$conn->begin_transaction();

try {
    /* =========================
       USERS TABLE (username/status + optional password)
    ========================== */
    if ($password !== "") {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, status = ?, password = ? WHERE id = ?");
        if (!$stmt) throw new Exception("Prepare failed (users): " . $conn->error);
        $stmt->bind_param("sssi", $username, $status, $hashed, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, status = ? WHERE id = ?");
        if (!$stmt) throw new Exception("Prepare failed (users): " . $conn->error);
        $stmt->bind_param("ssi", $username, $status, $userId);
    }
    if (!$stmt->execute()) throw new Exception("Execute failed (users): " . $stmt->error);

    /* =========================
       STUDENT
    ========================== */
    if ($role === "student") {
        $student_id       = trim($_POST['student_id'] ?? "");
        $student_fullname = trim($_POST['student_full_name'] ?? "");
        $student_email    = trim($_POST['student_email'] ?? "");
        $student_email_db = ($student_email !== "") ? $student_email : null;

        $guardian_name    = trim($_POST['guardian_full_name'] ?? "");
        $guardian_email   = trim($_POST['guardian_email'] ?? "");
        $guardian_contact = trim($_POST['guardian_contact_no'] ?? "");
        $guardian_contact_db = ($guardian_contact !== "") ? $guardian_contact : null;

        $card_uid    = trim($_POST['card_uid'] ?? "");
        $card_status = $_POST['card_status'] ?? "active";

        if ($student_id === "" || $student_fullname === "") {
            throw new Exception("Student info incomplete");
        }
        if ($guardian_name === "" || $guardian_email === "") {
            throw new Exception("Guardian name/email required");
        }
        if ($card_uid === "") {
            throw new Exception("Card UID required");
        }

        // Update student row (assumes it exists)
        $stmt = $conn->prepare("UPDATE students SET student_id = ?, full_name = ?, email = ? WHERE user_id = ?");
        if (!$stmt) throw new Exception("Prepare failed (students): " . $conn->error);
        $stmt->bind_param("sssi", $student_id, $student_fullname, $student_email_db, $userId);
        if (!$stmt->execute()) throw new Exception("Execute failed (students): " . $stmt->error);

        // Get student_db_id (needed for guardian/rfid)
        $stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
        if (!$stmt) throw new Exception("Prepare failed (students select): " . $conn->error);
        $stmt->bind_param("i", $userId);
        if (!$stmt->execute()) throw new Exception("Execute failed (students select): " . $stmt->error);
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) throw new Exception("Student record not found");
        $studentDbId = (int)$row['id'];

        // ✅ UPSERT guardian (if no row yet, insert)
        $stmt = $conn->prepare("
            INSERT INTO guardians (student_id, full_name, email, contact_no)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              full_name = VALUES(full_name),
              email = VALUES(email),
              contact_no = VALUES(contact_no)
        ");
        if (!$stmt) throw new Exception("Prepare failed (guardians upsert): " . $conn->error);
        $stmt->bind_param("isss", $studentDbId, $guardian_name, $guardian_email, $guardian_contact_db);
        if (!$stmt->execute()) throw new Exception("Execute failed (guardians upsert): " . $stmt->error);

        // ✅ UPSERT rfid_cards (needs UNIQUE(student_id) to work best)
        // If you don't have UNIQUE(student_id), add it:
        // ALTER TABLE rfid_cards ADD UNIQUE KEY uniq_student (student_id);
        $stmt = $conn->prepare("
            INSERT INTO rfid_cards (student_id, card_uid, status, issue_date)
            VALUES (?, ?, ?, CURDATE())
            ON DUPLICATE KEY UPDATE
              card_uid = VALUES(card_uid),
              status = VALUES(status)
        ");
        if (!$stmt) throw new Exception("Prepare failed (rfid upsert): " . $conn->error);
        $stmt->bind_param("iss", $studentDbId, $card_uid, $card_status);
        if (!$stmt->execute()) throw new Exception("Execute failed (rfid upsert): " . $stmt->error);
    }

    /* =========================
       TEACHER
    ========================== */
    if ($role === "teacher") {
        $teacher_id       = trim($_POST['teacher_id'] ?? "");
        $teacher_fullname = trim($_POST['teacher_full_name'] ?? "");
        $teacher_email    = trim($_POST['teacher_email'] ?? "");
        $teacher_email_db = ($teacher_email !== "") ? $teacher_email : null;

        if ($teacher_id === "" || $teacher_fullname === "") {
            throw new Exception("Teacher info incomplete");
        }

        $stmt = $conn->prepare("UPDATE teachers SET teacher_id = ?, full_name = ?, email = ? WHERE user_id = ?");
        if (!$stmt) throw new Exception("Prepare failed (teachers): " . $conn->error);
        $stmt->bind_param("sssi", $teacher_id, $teacher_fullname, $teacher_email_db, $userId);
        if (!$stmt->execute()) throw new Exception("Execute failed (teachers): " . $stmt->error);
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "User updated successfully"]);
} catch (Exception $e) {
    $conn->rollback();

    $msg = $e->getMessage();
    // friendlier duplicate detection
    if (stripos($msg, "Duplicate entry") !== false) {
        $msg = "Duplicate entry: username/ID/card UID already exists.";
    }

    http_response_code(400);
    echo json_encode(["success" => false, "message" => $msg]);
}
