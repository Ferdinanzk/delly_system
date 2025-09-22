<?php
// Fetch credentials from Vercel's Environment Variables
$servername = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

// The port is often included in the host, but sometimes separate
$port = getenv('DB_PORT') ?: 3306; // Default MySQL port is 3306

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    // It's better not to expose detailed error messages in production
    die("Database connection failed. Please try again later.");
}

// Set character set to prevent encoding issues with Traditional Chinese
$conn->set_charset("utf8mb4");
?>
