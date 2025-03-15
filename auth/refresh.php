<?php

date_default_timezone_set("Asia/Ho_Chi_Minh"); // Set to Vietnam time

header("Content-Type: application/json");

require "../config/connect.php";
require "../vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "mysecretkey";
$refresh_secret = "myrefreshkey";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy refresh token từ Authorization header
    $headers = getallheaders();
    if (!isset($headers["Authorization"])) {
        echo json_encode(["success" => false, "message" => "Missing Authorization header"]);
        exit();
    }

    $authHeader = $headers["Authorization"];
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        echo json_encode(["success" => false, "message" => "Invalid Authorization format"]);
        exit();
    }

    $refresh_token = $matches[1];

    try {
        // Giải mã refresh token
        $decoded = JWT::decode($refresh_token, new Key($refresh_secret, "HS256"));
        $acc_id = $decoded->id;
        $username = $decoded->username;

        // Kiểm tra token có trong database không
        $stmt = $conn->prepare("SELECT acc_id FROM account_sessions WHERE acc_id = ? AND session_token = ?");
        $stmt->bind_param("is", $acc_id, $refresh_token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(["success" => false, "message" => "Invalid refresh token"]);
            exit();
        }

        // Tạo access token mới (hết hạn sau 1 giờ)
        $access_created = time();
        $access_exp = $access_created + (60 * 60);
        $access_payload = [
            "acc_id" => $acc_id,
            "acc_username" => $username,
            "created_at" => $access_created,
            "exp" => $access_exp
        ];
        $access_token = JWT::encode($access_payload, $secret_key, "HS256");

        echo json_encode([
            "success" => true,
            "access_token" => $access_token,
            "access_created_at" => date("Y-m-d H:i:s", $access_created),
            "expires_in" => date("Y-m-d H:i:s", $access_exp)
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Invalid token: " . $e->getMessage()]);
    }
}

$conn->close();
?>
