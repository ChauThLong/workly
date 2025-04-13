<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $errors[] = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :u AND role = 'candidate'");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['role']     = 'candidate';
            $_SESSION['username'] = $user['username'];

            // Get candidate_id
            $stmt = $conn->prepare("SELECT id FROM candidates WHERE user_id = :uid");
            $stmt->execute([':uid' => $user['id']]);
            $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($candidate) {
                $_SESSION['candidate_id'] = $candidate['id'];
            }

            // Set login cookies
            $expiry = time() + (7 * 24 * 60 * 60);
            setcookie('user_id', $user['id'], $expiry, "/");
            setcookie('role', 'candidate', $expiry, "/");
            setcookie('username', $user['username'], $expiry, "/");

            header('Location: /index.php');
            exit;
        } else {
            $errors[] = 'Incorrect username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Ứng viên – WORKLY</title>
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
        .register-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
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

        .footer-text {
            text-align: center;
            margin-top: 15px;
        }

        .footer-text a {
            color: #2ecc71;
            font-weight: bold;
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="register-form">
        <h2>Đăng nhập Ứng viên</h2>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $err): ?>
                <div class="error"><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="username" placeholder="Tên người dùng" required value="<?= htmlspecialchars($username ?? '') ?>">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="submit" value="Đăng nhập">
        </form>

        <p class="footer-text">
            Chưa có tài khoản? <a href="../candidate/register_candidate.php">Đăng ký ngay</a>
        </p>
    </div>

    <?php include '../includes/footer.php'; ?>

</body>

</html>