<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        $sql = "SELECT * FROM notifications ORDER BY created_at DESC";


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
        }


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
