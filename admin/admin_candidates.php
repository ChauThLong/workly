<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin/login_admin.php');
    exit;
}

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$allowedSorts = ['id', 'full_name', 'phone'];
$sort = in_array($_GET['sort'] ?? '', $allowedSorts) ? $_GET['sort'] : 'id';
$order = ($_GET['order'] ?? 'asc') === 'asc' ? 'asc' : 'desc';

$orderBy = "c.$sort $order";

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
    ? (int)$_GET['page']
    : 1;

$offset = ($page - 1) * $limit;

$totalStmt = $conn->prepare("SELECT COUNT(*) FROM candidates c 
                            WHERE c.full_name LIKE :search 
                               OR c.phone LIKE :search 
                               OR c.address LIKE :search 
                               OR c.bio LIKE :search 
                               OR c.industry LIKE :search");
$totalStmt->execute([':search' => "%$searchTerm%"]);
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $conn->prepare("SELECT * FROM candidates c 
                        WHERE c.full_name LIKE :search 
                           OR c.phone LIKE :search 
                           OR c.address LIKE :search 
                           OR c.bio LIKE :search 
                           OR c.industry LIKE :search 
                        ORDER BY $orderBy
                        LIMIT :limit OFFSET :offset");
$stmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Manage Candidates</title>
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
                    <h4 class="page-title">Manage Candidates</h4>
                </div>

                <form method="GET" action="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search by Name, Phone, Address, Bio or Industry" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th><?= sort_link('id', 'ID', $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th><?= sort_link('full_name', 'Full Name', $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th><?= sort_link('phone', 'Phone', $_GET['sort'] ?? '', $_GET['order'] ?? '') ?></th>
                                <th>Address</th>
                                <th>Bio</th>
                                <th>Industry</th>
                                <th>Avatar</th>
                                <th>CV</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td><?= $candidate['id'] ?></td>
                                    <td><?= htmlspecialchars($candidate['full_name']) ?></td>
                                    <td><?= htmlspecialchars($candidate['phone']) ?></td>
                                    <td><?= htmlspecialchars($candidate['address']) ?></td>
                                    <td><?= htmlspecialchars($candidate['bio']) ?></td>
                                    <td><?= htmlspecialchars($candidate['industry']) ?></td>
                                    <td>
                                        <?php if (empty($candidate['cv_url'])): ?>
                                            <img src="/<?= $candidate['avatar'] ?>" alt="Avatar" width="60">
                                        <?php else: ?>
                                            <span class="text-muted">No avatar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($candidate['cv_url'])): ?>
                                            <a href="/<?= $candidate['cv_url'] ?>" target="_blank">View CV</a>
                                        <?php else: ?>
                                            <span class="text-muted">No CV</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit_candidate.php?id=<?= $candidate['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <a href="delete_candidate.php?id=<?= $candidate['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this candidate?');">
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