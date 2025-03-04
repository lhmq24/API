<?php
header("Content-Type: application/json");
require "config.php";
require "vendor/autoload.php";
use Firebase\JWT\JWT;

$secret_key = "your_secret_key"; 
$refresh_secret = "your_refresh_secret_key"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["username"]) && isset($data["password"])) {
        $username = $data["username"];
        $password = $data["password"];

        $stmt = $conn->prepare("SELECT acc_id, staff_id, acc_password FROM accounts WHERE acc_username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["acc_password"])) {

                // Generate Access Token (expires in 1 hour)
                $access_exp = time() + (60 * 60);
                $access_payload = [
                    "acc_id" => $row["acc_id"],
                    "staff_id" => $row["staff_id"],
                    "acc_username" => $username,
                    "exp" => $access_exp
                ];
                $access_token = JWT::encode($access_payload, $secret_key, "HS256");

                // Generate Refresh Token (valid for 7 days)
                $refresh_exp = time() + (7 * 24 * 60 * 60);
                $refresh_payload = [
                    "id" => $row["id"],
                    "username" => $username,
                    "exp" => $refresh_exp
                ];
                $refresh_token = JWT::encode($refresh_payload, $refresh_secret, "HS256");

                // Store refresh token and expiry in database
                $update_stmt = $conn->prepare("UPDATE users SET refresh_token = ?, token_expiry = FROM_UNIXTIME(?) WHERE id = ?");
                $update_stmt->bind_param("ssi", $refresh_token, $refresh_exp, $row["id"]);
                $update_stmt->execute();

                echo json_encode([
                    "success" => true,
                    "access_token" => $access_token,
                    "refresh_token" => $refresh_token,
                    "expires_in" => $access_exp
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid credentials"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "User not found"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
    }
}
$conn->close();
?>
