<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

$q      = trim($_GET['q'] ?? '');
$role   = trim($_GET['role'] ?? '');
$status = trim($_GET['status'] ?? '');

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = max(1, min(100, (int)($_GET['limit'] ?? 5))); // ✅ default 5 per page
$offset = ($page - 1) * $limit;

$allowedRoles  = ['admin','teacher','student'];
$allowedStatus = ['active','inactive'];

try {
  // ---------- PDO ----------
  if (isset($pdo) && $pdo instanceof PDO) {
    $where = [];
    $params = [];

    if ($role !== '' && in_array($role, $allowedRoles, true)) {
      $where[] = "u.role = ?";
      $params[] = $role;
    }
    if ($status !== '' && in_array($status, $allowedStatus, true)) {
      $where[] = "u.status = ?";
      $params[] = $status;
    }
    if ($q !== '') {
      $where[] = "(u.username LIKE ? OR COALESCE(t.full_name, s.full_name, '') LIKE ? OR COALESCE(s.student_id,'') LIKE ? OR COALESCE(t.teacher_id,'') LIKE ?)";
      $like = "%{$q}%";
      array_push($params, $like, $like, $like, $like);
    }

    $baseFrom = "
      FROM users u
      LEFT JOIN students s ON s.user_id = u.id
      LEFT JOIN teachers t ON t.user_id = u.id
    ";

    $whereSql = $where ? (" WHERE " . implode(" AND ", $where)) : "";

    // ✅ total count
    $countSql = "SELECT COUNT(*) AS total " . $baseFrom . $whereSql;
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)($stmt->fetchColumn() ?? 0);

    // ✅ paged results
    $sql = "
      SELECT
        u.id,
        u.username,
        u.role,
        u.status,
        u.profile_photo_path,
        u.last_login_at,
        COALESCE(t.full_name, s.full_name, u.username) AS full_name,
        CASE
          WHEN u.role = 'student' THEN COALESCE(s.student_id,'')
          WHEN u.role = 'teacher' THEN COALESCE(t.teacher_id,'')
          ELSE CONCAT('ADM-', LPAD(u.id, 4, '0'))
        END AS display_id,
        CASE
          WHEN u.role = 'student' THEN COALESCE(s.student_id,'')
          WHEN u.role = 'teacher' THEN COALESCE(t.teacher_id,'')
          ELSE 'All Departments'
        END AS associated_label
      " . $baseFrom . $whereSql . "
      ORDER BY u.created_at DESC
      LIMIT {$limit} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode([
      'ok' => true,
      'users' => $rows,
      'page' => $page,
      'limit' => $limit,
      'total' => $total
    ]);
    exit;
  }

  // ---------- mysqli ----------
  if (isset($conn) && $conn instanceof mysqli) {
    $where = [];
    $params = [];
    $types = "";

    if ($role !== '' && in_array($role, $allowedRoles, true)) {
      $where[] = "u.role = ?";
      $params[] = $role;
      $types .= "s";
    }
    if ($status !== '' && in_array($status, $allowedStatus, true)) {
      $where[] = "u.status = ?";
      $params[] = $status;
      $types .= "s";
    }
    if ($q !== '') {
      $where[] = "(u.username LIKE ? OR COALESCE(t.full_name, s.full_name, '') LIKE ? OR COALESCE(s.student_id,'') LIKE ? OR COALESCE(t.teacher_id,'') LIKE ?)";
      $like = "%{$q}%";
      array_push($params, $like, $like, $like, $like);
      $types .= "ssss";
    }

    $baseFrom = "
      FROM users u
      LEFT JOIN students s ON s.user_id = u.id
      LEFT JOIN teachers t ON t.user_id = u.id
    ";

    $whereSql = $where ? (" WHERE " . implode(" AND ", $where)) : "";

    // total
    $countSql = "SELECT COUNT(*) AS total " . $baseFrom . $whereSql;
    $stmt = $conn->prepare($countSql);
    if ($types !== "") $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $total = (int)($res->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    // results
    $sql = "
      SELECT
        u.id,
        u.username,
        u.role,
        u.status,
        u.profile_photo_path,
        u.last_login_at,
        COALESCE(t.full_name, s.full_name, u.username) AS full_name,
        CASE
          WHEN u.role = 'student' THEN COALESCE(s.student_id,'')
          WHEN u.role = 'teacher' THEN COALESCE(t.teacher_id,'')
          ELSE CONCAT('ADM-', LPAD(u.id, 4, '0'))
        END AS display_id,
        CASE
          WHEN u.role = 'student' THEN COALESCE(s.student_id,'')
          WHEN u.role = 'teacher' THEN COALESCE(t.teacher_id,'')
          ELSE 'All Departments'
        END AS associated_label
      " . $baseFrom . $whereSql . "
      ORDER BY u.created_at DESC
      LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    // bind params + limit/offset
    if ($types !== "") {
      $types2 = $types . "ii";
      $params2 = array_merge($params, [$limit, $offset]);
      $stmt->bind_param($types2, ...$params2);
    } else {
      $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode([
      'ok' => true,
      'users' => $rows,
      'page' => $page,
      'limit' => $limit,
      'total' => $total
    ]);
    exit;
  }

  throw new Exception("No DB connection found in db.php");

} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['message' => $e->getMessage()]);
}
