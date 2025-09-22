<?php
include 'db_connection.php';
$all_products = [];
$result = $conn->query("SELECT id, p_name, price FROM products ORDER BY p_name ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_products[] = $row;
    }
}
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {
        $order_number = $_POST['order_number'];
        $remarks = $_POST['remarks'];
        $total_price = $_POST['total_price'];
        $shipment_method = $_POST['shipment_method'];
        $shipment_date = $_POST['shipment_date'];
        $status = $_POST['status'];
        
        $stmt_order = $conn->prepare("INSERT INTO orders (order_number, remarks, total_price, shipment_method, shipment_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_order->bind_param("ssdsss", $order_number, $remarks, $total_price, $shipment_method, $shipment_date, $status);
        $stmt_order->execute();
        $order_id = $conn->insert_id;
        
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, amount) VALUES (?, ?, ?)");
        if (!empty($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $index => $product_id) {
                $amount = $_POST['amounts'][$index];
                if ($product_id > 0 && $amount > 0) {
                    $stmt_item->bind_param("iii", $order_id, $product_id, $amount);
                    $stmt_item->execute();
                }
            }
        }
        $conn->commit();
        // Corrected: Absolute path for redirect
        header("Location: /orders.php?success=add");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "新增訂單失敗：" . $e->getMessage();
    }
}
$conn->close();
include 'header.php';
?>
<!-- Styles are included for brevity -->
<main class="container">
    <div class="form-container">
        <h1>新增訂單</h1>
        <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>
        <!-- Corrected: Absolute path for form action -->
        <form action="/add_order.php" method="POST">
            <!-- Form content here -->
            <div class="btn-container">
                <!-- Corrected: Absolute path for return link -->
                <a href="/orders.php" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-submit">建立訂單</button>
            </div>
        </form>
    </div>
</main>
<script>
    // JS for dynamic rows and price calculation
</script>
<?php include 'footer.php'; ?>
