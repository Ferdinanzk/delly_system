<?php
include 'db_connection.php'; // Include the database connection

// --- VALIDATE ORDER ID ---
// Check if an ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // If no ID, redirect back to the main orders page
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['id']; // Sanitize the ID to prevent SQL injection
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the page number for a smooth redirect

// --- DATABASE DELETION LOGIC ---
// Use a transaction to ensure both deletions succeed or fail together
$conn->begin_transaction();

try {
    // Step 1: Delete the items associated with the order from the 'order_items' table.
    // This must be done first to respect the foreign key constraint.
    $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $stmt_items->close();

    // Step 2: Delete the main order from the 'orders' table.
    $stmt_order = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt_order->bind_param("i", $order_id);
    $stmt_order->execute();
    $stmt_order->close();

    // If both queries were successful, commit the transaction
    $conn->commit();

    // Redirect back to the orders list, preserving the page number and showing a success message
    header("Location: orders.php?page=$page&success=delete");
    exit();

} catch (Exception $e) {
    // If any part of the transaction fails, roll back all changes
    $conn->rollback();
    
    // For debugging, you could log the error: error_log($e->getMessage());
    // Redirect with an error message
    header("Location: orders.php?page=$page&error=delete_failed");
    exit();
}

$conn->close();
?>
