<?php
require "../db/db.php";

header("Content-Type: text/plain");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed");
}

/* ---------------- BASIC USER DATA ---------------- */
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$role     = $_POST["role"] ?? "";
$status   = $_POST["status"] ?? "active";

if ($username === "" || $password === "" || $role === "") {
    http_response_code(400);
    exit("Missing account information");
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

/* ---------------- START TRANSACTION ---------------- */
$conn->begin_transaction();

try {
    /* ---------- INSERT USER ---------- */
    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, status, must_reset_password)
         VALUES (?, ?, ?, ?, 1)"
    );
    
    if (!$stmt) throw new Exception("Prepare failed (users): " . $conn->error);

    $stmt->bind_param("ssss", $username, $hashed, $role, $status);
    if (!$stmt->execute()) throw new Exception("Execute failed (users): " . $stmt->error);

    $user_id = $conn->insert_id; // ✅ correct

    /* ================= STUDENT ================= */
    if ($role === "student") {

        $student_id_input = trim($_POST["student_id"] ?? "");
        $full_name        = trim($_POST["student_full_name"] ?? "");
        $email            = trim($_POST["student_email"] ?? "");

        if ($student_id_input === "" || $full_name === "") {
            throw new Exception("Student information is incomplete");
        }

        // ✅ bind_param needs VARIABLES, not expressions
        $student_email_db = ($email !== "") ? $email : null;

        /* INSERT STUDENT */
        $stmt = $conn->prepare(
            "INSERT INTO students (user_id, student_id, full_name, email)
             VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) throw new Exception("Prepare failed (students): " . $conn->error);

        $stmt->bind_param("isss", $user_id, $student_id_input, $full_name, $student_email_db);
        if (!$stmt->execute()) throw new Exception("Execute failed (students): " . $stmt->error);

        $student_db_id = $conn->insert_id; // ✅ correct

        /* ---------- GUARDIAN ---------- */
        $guardian_name    = trim($_POST["guardian_full_name"] ?? "");
        $guardian_email   = trim($_POST["guardian_email"] ?? "");
        $guardian_contact = trim($_POST["guardian_contact_no"] ?? "");

        if ($guardian_name === "" || $guardian_email === "") {
            throw new Exception("Guardian name and email are required");
        }

        $guardian_contact_db = ($guardian_contact !== "") ? $guardian_contact : null;

        $stmt = $conn->prepare(
            "INSERT INTO guardians (student_id, full_name, email, contact_no)
             VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) throw new Exception("Prepare failed (guardians): " . $conn->error);

        $stmt->bind_param("isss", $student_db_id, $guardian_name, $guardian_email, $guardian_contact_db);
        if (!$stmt->execute()) throw new Exception("Execute failed (guardians): " . $stmt->error);

        /* ---------- RFID CARD ---------- */
        $card_uid    = trim($_POST["card_uid"] ?? "");
        $card_status = $_POST["card_status"] ?? "active";

        if ($card_uid === "") {
            throw new Exception("RFID card UID is required");
        }

        $stmt = $conn->prepare(
            "INSERT INTO rfid_cards (student_id, card_uid, status, issue_date)
             VALUES (?, ?, ?, CURDATE())"
        );
        if (!$stmt) throw new Exception("Prepare failed (rfid_cards): " . $conn->error);

        $stmt->bind_param("iss", $student_db_id, $card_uid, $card_status);
        if (!$stmt->execute()) throw new Exception("Execute failed (rfid_cards): " . $stmt->error);
    }

    /* ================= TEACHER ================= */
    if ($role === "teacher") {

        $teacher_id   = trim($_POST["teacher_id"] ?? "");
        $teacher_name = trim($_POST["teacher_full_name"] ?? "");
        $teacher_mail = trim($_POST["teacher_email"] ?? "");

        if ($teacher_id === "" || $teacher_name === "") {
            throw new Exception("Teacher information is incomplete");
        }

        // ✅ variable for nullable email
        $teacher_email_db = ($teacher_mail !== "") ? $teacher_mail : null;

        $stmt = $conn->prepare(
            "INSERT INTO teachers (user_id, teacher_id, full_name, email)
             VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) throw new Exception("Prepare failed (teachers): " . $conn->error);

        $stmt->bind_param("isss", $user_id, $teacher_id, $teacher_name, $teacher_email_db);
        if (!$stmt->execute()) throw new Exception("Execute failed (teachers): " . $stmt->error);
    }

    /* ---------------- COMMIT ---------------- */
    $conn->commit();
    echo "success";

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);

    $msg = $e->getMessage();

    // Optional: friendlier duplicate handling
    if (stripos($msg, "Duplicate entry") !== false) {
        echo "Duplicate entry detected. Username/ID already exists.";
    } else {
        echo $msg;
    }
}
