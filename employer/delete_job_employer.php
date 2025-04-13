<?php
session_start();
require_once '../includes/config.php';

// Kiểm tra đăng nhập và vai trò
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../login.php');
    exit;
}

// Hàm lấy employer_id từ user_id
function getEmployerId($user_id, $conn)
{
    $stmt = $conn->prepare("SELECT id FROM employers WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}

$employer_id = getEmployerId($_SESSION['user_id'], $conn);
if (!$employer_id) {
    header('Location: posted_jobs.php');
    exit;
}

// Kiểm tra job_id từ URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: posted_jobs.php');
    exit;
}
$job_id = (int)$_GET['id'];

// Xóa bài đăng
$stmt = $conn->prepare("DELETE FROM job_posts WHERE id = :id AND employer_id = :employer_id");
$stmt->execute([':id' => $job_id, ':employer_id' => $employer_id]);

// Chuyển hướng về danh sách bài đăng
header('Location: posted_jobs.php');
exit;
