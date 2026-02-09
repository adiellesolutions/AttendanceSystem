<?php
require "../db/db.php";

$ids = $_POST['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    http_response_code(400);
    exit("No cards selected");
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmt = $conn->prepare(
    "UPDATE rfid_cards SET status='inactive' WHERE id IN ($placeholders)"
);
$stmt->bind_param($types, ...$ids);
$stmt->execute();

echo "success";
