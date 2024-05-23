<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT * FROM driving_range ORDER BY range_id DESC";


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
            $driving_range = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($driving_range);
        }


        break;

    case "POST":
        $driving_range = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO driving_range (range_image, range_number, price, color, created_at, type, availability_status) VALUES (:range_image, :range_number, :price, :color, :created_at, :type, :availability_status)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');
        $stmt->bindParam(':range_image', $driving_range->range_image);
        $stmt->bindParam(':range_number', $driving_range->range_number);
        $stmt->bindParam(':price', $driving_range->price);
        $stmt->bindParam(':color', $driving_range->color);
        $stmt->bindParam(':type', $driving_range->type);
        $stmt->bindParam(':availability_status', $driving_range->availability_status);
        $stmt->bindParam(':created_at',  $created_at);



        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "range successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "range failed"
            ];
        }




        echo json_encode($response);
        break;

    case "PUT":
        $driving_range = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE driving_range 
        SET availability_status = :availability_status
        WHERE range_id = :range_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':range_id', $driving_range->range_id);
        $stmt->bindParam(':availability_status', $driving_range->availability_status);

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
