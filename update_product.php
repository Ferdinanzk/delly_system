<?php
include 'db_connection.php'; // Include the database connection
include 'header.php';

$message = '';
$product = null;

// 1. Get Product ID from URL and validate
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
} else {
    // If no ID is provided, redirect or show an error
    header("Location: products.php");
    exit();
}

// 2. Handle Form Submission (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $p_name = $_POST['p_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $weight = $_POST['weight'] ?? '';
    $id = $_POST['id'] ?? 0;

    // Ensure the ID from the form matches the one in the URL
    if ($id == $product_id) {
        if (!empty($p_name) && !empty($category) && is_numeric($price)) {
            // Use prepared statements to prevent SQL injection
            $stmt = $conn->prepare("UPDATE products SET p_name = ?, category = ?, description = ?, price = ?, weight = ? WHERE id = ?");
            // 'sssisi' denotes the types: string, string, string, integer, string, integer
            $stmt->bind_param("sssisi", $p_name, $category, $description, $price, $weight, $id);

            if ($stmt->execute()) {
                // Redirect back to the products page on success
                header("Location: products.php?page=" . ($_GET['page'] ?? 1));
                exit();
            } else {
                $message = "更新失敗：" . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "請填寫所有必填欄位。";
        }
    } else {
        $message = "ID不匹配，更新失敗。";
    }
}

// 3. Fetch Existing Product Data (GET Request)
$stmt = $conn->prepare("SELECT id, p_name, category, description, price, weight FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $product = $result->fetch_assoc();
} else {
    // If product not found, display an error
    echo "<main class='container'><p>找不到該產品。</p></main>";
    include 'footer.php';
    exit();
}
$stmt->close();
$conn->close();
?>

<!-- Add custom styles for the form -->
<style>
    :root {
        --primary-color: #007bff;
        --light-gray: #f8f9fa;
        --medium-gray: #e9ecef;
        --dark-gray: #343a40;
        --text-color: #212529;
        --border-color: #ced4da;
        --form-shadow: 0 8px 25px rgba(0,0,0,0.07);
    }

    .container {
        max-width: 800px;
    }
    
    .page-header h1 {
        font-size: 2.5rem;
        color: var(--dark-gray);
        font-weight: 700;
        margin-bottom: 2.5rem;
        text-align: center;
    }

    .form-container {
        background: #fff;
        padding: 3rem;
        border-radius: 15px;
        box-shadow: var(--form-shadow);
    }
    
    .form-group {
        margin-bottom: 1.75rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--dark-gray);
    }
    
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        font-size: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn {
        width: 100%;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 14px 24px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .btn-submit {
        background-color: var(--primary-color);
        color: #fff !important;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }
    
    .btn-submit:hover {
        transform: translateY(-3px);
        background-color: #0069d9;
        box-shadow: 0 7px 20px rgba(0, 123, 255, 0.3);
    }
    
    .btn-return {
        background-color: var(--medium-gray);
        color: var(--dark-gray) !important;
        text-decoration: none;
    }
    
    .btn-return:hover {
        background-color: #d3d9df;
        transform: translateY(-3px);
    }
    
    .message {
        text-align: center;
        margin-bottom: 1.5rem;
        color: #dc3545;
        font-weight: 500;
    }
</style>

<main class="container">
    <div class="page-header">
        <h1>更新產品</h1>
    </div>

    <div class="form-container">
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($product): ?>
        <form action="update_product.php?id=<?php echo $product_id; ?>&page=<?php echo ($_GET['page'] ?? 1); ?>" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
            
            <div class="form-group">
                <label for="p_name">產品名稱</label>
                <input type="text" id="p_name" name="p_name" class="form-control" value="<?php echo htmlspecialchars($product['p_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category">分類</label>
                <input type="text" id="category" name="category" class="form-control" value="<?php echo htmlspecialchars($product['category']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">價格 (NTD)</label>
                <input type="number" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="weight">重量</label>
                <input type="text" id="weight" name="weight" class="form-control" value="<?php echo htmlspecialchars($product['weight']); ?>">
            </div>
            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-actions">
                <a href="products.php?page=<?php echo ($_GET['page'] ?? 1); ?>" class="btn btn-return">返回列表</a>
                <button type="submit" class="btn btn-submit">更新產品</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>

