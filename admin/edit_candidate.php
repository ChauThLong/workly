<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin/login_admin.php');
    exit;
}

$isEdit = false;
$errors = [];
$data = [
    'name'     => '',
    'email'    => '',
    'phone'    => '',
    'address'  => '',
    'cv_url'   => '',
    'avatar'   => '',
];

if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT c.*, u.email FROM candidates c JOIN users u ON c.user_id = u.id WHERE c.id = :id");
    $stmt->execute([':id' => $id]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidate) {
        die('Candidate not found');
    }

    $data = [
        'name'     => $candidate['full_name'],
        'email'    => $candidate['email'],
        'phone'    => $candidate['phone'],
        'address'  => $candidate['address'],
        'cv_url'   => $candidate['cv_url'],
        'avatar'   => $candidate['avatar'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'     => trim($_POST['name']),
        'email'    => trim($_POST['email']),
        'phone'    => trim($_POST['phone']),
        'address'  => trim($_POST['address']),
        'cv_url'   => trim($_POST['cv_url']),
    ];

    $avatarPath = $isEdit ? $data['avatar'] : 'assets/images/default-avatar.png';

    // Xử lý upload avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['avatar']['tmp_name'];
        $fileName = basename($_FILES['avatar']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate file type
        if (!in_array($fileExt, $allowedExts)) {
            $errors[] = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
        } else {
            $uploadDir = '../uploads/avatars/';
            $newFileName = time() . '_' . uniqid() . '.' . $fileExt; // Unique filename
            $targetFile = $uploadDir . $newFileName;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($tmpName, $targetFile)) {
                // Delete old avatar if it exists and is not the default
                if ($isEdit && $avatarPath && $avatarPath !== 'assets/images/default-avatar.png') {
                    $oldFile = '../' . $avatarPath;
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $avatarPath = 'uploads/avatars/' . $newFileName; // Store relative path
            } else {
                $errors[] = 'Failed to upload avatar.';
            }
        }
    }

    $data['avatar'] = $avatarPath;

    if ($isEdit) {
        $id = (int)$_POST['id'];
    }

    // Validation
    if ($data['name'] === '' || $data['email'] === '') {
        $errors[] = 'Name and Email are required.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($errors)) {
        if ($isEdit) {
            // Update candidates table
            $stmt = $conn->prepare("UPDATE candidates SET full_name = :name, phone = :phone, address = :address, cv_url = :cv_url, avatar = :avatar WHERE id = :id");
            $stmt->execute([
                ':name'    => $data['name'],
                ':phone'   => $data['phone'],
                ':address' => $data['address'],
                ':cv_url'  => $data['cv_url'],
                ':avatar'  => $data['avatar'],
                ':id'      => $id,
            ]);

            // Update email in users table
            $stmt = $conn->prepare("UPDATE users u JOIN candidates c ON u.id = c.user_id SET u.email = :email WHERE c.id = :id");
            $stmt->execute([
                ':email' => $data['email'],
                ':id'    => $id,
            ]);
        } else {
            $stmt = $conn->prepare("INSERT INTO candidates (user_id, full_name, phone, address, cv_url, avatar, created_at, updated_at) 
                                    VALUES (:user_id, :name, :phone, :address, :cv_url, :avatar, NOW(), NOW())");
            $stmt->execute([
                ':user_id' => $_POST['user_id'],
                ':name'    => $data['name'],
                ':phone'   => $data['phone'],
                ':address' => $data['address'],
                ':cv_url'  => $data['cv_url'],
                ':avatar'  => $data['avatar'],
            ]);

            // Update email in users table for new candidate
            $stmt = $conn->prepare("UPDATE users SET email = :email WHERE id = :user_id");
            $stmt->execute([
                ':email'   => $data['email'],
                ':user_id' => $_POST['user_id'],
            ]);
        }
        header('Location: admin_candidates.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit Candidate' : 'Create Candidate' ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/plugins.min.css">
    <link rel="stylesheet" href="/assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'includes/admin_sidebar.php'; ?>
<div class="main-panel">
    <div class="content">
        <div class="page-inner">
            <div class="page-header">
                <h4 class="page-title"><?= $isEdit ? 'Edit Candidate' : 'Create Candidate' ?></h4>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="edit_candidate.php<?= $isEdit ? '?id=' . $id : '' ?>" method="post" enctype="multipart/form-data">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                <?php else: ?>
                    <div class="form-group mb-3">
                        <label>User ID</label>
                        <input type="number" name="user_id" class="form-control" required>
                    </div>
                <?php endif; ?>

                <div class="form-group mb-3">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($data['phone']) ?>">
                </div>

                <div class="form-group mb-3">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($data['address']) ?>">
                </div>

                <div class="form-group mb-3">
                    <label>CV URL</label>
                    <input type="text" name="cv_url" class="form-control" value="<?= htmlspecialchars($data['cv_url']) ?>">
                </div>

                <div class="form-group mb-3">
                    <label>Avatar</label><br>
                    <?php if ($isEdit && !empty($data['avatar'])): ?>
                        <img src="/<?= htmlspecialchars($data['avatar']) ?>" alt="Avatar" width="400" class="mb-2"><br>
                    <?php endif; ?>
                    <input type="file" name="avatar" class="form-control-file" accept="image/jpeg,image/png,image/gif">
                </div>

                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
                <a href="admin_candidates.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="/assets/js/core/bootstrap.min.js"></script>
<script src="/assets/js/kaiadmin.min.js"></script>
</body>
</html>