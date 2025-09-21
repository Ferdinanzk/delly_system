<?php
include 'db_connection.php'; // Include the database connection

// --- FETCH ALL PRODUCTS FOR THE DROPDOWN ---
// We fetch them here to pass to the JavaScript part later
$products_result = $conn->query("SELECT id, p_name, price FROM products ORDER BY p_name ASC");
$products = [];
if ($products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
}

// --- HANDLE FORM SUBMISSION ---
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start a database transaction
    $conn->begin_transaction();

    try {
        // 1. Sanitize and retrieve order details
        $order_number = $_POST['order_number'];
        $remarks = $_POST['remarks'];
        $total_price = $_POST['total_price'];
        $shipment_method = $_POST['shipment_method'];
        $shipment_date = $_POST['shipment_date'];
        $status = $_POST['status'];
        
        // 2. Insert into the main 'orders' table
        $stmt_order = $conn->prepare("INSERT INTO orders (order_number, remarks, total_price, shipment_method, shipment_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_order->bind_param("ssdsss", $order_number, $remarks, $total_price, $shipment_method, $shipment_date, $status);
        $stmt_order->execute();
        
        // Get the ID of the newly inserted order
        $order_id = $conn->insert_id;
        
        // 3. Insert each item into the 'order_items' table
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, amount) VALUES (?, ?, ?)");
        
        // Loop through the submitted products and amounts
        if (!empty($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $index => $product_id) {
                $amount = $_POST['amounts'][$index];
                if ($product_id > 0 && $amount > 0) {
                    $stmt_item->bind_param("iii", $order_id, $product_id, $amount);
                    $stmt_item->execute();
                }
            }
        }

        // 4. If everything was successful, commit the transaction
        $conn->commit();
        
        // Redirect to the orders page
        header("Location: orders.php?success=1");
        exit();

    } catch (Exception $e) {
        // If any query failed, roll back the entire transaction
        $conn->rollback();
        $error_message = "新增訂單失敗：" . $e->getMessage();
    }
    $stmt_order->close();
    $stmt_item->close();
}
$conn->close();

// --- INCLUDE HEADER ---
include 'header.php';
?>

<!-- Custom styles for the form -->
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
        max-width: 900px;
        margin: 2rem auto;
        padding: 2.5rem;
        background: #fff;
        border-radius: 12px;
        box-shadow: var(--form-shadow);
    }
    .form-header h1 {
        font-size: 2.5rem;
        color: var(--dark-gray);
        font-weight: 700;
        margin-bottom: 2rem;
        text-align: center;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 0.5rem;
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .btn-container {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2.5rem;
        border-top: 1px solid var(--border-color);
        padding-top: 1.5rem;
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
    .btn-submit {
        background-color: var(--primary-color);
        color: #fff;
    }
    .btn-submit:hover {
        background-color: #0069d9;
    }
    .btn-secondary {
        background-color: var(--secondary-color);
        color: #fff;
    }
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    .order-items-container h2 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    /* New styles for item headers */
    .order-item-header {
        display: grid;
        grid-template-columns: 4fr 1fr 2fr 80px;
        gap: 1rem;
        font-weight: 600;
        color: var(--secondary-color);
        font-size: 0.9rem;
        padding: 0 1rem 0.5rem 1rem;
        border-bottom: 2px solid var(--medium-gray);
        margin-bottom: 1rem;
    }
    .order-item-header > div:nth-child(2),
    .order-item-header > div:nth-child(3) {
        text-align: right;
    }
    .order-item-header > div:last-child {
        text-align: center;
    }
    /* Updated styles for item rows */
    .order-item-row {
        display: grid;
        grid-template-columns: 4fr 1fr 2fr 80px; /* Matched to header */
        gap: 1rem;
        align-items: center;
        margin-bottom: 1rem;
    }
    .item-price {
        text-align: right;
        font-weight: 500;
    }
    .btn-add-item {
        background-color: var(--success-color);
        color: white;
        padding: 0.5rem 1rem;
    }
    .btn-remove-item {
        background-color: var(--danger-color);
        color: white;
        padding: 0.5rem 1rem;
        width: 100%; /* Ensure button fills its cell */
    }
    #total-price-display {
        text-align: right;
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 1rem;
        color: var(--dark-gray);
    }
</style>

<main class="container">
    <div class="form-container">
        <div class="form-header">
            <h1>新增訂單</h1>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="add_order.php" method="POST">
            <!-- Order Details Section -->
            <div class="form-row">
                <div class="form-group">
                    <label for="order_number">訂單編號</label>
                    <input type="text" class="form-control" id="order_number" name="order_number" required>
                </div>
                <div class="form-group">
                    <label for="shipment_date">出貨日期與時間</label>
                    <input type="datetime-local" class="form-control" id="shipment_date" name="shipment_date" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="shipment_method">出貨方式</label>
                    <input type="text" class="form-control" id="shipment_method" name="shipment_method" required>
                </div>
                <div class="form-group">
                    <label for="status">狀態</label>
                    <select class="form-control" id="status" name="status">
                        <option value="未出貨" selected>未出貨</option>
                        <option value="已出貨">已出貨</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="remarks">備註</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
            </div>

            <!-- Order Items Section -->
            <div class="order-items-container">
                <h2>訂單品項</h2>
                <!-- Added headers for clarity -->
                <div class="order-item-header">
                    <div>產品</div>
                    <div>數量</div>
                    <div>小計</div>
                    <div>操作</div>
                </div>
                <div id="order-items-list">
                    <!-- JavaScript will add item rows here -->
                </div>
                <button type="button" class="btn btn-add-item" id="add-item-btn">新增品項</button>
            </div>
            
            <!-- Total Price Display -->
            <div id="total-price-display">
                總計: $0
            </div>
            <input type="hidden" name="total_price" id="total_price_input" value="0">


            <!-- Form Actions -->
            <div class="btn-container">
                <a href="orders.php" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-submit">建立訂單</button>
            </div>
        </form>
    </div>
</main>

<script>
    // Pass the PHP products array to JavaScript
    const products = <?php echo json_encode($products); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const itemsList = document.getElementById('order-items-list');
        const addItemBtn = document.getElementById('add-item-btn');
        const totalPriceDisplay = document.getElementById('total-price-display');
        const totalPriceInput = document.getElementById('total_price_input');

        // Function to create a new product selection row
        function createItemRow() {
            const row = document.createElement('div');
            row.className = 'order-item-row';

            // Product dropdown
            const productSelect = document.createElement('select');
            productSelect.className = 'form-control product-select';
            productSelect.name = 'products[]';
            productSelect.innerHTML = '<option value="0">-- 選擇產品 --</option>' + 
                products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.p_name}</option>`).join('');

            // Amount input
            const amountInput = document.createElement('input');
            amountInput.type = 'number';
            amountInput.className = 'form-control amount-input';
            amountInput.name = 'amounts[]';
            amountInput.value = '1';
            amountInput.min = '1';

            // Price display
            const priceDisplay = document.createElement('span');
            priceDisplay.className = 'item-price';
            priceDisplay.textContent = '$0';
            
            // Remove button
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

            // Add event listeners to the new inputs
            productSelect.addEventListener('change', updateTotal);
            amountInput.addEventListener('input', updateTotal);
        }

        // Function to calculate and update the total price
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

        // Event listener for the "Add Item" button
        addItemBtn.addEventListener('click', createItemRow);
        
        // Add one item row by default when the page loads
        createItemRow();
    });
</script>

<?php include 'footer.php'; ?>

