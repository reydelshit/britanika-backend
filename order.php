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
        $order = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO orders (order_customer_name, amount, created_at, status, dish_id, quantity) VALUES (:order_customer_name, :amount, :created_at, :status, :dish_id, :quantity)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');
        $stmt->bindParam(':order_customer_name', $order->order_customer_name);
        $stmt->bindParam(':amount', $order->amount);
        $stmt->bindParam(':created_at', $created_at);
        $stmt->bindParam(':status', $order->status);
        $stmt->bindParam(':dish_id', $order->dish_id);
        $stmt->bindParam(':quantity', $order->quantity);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Order successfully placed"
            ];
        } else {
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
