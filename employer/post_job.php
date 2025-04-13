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

// Lấy thông tin nhà tuyển dụng
$stmt = $conn->prepare("SELECT company_name, website, description, company_logo FROM employers WHERE id = :employer_id");
$stmt->execute([':employer_id' => $employer_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$employer) {
    die("Không tìm thấy thông tin nhà tuyển dụng.");
}
$company_name = $employer['company_name'];
$website = $employer['website'];
$description = $employer['description'];
$logo_path = $employer['company_logo'] ?: 'assets/images/default-null.png';

// Xử lý đăng bài tuyển dụng
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

    if (empty($title) || empty($description) || empty($job_type)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO job_posts (employer_id, title, description, location, salary_min, salary_max, job_type, industry, keywords, expires_at, requirements, benefits, status, created_at, updated_at)
            VALUES (:employer_id, :title, :description, :location, :salary_min, :salary_max, :job_type, :industry, :keywords, :expires_at, :requirements, :benefits, 'active', NOW(), NOW())
        ");
        $stmt->execute([
            ':employer_id' => $employer_id,
            ':title' => $title,
            ':description' => $description,
            ':location' => $location,
            ':salary_min' => $salary_min ?: null,
            ':salary_max' => $salary_max ?: null,
            ':job_type' => $job_type,
            ':industry' => $industry ?: null,
            ':keywords' => $keywords ?: null,
            ':expires_at' => $expires_at ?: null,
            ':requirements' => $requirements ?: null,
            ':benefits' => $benefits ?: null
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
    <title>Đăng Bài Tuyển Dụng</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <div class="row">

            <!-- Phần bên trái: Thông tin nhà tuyển dụng -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-center">Thông Tin Nhà Tuyển Dụng</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center">
                            <img src="<?= $logo_path ?>" alt="Logo Công Ty" class="img-fluid mb-3" style="max-height: 150px;">
                        </div>
                        <h5><?= $company_name ?></h5>
                        <p><?= $description ?></p>
                        <?php if ($website): ?>
                            <a href="<?= $website ?>" target="_blank" class="text-primary"><?= $website ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Phần bên phải: Biểu mẫu đăng bài -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Đăng Bài Tuyển Dụng Mới</div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">Tiêu đề công việc *</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Nhập tiêu đề công việc" required>
                                <div class="invalid-feedback">Vui lòng nhập tiêu đề công việc.</div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">Mô tả công việc *</label>
                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Nhập mô tả công việc" required></textarea>
                                <div class="invalid-feedback">Vui lòng nhập mô tả công việc.</div>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label fw-bold">Địa điểm làm việc</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="Nhập địa điểm làm việc">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="salary_min" class="form-label fw-bold">Mức lương tối thiểu (VND)</label>
                                    <input type="number" class="form-control" id="salary_min" name="salary_min" placeholder="Nhập mức lương tối thiểu">
                                </div>
                                <div class="col-md-6">
                                    <label for="salary_max" class="form-label fw-bold">Mức lương tối đa (VND)</label>
                                    <input type="number" class="form-control" id="salary_max" name="salary_max" placeholder="Nhập mức lương tối đa">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="job_type" class="form-label fw-bold">Loại công việc *</label>
                                <select class="form-control" id="job_type" name="job_type" required>
                                    <option value="">-- Chọn loại công việc --</option>
                                    <option value="full-time">Toàn thời gian</option>
                                    <option value="part-time">Bán thời gian</option>
                                </select>
                                <div class="invalid-feedback">Vui lòng chọn loại công việc.</div>
                            </div>
                            <div class="mb-3">
                                <label for="industry" class="form-label fw-bold">Ngành nghề</label>
                                <input type="text" class="form-control" id="industry" name="industry" placeholder="Nhập ngành nghề">
                            </div>
                            <div class="mb-3">
                                <label for="keywords" class="form-label fw-bold">Từ khóa</label>
                                <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Nhập từ khóa (cách nhau bằng dấu phẩy)">
                            </div>
                            <div class="mb-3">
                                <label for="expires_at" class="form-label fw-bold">Ngày hết hạn</label>
                                <input type="date" class="form-control" id="expires_at" name="expires_at">
                            </div>
                            <div class="mb-3">
                                <label for="requirements" class="form-label fw-bold">Yêu cầu công việc</label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="3" placeholder="Nhập yêu cầu công việc"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="benefits" class="form-label fw-bold">Quyền lợi</label>
                                <textarea class="form-control" id="benefits" name="benefits" rows="3" placeholder="Nhập quyền lợi"></textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="posted_jobs.php" class="btn btn-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-primary">Đăng Tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>

</html>