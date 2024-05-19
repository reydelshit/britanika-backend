<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        $sql = "SELECT stocks.*, products.product_name, products.stocks, products.product_image FROM stocks INNER JOIN products ON products.product_id = stocks.product_id ORDER BY created_at DESC";


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
        }


        break;


    case "PUT":
        $cart = json_decode(file_get_contents('php://input'));

        $sql = "UPDATE products SET stocks = stocks + :stocks WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bindParam(':product_id', $cart->product_id);
        $stmt->bindParam(':stocks', $cart->stocks);

        if ($stmt->execute()) {

            $stockType = 'Stock In';
            $sql2 = "INSERT INTO stocks (product_id, quantity, created_at, stock_type) VALUES (:product_id, :quantity, :created_at, :stock_type)";
            $stmt2 = $conn->prepare($sql2);

            $stmt2->bindParam(':product_id', $cart->product_id);
            $stmt2->bindParam(':quantity', $cart->stocks);
            $stmt2->bindParam(':created_at', $updated_at);
            $stmt2->bindParam(':stock_type', $stockType);

            $stmt2->execute();

            $response = [
                "status" => "success",
                "message" => "products updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "products update failed"
            ];
        }

        echo json_encode($response);
        break;

    case "DELETE":
        $user = json_decode(file_get_contents('php://input'));
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':user_id', $user->user_id);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "user deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "user delete failed"
            ];
        }

        echo json_encode($response);
        break;
}
