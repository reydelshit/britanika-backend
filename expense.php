<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $sql = "SELECT orders.*, products.product_name, products.product_price, order_products.quantity FROM orders INNER JOIN order_products ON order_products.order_id = orders.order_id INNER JOIN products ON products.product_id = order_products.product_id ORDER BY orders.order_id DESC";

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($carts);
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
