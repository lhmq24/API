<?php
header("Content-Type: application/json");
require "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$secret_key = "mysecretkey"; 

// Hàm lấy Authorization header
function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        // Cho Apache/Nginx
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

// Hàm lấy JWT từ Bearer Token
function getBearerToken() {
    $headers = getAuthorizationHeader();
    if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
    }
    return null;
}

// Chỉ chấp nhận request dạng POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method Not Allowed"]);
    exit();
}

// Lấy token từ header
$token = getBearerToken();

if (!$token) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Missing access token"]);
    exit();
}

try {
    $decoded = JWT::decode($token, new Key($secret_key, "HS256"));
    echo json_encode(["status" => true, "message" => "Token is valid"]);
} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Token expired"]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Invalid token"]);
}
?>
