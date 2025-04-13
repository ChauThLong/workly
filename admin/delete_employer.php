<?php
session_start();
require_once '../includes/config.php';

// Chỉ admin mới được xóa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login_admin.php');
    exit;
}

// Kiểm tra ID hợp lệ
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_employers.php');
    exit;
}

$id = (int)$_GET['id'];

// Thực thi xóa
$stmt = $conn->prepare("DELETE FROM employers WHERE id = :id");
$stmt->execute([':id' => $id]);

// Chuyển hướng về trang quản lý
header('Location: admin_employers.php');
exit;
