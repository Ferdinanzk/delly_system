<?php
include 'db_connection.php';
include 'header.php';

// Pagination logic
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of orders
$total_result = $conn->query("SELECT COUNT(*) as count FROM orders");
$total_orders = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_orders / $limit);

// Fetch orders for the current page
$sql = "SELECT o.*, GROUP_CONCAT(CONCAT(p.p_name, ' (', oi.amount, ')') SEPARATOR ', ') as order_items 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN products p ON oi.product_id = p.id 
        GROUP BY o.id 
        ORDER BY o.shipment_date DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<main class="container">
    <div class="page-header">
        <h1>訂單管理</h1>
        <!-- Corrected: Absolute path for New Order button -->
        <a href="/add_order.php" class="btn btn-primary">新增訂單</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>訂單編號</th>
                    <th>品項</th>
                    <th>總價</th>
                    <th>出貨方式</th>
                    <th>出貨日期</th>
                    <th>狀態</th>
                    <th class="actions-column">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["order_number"]); ?></td>
                            <td class="item-list"><?php echo htmlspecialchars($row["order_items"]); ?></td>
                            <td>$<?php echo number_format($row["total_price"]); ?></td>
                            <td><?php echo htmlspecialchars($row["shipment_method"]); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row["shipment_date"])); ?></td>
                            <td><span class="status-<?php echo ($row['status'] == '已出貨' ? 'shipped' : 'pending'); ?>"><?php echo htmlspecialchars($row["status"]); ?></span></td>
                            <td class="actions-cell">
                                <!-- Corrected: Absolute paths for action links -->
                                <a href="/update_order.php?id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn btn-update">更新</a>
                                <a href="/delete_order.php?id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn btn-delete" onclick="return confirm('您確定要刪除這筆訂單嗎？');">刪除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center; padding: 2rem;">目前沒有訂單</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav class="pagination">
             <!-- Corrected: Absolute path for previous page link -->
            <a href="/orders.php?page=<?php echo max(1, $page - 1); ?>" class="page-link <?php if($page <= 1){ echo 'disabled'; } ?>">&laquo;</a>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                 <!-- Corrected: Absolute paths for page number links -->
                <a href="/orders.php?page=<?php echo $i; ?>" class="page-link <?php if($page == $i) {echo 'active'; } ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
             <!-- Corrected: Absolute path for next page link -->
            <a href="/orders.php?page=<?php echo min($total_pages, $page + 1); ?>" class="page-link <?php if($page >= $total_pages) { echo 'disabled'; } ?>">&raquo;</a>
        </nav>
    <?php endif; ?>
</main>
<?php
$conn->close();
include 'footer.php';
?>
