<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT * FROM products ORDER BY product_id DESC";


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
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($products);
        }


        break;

    case "POST":
        $products = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO products (product_name, product_price, availability_status, created_at, product_image, stocks) VALUES (:product_name, :product_price, :availability_status, :created_at, :product_image, :stocks)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');
        $stmt->bindParam(':product_name', $products->product_name);
        $stmt->bindParam(':product_price', $products->product_price);
        $stmt->bindParam(':availability_status', $products->availability_status);
        $stmt->bindParam(':product_image', $products->product_image);
        $stmt->bindParam(':created_at',  $created_at);
        $stmt->bindParam(':stocks', $products->stocks);





        if ($stmt->execute()) {

            $product_id = $conn->lastInsertId();

            $sql2 = "INSERT INTO stocks (product_id, quantity, created_at, stock_type) VALUES (:product_id, :quantity, :created_at, :stock_type)";
            $type = "Initial Stock";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':product_id', $product_id);
            $stmt2->bindParam(':quantity', $products->stocks);
            $stmt2->bindParam(':created_at',  $created_at);
            $stmt2->bindParam(':stock_type', $type);


            $stmt2->execute();
            $response = [
                "status" => "success",
                "message" => "product successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "product failed"
            ];
        }




        echo json_encode($response);
        break;

    case "PUT":
        $products = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE products 
        SET availability_status = :availability_status
        WHERE product_id = :product_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':product_id', $products->product_id);
        $stmt->bindParam(':availability_status', $products->availability_status);


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
        $products = json_decode(file_get_contents('php://input'));
        $sql = "DELETE FROM products WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $products->product_id);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "products deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "products delete failed"
            ];
        }

        echo json_encode($response);
        break;
}
