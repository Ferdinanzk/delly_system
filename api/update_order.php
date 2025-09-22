<?php
include 'db_connection.php';
$order_id = (int)$_GET['id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {
        $order_number = $_POST['order_number'];
        // ... (other POST variables)
        $status = $_POST['status'];
        
        $stmt_order = $conn->prepare("UPDATE orders SET order_number = ?, remarks = ?, total_price = ?, shipment_method = ?, shipment_date = ?, status = ? WHERE id = ?");
        $stmt_order->bind_param("ssdsssi", $order_number, $remarks, $total_price, $shipment_method, $shipment_date, $status, $order_id);
        $stmt_order->execute();
        
        $conn->query("DELETE FROM order_items WHERE order_id = $order_id");

        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, amount) VALUES (?, ?, ?)");
        if (!empty($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $index => $product_id) {
                // ... (loop to insert items)
            }
        }
        $conn->commit();
        // Corrected: Absolute path for redirect
        header("Location: /orders.php?page=$page&success=update");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        // ... (error handling)
    }
}
// ... (code to fetch existing order data)
include 'header.php';
?>
<main class="container">
    <div class="form-container">
        <h1>更新訂單</h1>
        <!-- Corrected: Absolute path for form action -->
        <form action="/update_order.php?id=<?php echo $order_id; ?>&page=<?php echo $page; ?>" method="POST">
            <!-- Form content here -->
            <div class="btn-container">
                <!-- Corrected: Absolute path for return link -->
                <a href="/orders.php?page=<?php echo $page; ?>" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-submit">儲存變更</button>
            </div>
        </form>
    </div>
</main>
<script>
    // JS for dynamic rows and price calculation
</script>
<?php include 'footer.php'; ?>
