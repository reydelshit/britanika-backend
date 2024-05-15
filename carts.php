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
        $sql = "UPDATE post 
        SET post_context = :post_context,
            post_image = :post_image,
            project_location = :project_location,
            project_name = :project_name,
            email_phone = :email_phone,
            starting_price = :starting_price,
            close_until = :close_until
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
        $sql = "DELETE FROM post WHERE post_id = :post_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':post_id', $carts->post_id);

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
