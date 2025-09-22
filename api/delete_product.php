<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Corrected: Absolute path for redirect
        header("Location: /products.php?page=$page&success=delete");
        exit();
    } else {
        die("刪除失敗: " . $conn->error);
    }
} else {
    // Corrected: Absolute path for redirect
    header("Location: /products.php");
    exit();
}
?>
