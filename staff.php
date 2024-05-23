<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['staff'])) {
            $sql = "SELECT * FROM users WHERE account_type = 'staff' ORDER BY user_id DESC";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
        }


        break;

    case "POST":
        $user = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO users (account_type, username, password, created_at, if_staff_type) VALUES (:account_type, :username, :password, :created_at, :if_staff_type)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $stmt->bindParam(':account_type', $user->account_type);
        $stmt->bindParam(':username', $user->username);
        $stmt->bindParam(':password', $user->password);
        $stmt->bindParam(':created_at', $created_at);
        $stmt->bindParam(':if_staff_type', $user->if_staff_type);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "User created successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User creation failed"
            ];
        }

        echo json_encode($response);
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
