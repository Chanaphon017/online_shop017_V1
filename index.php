<?php
session_start();
require_once 'config.php';

$isLoggedIn = isset($_SESSION['user_id']);

$stmt = $conn->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>หน้าหลัก - ร้านค้าออนไลน์</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* 🎨 Minimal Palette */
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

    /* Navbar */
    .navbar {
      background: var(--ash) !important;
    }
    .navbar .navbar-brand {
      font-weight: 600;
      color: var(--tan) !important;
    }

    /* Product Card */
    .product-card {
      background: #fff;
      border-radius: .75rem;
      overflow: hidden;
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }

    .product-thumb {
      height: 180px;
      object-fit: cover;
      width: 100%;
    }

    .product-meta {
      font-size: .75rem;
      color: var(--cloud);
      text-transform: uppercase;
    }

    .product-title {
      font-size: 1rem;
      margin: .25rem 0 .5rem;
      font-weight: 600;
      color: var(--night);
    }

    .price {
      font-weight: 700;
      color: var(--rust);
    }

    /* Buttons */
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

    /* Badge */
    .badge-top-left {
      position: absolute;
      top: .5rem;
      left: .5rem;
      border-radius: .375rem;
    }
    .badge-new {
      background: var(--tan);
      color: var(--night);
    }
    .badge-hot {
      background: var(--rust);
      color: #fff;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">🛒 ร้านค้าออนไลน์</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="ms-auto">
        <?php if ($isLoggedIn): ?>
          <span class="text-white me-3">👋 <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (<?= $_SESSION['role'] ?>)</span>
          <a href="profile.php" class="btn btn-outline-ash btn-sm me-2">ข้อมูลส่วนตัว</a>
          <a href="cart.php" class="btn btn-tan btn-sm me-2">ดูตะกร้า</a>
          <a href="logout.php" class="btn btn-outline-light btn-sm">ออกจากระบบ</a>
        <?php else: ?>
          <a href="login.php" class="btn btn-tan btn-sm me-2">เข้าสู่ระบบ</a>
          <a href="register.php" class="btn btn-outline-ash btn-sm">สมัครสมาชิก</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Main -->
<div class="container mt-5">
  <h2 class="fw-bold mb-4 text-center">🛍️ รายการสินค้า</h2>
  <div class="row g-4">
    <?php foreach ($products as $p): ?>
      <?php
        $img = !empty($p['image']) ? 'product_images/' . rawurlencode($p['image']) : 'product_images/no-image.jpg';
        $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7*24*3600);
        $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;
        $rating = isset($p['rating']) ? (float)$p['rating'] : 4.5;
        $full = floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
      ?>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card product-card h-100 position-relative shadow-sm">
          <!-- Badge -->
          <?php if ($isNew): ?>
            <span class="badge badge-new badge-top-left">NEW</span>
          <?php elseif ($isHot): ?>
            <span class="badge badge-hot badge-top-left">HOT</span>
          <?php endif; ?>

          <!-- Image -->
          <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="d-block">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="product-thumb">
          </a>

          <!-- Info -->
          <div class="px-3 pb-3 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <div class="product-meta"><?= htmlspecialchars($p['category_name'] ?? 'Category') ?></div>
              <button class="btn btn-link p-0 text-muted" title="Add to wishlist" type="button">
                <i class="bi bi-heart"></i>
              </button>
            </div>
            <a class="text-decoration-none" href="product_detail.php?id=<?= (int)$p['product_id'] ?>">
              <div class="product-title"><?= htmlspecialchars($p['product_name']) ?></div>
            </a>

            <!-- Rating -->
            <div class="rating mb-2">
              <?php for ($i=0; $i<$full; $i++): ?><i class="bi bi-star-fill text-warning"></i><?php endfor; ?>
              <?php if ($half): ?><i class="bi bi-star-half text-warning"></i><?php endif; ?>
              <?php for ($i=0; $i<5-$full-$half; $i++): ?><i class="bi bi-star text-warning"></i><?php endfor; ?>
            </div>

            <div class="price mb-3"><?= number_format((float)$p['price'], 2) ?> บาท</div>

            <div class="mt-auto d-flex gap-2">
              <?php if ($isLoggedIn): ?>
                <form action="cart.php" method="post" class="d-inline-flex gap-2 m-0">
                  <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="btn btn-sm btn-tan">เพิ่มในตะกร้า</button>
                </form>
              <?php else: ?>
                <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ</small>
              <?php endif; ?>
              <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-ash ms-auto">ดูรายละเอียด</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
