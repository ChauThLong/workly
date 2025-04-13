<?php
// Gọi session_start() ngay đầu file, trước bất kỳ dữ liệu nào
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'includes/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID bài đăng không hợp lệ.";
    exit;
}

$job_id = (int) $_GET['id'];

// Lấy thông tin chi tiết công việc
$stmt = $conn->prepare("
    SELECT jp.*, e.company_name, e.company_logo, e.website
    FROM job_posts jp
    JOIN employers e ON jp.employer_id = e.id
    WHERE jp.id = :id
");
$stmt->execute([':id' => $job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo "Không tìm thấy bài đăng.";
    exit;
}

// Lấy danh sách bài tuyển dụng hot cho sidebar
$hot_jobs_stmt = $conn->prepare("
    SELECT jp.*, e.company_name
    FROM job_posts jp
    JOIN employers e ON jp.employer_id = e.id
    WHERE jp.status = 'active'
    ORDER BY jp.created_at DESC
    LIMIT 5
");
$hot_jobs_stmt->execute();
$hot_jobs = $hot_jobs_stmt->fetchAll(PDO::FETCH_ASSOC);

function isNew($created_at)
{
    $posted = new DateTime($created_at);
    $now = new DateTime();
    return $now->diff($posted)->days <= 3;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> - Chi tiết công việc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="main-content">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="/index.php">DOANH NGHIỆP</a> / 
                <a href="/jobs.php">TUYỂN DỤNG</a> / 
                <span><?= htmlspecialchars($job['title']) ?></span>
            </div>

            <!-- Tiêu đề bài viết -->
            <h2>
                <?= htmlspecialchars($job['title']) ?>
                <?php if (isNew($job['created_at'])): ?>
                    <span class="new-badge">Mới</span>
                <?php endif; ?>
            </h2>

            <!-- Thông tin cơ bản -->
            <div class="job-info">
                <div class="job-image">
                    <img src="<?= $job['company_logo'] ?: 'uploads/companies/default.png' ?>" alt="Company Logo">
                </div>
                <div class="job-meta">
                    <h5><?= htmlspecialchars($job['company_name']) ?></h5>
                    <?php if (!empty($job['website'])): ?>
                        <p><strong>Website:</strong> <a href="<?= htmlspecialchars($job['website']) ?>" target="_blank"><?= htmlspecialchars($job['website']) ?></a></p>
                    <?php endif; ?>
                    <p><strong>Ngày đăng:</strong> <?= date('d/m/Y', strtotime($job['created_at'])) ?></p>
                </div>
            </div>

            <!-- Nội dung chi tiết -->
            <div class="job-details">
                <h5>Mô tả công việc</h5>
                <div class="job-description">
                    <?= nl2br(htmlspecialchars($job['description'])) ?>
                </div>

                <h5>Yêu cầu công việc</h5>
                <div class="job-requirements">
                    <?php if (!empty($job['requirements'])): ?>
                        <?= nl2br(htmlspecialchars($job['requirements'])) ?>
                    <?php else: ?>
                        <p><em>Không có.</em></p>
                    <?php endif; ?>
                </div>

                <h5>Quyền lợi</h5>
                <div class="job-benefits">
                    <?php if (!empty($job['benefit'])): ?>
                        <?= nl2br(htmlspecialchars($job['benefit'])) ?>
                    <?php else: ?>
                        <p><em>Không có.</em></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nút ứng tuyển -->
            <a href="/apply.php?job_id=<?= $job['id'] ?>" class="apply-btn">Ứng tuyển ngay</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>

</html>