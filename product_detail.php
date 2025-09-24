<?php
session_start();
require_once 'config.php';

if(!isset($_GET['id'])){
    header('Location: index.php');
    exit();
}

$product_id = $_GET['id'];
$isLoggedIn = isset($_SESSION['user_id']);

$stmt = $conn->prepare("SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit();
}
$img = !empty($product['image']) ? 'product_images/' . rawurlencode($product['image']) : 'product_images/no-image.jpg';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['product_name']) ?> - รายละเอียดสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* 🎨 Minimal Dark & Emotive Palette */
        :root {
            --tan: #D9AB82;
            --rust: #8C594D;
            --cloud: #A6A6A6;
            --ash: #404040;
            --night: #0D0D0D;
        }

        body {
            background: var(--cloud);
            color: var(--night);
            font-family: "Segoe UI", sans-serif;
        }

        .card {
            border-radius: 16px;
            border: none;
            background: #fff;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        }

        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--rust);
        }

        /* ปุ่ม */
        .btn-tan {
            background: var(--tan);
            color: var(--night);
            border: none;
        }
        .btn-tan:hover {
            background: var(--rust);
            color: #fff;
        }

        .btn-outline-ash {
            border: 1px solid var(--ash);
            color: var(--ash);
        }
        .btn-outline-ash:hover {
            background: var(--ash);
            color: #fff;
        }

        .product-img {
            max-height: 380px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">

    <a href="index.php" class="btn btn-outline-ash mb-4">← กลับหน้ารายการสินค้า</a>

    <div class="card p-4">
        <div class="row">
            <div class="col-md-5">
                <img src="<?= $img ?>" class="img-fluid product-img" alt="<?= htmlspecialchars($product['product_name']) ?>">
            </div>
            <div class="col-md-7">
                <h2 class="fw-bold"><?= htmlspecialchars($product['product_name']) ?></h2>
                <h6 class="text-muted mb-3">หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></h6>

                <p class="price mb-3">💰 <?= number_format($product['price'], 2) ?> บาท</p>
                <p><strong>คงเหลือ:</strong> <?= htmlspecialchars($product['stock']) ?> ชิ้น</p>

                <?php if ($isLoggedIn): ?>
                    <?php if ($product['stock'] > 0): ?>
                        <form action="cart.php" method="post" class="mt-4">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <div class="input-group mb-3" style="max-width: 220px;">
                                <label class="input-group-text bg-light" for="quantity">จำนวน</label>
                                <input type="number" name="quantity" id="quantity" value="1" min="1"
                                       max="<?= $product['stock'] ?>" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-tan btn-lg">+ เพิ่มในตะกร้า</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger mt-4">❌ สินค้าหมดชั่วคราว</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning mt-4">⚠️ กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
