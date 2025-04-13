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

// Kiểm tra job_id từ URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: posted_jobs.php');
    exit;
}
$job_id = (int)$_GET['id'];

// Lấy thông tin bài đăng để hiển thị trong biểu mẫu
$stmt = $conn->prepare("SELECT * FROM job_posts WHERE id = :id AND employer_id = :employer_id");
$stmt->execute([':id' => $job_id, ':employer_id' => $employer_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Bài đăng không tồn tại hoặc bạn không có quyền chỉnh sửa.");
}

// Xử lý cập nhật bài đăng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $salary_min = filter_input(INPUT_POST, 'salary_min', FILTER_SANITIZE_NUMBER_INT);
    $salary_max = filter_input(INPUT_POST, 'salary_max', FILTER_SANITIZE_NUMBER_INT);
    $job_type = filter_input(INPUT_POST, 'job_type', FILTER_SANITIZE_STRING);
    $industry = filter_input(INPUT_POST, 'industry', FILTER_SANITIZE_STRING);
    $keywords = filter_input(INPUT_POST, 'keywords', FILTER_SANITIZE_STRING);
    $expires_at = filter_input(INPUT_POST, 'expires_at', FILTER_SANITIZE_STRING);
    $requirements = filter_input(INPUT_POST, 'requirements', FILTER_SANITIZE_STRING);
    $benefits = filter_input(INPUT_POST, 'benefits', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    if (empty($title) || empty($description) || empty($job_type)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
    } else {
        $stmt = $conn->prepare("
            UPDATE job_posts 
            SET title = :title, description = :description, location = :location, 
                salary_min = :salary_min, salary_max = :salary_max, job_type = :job_type, 
                industry = :industry, keywords = :keywords, expires_at = :expires_at, 
                requirements = :requirements, benefits = :benefits, status = :status, 
                updated_at = NOW()
            WHERE id = :id AND employer_id = :employer_id
        ");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':location' => $location ?: null,
            ':salary_min' => $salary_min ?: null,
            ':salary_max' => $salary_max ?: null,
            ':job_type' => $job_type,
            ':industry' => $industry ?: null,
            ':keywords' => $keywords ?: null,
            ':expires_at' => $expires_at ?: null,
            ':requirements' => $requirements ?: null,
            ':benefits' => $benefits ?: null,
            ':status' => $status,
            ':id' => $job_id,
            ':employer_id' => $employer_id
        ]);
        header('Location: posted_jobs.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Bài Đăng Tuyển Dụng</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container--form">
        <h2>Chỉnh Sửa Bài Đăng Tuyển Dụng</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="post">
            <div class="form-section">
                <div class="form-group">
                    <label for="title">Tiêu đề công việc *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($job['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Mô tả công việc *</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($job['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="location">Địa điểm làm việc</label>
                    <input type="text" id="location" name="location" value="<?= htmlspecialchars($job['location'] ?? '') ?>">
                </div>
            </div>

            <div class="form-section">
                <div class="form-group">
                    <label for="salary_min">Mức lương tối thiểu (VND)</label>
                    <input type="number" id="salary_min" name="salary_min" value="<?= htmlspecialchars($job['salary_min'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="salary_max">Mức lương tối đa (VND)</label>
                    <input type="number" id="salary_max" name="salary_max" value="<?= htmlspecialchars($job['salary_max'] ?? '') ?>">
                </div>
            </div>

            <div class="form-section">
                <div class="form-group">
                    <label for="job_type">Loại công việc *</label>
                    <select id="job_type" name="job_type" required>
                        <option value="full-time" <?= $job['job_type'] === 'full-time' ? 'selected' : '' ?>>Toàn thời gian</option>
                        <option value="part-time" <?= $job['job_type'] === 'part-time' ? 'selected' : '' ?>>Bán thời gian</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="industry">Ngành nghề</label>
                    <input type="text" id="industry" name="industry" value="<?= htmlspecialchars($job['industry'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="keywords">Từ khóa</label>
                    <input type="text" id="keywords" name="keywords" value="<?= htmlspecialchars($job['keywords'] ?? '') ?>" placeholder="Nhập từ khóa (cách nhau bằng dấu phẩy)">
                </div>
                <div class="form-group">
                    <label for="expires_at">Ngày hết hạn</label>
                    <input type="date" id="expires_at" name="expires_at" value="<?= htmlspecialchars($job['expires_at'] ?? '') ?>">
                </div>
            </div>

            <div class="form-section">
                <div class="form-group">
                    <label for="requirements">Yêu cầu công việc</label>
                    <textarea id="requirements" name="requirements"><?= htmlspecialchars($job['requirements'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="benefits">Quyền lợi</label>
                    <textarea id="benefits" name="benefits"><?= htmlspecialchars($job['benefits'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="status">Trạng thái *</label>
                    <select id="status" name="status" required>
                        <option value="active" <?= $job['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= $job['status'] === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="update-btn">Cập Nhật</button>
                <a href="posted_jobs.php" class="apply-btn" style="background-color: #dc3545; margin-left: 10px;">Hủy</a>
            </div>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>