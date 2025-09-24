<?php
require '../config.php'; // เชื่อมต่อฐานข้อมูล PDO
require 'auth_admin.php'; // ตรวจสอบสิทธิ์ Admin

// ตรวจสอบว่ามี id ของสินค้า
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}
$product_id = intval($_GET['id']);

// ดึงข้อมูลสินค้า
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "สินค้าไม่พบ";
    exit;
}

// ดึงหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ประมวลผลเมื่อกดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $image = $product['image'];

    // ลบรูปเดิม
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
        if (!empty($image) && file_exists("../product_images/$image")) {
            unlink("../product_images/$image");
        }
        $image = null;
    }

    // อัปโหลดรูปใหม่
    if (!empty($_FILES['product_image']['name'])) {
        $file = $_FILES['product_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png'];
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . "." . $ext;
            move_uploaded_file($file['tmp_name'], "../product_images/$new_name");
            // ลบรูปเดิมถ้ามี
            if (!empty($image) && file_exists("../product_images/$image")) {
                unlink("../product_images/$image");
            }
            $image = $new_name;
        }
    }

    // อัปเดตฐานข้อมูล
    $stmt = $conn->prepare("UPDATE products SET product_name=?, price=?, stock=?, category_id=?, description=?, image=? WHERE product_id=?");
    $stmt->execute([$product_name, $price, $stock, $category_id, $description, $image, $product_id]);

    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --tan: #D9AB82;
            --rust: #8C594D;
            --cloud: #A6A6A6;
            --ash: #404040;
            --night: #0D0D0D;
        }
        body { background-color: var(--cloud); color: var(--night); font-family: "Segoe UI", Tahoma, sans-serif; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        h2 { color: var(--rust); }
        .btn-back { background-color: var(--ash); color: #fff; border: none; }
        .btn-back:hover { background-color: #2d2d2d; }
        .btn-save { background-color: var(--tan); color: var(--night); font-weight: bold; border: none; }
        .btn-save:hover { background-color: var(--rust); color: #fff; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid var(--cloud); }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">✏️ แก้ไขสินค้า</h2>
        <a href="products.php" class="btn btn-back">← กลับไปยังรายการสินค้า</a>
    </div>

    <div class="card shadow-lg border-0">
        <div class="card-body p-4">
            <form method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">ชื่อสินค้า</label>
                    <input type="text" name="product_name" class="form-control"
                           value="<?= htmlspecialchars($product['product_name']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ราคา</label>
                    <input type="number" step="0.01" name="price" class="form-control"
                           value="<?= htmlspecialchars($product['price']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">จำนวนในคลัง</label>
                    <input type="number" name="stock" class="form-control"
                           value="<?= htmlspecialchars($product['stock']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">หมวดหมู่</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= $product['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียดสินค้า</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">รูปปัจจุบัน</label>
                    <?php if (!empty($product['image']) && file_exists("../product_images/".$product['image'])): ?>
                        <img src="../product_images/<?= htmlspecialchars($product['image']) ?>"
                             class="img-thumbnail rounded mb-2" width="180">
                    <?php else: ?>
                        <span class="text-muted d-block mb-2">ไม่มีรูป</span>
                    <?php endif; ?>
                    <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">อัปโหลดรูปใหม่ (jpg, png)</label>
                    <input type="file" name="product_image" class="form-control">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                        <label class="form-check-label" for="remove_image">ลบรูปเดิม</label>
                    </div>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-save btn-lg">💾 บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
