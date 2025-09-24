<?php
require '../config.php';
require 'auth_admin.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แผงควบคุมผู้ดูแลระบบ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --tan: #D9AB82;
        --rust: #8C594D;
        --cloud: #A6A6A6;
        --ash: #404040;
        --night: #0D0D0D;
    }

    body {
        background-color: var(--cloud);
        color: var(--night);
        font-family: "Segoe UI", Tahoma, sans-serif;
    }

    h2 { color: var(--rust); }

    .dashboard-card {
        border-radius: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
        background-color: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    .btn-tan {
        background-color: var(--tan);
        color: var(--night);
        font-weight: bold;
        border: none;
    }
    .btn-tan:hover { background-color: var(--rust); color: #fff; }

    .btn-ash {
        background-color: var(--ash);
        color: #fff;
        border: none;
    }
    .btn-ash:hover { background-color: #2d2d2d; }

    .btn-outline-ash {
        border-color: var(--ash);
        color: var(--ash);
    }
    .btn-outline-ash:hover {
        background-color: var(--ash);
        color: #fff;
    }

    .text-muted { color: #6c757d !important; }
</style>
</head>
<body>
<div class="container mt-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">📊 ระบบผู้ดูแลระบบ</h2>
        <p class="text-muted">ยินดีต้อนรับ, 
            <span class="fw-semibold" style="color: var(--tan);"><?= htmlspecialchars($_SESSION['username']) ?></span>
        </p>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card dashboard-card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--tan)">สินค้า</h5>
                    <p class="card-text text-muted">จัดการสินค้าในระบบ</p>
                    <a href="products.php" class="btn btn-tan w-100">จัดการสินค้า</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--rust)">คำสั่งซื้อ</h5>
                    <p class="card-text text-muted">ตรวจสอบและอัปเดตคำสั่งซื้อ</p>
                    <a href="orders.php" class="btn btn-tan w-100">จัดการคำสั่งซื้อ</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--ash)">สมาชิก</h5>
                    <p class="card-text text-muted">จัดการข้อมูลผู้ใช้</p>
                    <a href="users.php" class="btn btn-tan w-100">จัดการสมาชิก</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
        <div class="card dashboard-card h-100 text-center">
            <div class="card-body">
                <h5 class="card-title" style="color: var(--night)">หมวดหมู่</h5>
                <p class="card-text text-muted">เพิ่ม/แก้ไขหมวดหมู่สินค้า</p>
                <a href="category.php" class="btn btn-tan w-100">จัดการหมวดหมู่</a>
            </div>
        </div>
    </div>
    </div>
    <div class="text-center mt-5">
        <a href="../logout.php" class="btn btn-outline-ash px-4">🚪 ออกจากระบบ</a>
    </div>
</div>
</body>
</html>
