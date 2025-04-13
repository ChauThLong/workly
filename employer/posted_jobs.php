<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../login.php');
    exit;
}

function getEmployerId($user_id, $conn)
{
    $stmt = $conn->prepare("SELECT id FROM employers WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}

$employer_id = getEmployerId($_SESSION['user_id'], $conn);
if (!$employer_id) {
    die("Không tìm thấy thông tin nhà tuyển dụng.");
}

$stmt = $conn->prepare("SELECT company_name FROM employers WHERE id = :employer_id");
$stmt->execute([':employer_id' => $employer_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);
$company_name = $employer['company_name'] ?? 'Nhà Tuyển Dụng';

$stmt = $conn->prepare("SELECT id, title, location, salary_min, salary_max, job_type, industry, created_at, expires_at, status FROM job_posts WHERE employer_id = :employer_id ORDER BY created_at DESC");
$stmt->execute([':employer_id' => $employer_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Bài Đăng Tuyển Dụng</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <h2 style="margin: 0px;">Danh Sách Bài Tuyển Dụng</h2>
            <a href="post_job.php" class="apply-btn mb-3 d-inline-block" style="margin-top: 0px; margin: 10px;">Đăng Bài Mới</a>
            <?php if (empty($jobs)): ?>
                <p class="error">Bạn chưa đăng bài tuyển dụng nào.</p>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="job-listing">
                        <div class="job-content">
                            <div class="job-title">
                                <?= htmlspecialchars($job['title']) ?>
                                <span class="new-badge <?= $job['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= $job['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </div>
                            <div class="job-desc">
                                <p><strong>Địa điểm:</strong> <?= htmlspecialchars($job['location'] ?? 'N/A') ?></p>
                                <p><strong>Mức lương:</strong>
                                    <?php
                                    if ($job['salary_min'] && $job['salary_max']) {
                                        echo number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']) . ' VND';
                                    } elseif ($job['salary_min']) {
                                        echo 'Từ ' . number_format($job['salary_min']) . ' VND';
                                    } elseif ($job['salary_max']) {
                                        echo 'Đến ' . number_format($job['salary_max']) . ' VND';
                                    } else {
                                        echo 'Thỏa thuận';
                                    }
                                    ?>
                                </p>
                                <p><strong>Loại công việc:</strong> <?= htmlspecialchars($job['job_type'] === 'full-time' ? 'Toàn thời gian' : 'Bán thời gian') ?></p>
                                <p><strong>Ngành nghề:</strong> <?= htmlspecialchars($job['industry'] ?? 'N/A') ?></p>
                                <p><strong>Ngày đăng:</strong> <?= date('d/m/Y', strtotime($job['created_at'])) ?></p>
                                <p><strong>Ngày hết hạn:</strong> <?= $job['expires_at'] ? date('d/m/Y', strtotime($job['expires_at'])) : 'N/A' ?></p>
                            </div>
                            <div class="job-actions">
                                <a href="/job_detail.php?id=<?= $job['id'] ?>" class="apply-btn btn-action">Xem</a>
                                <a href="edit_job_employer.php?id=<?= $job['id'] ?>" class="apply-btn btn-action" style="background-color: #ffc107;">Sửa</a>
                                <a href="delete_job_employer.php?id=<?= $job['id'] ?>" class="apply-btn btn-action" style="background-color: #dc3545;" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>