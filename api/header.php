<?php
// File: api/header.php
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>德利豆乾管理系統</title>
    
    <!-- Embedded CSS Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap');

        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --update-color: #ffc107;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #343a40;
            --text-color: #212529;
            --border-color: #dee2e6;
            --shadow-sm: 0 4px 6px rgba(0,0,0,0.05);
            --shadow-md: 0 8px 25px rgba(0,0,0,0.07);
        }

        body {
            font-family: 'Noto Sans TC', sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
            margin: 0;
            padding-top: 80px; /* Space for fixed navbar */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* --- Navbar --- */
        .navbar {
            background-color: #fff;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border-color);
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        .navbar-nav {
            display: flex;
            gap: 1.5rem;
        }
        .nav-link {
            font-size: 1rem;
            font-weight: 500;
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .nav-link:hover {
            color: var(--primary-color);
        }

        /* --- Page Header --- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }
        .page-header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--dark-gray);
        }

        /* --- Buttons --- */
        .btn {
            display: inline-block;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }
        .btn-primary { background-color: var(--primary-color); color: #fff; }
        .btn-primary:hover { background-color: #0069d9; transform: translateY(-2px); }
        .btn-update { background-color: var(--update-color); color: #212529; }
        .btn-update:hover { background-color: #e0a800; }
        .btn-delete { background-color: var(--danger-color); color: #fff; }
        .btn-delete:hover { background-color: #c82333; }
        .btn-secondary { background-color: var(--secondary-color); color: #fff; }
        .btn-secondary:hover { background-color: #5a6268; }

        /* --- Table Styles --- */
        .table-container {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        thead tr {
            background-color: var(--light-gray);
        }
        th {
            font-weight: 700;
            color: var(--dark-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        tbody tr:hover {
            background-color: #f1f3f5;
        }
        .actions-column { width: 200px; }
        .actions-cell { display: flex; gap: 0.5rem; }

        /* --- Pagination --- */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        .page-link {
            color: var(--primary-color);
            padding: 0.5rem 0.9rem;
            margin: 0 0.2rem;
            text-decoration: none;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .page-link:hover {
            background-color: var(--medium-gray);
        }
        .page-link.active {
            background-color: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
        }
        .page-link.disabled {
            color: var(--secondary-color);
            pointer-events: none;
            background-color: var(--light-gray);
        }

        /* --- Form Styles --- */
        .form-container {
            max-width: 800px; margin: 2rem auto; padding: 2.5rem; background: #fff;
            border-radius: 12px; box-shadow: var(--shadow-md);
        }
        .form-container h1 { font-size: 2rem; color: var(--dark-gray); margin-bottom: 2rem; text-align: center;}
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .form-control {
            width: 100%; padding: 0.75rem 1rem; font-size: 1rem;
            border: 1px solid var(--border-color); border-radius: 8px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none; border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
        }
        .btn-container { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }

        /* --- Footer --- */
        .footer {
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
    </style>

</head>
<body>
    <header class="navbar">
        <div class="container">
            <a href="/" class="navbar-brand">德利豆乾</a>
            <nav class="navbar-nav">
                <a href="/orders.php" class="nav-link">訂單管理</a>
                <a href="/products.php" class="nav-link">產品管理</a>
            </nav>
        </div>
    </header>

