<?php
header("Content-Type: application/json");
require "config.php";
require "vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "your_secret_key";
$refresh_secret = "your_refresh_secret_key";

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["refresh_token"])) {
    $refresh_token = $data["refresh_token"];

    $stmt = $conn->prepare("SELECT id, username FROM users WHERE refresh_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $refresh_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Generate new Access Token
        $access_exp = time() + (60 * 60);
        $access_payload = [
            "id" => $row["id"],
            "username" => $row["username"],
            "exp" => $access_exp
        ];
        $access_token = JWT::encode($access_payload, $secret_key, "HS256");

        echo json_encode([
            "success" => true,
            "access_token" => $access_token,
            "expires_in" => $access_exp
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid or expired refresh token"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No refresh token provided"]);
}
$conn->close();
?>
