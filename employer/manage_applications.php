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
    die("Không tìm thấy thông tin nhà tuyển dụng.");
}

// Lấy danh sách bài ứng tuyển
$stmt = $conn->prepare("
    SELECT a.id, a.candidate_id, a.job_post_id, a.applied_at, a.status, 
           u.username AS candidate_name, 
           jp.title AS job_title
    FROM applications a
    JOIN job_posts jp ON a.job_post_id = jp.id
    JOIN candidates c ON a.candidate_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE jp.employer_id = :employer_id
    ORDER BY a.applied_at DESC
");
$stmt->execute([':employer_id' => $employer_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Ứng Tuyển</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .application-listing {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .status-not-approved {
            background: #dc3545;
            color: white;
        }

        .status-approved {
            background: #28a745;
            color: white;
        }

        .status-rejected {
            background: #ffcc00;
            color: #333;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <div>
            <h2 class="mb-4">Quản Lý Bài Ứng Tuyển</h2>
            <?php if (empty($applications)): ?>
                <div class="alert alert-info">Chưa có bài ứng tuyển nào.</div>
            <?php else: ?>
                <?php foreach ($applications as $application): ?>
                    <div class="application-listing">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="application-content">
                                <div class="job-title">
                                    Ứng viên: <?= htmlspecialchars($application['candidate_name']) ?>
                                </div>
                                <div class="job-desc">
                                    <p><strong>Công việc:</strong> <?= htmlspecialchars($application['job_title']) ?></p>
                                    <p><strong>Ngày ứng tuyển:</strong> <?= date('d/m/Y H:i', strtotime($application['applied_at'])) ?></p>
                                    <p><strong>Trạng thái:</strong>
                                        <span class="new-badge status-<?= strtolower($application['status']) ?>">
                                            <?php
                                            switch ($application['status']) {
                                                case 'not_approved':
                                                    echo 'Chưa được duyệt';
                                                    break;
                                                case 'approved':
                                                    echo 'Đã được duyệt';
                                                    break;
                                                case 'rejected':
                                                    echo 'Bị từ chối';
                                                    break;
                                                default:
                                                    echo 'Không xác định';
                                            }
                                            ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="job-actions">
                                <a href="/application_detail.php?id=<?= $application['id'] ?>" class="apply-btn">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>