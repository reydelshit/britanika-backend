<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT order_cart.*, carts.cart_number FROM order_cart LEFT JOIN carts ON carts.cart_id = order_cart.cart_id ORDER BY order_cart.order_cart_id DESC";

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($carts);
        }


        break;

    case "POST":
        $order = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO order_cart (customer_name, cart_id, amount, created_at) VALUES (:customer_name, :cart_id, :amount, :created_at)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');
        $stmt->bindParam(':customer_name', $order->customer_name);
        $stmt->bindParam(':cart_id', $order->cart_id);
        $stmt->bindParam(':amount', $order->amount);
        $stmt->bindParam(':created_at', $created_at);

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
        $sql = "UPDATE post 
        SET post_context = :post_context
        WHERE post_id = :post_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':post_id', $carts->post_id);
        $stmt->bindParam(':post_context', $carts->post_context);
        $stmt->bindParam(':post_image', $carts->post_image);
        $stmt->bindParam(':project_location', $carts->project_location);
        $stmt->bindParam(':project_name', $carts->project_name);
        $stmt->bindParam(':email_phone', $carts->email_phone);
        $stmt->bindParam(':starting_price', $carts->starting_price);
        $stmt->bindParam(':close_until', $carts->close_until);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "post updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "post update failed"
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
