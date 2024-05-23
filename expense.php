<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();
date_default_timezone_set('Asia/Manila');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['expenses'])) {
            $sql = "SELECT * FROM expenses ORDER BY created_at DESC";
        }

        if (isset($_GET['expense_with_products'])) {
            $expense_id = $_GET['expense_id'];

            $sql = "SELECT * FROM expense_product WHERE expense_id = :expense_id";
        }

        if (isset($_GET['expense_single'])) {
            $expense_id = $_GET['expense_id'];

            $sql = "SELECT * FROM expenses WHERE expense_id = :expense_id";
        }


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($expense_id)) {
                $stmt->bindParam(':expense_id', $expense_id);
            }

            if (isset($_GET['expense_single'])) {
                $stmt->bindParam(':expense_id', $expense_id);
            }



            $stmt->execute();
            $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($expenses);
        }

        break;

    case "POST":
        $expense = json_decode(file_get_contents('php://input'));

        $sql = "INSERT INTO expenses (expense_id, department, expense_date, receipt_image, purchaser_name, created_at, total) 
                    VALUES (NULL, :department, :expense_date, :receipt_image, :purchaser_name, :created_at, :total)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');

        $stmt->bindParam(':department', $expense->department);
        $stmt->bindParam(':expense_date', $expense->expense_date);
        $stmt->bindParam(':receipt_image', $expense->receipt_image);
        $stmt->bindParam(':purchaser_name', $expense->purchaser_name);
        $stmt->bindParam(':total', $expense->total);
        $stmt->bindParam(':created_at', $created_at);

        $expenseInserted = $stmt->execute();

        if ($expenseInserted) {
            $lastExpenseId = $conn->lastInsertId();

            $productsInserted = true;

            foreach ($expense->purchasedProducts as $product) {
                $sqlProduct = "INSERT INTO expense_product (expense_product_id, expense_id, quantity, product_name, unit_price, created_at) 
                                   VALUES (NULL, :expense_id, :quantity, :product_name, :unit_price, :created_at)";
                $stmtProduct = $conn->prepare($sqlProduct);

                $stmtProduct->bindParam(':expense_id', $lastExpenseId);
                $stmtProduct->bindParam(':quantity', $product->quantity);
                $stmtProduct->bindParam(':product_name', $product->productName);
                $stmtProduct->bindParam(':unit_price', $product->price);
                $stmtProduct->bindParam(':created_at', $created_at);

                $productInserted = $stmtProduct->execute();

                if (!$productInserted) {
                    $productsInserted = false;
                    break;
                }
            }

            if ($productsInserted) {
                $response = [
                    "status" => "success",
                    "message" => "Expense and products added successfully"
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Expense added successfully, but failed to add products"
                ];
            }
        } else {
            $response = [
                "status" => "error",
                "message" => "Failed to add expense"
            ];
        }

        echo json_encode($response);
        break;
}
