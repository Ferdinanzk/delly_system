<?php
include 'db_connection.php'; // Include the database connection
$message = ''; // To store success or error messages

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- FORM DATA PROCESSING ---
    $p_name = $_POST['p_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $weight = $_POST['weight'] ?? '';

    // Basic validation
    if (!empty($p_name) && !empty($category) && is_numeric($price)) {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO products (p_name, category, description, price, weight) VALUES (?, ?, ?, ?, ?)");
        // 'sssis' denotes the types of the variables: string, string, string, integer, string
        $stmt->bind_param("sssis", $p_name, $category, $description, $price, $weight);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            // Redirect back to the products page on success
            header("Location: products.php");
            exit();
        } else {
            $message = "錯誤： " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "請填寫所有必填欄位。";
    }
    $conn->close();
}
?>

<?php include 'header.php'; ?>

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
        max-width: 800px; /* Optimal width for a form */
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
        <h1>新增產品</h1>
    </div>

    <div class="form-container">
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="add_product.php" method="post">
            <div class="form-group">
                <label for="p_name">產品名稱</label>
                <input type="text" id="p_name" name="p_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="category">分類</label>
                <input type="text" id="category" name="category" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="price">價格 (NTD)</label>
                <input type="number" id="price" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="weight">重量</label>
                <input type="text" id="weight" name="weight" class="form-control">
            </div>
            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>
            <div class="form-actions">
                <a href="products.php" class="btn btn-return">返回列表</a>
                <button type="submit" class="btn btn-submit">新增產品</button>
            </div>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>

