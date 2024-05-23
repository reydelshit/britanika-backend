<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        $sql = "SELECT 
        MONTHNAME(created_at) AS name, 
        COUNT(*) AS total
    FROM 
        order_range
    GROUP BY 
        MONTH(created_at), 
        MONTHNAME(created_at)
    ORDER BY 
        MONTH(created_at)";


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
        }


        break;
}
