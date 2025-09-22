<?php
include 'db_connection.php';
$message = '';
$product = null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if (!isset($_GET['id'])) {
    header("Location: /products.php");
    exit();
}
$id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_name = $_POST['p_name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $weight = $_POST['weight'];

    $stmt = $conn->prepare("UPDATE products SET p_name = ?, category = ?, description = ?, price = ?, weight = ? WHERE id = ?");
    $stmt->bind_param("sssisi", $p_name, $category, $description, $price, $weight, $id);

    if ($stmt->execute()) {
        // Corrected: Absolute path for redirect
        header("Location: /products.php?page=$page&success=update");
        exit();
    } else {
        $message = '<div class="alert alert-danger">更新產品失敗：' . $conn->error . '</div>';
    }
    $stmt->close();
}

// Fetch existing product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    header("Location: /products.php");
    exit();
}
$stmt->close();
$conn->close();
include 'header.php';
?>
<main class="container">
    <div class="form-container">
        <h1>更新產品</h1>
        <?php echo $message; ?>
        <!-- Corrected: Absolute path for form action -->
        <form action="/update_product.php?id=<?php echo $id; ?>&page=<?php echo $page; ?>" method="POST">
            <div class="form-group">
                <label for="p_name">產品名稱</label>
                <input type="text" id="p_name" name="p_name" class="form-control" value="<?php echo htmlspecialchars($product['p_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category">類別</label>
                <input type="text" id="category" name="category" class="form-control" value="<?php echo htmlspecialchars($product['category']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">價格</label>
                <input type="number" id="price" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
            </div>
             <div class="form-group">
                <label for="weight">重量</label>
                <input type="text" id="weight" name="weight" class="form-control" value="<?php echo htmlspecialchars($product['weight']); ?>">
            </div>
            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="btn-container">
                <!-- Corrected: Absolute path for return link -->
                <a href="/products.php?page=<?php echo $page; ?>" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-submit">儲存變更</button>
            </div>
        </form>
    </div>
</main>
<?php include 'footer.php'; ?>
