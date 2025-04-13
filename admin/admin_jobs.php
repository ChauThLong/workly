<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin/login_admin.php');
    exit;
}

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Xử lý sắp xếp
$allowedSorts = ['id', 'company_name', 'location', 'salary', 'status'];
$sort = in_array($_GET['sort'] ?? '', $allowedSorts) ? $_GET['sort'] : 'id';
$order = ($_GET['order'] ?? 'asc') === 'asc' ? 'asc' : 'desc';

switch ($sort) {
    case 'company_name':
        $orderBy = "e.company_name $order";
        break;
    case 'location':
        $orderBy = "jp.location $order";
        break;
    case 'salary':
        $orderBy = "jp.salary_min $order";
        break;
    case 'status':
        $orderBy = "jp.status $order";
        break;
    default:
        $orderBy = "jp.id $order";
}

// Tìm kiếm
$searchSql = "WHERE jp.title LIKE :search OR jp.id LIKE :search OR e.company_name LIKE :search OR jp.location LIKE :search";

$limit  = 10;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
    ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM job_posts jp
    JOIN employers e ON jp.employer_id = e.id
    $searchSql");
$totalStmt->execute([':search' => "%$searchTerm%"]);
$totalRows  = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Lấy dữ liệu
$stmt = $conn->prepare("SELECT jp.*, e.company_name
    FROM job_posts jp
    JOIN employers e ON jp.employer_id = e.id
    $searchSql
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset");
$stmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function sort_link($column, $label, $currentSort, $currentOrder)
{
    $order = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
    $arrow = $currentSort === $column
        ? ($currentOrder === 'asc' ? ' ▲' : ' ▼')
        : '';
    return "<a href=\"?sort=$column&order=$order\">$label$arrow</a>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Job Posts</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/plugins.min.css">
    <link rel="stylesheet" href="/assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-light th a {
            color: black !important;
            text-decoration: none;
        }

        .table-light th a:hover {
            color: black !important;
        }

        .badge-status {
            display: inline-block;
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }
    </style>
</head>

<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="content">
            <div class="page-inner">
                <div class="page-header">
                    <h4 class="page-title">Manage Job Posts</h4>
                </div>

                <!-- Search -->
                <form method="GET" action="">
                    <div class="input-group mb-3">
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by Title, ID, Company, or Location"
                            value="<?= htmlspecialchars($searchTerm) ?>">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th><?= sort_link('id',           'ID',       $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th>Title</th>
                                <th><?= sort_link('company_name', 'Company',  $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th><?= sort_link('location',     'Location', $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th><?= sort_link('salary',       'Salary',   $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th><?= sort_link('status',       'Status',   $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="jobPostsTable">
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td><?= $job['id'] ?></td>
                                    <td><?= htmlspecialchars($job['title']) ?></td>
                                    <td><?= htmlspecialchars($job['company_name']) ?></td>
                                    <td><?= htmlspecialchars($job['location']) ?></td>
                                    <td>
                                        <?= number_format($job['salary_min'], 0, ',', '.') ?> vnđ
                                        –
                                        <?= number_format($job['salary_max'], 0, ',', '.') ?> vnđ
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $job['status'] === 'active' ? 'success' : 'secondary' ?> badge-status">
                                            <?= ucfirst($job['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_job.php?id=<?= $job['id'] ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <a href="delete_job.php?id=<?= $job['id'] ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this job?')">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => 1, 'search' => $searchTerm]) ?>">
                                    &laquo;
                                </a>
                            </li>
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => $page - 1, 'search' => $searchTerm]) ?>">
                                    &lt;
                                </a>
                            </li>
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link"
                                        href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => $p, 'search' => $searchTerm]) ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => $page + 1, 'search' => $searchTerm]) ?>">
                                    &gt;
                                </a>
                            </li>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => $totalPages, 'search' => $searchTerm]) ?>">
                                    &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/kaiadmin.min.js"></script>
</body>

</html>