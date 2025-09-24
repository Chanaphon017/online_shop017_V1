<?php

require '../config.php';
require 'auth_admin.php';

// เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$category_name]);
        $_SESSION['success'] = "เพิ่มหมวดหมู่สำเร็จแล้ว";
        header("Location: category.php");
        exit;
    }
}

// ลบหมวดหมู่
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        $_SESSION['error'] = "ไม่สามารถลบได้ เนื่องจากยังมีสินค้าอยู่ในหมวดหมู่นี้";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
    }
    header("Location: category.php");
    exit;
}

// แก้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['new_name']);
    if ($category_name) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$category_name, $category_id]);
        $_SESSION['success'] = "อัปเดตชื่อหมวดหมู่เรียบร้อย";
        header("Location: category.php");
        exit;
    }
}

// ดึงหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการหมวดหมู่</title>
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

        .card {
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            background-color: #fff;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--cloud);
        }

        .btn-tan {
            background-color: var(--tan);
            color: var(--night);
            font-weight: bold;
            border: none;
        }
        .btn-tan:hover {
            background-color: var(--rust);
            color: #fff;
        }

        .btn-ash {
            background-color: var(--ash);
            color: #fff;
            border: none;
        }
        .btn-ash:hover {
            background-color: #2d2d2d;
        }

        .btn-warning {
            background-color: var(--tan);
            color: var(--night);
            border: none;
        }
        .btn-warning:hover {
            background-color: var(--rust);
            color: #fff;
        }

        .btn-danger {
            background-color: var(--ash);
            color: #fff;
            border: none;
        }
        .btn-danger:hover {
            background-color: #2d2d2d;
        }

        .table thead {
            background-color: var(--tan);
            color: var(--night);
        }

        .table tbody tr:hover {
            background-color: rgba(217,171,130,0.2);
        }

        .text-muted { color: #6c757d !important; }
    </style>
</head>
<body>
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📂 จัดการหมวดหมู่สินค้า</h2>
        <a href="index.php" class="btn btn-ash">← กลับหน้าผู้ดูแล</a>
    </div>

    <!-- แจ้งเตือน -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- ฟอร์มเพิ่มหมวดหมู่ -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">➕ เพิ่มหมวดหมู่ใหม่</h5>
            <form method="post" class="row g-3 mt-2">
                <div class="col-md-6">
                    <input type="text" name="category_name" class="form-control" placeholder="กรอกชื่อหมวดหมู่" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="add_category" class="btn btn-tan w-100">เพิ่ม</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ตารางหมวดหมู่ -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">📋 รายการหมวดหมู่</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mt-3">
                    <thead>
                        <tr>
                            <th style="width:40%">ชื่อหมวดหมู่</th>
                            <th style="width:40%">แก้ไขชื่อ</th>
                            <th style="width:20%" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['category_name']) ?></td>
                            <td>
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                    <input type="text" name="new_name" class="form-control me-2" placeholder="ชื่อใหม่" required>
                                    <button type="submit" name="update_category" class="btn btn-warning btn-sm">บันทึก</button>
                                </form>
                            </td>
                            <td class="text-center">
                                <a href="category.php?delete=<?= $cat['category_id'] ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('คุณต้องการลบหมวดหมู่นี้หรือไม่ ?')">ลบ</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">— ยังไม่มีหมวดหมู่ —</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
