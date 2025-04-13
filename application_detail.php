<?php
session_start();
require_once 'includes/config.php';

// Kiểm tra đăng nhập và vai trò
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: employer/login_employer.php');
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

// Lấy ID bài ứng tuyển từ URL
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($application_id <= 0) {
    header('Location: manage_applications.php');
    exit;
}

// Lấy thông tin bài ứng tuyển
$stmt = $conn->prepare("
    SELECT a.id, a.candidate_id, a.job_post_id, a.applied_at, a.status, 
           u.username, u.email, 
           c.full_name, c.phone, 
           jp.title, jp.location, jp.salary_min, jp.salary_max, jp.job_type, jp.industry, jp.expires_at, jp.status AS job_status, 
           e.company_name, e.company_logo
    FROM applications a
    JOIN candidates c ON a.candidate_id = c.id
    JOIN users u ON c.user_id = u.id
    JOIN job_posts jp ON a.job_post_id = jp.id
    JOIN employers e ON jp.employer_id = e.id
    WHERE a.id = :application_id AND jp.employer_id = :employer_id
");
$stmt->execute([':application_id' => $application_id, ':employer_id' => $employer_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    die("Không tìm thấy bài ứng tuyển hoặc bạn không có quyền truy cập.");
}

$logo_path = $application['company_logo'] ?: 'assets/images/default-null.png';

// Xử lý cập nhật trạng thái
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    if (in_array($status, ['not_approved', 'approved', 'rejected'])) {
        $stmt = $conn->prepare("UPDATE applications SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $application_id]);
        $success_message = "Cập nhật trạng thái thành công!";
        // Làm mới dữ liệu
        $application['status'] = $status;
    } else {
        $error_message = "Trạng thái không hợp lệ.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Bài Ứng Tuyển</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .application-detail {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .status-not-approved {
            background: #ffcc00;
            color: #333;
        }

        .status-approved {
            background: #28a745;
            color: white;
        }

        .status-rejected {
            background: #dc3545;
            color: white;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-4">
        <div>
            <h2 class="mb-4">Chi Tiết Bài Ứng Tuyển</h2>
            <div class="container">
                <div>
                    <!-- Thông tin công việc -->
                    <div class="application-detail">
                        <h3 class="mb-3">Thông Tin Công Việc</h3>
                        <div class="d-flex gap-3">
                            <div class="job-image">
                                <img src="../<?= htmlspecialchars($logo_path) ?>" alt="Logo Công Ty" style="width: 150px; height: 100px; object-fit: contain;">
                            </div>
                            <div class="job-content flex-grow-1">
                                <div class="job-title">
                                    <?= htmlspecialchars($application['title']) ?>
                                    <span class="new-badge <?= $application['job_status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                        <?= $application['job_status'] === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                    </span>
                                </div>
                                <div class="job-desc">
                                    <p><strong>Công ty:</strong> <?= htmlspecialchars($application['company_name']) ?></p>
                                    <p><strong>Địa điểm:</strong> <?= htmlspecialchars($application['location'] ?? 'N/A') ?></p>
                                    <p><strong>Mức lương:</strong>
                                        <?php
                                        if ($application['salary_min'] && $application['salary_max']) {
                                            echo number_format($application['salary_min']) . ' - ' . number_format($application['salary_max']) . ' VND';
                                        } elseif ($application['salary_min']) {
                                            echo 'Từ ' . number_format($application['salary_min']) . ' VND';
                                        } elseif ($application['salary_max']) {
                                            echo 'Đến ' . number_format($application['salary_max']) . ' VND';
                                        } else {
                                            echo 'Thỏa thuận';
                                        }
                                        ?>
                                    </p>
                                    <p><strong>Loại công việc:</strong> <?= htmlspecialchars($application['job_type'] === 'full-time' ? 'Toàn thời gian' : 'Bán thời gian') ?></p>
                                    <p><strong>Ngành nghề:</strong> <?= htmlspecialchars($application['industry'] ?? 'N/A') ?></p>
                                    <p><strong>Ngày hết hạn:</strong> <?= $application['expires_at'] ? date('d/m/Y', strtotime($application['expires_at'])) : 'N/A' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin ứng viên -->
                    <div class="application-detail">
                        <h3 class="mb-3">Thông Tin Ứng Viên</h3>
                        <div class="job-desc">
                            <p><strong>Tên:</strong> <?= htmlspecialchars($application['full_name'] ?? $application['username']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($application['email']) ?></p>
                            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($application['phone'] ?? 'N/A') ?></p>
                        </div>
                    </div>
    
                    <!-- Thông tin ứng tuyển -->
                    <div class="application-detail">
                        <h3 class="mb-3">Thông Tin Ứng Tuyển</h3>
                        <div class="job-desc">
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
                </div>

                <!-- Cập nhật trạng thái -->
                <div>
                    <div class="form-section">
                        <h3 class="mb-3">Duyệt ứng viên</h3>
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?= $success_message ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?= $error_message ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control" required>
                                    <option value="not_approved" <?= $application['status'] === 'not_approved' ? 'selected' : '' ?>>Chưa được duyệt</option>
                                    <option value="approved" <?= $application['status'] === 'approved' ? 'selected' : '' ?>>Đã được duyệt</option>
                                    <option value="rejected" <?= $application['status'] === 'rejected' ? 'selected' : '' ?>>Bị từ chối</option>
                                </select>
                            </div>
                            <div class="form-actions mt-3">
                                <button type="submit" class="apply-btn">Cập Nhật</button>
                                <a href="employer/manage_applications.php" class="apply-btn" style="background-color: #dc3545;">Quay Lại</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>