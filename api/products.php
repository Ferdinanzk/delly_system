<?php
include 'db_connection.php';
include 'header.php';

// --- PAGINATION LOGIC ---
$limit = 10; // Products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of products
$total_result = $conn->query("SELECT COUNT(*) as count FROM products");
$total_products = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_products / $limit);

// Fetch products for the current page
$sql = "SELECT id, p_name, category, price, weight FROM products ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<main class="container">
    <div class="page-header">
        <h1>產品管理</h1>
        <a href="/add_product.php" class="btn btn-primary">新增產品</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>產品名稱</th>
                    <th>分類</th>
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
                                <a href="/update_product.php?id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn btn-update">更新</a>
                                <a href="/delete_product.php?id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn btn-delete" onclick="return confirm('確定要刪除這個產品嗎？');">刪除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">沒有找到任何產品。</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if($total_pages > 1): ?>
    <nav class="pagination">
        <a href="?page=<?php echo max(1, $page - 1); ?>" class="page-link <?php echo ($page <= 1) ? 'disabled' : ''; ?>"> &laquo; 上一頁 </a>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <a href="?page=<?php echo min($total_pages, $page + 1); ?>" class="page-link <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"> 下一頁 &raquo; </a>
    </nav>
    <?php endif; ?>

</main>

<?php
$conn->close();
include 'footer.php';
?>

