<?php
require_once '../config.php';
require_once 'auth_admin.php';

if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];

    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
    }
    header("Location: users.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสมาชิก</title>
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

.table thead {
    background-color: var(--tan);
    color: var(--night);
}

.table tbody tr:hover {
    background-color: rgba(217,171,130,0.2);
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
</style>
</head>

<body>
<div class="container mt-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">👥 จัดการสมาชิก</h2>
        <a href="index.php" class="btn btn-ash">← กลับหน้าผู้ดูแล</a>
    </div>

    <div class="card p-4">
        <?php if (count($users) === 0): ?>
            <div class="alert alert-warning text-center">ยังไม่มีสมาชิกในระบบ</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead>
                        <tr>
                            <th>ชื่อผู้ใช้</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>อีเมล</th>
                            <th>วันที่สมัคร</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                                <a href="users.php?delete=<?= $user['user_id'] ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('คุณต้องการลบสมาชิกคนนี้หรือไม่?')">ลบ</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>    
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
