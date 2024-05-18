<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT order_range.*, driving_range.range_number FROM order_range LEFT JOIN driving_range ON driving_range.range_id = order_range.range_id ORDER BY order_range.order_range_id DESC";

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $driving_range = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($driving_range);
        }


        break;

    case "POST":
        $order = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO order_range (customer_name, range_id, amount, created_at) VALUES (:customer_name, :range_id, :amount, :created_at)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');
        $stmt->bindParam(':customer_name', $order->customer_name);
        $stmt->bindParam(':range_id', $order->range_id);
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
        $driving_range = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE post 
        SET post_context = :post_context
        WHERE post_id = :post_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':post_id', $driving_range->post_id);
        $stmt->bindParam(':post_context', $driving_range->post_context);
        $stmt->bindParam(':post_image', $driving_range->post_image);
        $stmt->bindParam(':project_location', $driving_range->project_location);
        $stmt->bindParam(':project_name', $driving_range->project_name);
        $stmt->bindParam(':email_phone', $driving_range->email_phone);
        $stmt->bindParam(':starting_price', $driving_range->starting_price);
        $stmt->bindParam(':close_until', $driving_range->close_until);

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
        $driving_range = json_decode(file_get_contents('php://input'));
        $sql = "DELETE FROM driving_range WHERE range_id = :range_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':range_id', $driving_range->range_id);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "driving_range deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "driving_range delete failed"
            ];
        }

        echo json_encode($response);
        break;
}
