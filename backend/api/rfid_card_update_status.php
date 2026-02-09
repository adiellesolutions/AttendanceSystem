<?php
require "../db/db.php";

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$id || !$status) {
    http_response_code(400);
    exit("Missing data");
}

$stmt = $conn->prepare(
    "UPDATE rfid_cards SET status = ? WHERE id = ?"
);
$stmt->bind_param("si", $status, $id);
$stmt->execute();

echo "success";
