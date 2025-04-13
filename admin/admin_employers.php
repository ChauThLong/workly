<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin/login_admin.php');
    exit;
}

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$allowedSorts = ['id', 'company_name', 'website'];
$sort = in_array($_GET['sort'] ?? '', $allowedSorts) ? $_GET['sort'] : 'id';
$order = ($_GET['order'] ?? 'asc') === 'asc' ? 'asc' : 'desc';

$orderBy = "e.$sort $order";

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
    ? (int)$_GET['page']
    : 1;

$offset = ($page - 1) * $limit;

$totalStmt = $conn->prepare("SELECT COUNT(*) FROM employers e 
                            WHERE e.company_name LIKE :search 
                               OR e.website LIKE :search 
                               OR e.description LIKE :search");
$totalStmt->execute([':search' => "%$searchTerm%"]);
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $conn->prepare("SELECT * FROM employers e 
                        WHERE e.company_name LIKE :search 
                           OR e.website LIKE :search 
                           OR e.description LIKE :search 
                        ORDER BY $orderBy
                        LIMIT :limit OFFSET :offset");
$stmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$employers = $stmt->fetchAll(PDO::FETCH_ASSOC);

function sort_link($column, $label, $currentSort, $currentOrder)
{
    $order = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
    $arrow = $currentSort === $column ? ($currentOrder === 'asc' ? ' ▲' : ' ▼') : '';
    return "<a href=\"?sort=$column&order=$order\">$label$arrow</a>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Employers</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/plugins.min.css">
    <link rel="stylesheet" href="/assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-light th a {
            color: black !important;
            text-decoration: none;
            /* Optional: removes the underline from links */
        }

        .table-light th a:hover {
            color: black !important;
            /* Ensures the color stays black on hover */
        }
    </style>
</head>

<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="content">
            <div class="page-inner">
                <div class="page-header">
                    <h4 class="page-title">Manage Employers</h4>
                </div>

                <form method="GET" action="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search by Company, Website or Description" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th><?= sort_link('id', 'ID', $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th><?= sort_link('company_name', 'Company Name', $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th><?= sort_link('website', 'Website', $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th>Description</th>
                                <th>Logo</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employers as $employer): ?>
                                <tr>
                                    <td><?= $employer['id'] ?></td>
                                    <td><?= htmlspecialchars($employer['company_name']) ?></td>
                                    <td><?= htmlspecialchars($employer['website']) ?></td>
                                    <td><?= htmlspecialchars($employer['description']) ?></td>
                                    <td><img src="/<?= $employer['company_logo'] ?>" alt="Logo" width="60"></td>
                                    <td>
                                        <a href="edit_employer.php?id=<?= $employer['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <a href="delete_employer.php?id=<?= $employer['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this employer?');">
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

                            <!-- First -->
                            <li class="page-item mx-1 <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => 1]) ?>">&laquo;</a>
                            </li>

                            <!-- Prev -->
                            <li class="page-item mx-1 <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => $page - 1]) ?>">&lt;</a>
                            </li>

                            <!-- Số trang -->
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item mx-1 <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => $p]) ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item mx-1 <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => $page + 1]) ?>">&gt;</a>
                            </li>

                            <!-- Last -->
                            <li class="page-item mx-1 <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(['sort' => $sort, 'order' => $order, 'page' => $totalPages]) ?>">&raquo;</a>
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