<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/connect.php';

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method == "GET") {
    // GET all tables or a specific table
    if (isset($_GET['tbl_id'])) {
        $tbl_id = intval($_GET['tbl_id']);
        $sql = "SELECT * FROM tables WHERE tbl_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tbl_id);
    } else {
        $sql = "SELECT * FROM tables";
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

// POST: Retrieve specific field in tables
// elseif ($request_method == "POST") {
//     $input = json_decode(file_get_contents("php://input"), true);

//     //Update table status to 1
//     if (isset($input['tbl_id'])) {
//         $tbl_id = $input['tbl_id'];

//         $sql = "Update tables set tbl_status = 1 where tbl_id = $tbl_id";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("i", $tbl_id);

//         if ($stmt->execute()) {
//             echo json_encode(["success" => true, "message" => "Table status changed to Occupied"]);
//         } else {
//             echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
//         }

//         $stmt->close();
//     } else {
//         echo json_encode(["success" => false, "message" => "Missing fields"]);
//     }
// }

// $conn->close();
// ?>
