<?php
require '../config/connect.php';

$sql = "SELECT * "  .
 "FROM tables t " .
 "LEFT JOIN orders o ON t.tbl_id = o.tbl_id ".
 "and  o.ord_status = 'unpaid';";

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
                "tbl_status" => $row["tbl_status"]
            ],
            "order" => [
                "ord_id" => $row["ord_id"],
                "tbl_id" => $row["tbl_id"],
                "ord_total_price" => $row["ord_total_price"],
                "ord_status" => $row["ord_status"],
                "ord_created_at" => $row["ord_created_at"],
                "ord_paid_at" => $row["ord_paid_at"]
            ]
        ];
    }
}

echo json_encode($data);
$conn->close();
?>
