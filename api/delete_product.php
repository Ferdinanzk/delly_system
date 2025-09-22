<?php
// Include the database connection
include 'db_connection.php';

// 1. Check if an ID is provided in the URL and if it's a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    // Sanitize the ID to ensure it's an integer
    $product_id = (int)$_GET['id'];

    // 2. Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    
    // Bind the integer parameter
    $stmt->bind_param("i", $product_id);

    // 3. Execute the deletion query
    // We don't need to check for success for this simple case,
    // but in a larger application, you might add error logging.
    $stmt->execute();

    // 4. Close the statement and the connection
    $stmt->close();
    $conn->close();
}

// 5. Redirect back to the products page after deletion
// This will happen whether the deletion was successful or if no ID was provided.
// It also preserves the current page number in the URL.
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
header("Location: products.php?page=" . $page);
exit(); // Ensure no further code is executed after the redirect
?>

