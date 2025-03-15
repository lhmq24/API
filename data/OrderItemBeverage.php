<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/connect.php';

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method == "GET") {
    if (isset($_GET["tbl_id"])) {
        $tbl_id = $_GET["tbl_id"];

        $sql = "SELECT 
                    b.bev_id, b.bev_name, 
                    oi.item_id, oi.ord_id, oi.item_quantity, oi.item_current_price, oi.item_status
                FROM order_items oi
                JOIN beverages b ON oi.bev_id = b.bev_id
                JOIN orders o ON oi.ord_id = o.ord_id
                WHERE o.tbl_id = ? AND o.ord_status = 0";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tbl_id);
    } 

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "beverages" => [
                "bev_id" => $row["bev_id"],
                "bev_name" => $row["bev_name"]
            ],
            "order_items" => [
                "item_id" => $row["item_id"],
                "ord_id" => $row["ord_id"],
                "item_quantity" => $row["item_quantity"],
                "item_current_price" => $row["item_current_price"],
                "item_status" => $row["item_status"]
            ]
        ];
    }

    echo json_encode($data);
    $stmt->close();
    $conn->close();
}
?>
