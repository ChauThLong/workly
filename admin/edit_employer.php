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
    'company_name' => '',
    'website'      => '',
    'description'  => '',
];

if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM employers WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $employer = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$employer) {
        die('Employer not found');
    }

    $data = [
        'company_name' => $employer['company_name'],
        'website'      => $employer['website'],
        'description'  => $employer['description'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'company_name' => trim($_POST['company_name']),
        'website'      => trim($_POST['website']),
        'description'  => trim($_POST['description']),
    ];

    $logoPath = $isEdit && !empty($employer['company_logo']) ? $employer['company_logo'] : 'assets/images/default-null.png';

    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['company_logo']['tmp_name'];
        $fileName = basename($_FILES['company_logo']['name']);
        $uploadDir = '../uploads/companies/';
        $targetFile = $uploadDir . time() . '_' . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($tmpName, $targetFile)) {
            $logoPath = str_replace('../', '', $targetFile); // uploads/companies/...
        }
    }

    if ($isEdit) {
        $id = (int)$_POST['id'];
    } else {
        $user_id = (int)$_POST['user_id'];
    }

    if ($data['company_name'] === '') {
        $errors[] = 'Tên công ty không được để trống.';
    }

    if (empty($errors)) {
        if ($isEdit) {
            $updateQuery = "UPDATE employers SET company_name = :company_name, website = :website, description = :description, company_logo = :company_logo WHERE id = :id";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([
                ':company_name' => $data['company_name'],
                ':website'      => $data['website'],
                ':description'  => $data['description'],
                ':company_logo' => $logoPath,
                ':id'           => $id,
            ]);
        } else {
            $stmt = $conn->prepare("INSERT INTO employers (user_id, company_name, website, description, company_logo) VALUES (:user_id, :company_name, :website, :description, :company_logo)");
            $stmt->execute([
                ':user_id'      => $user_id,
                ':company_name' => $data['company_name'],
                ':website'      => $data['website'],
                ':description'  => $data['description'],
                ':company_logo' => $logoPath,
            ]);
        }

        header('Location: admin_employers.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit Employer' : 'Create Employer' ?></title>
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
                    <h4 class="page-title"><?= $isEdit ? 'Edit Employer' : 'Create Employer' ?></h4>
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

                <form action="edit_employer.php<?= $isEdit ? '?id=' . $id : '' ?>" method="post" enctype="multipart/form-data">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                    <?php else: ?>
                        <div class="form-group mb-3">
                            <label>User ID</label>
                            <input type="number" name="user_id" class="form-control" required>
                        </div>
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($data['company_name']) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>Website</label>
                        <input type="text" name="website" class="form-control" value="<?= htmlspecialchars($data['website']) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($data['description']) ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label>Company Logo</label><br>
                        <?php if ($isEdit && !empty($employer['company_logo'])): ?>
                            <img src="/<?= htmlspecialchars($employer['company_logo']) ?>" alt="Logo" width="400" class="mb-2"><br>
                        <?php endif; ?>
                        <input type="file" name="company_logo" class="form-control-file">
                    </div>

                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
                    <a href="admin_employers.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/kaiadmin.min.js"></script>
</body>

</html>