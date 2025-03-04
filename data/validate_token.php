<?php
header("Content-Type: application/json");
require "vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "your_secret_key"; // Use the same secret key as in login.php

$headers = getallheaders();
if (isset($headers["Authorization"])) {
    $token = str_replace("Bearer ", "", $headers["Authorization"]);

    try {
        $decoded = JWT::decode($token, new Key($secret_key, "HS256"));
        echo json_encode(["success" => true, "message" => "Token is valid", "user" => $decoded]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Invalid token"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Token required"]);
}
?>
