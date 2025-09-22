<?php
include 'db_connection.php'; // Include the database connection
include 'header.php'; // Include the header

// --- PAGINATION LOGIC ---
$limit = 10; // Number of orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page, default to 1
$offset = ($page - 1) * $limit; // Calculate the offset for the query

// --- GET TOTAL NUMBER OF ORDERS ---
$total_result = $conn->query("SELECT COUNT(*) AS total FROM orders");
$total_orders = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $limit); // Calculate total pages

// --- FETCH ORDERS FOR THE CURRENT PAGE ---
// This query joins orders, order_items, and products to get all details
// and uses GROUP_CONCAT to list all items for an order in a single field.
$stmt = $conn->prepare("
    SELECT 
        o.id, 
        o.order_number, 
        o.total_price, 
        o.status, 
        o.shipment_method, 
        o.shipment_date,
        GROUP_CONCAT(CONCAT(p.p_name, ' (x', oi.amount, ')') SEPARATOR '<br>') as order_items
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.id DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

?>

<!-- Custom styles for the orders page -->
<style>
    :root {
        --primary-color: #007bff;
        --light-gray: #f8f9fa;
        --medium-gray: #e9ecef;
        --dark-gray: #343a40;
        --text-color: #212529;
        --border-color: #dee2e6;
        --table-shadow: 0 8px 25px rgba(0,0,0,0.07);
        --green-status: #28a745;
        --gray-status: #6c757d;
    }
    .container {
        max-width: 1200px; /* Wider container for more columns */
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .page-header h1 {
        font-size: 2.5rem;
        color: var(--dark-gray);
        font-weight: 700;
    }
    .btn {
        display: inline-block;
        font-weight: 600;
        font-size: 1rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .btn-add {
        background-color: var(--primary-color);
        color: #fff;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }
    .btn-add:hover {
        transform: translateY(-3px);
        background-color: #0069d9;
        box-shadow: 0 7px 20px rgba(0, 123, 255, 0.3);
    }
    .table-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: var(--table-shadow);
        overflow: hidden; /* Important for border-radius on table */
    }
    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }
    .orders-table th, .orders-table td {
        padding: 1rem 1.25rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .orders-table thead th {
        background-color: var(--light-gray);
        color: var(--dark-gray);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .orders-table tbody tr:last-child td {
        border-bottom: none;
    }
    .orders-table tbody tr:hover {
        background-color: #f1f3f5;
    }
    .status-badge {
        padding: 0.3rem 0.6rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #fff;
    }
    .status-shipped {
        background-color: var(--green-status);
    }
    .status-pending {
        background-color: var(--gray-status);
    }
    .actions a {
        color: var(--primary-color);
        text-decoration: none;
        margin-right: 1rem;
        font-weight: 500;
        transition: color 0.2s ease;
    }
    .actions a:hover {
        color: #0056b3;
    }
    .actions a.delete {
        color: #dc3545;
    }
    .actions a.delete:hover {
        color: #a71d2a;
    }
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
        gap: 0.5rem;
    }
    .pagination a, .pagination span {
        color: var(--primary-color);
        padding: 0.6rem 1rem;
        text-decoration: none;
        border: 1px solid var(--medium-gray);
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .pagination a:hover {
        background-color: var(--medium-gray);
        border-color: var(--border-color);
    }
    .pagination .current-page {
        background-color: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }
    .pagination .disabled {
        color: #adb5bd;
        pointer-events: none;
    }
    .order-items-list {
        font-size: 0.9rem;
        line-height: 1.6;
    }
</style>

<main class="container">
    <div class="page-header">
        <h1>訂單管理</h1>
        <a href="add_order.php" class="btn btn-add">新增訂單</a>
    </div>

    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>訂單編號</th>
                    <th>訂單品項</th>
                    <th>總額</th>
                    <th>狀態</th>
                    <th>出貨方式</th>
                    <th>出貨日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($order = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td class="order-items-list"><?php echo $order['order_items']; // HTML is allowed here for <br> ?></td>
                            <td>$<?php echo number_format($order['total_price']); ?></td>
                            <td>
                                <?php if ($order['status'] == '已出貨'): ?>
                                    <span class="status-badge status-shipped">已出貨</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">未出貨</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($order['shipment_method']); ?></td>
                            <td><?php echo htmlspecialchars($order['shipment_date']); ?></td>
                            <td class="actions">
                                <!-- Links for future update/delete functionality -->
                                <a href="update_order.php?id=<?php echo $order['id']; ?>&page=<?php echo $page; ?>">更新</a>
                                <a href="delete_order.php?id=<?php echo $order['id']; ?>&page=<?php echo $page; ?>" class="delete" onclick="return confirm('確定要刪除這筆訂單嗎？');">刪除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem;">目前沒有訂單。</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">&laquo; 上一頁</a>
        <?php else: ?>
            <span class="disabled">&laquo; 上一頁</span>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'current-page' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">下一頁 &raquo;</a>
        <?php else: ?>
            <span class="disabled">下一頁 &raquo;</span>
        <?php endif; ?>
    </div>
</main>

<?php 
$stmt->close();
$conn->close();
include 'footer.php'; 
?>

