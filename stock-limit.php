<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {


    case "PUT":
        $products = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE products 
        SET stock_limit = :stock_limit
        WHERE product_id = :product_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':product_id', $products->product_id);
        $stmt->bindParam(':stock_limit', $products->stock_limit);


        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "stock_limit updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "stock_limit update failed"
            ];
        }

        echo json_encode($response);

        break;
}
