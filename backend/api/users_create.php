<?php
session_start();
header('Content-Type: application/json');

// if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
//   http_response_code(403);
//   echo json_encode(['message' => 'Forbidden']);
//   exit;
// }

require_once __DIR__ . '/../config/db.php';

/**
 * This endpoint now supports BOTH:
 * 1) JSON body (application/json)  -> your old payload structure
 * 2) FormData (multipart/form-data) -> for profile photo upload + flat fields
 *
 * Profile photo is optional and saved to: /uploads/profile/
 * DB column used: users.profile_photo_path
 */

// -------------------------
// Detect request type
// -------------------------
$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
$isJson = stripos($contentType, 'application/json') !== false;

// Parse input
$input = [];
if ($isJson) {
  $input = json_decode(file_get_contents("php://input"), true) ?? [];
}

// Account fields
$username = $isJson ? trim($input['username'] ?? '') : trim($_POST['username'] ?? '');
$password = $isJson ? (string)($input['password'] ?? '') : (string)($_POST['password'] ?? '');
$role     = $isJson ? ($input['role'] ?? '') : ($_POST['role'] ?? '');
$status   = $isJson ? ($input['status'] ?? 'active') : ($_POST['status'] ?? 'active');

$allowedRoles  = ['admin','teacher','student'];
$allowedStatus = ['active','inactive'];

if ($username === '' || $password === '' || !in_array($role, $allowedRoles, true) || !in_array($status, $allowedStatus, true)) {
  http_response_code(400);
  echo json_encode(['message' => 'Invalid input (account fields).']);
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// -------------------------
// Optional profile photo upload (FormData only)
// -------------------------
$profilePath = null;

try {
  if (!$isJson && !empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['profile_photo']['tmp_name'];
    $name = $_FILES['profile_photo']['name'];
    $size = (int)$_FILES['profile_photo']['size'];

    // max 2MB
    if ($size > 2 * 1024 * 1024) {
      throw new Exception("Profile photo too large (max 2MB).");
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowedExt = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowedExt, true)) {
      throw new Exception("Invalid profile photo type. Use JPG/PNG/WEBP.");
    }

    // Project root: backend/api -> backend -> project root
    $projectRoot = realpath(__DIR__ . '/../../');
    if ($projectRoot === false) {
      throw new Exception("Cannot resolve project root path.");
    }

    $uploadDir = $projectRoot . '/uploads/profile';
    if (!is_dir($uploadDir)) {
      if (!mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new Exception("Failed to create upload directory.");
      }
    }

    $safeFile = 'u_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = $uploadDir . '/' . $safeFile;

    if (!move_uploaded_file($tmp, $dest)) {
      throw new Exception("Failed to upload profile photo.");
    }

    // Save relative path in DB
    $profilePath = 'uploads/profile/' . $safeFile;
  }

  // -------------------------
  // DB Operations
  // -------------------------

  // PDO
  if (isset($pdo) && $pdo instanceof PDO) {
    $pdo->beginTransaction();

    // users (now includes profile_photo_path)
    $stmt = $pdo->prepare("
      INSERT INTO users (username, password_hash, role, status, profile_photo_path)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$username, $hash, $role, $status, $profilePath]);
    $userId = (int)$pdo->lastInsertId();

    // student flow
    if ($role === 'student') {
      if ($isJson) {
        $st = $input['student'] ?? [];
        $gd = $input['guardian'] ?? [];
        $cd = $input['card'] ?? [];

        $student_id = trim($st['student_id'] ?? '');
        $full_name  = trim($st['full_name'] ?? '');
        $email      = isset($st['email']) ? trim((string)$st['email']) : null;

        $g_full_name = trim($gd['full_name'] ?? '');
        $g_email     = trim($gd['email'] ?? '');
        $g_contact   = isset($gd['contact_no']) ? trim((string)$gd['contact_no']) : null;

        $card_uid    = trim($cd['card_uid'] ?? '');
        $card_status = $cd['status'] ?? 'active';
      } else {
        // FormData fields
        $student_id = trim($_POST['student_id'] ?? '');
        $full_name  = trim($_POST['student_full_name'] ?? '');
        $email      = isset($_POST['student_email']) ? trim((string)$_POST['student_email']) : null;

        $g_full_name = trim($_POST['guardian_full_name'] ?? '');
        $g_email     = trim($_POST['guardian_email'] ?? '');
        $g_contact   = isset($_POST['guardian_contact_no']) ? trim((string)$_POST['guardian_contact_no']) : null;

        $card_uid    = trim($_POST['card_uid'] ?? '');
        $card_status = $_POST['card_status'] ?? 'active';
      }

      $allowedCardStatus = ['active','inactive','lost'];

      if ($student_id === '' || $full_name === '' || $g_full_name === '' || $g_email === '' || $card_uid === '') {
        throw new Exception("Student requires student info + guardian info + RFID UID.");
      }
      if (!in_array($card_status, $allowedCardStatus, true)) {
        throw new Exception("Invalid RFID card status.");
      }

      // students
      $stmt = $pdo->prepare("INSERT INTO students (user_id, student_id, full_name, email) VALUES (?, ?, ?, ?)");
      $stmt->execute([$userId, $student_id, $full_name, ($email === '' ? null : $email)]);
      $studentPk = (int)$pdo->lastInsertId();

      // guardians
      $stmt = $pdo->prepare("INSERT INTO guardians (student_id, full_name, contact_no, email) VALUES (?, ?, ?, ?)");
      $stmt->execute([$studentPk, $g_full_name, ($g_contact === '' ? null : $g_contact), $g_email]);

      // rfid_cards
      $stmt = $pdo->prepare("INSERT INTO rfid_cards (card_uid, student_id, status) VALUES (?, ?, ?)");
      $stmt->execute([$card_uid, $studentPk, $card_status]);
    }

    // teacher flow
    if ($role === 'teacher') {
      if ($isJson) {
        $tc = $input['teacher'] ?? [];
        $teacher_id = trim($tc['teacher_id'] ?? '');
        $full_name  = trim($tc['full_name'] ?? '');
        $email      = isset($tc['email']) ? trim((string)$tc['email']) : null;
      } else {
        $teacher_id = trim($_POST['teacher_id'] ?? '');
        $full_name  = trim($_POST['teacher_full_name'] ?? '');
        $email      = isset($_POST['teacher_email']) ? trim((string)$_POST['teacher_email']) : null;
      }

      if ($teacher_id === '' || $full_name === '') {
        throw new Exception("Teacher requires Teacher ID and Full Name.");
      }

      $stmt = $pdo->prepare("INSERT INTO teachers (user_id, teacher_id, full_name, email) VALUES (?, ?, ?, ?)");
      $stmt->execute([$userId, $teacher_id, $full_name, ($email === '' ? null : $email)]);
    }

    $pdo->commit();
    echo json_encode(['ok' => true, 'message' => 'Created successfully', 'profile_photo_path' => $profilePath]);
    exit;
  }

  // mysqli
  if (isset($conn) && $conn instanceof mysqli) {
    $conn->begin_transaction();

    // users (now includes profile_photo_path)
    $stmt = $conn->prepare("
      INSERT INTO users (username, password_hash, role, status, profile_photo_path)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $username, $hash, $role, $status, $profilePath);
    $stmt->execute();
    $userId = (int)$stmt->insert_id;
    $stmt->close();

    if ($role === 'student') {
      if ($isJson) {
        $st = $input['student'] ?? [];
        $gd = $input['guardian'] ?? [];
        $cd = $input['card'] ?? [];

        $student_id = trim($st['student_id'] ?? '');
        $full_name  = trim($st['full_name'] ?? '');
        $email      = isset($st['email']) ? trim((string)$st['email']) : null;

        $g_full_name = trim($gd['full_name'] ?? '');
        $g_email     = trim($gd['email'] ?? '');
        $g_contact   = isset($gd['contact_no']) ? trim((string)$gd['contact_no']) : null;

        $card_uid    = trim($cd['card_uid'] ?? '');
        $card_status = $cd['status'] ?? 'active';
      } else {
        $student_id = trim($_POST['student_id'] ?? '');
        $full_name  = trim($_POST['student_full_name'] ?? '');
        $email      = isset($_POST['student_email']) ? trim((string)$_POST['student_email']) : null;

        $g_full_name = trim($_POST['guardian_full_name'] ?? '');
        $g_email     = trim($_POST['guardian_email'] ?? '');
        $g_contact   = isset($_POST['guardian_contact_no']) ? trim((string)$_POST['guardian_contact_no']) : null;

        $card_uid    = trim($_POST['card_uid'] ?? '');
        $card_status = $_POST['card_status'] ?? 'active';
      }

      $allowedCardStatus = ['active','inactive','lost'];

      if ($student_id === '' || $full_name === '' || $g_full_name === '' || $g_email === '' || $card_uid === '') {
        throw new Exception("Student requires student info + guardian info + RFID UID.");
      }
      if (!in_array($card_status, $allowedCardStatus, true)) {
        throw new Exception("Invalid RFID card status.");
      }

      $stmt = $conn->prepare("INSERT INTO students (user_id, student_id, full_name, email) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("isss", $userId, $student_id, $full_name, $email);
      $stmt->execute();
      $studentPk = (int)$stmt->insert_id;
      $stmt->close();

      $stmt = $conn->prepare("INSERT INTO guardians (student_id, full_name, contact_no, email) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("isss", $studentPk, $g_full_name, $g_contact, $g_email);
      $stmt->execute();
      $stmt->close();

      $stmt = $conn->prepare("INSERT INTO rfid_cards (card_uid, student_id, status) VALUES (?, ?, ?)");
      $stmt->bind_param("sis", $card_uid, $studentPk, $card_status);
      $stmt->execute();
      $stmt->close();
    }

    if ($role === 'teacher') {
      if ($isJson) {
        $tc = $input['teacher'] ?? [];
        $teacher_id = trim($tc['teacher_id'] ?? '');
        $full_name  = trim($tc['full_name'] ?? '');
        $email      = isset($tc['email']) ? trim((string)$tc['email']) : null;
      } else {
        $teacher_id = trim($_POST['teacher_id'] ?? '');
        $full_name  = trim($_POST['teacher_full_name'] ?? '');
        $email      = isset($_POST['teacher_email']) ? trim((string)$_POST['teacher_email']) : null;
      }

      if ($teacher_id === '' || $full_name === '') {
        throw new Exception("Teacher requires Teacher ID and Full Name.");
      }

      $stmt = $conn->prepare("INSERT INTO teachers (user_id, teacher_id, full_name, email) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("isss", $userId, $teacher_id, $full_name, $email);
      $stmt->execute();
      $stmt->close();
    }

    $conn->commit();
    echo json_encode(['ok' => true, 'message' => 'Created successfully', 'profile_photo_path' => $profilePath]);
    exit;
  }

  throw new Exception("No DB connection found in db.php");

} catch (Throwable $e) {
  if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
  if (isset($conn) && $conn instanceof mysqli) $conn->rollback();

  // Optional: if insert failed and we uploaded a photo, you may want to delete it
  // but not required. (kept simple)

  http_response_code(400);
  echo json_encode(['message' => $e->getMessage()]);
}
