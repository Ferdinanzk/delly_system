<?php
include 'db_connection.php';
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_name = $_POST['p_name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $weight = $_POST['weight'];
    
    $stmt = $conn->prepare("INSERT INTO products (p_name, category, description, price, weight) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $p_name, $category, $description, $price, $weight);
    
    if ($stmt->execute()) {
        header("Location: /products.php?success=add");
        exit();
    } else {
        $message = '<div class="alert alert-danger">新增產品失敗：' . $conn->error . '</div>';
    }
    $stmt->close();
}
$conn->close();
include 'header.php';
?>
<main class="container">
    <div class="form-container">
        <h1>新增產品</h1>
        <?php echo $message; ?>
        <!-- Corrected: Absolute path for form action -->
        <form action="/add_product.php" method="POST">
            <div class="form-group">
                <label for="p_name">產品名稱</label>
                <input type="text" id="p_name" name="p_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="category">類別</label>
                <input type="text" id="category" name="category" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="price">價格</label>
                <input type="number" id="price" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="weight">重量</label>
                <input type="text" id="weight" name="weight" class="form-control">
            </div>
            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="btn-container">
                <!-- Corrected: Absolute path for return link -->
                <a href="/products.php" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-submit">新增產品</button>
            </div>
        </form>
    </div>
</main>
<?php include 'footer.php'; ?>
