<?php
require "../db/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed");
}

/* BASIC USER DATA */
$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";
$role     = $_POST["role"] ?? "";
$status   = $_POST["status"] ?? "active";

if ($username === "" || $password === "" || $role === "") {
    http_response_code(400);
    exit("Missing required fields");
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

/* START TRANSACTION */
$conn->begin_transaction();

try {
    /* INSERT USER */
    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, status)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $username, $hashed, $role, $status);
    $stmt->execute();

    $user_id = $stmt->insert_id;

    /* STUDENT */
    if ($role === "student") {
        $student_id_input = $_POST["student_id"] ?? "";
        $full_name        = $_POST["student_full_name"] ?? "";
        $email            = $_POST["student_email"] ?? "";

        if ($student_id_input === "" || $full_name === "") {
            throw new Exception("Missing student information");
        }

        $stmt = $conn->prepare(
            "INSERT INTO students (user_id, student_id, full_name, email)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "isss",
            $user_id,
            $student_id_input,
            $full_name,
            $email
        );
        $stmt->execute();

        $student_db_id = $stmt->insert_id;

        /* GUARDIAN */
        $guardian_name    = $_POST["guardian_full_name"] ?? "";
        $guardian_email   = $_POST["guardian_email"] ?? "";
        $guardian_contact = $_POST["guardian_contact_no"] ?? null;

        if ($guardian_name === "" || $guardian_email === "") {
            throw new Exception("Guardian information is required");
        }

        $stmt = $conn->prepare(
            "INSERT INTO guardians (student_id, full_name, email, contact_no)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "isss",
            $student_db_id,
            $guardian_name,
            $guardian_email,
            $guardian_contact
        );
        $stmt->execute();

        /* RFID CARD */
        $card_uid    = $_POST["card_uid"] ?? "";
        $card_status = $_POST["card_status"] ?? "active";

        if ($card_uid === "") {
            throw new Exception("RFID UID is required for students");
        }

        $stmt = $conn->prepare(
            "INSERT INTO rfid_cards (student_id, card_uid, status, issue_date)
             VALUES (?, ?, ?, CURDATE())"
        );
        $stmt->bind_param(
            "iss",
            $student_db_id,
            $card_uid,
            $card_status
        );
        $stmt->execute();
    }

    /* TEACHER */
    if ($role === "teacher") {
        $stmt = $conn->prepare(
            "INSERT INTO teachers (user_id, teacher_id, full_name, email)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "isss",
            $user_id,
            $_POST["teacher_id"],
            $_POST["teacher_full_name"],
            $_POST["teacher_email"]
        );
        $stmt->execute();
    }

    /* COMMIT EVERYTHING */
    $conn->commit();
    echo "success";

} catch (Exception $e) {
    /* ROLLBACK ON ANY FAILURE */
    $conn->rollback();
    http_response_code(400);
    echo $e->getMessage();
}
