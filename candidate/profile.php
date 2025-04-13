<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'candidate') {
    header('Location: ../candidate/login_candidate.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT u.username, u.email, c.full_name, c.phone, c.address, c.cv_url, c.avatar, c.bio, c.industry
    FROM users u
    JOIN candidates c ON u.id = c.user_id
    WHERE u.id = :uid
");

$stmt->execute([':uid' => $user_id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    echo "Can't find user.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Candidate Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function showCVPopup(cvUrl) {
            const popup = document.getElementById('cvPopup');
            const iframe = document.getElementById('cvIframe');
            iframe.src = cvUrl;
            popup.style.display = 'block';
        }

        function closeCVPopup() {
            const popup = document.getElementById('cvPopup');
            popup.style.display = 'none';
            document.getElementById('cvIframe').src = '';
        }
    </script>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="parent">
        <div class="profile-container">
            <img src="/<?= htmlspecialchars($candidate['avatar']) ?>" alt="Avatar" class="profile-avatar" style="width: 120px; border-radius: 50%;">
            <h2><?= htmlspecialchars($candidate['full_name']) ?></h2>

            <div class="profile-info">
                <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($candidate['bio'] ?? 'Not updated yet')) ?></p>
                <p><strong>Full name:</strong> <?= htmlspecialchars($candidate['full_name'] ?? 'Not updated yet') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($candidate['email']) ?></p>
                <p><strong>Phone number:</strong> <?= htmlspecialchars($candidate['phone'] ?? 'Not updated yet') ?></p>
                <p><strong>Industry:</strong> <?= htmlspecialchars($candidate['industry'] ?? 'Not updated yet') ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($candidate['address'] ?? 'Not updated yet') ?></p>
            </div>
        </div>
        <div class="applied-jobs">
            <?php include '../includes/applied_jobs.php'; ?>
        </div>
    </div>

    <!-- CV PDF Popup -->
    <div id="cvPopup" class="cv