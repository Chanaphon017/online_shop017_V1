<?php
session_start();
require_once 'config.php';

// ถ้าผู้ใช้ล็อกอินแล้วให้ไปหน้า index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
    $password        = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $error = "กรุณากรอกชื่อผู้ใช้หรือรหัสผ่าน";
    } else {
        $sql  = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root {
    --tan: #D9AB82;
    --rust: #8C594D;
    --cloud: #A6A6A6;
    --ash: #404040;
    --night: #0D0D0D;
}

body {
    font-family: "Segoe UI", Tahoma, sans-serif;
    background-color: var(--cloud);
    padding-top: 60px;
    color: var(--night);
}

.login-card {
    background: #fff;
    padding: 30px;
    max-width: 400px;
    margin: auto;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
    color: var(--rust);
}

.btn-login {
    width: 100%;
    background-color: var(--tan);
    color: var(--night);
    font-weight: bold;
    border: none;
}

.btn-login:hover {
    background-color: var(--rust);
    color: #fff;
}

.btn-register {
    display: block;
    text-align: center;
    margin-top: 10px;
    color: var(--ash);
    text-decoration: none;
}

.btn-register:hover {
    color: #000;
    text-decoration: underline;
}

.alert {
    max-width: 400px;
    margin: 10px auto;
    text-align: center;
    border-radius: 12px;
}

.alert-success {
    background-color: #D9AB82;
    color: var(--night);
    border: none;
}

.alert-danger {
    background-color: #8C594D;
    color: #fff;
    border: none;
}
</style>
</head>
<body>

<?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
    <div class="alert alert-success">สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="login-card">
    <h2>เข้าสู่ระบบ</h2>
    <form method="post">
        <div class="mb-3">
            <label for="username_or_email" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
            <input type="text" class="form-control" id="username_or_email" name="username_or_email"
                   placeholder="กรอกชื่อผู้ใช้หรืออีเมล"
                   value="<?= htmlspecialchars($_POST['username_or_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="กรอกรหัสผ่าน" required>
        </div>
        <button type="submit" class="btn btn-login">เข้าสู่ระบบ</button>
        <a href="register.php" class="btn-register">สมัครสมาชิก</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
