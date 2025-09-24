<?php
require '../config.php'; // เชื่อมต่อ PDO
require 'auth_admin.php'; // ตรวจสอบสิทธิ์ Admin

// ตรวจสอบว่ามีพารามิเตอร์ id มาจริงไหม
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = (int)$_GET['id'];

// ดึงข้อมูลสมาชิก (เฉพาะ role = member)
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<h3 class='text-danger text-center mt-5'>❌ ไม่พบสมาชิก</h3>";
    exit;
}

$error = null;

// เมื่อกด Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    // ตรวจสอบความครบถ้วน
    if ($username === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    }

    // ตรวจสอบซ้ำ (username/email ชนกับคนอื่น)
    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
            $error = "❌ Username หรือ Email มีอยู่ในระบบแล้ว";
        }
    }

    // ตรวจรหัสผ่าน (กรณีต้องการเปลี่ยน)
    $updatePassword = false;
    $hashed = null;
    if (!$error && ($password !== '' || $confirm !== '')) {
        if (strlen($password) < 6) {
            $error = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
        } elseif ($password !== $confirm) {
            $error = "รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = true;
        }
    }

    // อัปเดตข้อมูล
    if (!$error) {
        if ($updatePassword) {
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $hashed, $user_id];
        } else {
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $user_id];
        }
        $upd = $conn->prepare($sql);
        $upd->execute($args);

        header("Location: users.php");
        exit;
    }

    // สะท้อนค่ากลับในฟอร์ม
    $user['username']  = $username;
    $user['full_name'] = $full_name;
    $user['email']     = $email;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">👤 แก้ไขข้อมูลสมาชิก</h2>
        <a href="users.php" class="btn btn-outline-secondary">← กลับหน้ารายชื่อสมาชิก</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" required 
                           value="<?= htmlspecialchars($user['username']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="full_name" class="form-control" 
                           value="<?= htmlspecialchars($user['full_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required 
                           value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">รหัสผ่านใหม่ <small class="text-muted">(เว้นว่างหากไม่ต้องการเปลี่ยน)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">💾 บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
