<?php
require "../db/db.php";

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';

$sql = "
    SELECT 
        r.id,
        r.card_uid,
        r.status,
        r.issue_date,
        s.full_name,
        s.student_id
    FROM rfid_cards r
    JOIN students s ON s.id = r.student_id
    WHERE 1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (s.full_name LIKE ? OR r.card_uid LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($status !== "all") {
    $sql .= " AND r.status = ?";
    $params[] = $status;
    $types .= "s";
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$cards = [];
while ($row = $result->fetch_assoc()) {
    $cards[] = $row;
}

echo json_encode([
    "cards" => $cards,
    "count" => count($cards)
]);
