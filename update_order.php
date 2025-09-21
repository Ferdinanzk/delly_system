<?php
include 'db_connection.php'; // Include the database connection

// --- VALIDATE ORDER ID ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit();
}
$order_id = (int)$_GET['id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Keep track of the page for redirect

// --- FETCH ALL PRODUCTS FOR DROPDOWNS ---
$products_result = $conn->query("SELECT id, p_name, price FROM products ORDER BY p_name ASC");
$all_products = [];
if ($products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $all_products[] = $row;
    }
}

// --- HANDLE FORM SUBMISSION (UPDATE LOGIC) ---
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {
        // 1. Sanitize and retrieve updated order details
        $order_number = $_POST['order_number'];
        $remarks = $_POST['remarks'];
        $total_price = $_POST['total_price'];
        $shipment_method = $_POST['shipment_method'];
        $shipment_date = $_POST['shipment_date'];
        $status = $_POST['status'];
        
        // 2. Update the main 'orders' table
        $stmt_order = $conn->prepare("UPDATE orders SET order_number = ?, remarks = ?, total_price = ?, shipment_method = ?, shipment_date = ?, status = ? WHERE id = ?");
        $stmt_order->bind_param("ssdsssi", $order_number, $remarks, $total_price, $shipment_method, $shipment_date, $status, $order_id);
        $stmt_order->execute();
        
        // 3. Delete all existing items for this order to avoid complexity
        $conn->query("DELETE FROM order_items WHERE order_id = $order_id");

        // 4. Insert the new set of items into 'order_items'
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, amount) VALUES (?, ?, ?)");
        if (!empty($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $index => $product_id) {
                $amount = $_POST['amounts'][$index];
                if ($product_id > 0 && $amount > 0) {
                    $stmt_item->bind_param("iii", $order_id, $product_id, $amount);
                    $stmt_item->execute();
                }
            }
        }

        // 5. If successful, commit the transaction
        $conn->commit();
        
        // Redirect back to the orders page, preserving the page number
        header("Location: orders.php?page=$page&success=update");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "更新訂單失敗：" . $e->getMessage();
    }
}

// --- FETCH EXISTING ORDER DATA FOR THE FORM ---
$order_result = $conn->query("SELECT * FROM orders WHERE id = $order_id");
if ($order_result->num_rows === 0) {
    // No order found, redirect
    header("Location: orders.php");
    exit();
}
$order = $order_result->fetch_assoc();

// Fetch existing items for this order
$items_result = $conn->query("SELECT product_id, amount FROM order_items WHERE order_id = $order_id");
$order_items = [];
if ($items_result->num_rows > 0) {
    while ($row = $items_result->fetch_assoc()) {
        $order_items[] = $row;
    }
}

$conn->close();

// --- INCLUDE HEADER ---
include 'header.php';
?>

<!-- Re-using the styles from add_order.php for consistency -->
<style>
    :root {
        --primary-color: #007bff;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --light-gray: #f8f9fa;
        --medium-gray: #e9ecef;
        --dark-gray: #343a40;
        --text-color: #212529;
        --border-color: #dee2e6;
        --form-shadow: 0 8px 25px rgba(0,0,0,0.07);
    }
    .form-container {
        max-width: 900px; margin: 2rem auto; padding: 2.5rem; background: #fff;
        border-radius: 12px; box-shadow: var(--form-shadow);
    }
    .form-header h1 {
        font-size: 2.5rem; color: var(--dark-gray); font-weight: 700;
        margin-bottom: 2rem; text-align: center;
    }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label {
        display: block; font-weight: 600; color: var(--text-color); margin-bottom: 0.5rem;
    }
    .form-control {
        width: 100%; padding: 0.75rem 1rem; font-size: 1rem;
        border: 1px solid var(--border-color); border-radius: 8px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus {
        outline: none; border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .btn-container {
        display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2.5rem;
        border-top: 1px solid var(--border-color); padding-top: 1.5rem;
    }
    .btn {
        display: inline-block; font-weight: 600; font-size: 1rem; padding: 0.75rem 1.5rem;
        border-radius: 8px; border: none; cursor: pointer; text-align: center;
        text-decoration: none; transition: all 0.3s ease;
    }
    .btn-submit { background-color: var(--primary-color); color: #fff; }
    .btn-submit:hover { background-color: #0069d9; }
    .btn-secondary { background-color: var(--secondary-color); color: #fff; }
    .btn-secondary:hover { background-color: #5a6268; }
    .order-items-container h2 {
        font-size: 1.5rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    .order-item-header {
        display: grid; grid-template-columns: 4fr 1fr 2fr 80px; gap: 1rem; font-weight: 600;
        color: var(--secondary-color); font-size: 0.9rem; padding: 0 1rem 0.5rem 1rem;
        border-bottom: 2px solid var(--medium-gray); margin-bottom: 1rem;
    }
     .order-item-header > div:nth-child(2), .order-item-header > div:nth-child(3) { text-align: right; }
    .order-item-row {
        display: grid; grid-template-columns: 4fr 1fr 2fr 80px; gap: 1rem;
        align-items: center; margin-bottom: 1rem;
    }
    .item-price { text-align: right; font-weight: 500; }
    .btn-add-item { background-color: var(--success-color); color: white; padding: 0.5rem 1rem; }
    .btn-remove-item { background-color: var(--danger-color); color: white; padding: 0.5rem 1rem; width: 100%; }
    #total-price-display {
        text-align: right; font-size: 1.8rem; font-weight: 700;
        margin-top: 1rem; color: var(--dark-gray);
    }
</style>

<main class="container">
    <div class="form-container">
        <div class="form-header">
            <h1>更新訂單</h1>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="update_order.php?id=<?php echo $order_id; ?>&page=<?php echo $page; ?>" method="POST">
            <!-- Order Details -->
            <div class="form-row">
                <div class="form-group">
                    <label for="order_number">訂單編號</label>
                    <input type="text" class="form-control" id="order_number" name="order_number" value="<?php echo htmlspecialchars($order['order_number']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="shipment_date">出貨日期與時間</label>
                    <input type="datetime-local" class="form-control" id="shipment_date" name="shipment_date" value="<?php echo date('Y-m-d\TH:i', strtotime($order['shipment_date'])); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="shipment_method">出貨方式</label>
                    <input type="text" class="form-control" id="shipment_method" name="shipment_method" value="<?php echo htmlspecialchars($order['shipment_method']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">狀態</label>
                    <select class="form-control" id="status" name="status">
                        <option value="未出貨" <?php echo ($order['status'] == '未出貨') ? 'selected' : ''; ?>>未出貨</option>
                        <option value="已出貨" <?php echo ($order['status'] == '已出貨') ? 'selected' : ''; ?>>已出貨</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="remarks">備註</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($order['remarks']); ?></textarea>
            </div>

            <!-- Order Items -->
            <div class="order-items-container">
                <h2>訂單品項</h2>
                <div class="order-item-header">
                    <div>產品</div><div>数量</div><div>小計</div><div>操作</div>
                </div>
                <div id="order-items-list">
                    <!-- JS will populate existing items here -->
                </div>
                <button type="button" class="btn btn-add-item" id="add-item-btn">新增品項</button>
            </div>
            
            <!-- Total Price -->
            <div id="total-price-display">
                總計: $<?php echo number_format($order['total_price']); ?>
            </div>
            <input type="hidden" name="total_price" id="total_price_input" value="<?php echo $order['total_price']; ?>">

            <!-- Actions -->
            <div class="btn-container">
                <a href="orders.php?page=<?php echo $page; ?>" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-submit">儲存變更</button>
            </div>
        </form>
    </div>
</main>

<script>
    // Pass PHP data to JavaScript
    const allProducts = <?php echo json_encode($all_products); ?>;
    const existingItems = <?php echo json_encode($order_items); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const itemsList = document.getElementById('order-items-list');
        const addItemBtn = document.getElementById('add-item-btn');
        const totalPriceDisplay = document.getElementById('total-price-display');
        const totalPriceInput = document.getElementById('total_price_input');

        function createItemRow(item = { product_id: 0, amount: 1 }) {
            const row = document.createElement('div');
            row.className = 'order-item-row';

            const productSelect = document.createElement('select');
            productSelect.className = 'form-control product-select';
            productSelect.name = 'products[]';
            let optionsHTML = '<option value="0">-- 選擇產品 --</option>';
            allProducts.forEach(p => {
                const isSelected = p.id == item.product_id ? 'selected' : '';
                optionsHTML += `<option value="${p.id}" data-price="${p.price}" ${isSelected}>${p.p_name}</option>`;
            });
            productSelect.innerHTML = optionsHTML;

            const amountInput = document.createElement('input');
            amountInput.type = 'number';
            amountInput.className = 'form-control amount-input';
            amountInput.name = 'amounts[]';
            amountInput.value = item.amount;
            amountInput.min = '1';

            const priceDisplay = document.createElement('span');
            priceDisplay.className = 'item-price';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-remove-item';
            removeBtn.textContent = '移除';
            removeBtn.onclick = function() {
                row.remove();
                updateTotal();
            };

            row.appendChild(productSelect);
            row.appendChild(amountInput);
            row.appendChild(priceDisplay);
            row.appendChild(removeBtn);
            itemsList.appendChild(row);

            productSelect.addEventListener('change', updateTotal);
            amountInput.addEventListener('input', updateTotal);
            
            return row;
        }

        function updateTotal() {
            let total = 0;
            itemsList.querySelectorAll('.order-item-row').forEach(row => {
                const productSelect = row.querySelector('.product-select');
                const amountInput = row.querySelector('.amount-input');
                const priceDisplay = row.querySelector('.item-price');
                
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const amount = parseInt(amountInput.value) || 0;
                const itemTotal = price * amount;
                
                priceDisplay.textContent = `$${itemTotal.toLocaleString()}`;
                total += itemTotal;
            });

            totalPriceDisplay.textContent = `總計: $${total.toLocaleString()}`;
            totalPriceInput.value = total;
        }

        addItemBtn.addEventListener('click', () => createItemRow());
        
        // Populate form with existing items on load
        if(existingItems.length > 0) {
            existingItems.forEach(item => createItemRow(item));
        } else {
            createItemRow(); // Add one empty row if order has no items
        }
        updateTotal(); // Calculate initial total
    });
</script>

<?php include 'footer.php'; ?>
