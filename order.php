<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT * FROM orders ORDER BY order_id DESC";


        // if (isset($_GET['post_id'])) {
        //     $post_id = $_GET['post_id'];

        //     $sql = "SELECT * FROM post 
        //     INNER JOIN users ON post.user_id = users.user_id 
        //     WHERE post.post_id = :post_id
        //     ORDER BY post.post_id DESC";
        // }



        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($carts);
        }


        break;

    case "POST":
        $order = json_decode(file_get_contents('php://input'), true);

        $order_customer_name = $order['order_customer_name'];
        $amount = $order['amount'];
        $created_at = date('Y-m-d');
        $status = 'Pending'; // Assuming you have a default status

        try {
            $conn->beginTransaction();

            // Insert the order into the orders table
            $sql = "INSERT INTO orders (order_customer_name, amount, created_at, status) 
                        VALUES (:order_customer_name, :amount, :created_at, :status)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':order_customer_name', $order_customer_name);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            // Get the last inserted order ID
            $order_id = $conn->lastInsertId();

            // Insert each product in the order_products table
            $sql = "INSERT INTO order_products (order_id, product_id, quantity, created_at) 
                        VALUES (:order_id, :product_id, :quantity, :created_at)";
            $stmt = $conn->prepare($sql);


            $sql2 = "UPDATE cart 
            SET isPaid = 1
            WHERE cart_id = :cart_id";

            $stmt2 = $conn->prepare($sql2);

            $sql3 = "UPDATE products SET stocks = stocks - :quantity WHERE product_id = :product_id";
            $stmt3 = $conn->prepare($sql3);

            $type = 'Stock Out';
            $sql4 = "INSERT INTO stocks (product_id, quantity, created_at, stock_type) VALUES (:product_id, :quantity, :created_at, :stock_type)";
            $stmt4 = $conn->prepare($sql4);

            $sql5 = "INSERT INTO notifications (notification_message, created_at) VALUES (:notification_message, :created_at)";
            $stmt5 = $conn->prepare($sql5);

            // check if the stocks of the product is less than 50 then notify the admin to restock of that product 
            $sql6 = "SELECT * FROM products WHERE product_id = :product_id";
            $stmt6 = $conn->prepare($sql6);

            foreach ($order['products'] as $product) {
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $product['product_id']);
                $stmt->bindParam(':quantity', $product['quantity']);
                $stmt->bindParam(':created_at', $created_at);

                $stmt->execute();

                $stmt2->bindParam(':cart_id', $product['cart_id']);
                $stmt2->execute();

                $stmt3->bindParam(':quantity', $product['quantity']);
                $stmt3->bindParam(':product_id', $product['product_id']);
                $stmt3->execute();

                $stmt4->bindParam(':product_id', $product['product_id']);
                $stmt4->bindParam(':quantity', $product['quantity']);
                $stmt4->bindParam(':created_at', $created_at);
                $stmt4->bindParam(':stock_type', $type);
                $stmt4->execute();


                $stmt6->bindParam(':product_id', $product['product_id']);
                $stmt6->execute();

                $product = $stmt6->fetch(PDO::FETCH_ASSOC);

                if ($product['stocks'] < $product['stock_limit']) {
                    $notification_message = "Product " . $product['product_name'] . " with ID " . $product['product_id'] . " is running out of stock. Please restock";
                    $stmt5->bindParam(':notification_message', $notification_message);
                    $stmt5->bindParam(':created_at', $created_at);
                    $stmt5->execute();
                }
            }

            $conn->commit();

            $response = [
                "status" => "success",
                "message" => "Order successfully placed"
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            $response = [
                "status" => "error",
                "message" => "Failed to place order"
            ];
        }

        echo json_encode($response);
        break;

    case "PUT":
        $carts = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE orders 
        SET status = :status
        WHERE order_id = :order_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':order_id', $carts->order_id);
        $stmt->bindParam(':status', $carts->status);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "order_id updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "order_id update failed"
            ];
        }

        break;
    case "DELETE":
        $carts = json_decode(file_get_contents('php://input'));
        $sql = "DELETE FROM carts WHERE cart_id = :cart_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':cart_id', $carts->cart_id);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "carts deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "carts delete failed"
            ];
        }

        echo json_encode($response);
        break;
}
