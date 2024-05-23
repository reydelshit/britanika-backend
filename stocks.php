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

        if ($cart->type === 'In') {
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
        } else {
            $sql = "UPDATE products SET stocks = stocks - :stocks WHERE product_id = :product_id";
            $stmt = $conn->prepare($sql);
            $updated_at = date('Y-m-d');
            $created_at = date('Y-m-d');
            $stmt->bindParam(':product_id', $cart->product_id);
            $stmt->bindParam(':stocks', $cart->stocks);

            $sql5 = "INSERT INTO notifications (notification_message, created_at) VALUES (:notification_message, :created_at)";
            $stmt5 = $conn->prepare($sql5);


            // check if the stocks of the product is less than 50 then notify the admin to restock of that product 
            $sql6 = "SELECT * FROM products WHERE product_id = :product_id";
            $stmt6 = $conn->prepare($sql6);

            if ($stmt->execute()) {

                $stockType = 'Stock Out';
                $sql2 = "INSERT INTO stocks (product_id, quantity, created_at, stock_type) VALUES (:product_id, :quantity, :created_at, :stock_type)";
                $stmt2 = $conn->prepare($sql2);

                $stmt2->bindParam(':product_id', $cart->product_id);
                $stmt2->bindParam(':quantity', $cart->stocks);
                $stmt2->bindParam(':created_at', $updated_at);
                $stmt2->bindParam(':stock_type', $stockType);

                $stmt2->execute();



                $stmt6->bindParam(':product_id', $cart->product_id);
                $stmt6->execute();

                $product = $stmt6->fetch(PDO::FETCH_ASSOC);

                if ($product['stocks'] < $product['stock_limit']) {
                    $notification_message = "Product " . $product['product_name'] . " with ID " . $product['product_id'] . " is running out of stock. Please restock";
                    $stmt5->bindParam(':notification_message', $notification_message);
                    $stmt5->bindParam(':created_at', $created_at);
                    $stmt5->execute();
                }

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
