<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT * FROM carts ORDER BY cart_id DESC";


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
        $carts = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO carts (cart_image, cart_number, price, color, created_at, type, availability_status) VALUES (:cart_image, :cart_number, :price, :color, :created_at, :type, :availability_status)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');
        $stmt->bindParam(':cart_image', $carts->cart_image);
        $stmt->bindParam(':cart_number', $carts->cart_number);
        $stmt->bindParam(':price', $carts->price);
        $stmt->bindParam(':color', $carts->color);
        $stmt->bindParam(':type', $carts->type);
        $stmt->bindParam(':availability_status', $carts->availability_status);
        $stmt->bindParam(':created_at',  $created_at);



        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "cart successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "cart failed"
            ];
        }




        echo json_encode($response);
        break;

    case "PUT":
        $carts = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE carts 
        SET availability_status = :availability_status
        WHERE cart_id = :cart_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':cart_id', $carts->cart_id);
        $stmt->bindParam(':availability_status', $carts->availability_status);

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
