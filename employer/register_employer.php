<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirm      = $_POST['confirm_password'] ?? '';
    $company_name = trim($_POST['company_name'] ?? '');
    $website      = trim($_POST['website'] ?? '');
    $description  = trim($_POST['description'] ?? '');

    // Validate
    if (!$username || !$email || !$password || !$confirm || !$company_name) {
        $errors[] = 'Vui lòng điền đầy đủ các trường có dấu *.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Mật khẩu xác nhận không khớp.';
    }

    // Check unique
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
        $stmt->execute([':u' => $username, ':e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Tên người dùng hoặc email đã tồn tại.';
        }
    }

    // Xử lý upload hình ảnh
    $logoPath = null;
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/companies/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
        $fileName = 'logo_' . uniqid() . '.' . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $targetPath)) {
            $logoPath = $targetPath;
        } else {
            $errors[] = 'Tải lên logo thất bại.';
        }
    }

    // Insert
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, password, role)
             VALUES (:u, :e, :p, 'employer')"
        );
        $stmt->execute([
            ':u' => $username,
            ':e' => $email,
            ':p' => $hash
        ]);
        $user_id = $conn->lastInsertId();

        $stmt2 = $conn->prepare(
            "INSERT INTO employers (user_id, company_name, website, description, company_logo)
             VALUES (:uid, :cn, :w, :d, :logo)"
        );
        $stmt2->execute([
            ':uid'  => $user_id,
            ':cn'   => $company_name,
            ':w'    => $website,
            ':d'    => $description,
            ':logo' => $logoPath
        ]);

        header('Location: /employer/login_employer.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký Nhà tuyển dụng – WORKLY</title>
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
        .register-form input[type="password"],
        .register-form input[type="file"] {
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

    <main>
        <div class="register-form">
            <h2>REGISTER FOR EMPLOYER</h2>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $err): ?>
                    <div class="error"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="post" action="" enctype="multipart/form-data">
                <input type="text" name="username" placeholder="Tên người dùng *" required value="<?= htmlspecialchars($username ?? '') ?>">
                <input type="email" name="email" placeholder="Email *" required value="<?= htmlspecialchars($email ?? '') ?>">
                <input type="password" name="password" placeholder="Mật khẩu *" required>
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu *" required>
                <input type="text" name="company_name" placeholder="Tên công ty *" required value="<?= htmlspecialchars($company_name ?? '') ?>">
                <input type="text" name="website" placeholder="Website" value="<?= htmlspecialchars($website ?? '') ?>">
                <textarea name="description" placeholder="Mô tả công ty"><?= htmlspecialchars($description ?? '') ?></textarea>
                <input type="file" name="company_logo" accept="image/*">
                <input type="submit" value="Đăng ký">
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

</body>

</html>