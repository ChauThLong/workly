<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin/login_admin.php');
    exit;
}

$employersStmt = $conn->query("SELECT id, company_name FROM employers ORDER BY company_name");
$employers = $employersStmt->fetchAll(PDO::FETCH_ASSOC);

$isEdit = false;
$errors = [];
$data = [
    'title'        => '',
    'description'  => '',
    'location'     => '',
    'salary_min'   => '',
    'salary_max'   => '',
    'status'       => 'inactive',
    'employer_id'  => '',
];

if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM job_posts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$job) {
        die('Job post not found');
    }
    $data = [
        'title'        => $job['title'],
        'description'  => $job['description'],
        'location'     => $job['location'],
        'salary_min'   => $job['salary_min'],
        'salary_max'   => $job['salary_max'],
        'status'       => $job['status'],
        'employer_id'  => $job['employer_id'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'        => trim($_POST['title'] ?? ''),
        'description'  => trim($_POST['description'] ?? ''),
        'location'     => trim($_POST['location'] ?? ''),
        'salary_min'   => trim($_POST['salary_min'] ?? ''),
        'salary_max'   => trim($_POST['salary_max'] ?? ''),
        'status'       => ($_POST['status'] === 'active') ? 'active' : 'inactive',
        'employer_id'  => (int)($_POST['employer_id'] ?? 0),
    ];
    if ($isEdit) {
        $id = (int)$_POST['id'];
    }

    if ($data['title'] === '') {
        $errors[] = 'Title không được để trống.';
    }
    if ($data['employer_id'] <= 0) {
        $errors[] = 'Bạn phải chọn một công ty.';
    }
    if (!is_numeric($data['salary_min']) || !is_numeric($data['salary_max'])) {
        $errors[] = 'Salary phải là số.';
    } elseif ($data['salary_min'] > $data['salary_max']) {
        $errors[] = 'Salary min phải nhỏ hơn hoặc bằng salary max.';
    }

    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE job_posts SET
                        title        = :title,
                        description  = :description,
                        location     = :location,
                        salary_min   = :salary_min,
                        salary_max   = :salary_max,
                        status       = :status,
                        employer_id  = :employer_id,
                        updated_at   = NOW()
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $params = [
                ':title'       => $data['title'],
                ':description' => $data['description'],
                ':location'    => $data['location'],
                ':salary_min'  => $data['salary_min'],
                ':salary_max'  => $data['salary_max'],
                ':status'      => $data['status'],
                ':employer_id' => $data['employer_id'],
                ':id'          => $id,
            ];
            $stmt->execute($params);
        } else {
            $sql = "INSERT INTO job_posts
                        (title, description, location, salary_min, salary_max, status, employer_id, created_at, updated_at)
                    VALUES
                        (:title, :description, :location, :salary_min, :salary_max, :status, :employer_id, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $params = [
                ':title'       => $data['title'],
                ':description' => $data['description'],
                ':location'    => $data['location'],
                ':salary_min'  => $data['salary_min'],
                ':salary_max'  => $data['salary_max'],
                ':status'      => $data['status'],
                ':employer_id' => $data['employer_id'],
            ];
            $stmt->execute($params);
        }

        header('Location: admin_jobs.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit Job Post' : 'Create Job Post' ?></title>
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
                    <h4 class="page-title"><?= $isEdit ? 'Edit Job Post' : 'Create Job Post' ?></h4>
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

                <form action="job_form.php<?= $isEdit ? '?id=' . $id : '' ?>" method="post">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control"
                            value="<?= htmlspecialchars($data['title']) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($data['description']) ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4 mb-3">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control"
                                value="<?= htmlspecialchars($data['location']) ?>">
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <label>Salary Min</label>
                            <input type="number" name="salary_min" class="form-control"
                                value="<?= htmlspecialchars($data['salary_min']) ?>">
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <label>Salary Max</label>
                            <input type="number" name="salary_max" class="form-control"
                                value="<?= htmlspecialchars($data['salary_max']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6 mb-3">
                            <label>Company</label>
                            <select name="employer_id" class="form-control">
                                <option value="">-- Chọn công ty --</option>
                                <?php foreach ($employers as $emp): ?>
                                    <option value="<?= $emp['id'] ?>"
                                        <?= $emp['id'] == $data['employer_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($emp['company_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active" <?= $data['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?= $isEdit ? 'Update Job' : 'Create Job' ?>
                    </button>
                    <a href="admin_jobs.php" class="btn btn-secondary">Cancel</a>
                </form>

            </div>
        </div>
    </div>

    <script src="/assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/kaiadmin.min.js"></script>
</body>

</html>