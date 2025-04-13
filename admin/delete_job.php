<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login_admin.php');
    exit;
}

if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_jobs.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM job_posts WHERE id = :id");
$stmt->execute([':id' => $id]);

header('Location: admin_jobs.php');
exit;
