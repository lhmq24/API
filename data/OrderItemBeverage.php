<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/connect.php';

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method == "GET") {
    //TH 1: Lay danh sach nuoc uong trong order item theo ban (Khong co floor_number)
    if(count($_GET) == 1 && isset($_GET["tbl_number"])) {
        $tbl_number = $_GET["tbl_number"];

        $sql = "SELECT 
                    b.bev_id, b.bev_name, 
                    oi.item_id, oi.ord_id, oi.item_quantity, oi.item_current_price, oi.item_status
                FROM order_items oi
                JOIN beverages b ON oi.bev_id = b.bev_id
                JOIN orders o ON oi.ord_id = o.ord_id
                WHERE o.tbl_number = ? AND  o.ord_status = 0";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tbl_number);
    } elseif (count($_GET) == 2 && isset($_GET["tbl_number"]) && isset($_GET["floor_number"])){
        //TH 2: Lay danh sach nuoc uong trong order item theo ban (Co floor_number)

        $floor_number = $_GET["floor_number"];
        $tbl_number = $_GET["tbl_number"];

        $sql = "SELECT 
            b.*,
            oi.*
        FROM order_items oi
        JOIN orders o ON oi.ord_id = o.ord_id
        JOIN tables t ON o.tbl_id = t.tbl_id
        JOIN beverages b ON oi.bev_id = b.bev_id
        WHERE t.floor_number = ? AND t.tbl_number = ? AND  o.ord_status = 0
        ORDER BY o.ord_id";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $floor_number, $tbl_number);
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
