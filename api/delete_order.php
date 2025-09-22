<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    $conn->begin_transaction();
    try {
        // First, delete the associated items in order_items
        $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt_items->bind_param("i", $id);
        $stmt_items->execute();
        
        // Then, delete the main order
        $stmt_order = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt_order->bind_param("i", $id);
        $stmt_order->execute();

        $conn->commit();
        header("Location: /orders.php?page=$page&success=delete");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: /orders.php?page=$page&error=deletefailed");
        exit();
    }
}
$conn->close();
?>

