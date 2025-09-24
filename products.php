<?php
require '../config.php';
require 'auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    if (!empty($name) && $price > 0) {
        $imageName = 'no-image.jpg';
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png'];
            if (in_array($file['type'], $allowed)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], __DIR__ . '/../product_images/' . $imageName);
            }
        }
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);
        $_SESSION['success'] = "เพิ่มสินค้าใหม่เรียบร้อยแล้ว ✅";
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกข้อมูลสินค้าให้ครบถ้วน ❌";
    }
}

if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $imageName = $stmt->fetchColumn();

    try {
        $conn->beginTransaction();
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: products.php");
        exit;
    }

    if ($imageName && $imageName !== 'no-image.jpg') {
        $baseDir = realpath(__DIR__ . '/../product_images');
        $filePath = realpath($baseDir . '/' . $imageName);
        if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath);
        }
    }
    header("Location: products.php");
    exit;
}

$products = $conn->query("SELECT p.*, c.category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.category_id 
                          ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสินค้า</title>
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

    h2, h5.card-title { 
        color: var(--rust); 
        font-weight: bold;
    }

    .card { 
        background: #fff; 
        border-radius: 16px; 
        border: 1px solid var(--cloud);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15); 
    }

    .btn-primary { 
        background-color: var(--tan); 
        border: none; 
        font-weight: bold; 
        transition: all 0.3s ease; 
        color: var(--night);
    }
    .btn-primary:hover { 
        background-color: var(--rust); 
        color: #fff; 
    }

    .btn-outline-secondary { 
        border-color: var(--ash); 
        color: var(--ash); 
        transition: all 0.3s ease; 
    }
    .btn-outline-secondary:hover { 
        background-color: var(--ash); 
        color: #fff; 
    }

    .btn-sm {
        border-radius: 8px;
        font-weight: bold;
        padding: 0.35rem 0.6rem;
        transition: all 0.3s ease;
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
        background-color: #B22222;
        color: #fff;
        border: none;
    }
    .btn-danger:hover {
        background-color: #7A1515; 
        color: #fff;
    }

    .form-control, .form-select { 
        border-radius: 10px; 
        border: 1px solid var(--cloud); 
    }

    .table thead { 
        background-color: var(--ash); 
        color: #fff; 
    }

    .table tbody tr:hover { 
        background-color: rgba(140,89,77,0.15); 
    }

    .img-thumbnail { 
        border-radius: 8px; 
        border: 2px solid var(--cloud);
    }
</style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📦 จัดการสินค้า</h2>
        <a href="index.php" class="btn btn-outline-secondary">← กลับหน้าผู้ดูแล</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- ฟอร์มเพิ่มสินค้า -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">➕ เพิ่มสินค้าใหม่</h5>
            <form method="post" enctype="multipart/form-data" class="row g-3 mt-2">
                <div class="col-md-4"><input type="text" name="product_name" class="form-control" placeholder="ชื่อสินค้า" required></div>
                <div class="col-md-2"><input type="number" step="0.01" name="price" class="form-control" placeholder="ราคา (บาท)" required></div>
                <div class="col-md-2"><input type="number" name="stock" class="form-control" placeholder="จำนวน" required></div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select" required>
                        <option value="">เลือกหมวดหมู่</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><textarea name="description" class="form-control" placeholder="รายละเอียดสินค้า" rows="2"></textarea></div>
                <div class="col-md-6">
                    <label class="form-label">รูปภาพสินค้า (jpg, png)</label>
                    <input type="file" name="product_image" class="form-control">
                </div>
                <div class="col-12"><button type="submit" name="add_product" class="btn btn-primary">บันทึกสินค้า</button></div>
            </form>
        </div>
    </div>

    <!-- ตารางสินค้า -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">📋 รายการสินค้า</h5>
            <table class="table table-hover align-middle mt-3">
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>หมวดหมู่</th>
                        <th>ราคา</th>
                        <th>คงเหลือ</th>
                        <th>รูป</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['product_name']) ?></td>
                        <td><?= htmlspecialchars($p['category_name']) ?></td>
                        <td><?= number_format($p['price'],2) ?> บาท</td>
                        <td><?= $p['stock'] ?></td>
                        <td>
                            <?php if (!empty($p['image'])): ?>
                                <img src="../product_images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="edit_products.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-warning">✏️ แก้ไข</a>
                            <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้านี้?')">🗑️ ลบ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" class="text-center text-muted">— ยังไม่มีสินค้า —</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
