<?php
header("Content-Type: application/json");

$uid = @file_get_contents(__DIR__ . "/last_uid.txt");

echo json_encode(["uid" => trim($uid)]);
