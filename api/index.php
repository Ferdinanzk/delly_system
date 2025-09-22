<?php
// File: api/index.php
include 'header.php';
?>

<style>
    .welcome-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 70vh;
        text-align: center;
    }
    .welcome-message h1 {
        font-size: 3.5rem;
        font-weight: 700;
        color: #343a40;
    }
    .welcome-message p {
        font-size: 1.25rem;
        color: #6c757d;
    }
</style>

<main class="container">
    <div class="welcome-container">
        <div class="welcome-message">
            <h1>歡迎來到德利豆乾管理系統</h1>
            <p>請從上方的導覽列選擇要管理的項目。</p>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>
