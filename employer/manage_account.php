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

// Lấy thông tin từ bảng users
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die("Không tìm thấy thông tin người dùng.");
}

// Lấy thông tin từ bảng employers
$stmt = $conn->prepare("SELECT company_name, website, description, company_logo FROM employers WHERE id = :employer_id");
$stmt->execute([':employer_id' => $employer_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$employer) {
    die("Không tìm thấy thông tin công ty.");
}

$logo_path = $employer['company_logo'] ?: 'assets/images/default-null.png';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $company_name = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING);
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Kiểm tra các trường bắt buộc
    if (empty($username) || empty($email) || empty($company_name)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } else {
        // Xử lý cập nhật logo
        $logo_path = $employer['company_logo'];
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/';
            $file_name = time() . '_' . basename($_FILES['company_logo']['name']);
            $upload_path = $upload_dir . $file_name;

            // Kiểm tra loại file (chỉ cho phép hình ảnh)
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['company_logo']['tmp_name']);
            if (!in_array($file_type, $allowed_types)) {
                $error = "Chỉ được tải lên file hình ảnh (JPEG, PNG, GIF).";
            } elseif (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
                $logo_path = 'assets/images/' . $file_name;
            } else {
                $error = "Có lỗi xảy ra khi tải lên logo.";
            }
        }

        // Xử lý cập nhật mật khẩu
        if ($current_password && $new_password && $confirm_password) {
            // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = :user_id");
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($current_password, $current_user['password'])) {
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) < 6) {
                        $error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                        $stmt->execute([':password' => $hashed_password, ':user_id' => $_SESSION['user_id']]);
                    }
                } else {
                    $error = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
                }
            } else {
                $error = "Mật khẩu hiện tại không đúng.";
            }
        }

        // Nếu không có lỗi, cập nhật thông tin
        if (!isset($error)) {
            // Cập nhật bảng users
            $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email WHERE id = :user_id");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':user_id' => $_SESSION['user_id']
            ]);

            // Cập nhật bảng employers
            $stmt = $conn->prepare("
                UPDATE employers 
                SET company_name = :company_name, website = :website, description = :description, company_logo = :company_logo 
                WHERE id = :employer_id
            ");
            $stmt->execute([
                ':company_name' => $company_name,
                ':website' => $website ?: null,
                ':description' => $description ?: null,
                ':company_logo' => $logo_path,
                ':employer_id' => $employer_id
            ]);

            // Làm mới trang để hiển thị thông tin mới
            header('Location: manage_account.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tài Khoản Nhà Tuyển Dụng</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container--form">
        <h2>Quản Lý Tài Khoản Nhà Tuyển Dụng</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <!-- Thông tin tài khoản -->
            <div class="form-section">
                <h3>Thông Tin Tài Khoản</h3>
                <div class="form-group">
                    <label for="username">Tên đăng nhập *</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>

            <!-- Thông tin công ty -->
            <div class="form-section">
                <h3>Thông Tin Công Ty</h3>
                <div class="form-group">
                    <label for="company_name">Tên công ty *</label>
                    <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($employer['company_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" value="<?= htmlspecialchars($employer['website'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="description">Mô tả công ty</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($employer['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group " style="margin-bottom: 0;">
                    <label for="company_logo">Logo công ty</label>
                    <div class="avatar-preview">
                        <img src="../<?= htmlspecialchars($logo_path) ?>" alt="Logo Công Ty" class="profile-avatar">
                    </div>
                    <input type="file" id="company_logo" name="company_logo" accept="image/*">
                </div>
            </div>

            <!-- Đổi mật khẩu -->
            <div class="form-section">
                <h3>Đổi Mật Khẩu</h3>
                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="confirm_password">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <div class="form-actions" style="display: flex; justify-content: center;">
                <button type="submit" class="update-btn">Cập Nhật</button>
            </div>
            <div style="display: flex; justify-content: center;">
                <a href="posted_jobs.php" class="apply-btn" style="background-color: #dc3545;">Quay Lại</a>
            </div>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('Bạn có chắc muốn cập nhật thông tin?')) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>