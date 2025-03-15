<?php
date_default_timezone_set("Asia/Ho_Chi_Minh"); // Set to Vietnam time

header("Content-Type: application/json");

require "../config/connect.php";
require "../vendor/autoload.php";
use Firebase\JWT\JWT;

$secret_key = "mysecretkey"; 
$refresh_secret = "myrefreshkey"; 

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
                $access_created = time();
                $access_exp = time() + (60 * 60);
                $access_payload = [
                    "acc_id" => $row["acc_id"], // Keep as integer
                    "staff_id" => $row["staff_id"],
                    "acc_username" => $username,
                    "created_at" => strval($access_created),
                    "exp" => strval($access_exp)
                ];
                $access_token = JWT::encode($access_payload, $secret_key, "HS256");

                // Generate Refresh Token (valid for 8 hours)
                $refresh_created = time();
                $refresh_exp = time() + (8 * 60 * 60);
                $refresh_payload = [
                    "id" => $row["acc_id"], // Keep as integer
                    "username" => $username,
                    "created_at" => strval($refresh_created),
                    "exp" => strval($refresh_exp)
                ];
                $refresh_token = JWT::encode($refresh_payload, $refresh_secret, "HS256");

                // Store refresh token and expiry in database
                $update_stmt = $conn->prepare(
                    "REPLACE INTO account_sessions (acc_id, session_token, session_created_at, session_expires_at) 
                    VALUES (?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?))"
                );
                $update_stmt->bind_param("issi", $row["acc_id"], $refresh_token, $refresh_created, $refresh_exp);
                $update_stmt->execute();

                // Fetch stored timestamps from MySQL
                $query = $conn->prepare("SELECT UNIX_TIMESTAMP(session_created_at) AS created_at, 
                                        UNIX_TIMESTAMP(session_expires_at) AS expires_in 
                                        FROM account_sessions 
                                        WHERE acc_id = ? 
                                        ORDER BY session_created_at DESC 
                                        LIMIT 1");
                $query->bind_param("i", $row["acc_id"]);
                $query->execute();
                $session_result = $query->get_result();

                if ($session_row = $session_result->fetch_assoc()) {
                    $access_created = strval($session_row["created_at"]);
                    $access_exp = strval($session_row["expires_in"]);
                }

                echo json_encode([
                    "success" => true,
                    "access_token" => $access_token,
                    "refresh_token" => $refresh_token,
                    "access_created_at" => date("Y-m-d H:i:s", $access_created), // Convert to readable format
                    "expires_in" => date("Y-m-d H:i:s", $access_exp) // Convert to readable format
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
