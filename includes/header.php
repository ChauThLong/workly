<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

// Gán session từ cookie nếu có
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'candidate';
    $_SESSION['username'] = $_COOKIE['username'];
}

// Lấy tên hiển thị dựa trên vai trò
$displayName = $_SESSION['username'] ?? 'Người dùng';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'candidate') {
        $stmt = $conn->prepare("SELECT full_name FROM candidates WHERE user_id = :uid");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['full_name']) {
            $displayName = $row['full_name'];
        }
    } elseif ($_SESSION['role'] === 'employer') {
        $stmt = $conn->prepare("SELECT company_name FROM employers WHERE user_id = :uid");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['company_name']) {
            $displayName = $row['company_name'];
        }
    } elseif ($_SESSION['role'] === 'admin') {
        $displayName = 'Admin';
    }
}
?>
<!-- Header Navigation -->
<div class="navbar">
    <div class="left-side">
        <a href="/index.php" class="logo">WORKLY</a>
        <a href="/index.php">Home</a>
        <a href="/jobs.php">Jobs</a>
        <!-- <a href="/about.php">About</a>
        <a href="/contact.php">Contact</a> -->
    </div>

    <div class="right-side">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
                <button class="dropdown-btn">
                    👋 Xin chào, <?= htmlspecialchars($displayName) ?>
                </button>
                <div class="dropdown-content">
                    <?php if ($_SESSION['role'] === 'candidate'): ?>
                        <a href="/candidate/profile.php">Hồ sơ ứng viên</a>
                        <a href="/candidate/edit_profile.php">Tài khoản</a>
                    <?php elseif ($_SESSION['role'] === 'employer'): ?>
                        <a href="/employer/posted_jobs.php">Quản lý bài đăng</a>
                        <a href="/employer/manage_applications.php">Quản lý ứng tuyển</a>
                        <a href="/employer/manage_account.php">Tài khoản</a>
                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                        <a href="/admin/admin_candidates.php">Quản lý ứng viên</a>
                        <a href="/admin/admin_employers.php">Quản lý nhà tuyển dụng</a>
                        <a href="/admin/admin_jobs.php">Quản lý công việc</a>
                    <?php endif; ?>
                    <a href="/logout.php">Đăng xuất</a>
                </div>
            </div>
        <?php else: ?>
            <div class="dropdown">
                <button class="dropdown-btn">Đăng nhập</button>
                <div class="dropdown-content">
                    <a href="/candidate/login_candidate.php">For Candidate</a>
                    <a href="/employer/login_employer.php">For Employer</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>