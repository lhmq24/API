<?php
require '../config/connect.php';

$status = isset($_GET['status']) ? (int)$_GET['status'] : null;

if ($status === 0) {
    // Get all tables and their unpaid orders (if available)
    $sql = "SELECT t.tbl_id, t.floor_number, t.tbl_number, t.tbl_capacity, t.tbl_status, t.tbl_image,
                   o.ord_id, o.ord_total_price, o.ord_status, o.ord_created_at, o.ord_paid_at
            FROM tables t
            LEFT JOIN orders o 
            ON t.tbl_id = o.tbl_id 
            AND o.ord_status = 0";
} elseif ($status === 1) {
    // Get all paid orders and their associated tables
    $sql = "SELECT t.tbl_id, t.floor_number, t.tbl_number, t.tbl_capacity, t.tbl_status, t.tbl_image,
                   o.ord_id, o.ord_total_price, o.ord_status, o.ord_created_at, o.ord_paid_at
            FROM orders o
            INNER JOIN tables t 
            ON o.tbl_id = t.tbl_id 
            WHERE o.ord_status = 1";
} else {
    // Invalid or missing status, return an empty response
    echo json_encode([]);
    exit;
}

$result = $conn->query($sql);
$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "table" => [
                "tbl_id" => $row["tbl_id"],
                "floor_number" => $row["floor_number"],
                "tbl_number" => $row["tbl_number"],
                "tbl_capacity" => $row["tbl_capacity"],
                "tbl_status" => $row["tbl_status"],
                "tbl_image" => $row["tbl_image"]
            ],
            "order" => ($row["ord_id"] !== null) ? [
                "ord_id" => $row["ord_id"],
                "tbl_id" => $row["tbl_id"],
                "ord_total_price" => $row["ord_total_price"],
                "ord_status" => $row["ord_status"],
                "ord_created_at" => $row["ord_created_at"],
                "ord_paid_at" => $row["ord_paid_at"]
            ] : null
        ];
    }
}

echo json_encode($data);
$conn->close();
?>
