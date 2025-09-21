<?php
include 'header.php';
include 'db_connection.php'; // Include the database connection
?>

<!-- Add custom styles for this page -->
<style>
    :root {
        --primary-color: #007bff;
        --light-gray: #f8f9fa;
        --medium-gray: #e9ecef;
        --dark-gray: #343a40;
        --text-color: #212529;
        --table-shadow: 0 8px 25px rgba(0,0,0,0.07);
    }

    /* Main Container Styles */
    .container {
        max-width: 1400px;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
    }
    
    .page-header h1 {
        font-size: 2.5rem;
        color: var(--dark-gray);
        font-weight: 700;
        margin: 0;
    }
    
    /* Styled "Add Product" Button */
    .btn-add {
        background-color: var(--primary-color);
        color: #fff !important;
        font-weight: 600;
        padding: 12px 24px;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }

    .btn-add:hover {
        transform: translateY(-3px);
        background-color: #0069d9;
        box-shadow: 0 7px 20px rgba(0, 123, 255, 0.3);
    }

    .btn-add:active {
        transform: translateY(0);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }

    /* Table Container */
    .table-container {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: var(--table-shadow);
        margin-bottom: 2.5rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table thead th {
        background-color: var(--light-gray);
        color: var(--dark-gray);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1.25rem 1.5rem;
        text-align: left;
        border-bottom: 2px solid var(--medium-gray);
    }

    table tbody tr {
        border-bottom: 1px solid var(--medium-gray);
        transition: background-color 0.2s ease, transform 0.2s ease;
        /* Animation */
        opacity: 0;
        animation: fadeInRow 0.5s ease-out forwards;
    }
    
    table tbody tr:last-child {
        border-bottom: none;
    }

    table tbody tr:hover {
        background-color: #f1f3f5;
        transform: scale(1.01); /* Subtle lift effect */
    }

    table td {
        padding: 1.25rem 1.5rem;
        vertical-align: middle;
        color: #495057;
    }
    
    .actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn {
        text-align: center;
        padding: 8px 16px;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .btn-update {
        background-color: var(--medium-gray);
        color: var(--text-color) !important;
    }
    .btn-update:hover {
        background-color: #d3d9df;
    }

    .btn-delete {
        background-color: #ffeef0;
        color: #dc3545 !important;
    }
    .btn-delete:hover {
        background-color: #fdd8db;
    }
    
    /* Staggered animation delay */
    <?php 
    $itemsPerPage = 10;
    for ($i = 1; $i <= $itemsPerPage; $i++): ?>
    table tbody tr:nth-child(<?php echo $i; ?>) {
        animation-delay: <?php echo ($i - 1) * 0.06; ?>s;
    }
    <?php endfor; ?>

    @keyframes fadeInRow {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Pagination Styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        list-style: none;
        padding: 0;
    }
    .page-item {
        margin: 0 4px;
    }
    .page-link {
        display: block;
        padding: 10px 16px;
        border: 1px solid var(--medium-gray);
        border-radius: 8px;
        color: var(--text-color);
        transition: all 0.2s ease;
        font-weight: 600;
    }
    .page-link:hover {
        background-color: var(--medium-gray);
        border-color: #ced4da;
    }
    .page-item.active .page-link {
        background-color: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }
    .page-item.disabled .page-link {
        color: #adb5bd;
        pointer-events: none;
        background-color: var(--light-gray);
    }

</style>

<?php
// --- PAGINATION LOGIC ---
// 1. Get the current page number, default to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// 2. Get total number of products to calculate total pages
$countSql = "SELECT COUNT(id) as total FROM products";
$countResult = $conn->query($countSql);
$totalProducts = $countResult ? (int)$countResult->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalProducts / $itemsPerPage);

// 3. Calculate the OFFSET for the SQL query
$offset = ($page - 1) * $itemsPerPage;

// 4. Fetch the products for the current page
$sql = "SELECT id, p_name, category, price, weight FROM products LIMIT $itemsPerPage OFFSET $offset";
$result = $conn->query($sql);
?>

<main class="container">
    <div class="page-header">
        <h1>產品列表</h1>
        <a href="add_product.php" class="btn btn-add">新增產品</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>產品名稱</th>
                    <th>分類</th>
                    <th>價格 (NTD)</th>
                    <th>重量</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["id"]); ?></td>
                            <td><?php echo htmlspecialchars($row["p_name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["category"]); ?></td>
                            <td><?php echo number_format($row["price"]); ?></td>
                            <td><?php echo htmlspecialchars($row["weight"]); ?></td>
                            <td class="actions">
                                <a href="update_product.php?id=<?php echo $row['id']; ?>" class="btn btn-update">更新</a>
                                <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('您確定要刪除「<?php echo htmlspecialchars($row['p_name']); ?>」嗎？');">刪除</a>
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

    <?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination">
            <!-- Backward Arrow -->
            <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                <a class="page-link" href="<?php if($page <= 1){ echo '#'; } else { echo "?page=" . ($page - 1); } ?>">&laquo;</a>
            </li>

            <!-- Page Numbers -->
            <?php for($i = 1; $i <= $totalPages; $i++ ): ?>
            <li class="page-item <?php if($page == $i) {echo 'active'; } ?>">
                <a class="page-link" href="products.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>

            <!-- Forward Arrow -->
            <li class="page-item <?php if($page >= $totalPages) { echo 'disabled'; } ?>">
                <a class="page-link" href="<?php if($page >= $totalPages){ echo '#'; } else {echo "?page=" . ($page + 1); } ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</main>

<?php include 'footer.php'; ?>

