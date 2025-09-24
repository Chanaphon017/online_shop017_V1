<?php
session_start();

    // ตรวจสอบสิทธิ์ผู้ใช้
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
        }
?>