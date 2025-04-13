<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'includes/config.php';

$limit = 10;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT jp.*, e.company_name, e.company_logo
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.id
        WHERE jp.status = 'active'";

$conditions = [];
$params = [];

if ($searchQuery !== '') {
    $conditions[] = "(jp.title LIKE :search OR e.company_name LIKE :search OR jp.location LIKE :search)";
    $params[':search'] = '%' . $searchQuery . '%';
}

if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY jp.created_at DESC";

$totalSql = "SELECT COUNT(*) FROM job_posts jp JOIN employers e ON jp.employer_id = e.id WHERE jp.status = 'active'";
if (!empty($conditions)) {
    $totalSql .= " AND " . implode(" AND ", $conditions);
}

$totalStmt = $conn->prepare($totalSql);
$totalStmt->execute($params);
$totalJobs = $totalStmt->fetchColumn();
$totalPages = ceil($totalJobs / $limit);

$sql .= " LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function isNew($created_at)
{
    $posted = new DateTime($created_at);
    $now = new DateTime();
    return $now->diff($posted)->days <= 3;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách việc làm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        h2 {
            font-size: 24px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="main-content">
            <h2>DOANH NGHIỆP - TUYỂN DỤNG</h2>
            <?php foreach ($jobs as $job): ?>
                <div class="job-listing">
                    <?php
                    $logo = !empty($job['company_logo']) ? htmlspecialchars($job['company_logo']) : 'uploads/companies/default.png';
                    $title = !empty($job['title']) ? htmlspecialchars($job['title']) : 'Không có tiêu đề';
                    $company = !empty($job['company_name']) ? htmlspecialchars($job['company_name']) : 'Không có công ty';
                    $description = !empty($job['description']) ? htmlspecialchars($job['description']) : 'Không có mô tả';
                    ?>
                    <div class="job-image">
                        <img src="<?= $logo ?>" alt="Company Logo">
                    </div>
                    <div class="job-content">
                        <div class="job-title">
                            <a href="job_detail.php?id=<?= $job['id'] ?>">
                                <?= $title ?> - <?= $company ?>
                            </a>
                            <?php if (isNew($job['created_at'])): ?>
                                <span class="new-badge">Mới</span>
                            <?php endif; ?>
                        </div>
                        <div class="job-desc">
                            <?= nl2br(mb_strimwidth($description, 0, 200, '...')) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Phân trang -->
            <div class="pagination">
                <?php
                $adj = 2;
                $start = max(1, $page - $adj);
                $end   = min($totalPages, $page + $adj);

                $queryParams = array_merge($_GET, ['page' => '']);
                unset($queryParams['page']);
                $queryString = http_build_query($queryParams);

                if ($page > 1) {
                    echo '<a href="?page=' . ($page - 1) . ($queryString ? '&' . $queryString : '') . '">«</a>';
                }

                if ($start > 1) {
                    echo '<a href="?page=1' . ($queryString ? '&' . $queryString : '') . '">1</a>';
                    if ($start > 2) echo '<span class="ellipsis">…</span>';
                }

                for ($i = $start; $i <= $end; $i++) {
                    if ($i == $page) {
                        echo '<span class="current">' . $i . '</span>';
                    } else {
                        echo '<a href="?page=' . $i . ($queryString ? '&' . $queryString : '') . '">' . $i . '</a>';
                    }
                }

                if ($end < $totalPages) {
                    if ($end < $totalPages - 1) echo '<span class="ellipsis">…</span>';
                    echo '<a href="?page=' . $totalPages . ($queryString ? '&' . $queryString : '') . '">' . $totalPages . '</a>';
                }

                if ($page < $totalPages) {
                    echo '<a href="?page=' . ($page + 1) . ($queryString ? '&' . $queryString : '') . '">»</a>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>

</html>