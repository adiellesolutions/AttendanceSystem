<?php
require "../db/db.php";

$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit("Invalid card");
}

$stmt = $conn->prepare("UPDATE rfid_cards SET status='inactive' WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo "success";
