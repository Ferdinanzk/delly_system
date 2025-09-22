<?php
// File: api/header.php
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>德利豆乾管理系統</title>
    <!-- Corrected: Use absolute path for CSS -->
    <link rel="stylesheet" href="/style.css"> 
</head>
<body>
    <header class="navbar">
        <div class="container">
            <!-- Corrected: Use absolute path for brand link -->
            <a href="/" class="navbar-brand">德利豆乾</a>
            <nav class="navbar-nav">
                <!-- Corrected: Use absolute paths for navigation -->
                <a href="/orders.php" class="nav-link">訂單管理</a>
                <a href="/products.php" class="nav-link">產品管理</a>
            </nav>
        </div>
    </header>
