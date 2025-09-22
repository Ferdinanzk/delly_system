<?php
include 'db_connection.php';
include 'header.php';

// Pagination logic
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of products
$total_result = $conn->query("SELECT COUNT(*) as count FROM products");
$total_products = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_products / $limit);

// Fetch products for the current page
$result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT $limit OFFSET $offset");

?>
<main class="container">
    <div class="page-header">
        <h1>產品管理</h1>
        <!-- Corrected: Absolute path for Add Product button -->
        <a href="/add_product.php" class="btn btn-primary">新增產品</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>產品名稱</th>
                    <th>類別</th>
                    <th>價格</th>
                    <th>重量</th>
                    <th class="actions-column">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo htmlspecialchars($row["p_name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["category"]); ?></td>
                            <td>$<?php echo number_format($row["price"]); ?></td>
                            <td><?php echo htmlspecialchars($row["weight"]); ?></td>
                            <td class="actions-cell">
                                <!-- Corrected: Absolute paths for action links -->
                                <a href="/update_product.php?id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn btn-update">更新</a>
                                <a href="/delete_product.php?id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn btn-delete" onclick="return confirm('您確定要刪除這項產品嗎？');">刪除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">目前沒有產品</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav class="pagination">
            <!-- Corrected: Absolute path for previous page link -->
            <a href="/products.php?page=<?php echo max(1, $page - 1); ?>" class="page-link <?php if($page <= 1){ echo 'disabled'; } ?>">&laquo;</a>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <!-- Corrected: Absolute paths for page number links -->
                <a href="/products.php?page=<?php echo $i; ?>" class="page-link <?php if($page == $i) {echo 'active'; } ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <!-- Corrected: Absolute path for next page link -->
            <a href="/products.php?page=<?php echo min($total_pages, $page + 1); ?>" class="page-link <?php if($page >= $total_pages) { echo 'disabled'; } ?>">&raquo;</a>
        </nav>
    <?php endif; ?>

</main>
<?php
$conn->close();
include 'footer.php';
?>
