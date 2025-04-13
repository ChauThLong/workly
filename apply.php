<?php
session_start();
require_once 'includes/config.php';

// Kiểm tra đăng nhập và vai trò là candidate
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'candidate') {
    header('Location: candidate/login_candidate.php');
    exit;
}

// Hàm lấy candidate_id từ user_id
function getCandidateId($user_id, $conn)
{
    $stmt = $conn->prepare("SELECT id FROM candidates WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}

$candidate_id = getCandidateId($_SESSION['user_id'], $conn);
if (!$candidate_id) {
    die("Không tìm thấy thông tin ứng viên.");
}

// Lấy ID công việc từ URL
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
if ($job_id <= 0) {
    die("ID công việc không hợp lệ.");
}

// Lấy thông tin công việc
$stmt = $conn->prepare("
    SELECT jp.*, e.company_name, e.company_logo
    FROM job_posts jp
    JOIN employers e ON jp.employer_id = e.id
    WHERE jp.id = :jid
");
$stmt->execute([':jid' => $job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Không tìm thấy công việc.");
}

$logo_path = $job['company_logo'] ?: 'assets/images/default-null.png';

// Xử lý khi gửi form
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_letter = filter_input(INPUT_POST, 'cover_letter', FILTER_SANITIZE_STRING);

    // Kiểm tra đã ứng tuyển chưa
    $check = $conn->prepare("SELECT * FROM applications WHERE candidate_id = :cid AND job_post_id = :jid");
    $check->execute([':cid' => $candidate_id, ':jid' => $job_id]);

    if ($check->fetch()) {
        $error_message = "Bạn đã ứng tuyển công việc này rồi.";
    } else {
        $apply = $conn->prepare("
            INSERT INTO applications (candidate_id, job_post_id, cover_letter, applied_at, status)
            VALUES (:cid, :jid, :cover, NOW(), 'not_approved')
        ");
        $apply->execute([
            ':cid' => $candidate_id,
            ':jid' => $job_id,
            ':cover' => $cover_letter
        ]);

        $success_message = "Ứng tuyển thành công! Nhà tuyển dụng sẽ xem xét hồ sơ của bạn.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ứng Tuyển Công Việc</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .job-listing {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .job-image img {
            max-width: 100%;
            height: auto;
        }

        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div>
            <h2 class="mb-4">Ứng Tuyển Công Việc</h2>
            <div class="container">
                <!-- Thông tin công việc -->
                <div class="job-listing">
                    <div class="d-flex gap-3">
                        <div class="job-image">
                            <img src="<?= htmlspecialchars($logo_path) ?>" alt="Logo Công Ty" style="width: 150px; height: 100px; object-fit: contain;">
                        </div>
                        <div class="job-content flex-grow-1">
                            <div class="job-title">
                                <?= htmlspecialchars($job['title']) ?>
                                <span class="new-badge <?= $job['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= $job['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </div>
                            <div class="job-desc">
                                <p><strong>Công ty:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
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
                                <p><strong>Ngày hết hạn:</strong> <?= $job['expires_at'] ? date('d/m/Y', strtotime($job['expires_at'])) : 'N/A' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biểu mẫu ứng tuyển -->
                <div class="form-section">
                    <h3 class="mb-3">Lời giới thiệu ngắn</h3>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?= $success_message ?></div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <textarea name="cover_letter" id="cover_letter" class="form-control" rows="6" required placeholder="Nói một chút về chính bạn nào..."></textarea>
                        </div>
                        <div class="form-actions mt-3">
                            <button type="submit" class="apply-btn">Ứng tuyển</button>
                            <a href="jobs.php" class="apply-btn" style="background-color: #dc3545; margin-left: 10px;">Quay Lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>