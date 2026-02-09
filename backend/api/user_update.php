<?php
session_start();
require_once "../db/db.php";

header("Content-Type: application/json");

// 🔒 Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

// required
if (!isset($_POST['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing user ID"
    ]);
    exit;
}

$userId  = (int) $_POST['id'];
$username = trim($_POST['username']);
$status   = $_POST['status'];
$role     = $_POST['role'];

$conn->begin_transaction();

try {

    /* =========================
       USERS TABLE
    ========================== */
    $stmt = $conn->prepare("
        UPDATE users
        SET username = ?, status = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $username, $status, $userId);
    $stmt->execute();

    /* =========================
       STUDENT
    ========================== */
    if ($role === "student") {

        // update students
        $stmt = $conn->prepare("
            UPDATE students
            SET student_id = ?, full_name = ?, email = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param(
            "sssi",
            $_POST['student_id'],
            $_POST['student_full_name'],
            $_POST['student_email'],
            $userId
        );
        $stmt->execute();

        // guardian
        $stmt = $conn->prepare("
            UPDATE guardians
            SET full_name = ?, email = ?, contact_no = ?
            WHERE student_id = (
                SELECT id FROM students WHERE user_id = ?
            )
        ");
        $stmt->bind_param(
            "sssi",
            $_POST['guardian_full_name'],
            $_POST['guardian_email'],
            $_POST['guardian_contact_no'],
            $userId
        );
        $stmt->execute();

        // RFID card
        $stmt = $conn->prepare("
            UPDATE rfid_cards
            SET card_uid = ?, status = ?
            WHERE student_id = (
                SELECT id FROM students WHERE user_id = ?
            )
        ");
        $stmt->bind_param(
            "ssi",
            $_POST['card_uid'],
            $_POST['card_status'],
            $userId
        );
        $stmt->execute();
    }

    /* =========================
       TEACHER
    ========================== */
    if ($role === "teacher") {

        $stmt = $conn->prepare("
            UPDATE teachers
            SET teacher_id = ?, full_name = ?, email = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param(
            "sssi",
            $_POST['teacher_id'],
            $_POST['teacher_full_name'],
            $_POST['teacher_email'],
            $userId
        );
        $stmt->execute();
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "User updated successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Update failed"
    ]);
}
