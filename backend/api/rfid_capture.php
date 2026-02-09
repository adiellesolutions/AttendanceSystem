<?php
header("Content-Type: application/json");

$uid = $_POST['uid'] ?? '';

if ($uid) {
    file_put_contents(__DIR__ . "/last_uid.txt", $uid);
}

echo json_encode([
    "ok" => true,
    "uid" => $uid
]);
