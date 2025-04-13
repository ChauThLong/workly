<?php
require_once '../includes/config.php';

$candidate_id = $_SESSION['candidate_id'] ?? null;

if ($candidate_id) {
    $stmt = $conn->prepare("
        SELECT j.title, j.created_at, j.status, e.company_name, a.applied_at
        FROM applications a
        JOIN job_posts j ON a.job_post_id = j.id
        JOIN employers e ON j.employer_id = e.id
        WHERE a.candidate_id = :cid
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([':cid' => $candidate_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h4 class="mb-4">Danh sách công việc đã ứng tuyển</h4>

<?php if (!empty($applications)): ?>
    <div class="list-group">
        <?php foreach ($applications as $app): ?>
            <div class="list-group-item list-group-item-action mb-2">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1"><?= htmlspecialchars($app['title']) ?> tại <?= htmlspecialchars($app['company_name']) ?></h5>
                    <small><?= date('d/m/Y', strtotime($app['applied_at'])) ?></small>
                </div>
                <p class="mb-1">
                    <strong>Trạng thái: </strong>
                    <span class="<?= $app['status'] === 'approved' ? 'text-success' : ($app['status'] === 'rejected' ? 'text-danger' : 'text-warning') ?>">
                        <?= $app['status'] === 'approved' ? 'Đã duyệt' : ($app['status'] === 'rejected' ? 'Từ chối' : 'Chưa duyệt') ?>
                    </span>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">Bạn chưa ứng tuyển công việc nào.</div>
<?php endif; ?>
