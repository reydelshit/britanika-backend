<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT * FROM dishes ORDER BY dish_id DESC";


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
            $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($dishes);
        }


        break;

    case "POST":
        $dishes = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO dishes (dish_name, dish_price, availability_status, created_at, dish_image) VALUES (:dish_name, :dish_price, :availability_status, :created_at, :dish_image)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');
        $stmt->bindParam(':dish_name', $dishes->dish_name);
        $stmt->bindParam(':dish_price', $dishes->dish_price);
        $stmt->bindParam(':availability_status', $dishes->availability_status);
        $stmt->bindParam(':dish_image', $dishes->dish_image);
        $stmt->bindParam(':created_at',  $created_at);



        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "dish successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "dish failed"
            ];
        }




        echo json_encode($response);
        break;

    case "PUT":
        $dishes = json_decode(file_get_contents('php://input'));
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

        $stmt->bindParam(':post_id', $dishes->post_id);
        $stmt->bindParam(':post_context', $dishes->post_context);
        $stmt->bindParam(':post_image', $dishes->post_image);
        $stmt->bindParam(':project_location', $dishes->project_location);
        $stmt->bindParam(':project_name', $dishes->project_name);
        $stmt->bindParam(':email_phone', $dishes->email_phone);
        $stmt->bindParam(':starting_price', $dishes->starting_price);
        $stmt->bindParam(':close_until', $dishes->close_until);

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
        $dishes = json_decode(file_get_contents('php://input'));
        $sql = "DELETE FROM post WHERE post_id = :post_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':post_id', $dishes->post_id);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "dishes deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "dishes delete failed"
            ];
        }

        echo json_encode($response);
        break;
}
