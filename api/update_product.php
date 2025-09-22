<?php
include 'db_connection.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /products.php");
    exit();
}
$id = (int)$_GET['id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_name = $_POST['p_name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = (int)$_POST['price'];
    $weight = $_POST['weight'];

    $stmt = $conn->prepare("UPDATE products SET p_name = ?, category = ?, description = ?, price = ?, weight = ? WHERE id = ?");
    $stmt->bind_param("sssisi", $p_name, $category, $description, $price, $weight, $id);

    if ($stmt->execute()) {
        header("Location: /products.php?page=$page&success=update");
        exit();
    } else {
        $error = "更新失敗：" . $stmt->error;
    }
    $stmt->close();
}

$result = $conn->query("SELECT * FROM products WHERE id = $id");
if ($result->num_rows === 0) {
    header("Location: /products.php");
    exit();
}
$product = $result->fetch_assoc();
$conn->close();

include 'header.php';
?>
<main class="container">
    <div class="form-container">
        <h1>更新產品</h1>
        <?php if(isset($error)): ?>
            <p style="color: red; text-align: center;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="/update_product.php?id=<?php echo $id; ?>&page=<?php echo $page; ?>" method="POST">
            <div class="form-group">
                <label for="p_name">產品名稱</label>
                <input type="text" class="form-control" id="p_name" name="p_name" value="<?php echo htmlspecialchars($product['p_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category">分類</label>
                <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">價格</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="form-group">
                <label for="weight">重量</label>
                <input type="text" class="form-control" id="weight" name="weight" value="<?php echo htmlspecialchars($product['weight']); ?>">
            </div>
            <div class="form-group">
                <label for="description">描述</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="btn-container">
                <a href="/products.php?page=<?php echo $page; ?>" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-primary">儲存變更</button>
            </div>
        </form>
    </div>
</main>
<?php include 'footer.php'; ?>

