<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/connect.php';

$request_method = $_SERVER["REQUEST_METHOD"];

//GET: Get all beverages
if ($request_method == "GET") {
    if (isset($_GET["bev_name"])) {
        $bev_name = "%" . $_GET["bev_name"] . "%"; 
        $sql = "SELECT * FROM beverages WHERE bev_name LIKE ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $bev_name);
    } else {
        $sql = "SELECT * FROM beverages";
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    $stmt->close();
    $conn->close();
}

//Put new beverage
if ($request_method == "PUT") {
    if(isset($_PUT["bev_name"])){
        $bev_id = $_PUT["bev_id"];
        $price_id = $_PUT["price_id"];
        $bev_name = $_PUT["bev_name"];

        $query("insert into beverages values (?,?,?);");
        $stmt = $conn->prepare($query);
        $stmt->bind_params("iis", $bev_id, $price_id, $bev_name);
        $stmt->execute();
    }
}
?>