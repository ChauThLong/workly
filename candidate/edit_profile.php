<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'candidate') {
    header('Location: ../candidate/login_candidate.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$candidate_id = $_SESSION['candidate_id'];

$stmt = $conn->prepare("SELECT full_name, phone, address, avatar, bio, industry FROM candidates WHERE id = :cid");
$stmt->execute([':cid' => $candidate_id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

$user_stmt = $conn->prepare("SELECT password FROM users WHERE id = :uid");
$user_stmt->execute([':uid' => $user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $bio = trim($_POST['bio']);
    $industry = trim($_POST['industry']);

    // Avatar xử lý
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/avatars/';
        $fileName = basename($_FILES['avatar']['name']);
        $filePath = $uploadDir . time() . "_" . $fileName;
        move_uploaded_file($_FILES['avatar']['tmp_name'], $filePath);
        $avatarPath = substr($filePath, 3); // bỏ ../
    } else {
        $avatarPath = $candidate['avatar'];
    }

    // Cập nhật thông tin
    $update = $conn->prepare("
        UPDATE candidates 
        SET full_name = :name, phone = :phone, address = :addr, avatar = :avatar, bio = :bio, industry = :industry 
        WHERE id = :cid
    ");
    $update->execute([
        ':name' => $full_name,
        ':phone' => $phone,
        ':addr' => $address,
        ':avatar' => $avatarPath,
        ':bio' => $bio,
        ':industry' => $industry,
        ':cid' => $candidate_id
    ]);

    // Xử lý đổi mật khẩu nếu có nhập
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if (!password_verify($current, $user['password'])) {
            $error = "Mật khẩu hiện tại không đúng!";
        } elseif ($new !== $confirm) {
            $error = "Mật khẩu mới không khớp!";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $pwd_update = $conn->prepare("UPDATE users SET password = :pwd WHERE id = :uid");
            $pwd_update->execute([':pwd' => $hashed, ':uid' => $user_id]);
        }
    }

    if (!isset($error)) {
        header('Location: profile.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa tài khoản</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container--form">
        <h2>Tùy chỉnh tài khoản cá nhân của bạn!</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <!-- Thông tin cá nhân -->
            <div class="form-section">
                <h3>Thông tin cá nhân</h3>
                <div class="form-group">
                    <label for="full_name">Họ và tên:</label>
                    <input type="text" id="full_name" name="full_name"
                        value="<?= htmlspecialchars($candidate['full_name'] ?? '') ?>"
                        placeholder="Nhập họ và tên của bạn" required>
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại:</label>
                    <input type="text" id="phone" name="phone"
                        value="<?= htmlspecialchars($candidate['phone'] ?? '') ?>"
                        placeholder="Nhập số điện thoại của bạn">
                </div>

                <div class="form-group">
                    <label for="address">Địa chỉ:</label>
                    <input type="text" id="address" name="address"
                        value="<?= htmlspecialchars($candidate['address'] ?? '') ?>"
                        placeholder="Nhập địa chỉ của bạn">
                </div>

                <div class="form-group">
                    <label for="industry">Ngành nghề:</label>
                    <select id="industry" name="industry">
                        <option value="">-- Chọn ngành nghề --</option>
                        <?php
                        $industries = ['Information Technology', 'Marketing', 'Finance', 'Healthcare', 'Education', 'Engineering'];
                        foreach ($industries as $industry):
                            $selected = (!empty($candidate['industry']) && $candidate['industry'] === $industry) ? 'selected' : '';
                            echo "<option value=\"$industry\" $selected>$industry</option>";
                        endforeach;
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bio">Giới thiệu ngắn:</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Hãy giới thiệu một chút về bản thân bạn"><?= htmlspecialchars($candidate['bio'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Avatar -->
            <div class="form-section">
                <h3>Ảnh đại diện</h3>
                <div class="avatar-preview">
                    <?php if (!empty($candidate['avatar'])): ?>
                        <img src="/<?= htmlspecialchars($candidate['avatar']) ?>" alt="Avatar" class="profile-avatar">
                    <?php else: ?>
                        <p><em>Chưa có ảnh đại diện</em></p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="avatar">Chọn ảnh mới:</label>
                    <input type="file" id="avatar" name="avatar">
                </div>
            </div>

            <!-- Thay đổi mật khẩu -->
            <div class="form-section">
                <h3>Thay đổi mật khẩu</h3>
                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại:</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Nhập mật khẩu hiện tại">
                </div>

                <div class="form-group">
                    <label for="new_password">Mật khẩu mới:</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu mới">
                </div>
            </div>

            <!-- Nút cập nhật -->
            <div class="form-actions">
                <button type="submit" class="update-btn">Cập nhật</button>
            </div>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>