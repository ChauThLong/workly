<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Mật khẩu xác nhận không khớp.";
    }

    // Kiểm tra username hoặc email đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $username, ':email' => $email]);
    if ($stmt->fetch()) {
        $errors[] = "Tên người dùng hoặc email đã tồn tại.";
    }

    // Nếu không có lỗi thì tiến hành đăng ký
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Lưu vào bảng users
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'candidate')");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashed_password
        ]);

        $user_id = $conn->lastInsertId();

        // Lưu vào bảng candidates
        $stmt = $conn->prepare("INSERT INTO candidates (user_id) VALUES (:user_id)");
        $stmt->execute([':user_id' => $user_id]);

        $success = true;
        header("Location: /candidate/login_candidate.php?registered=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký Ứng viên</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .register-form {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }

        .register-form h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2ecc71;
        }

        .register-form input[type="text"],
        .register-form input[type="email"],
        .register-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .register-form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            height: 100px;
            resize: none;
            box-sizing: border-box;
        }

        .register-form input[type="submit"] {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            font-size: 15px;
            text-align: center;
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="register-form">
        <h2>ĐĂNG KÝ</h2>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $err): ?>
                <div class="error"><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Tên người dùng" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
            <input type="submit" value="Đăng ký">
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>

</body>

</html>