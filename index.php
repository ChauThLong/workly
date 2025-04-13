<?php
require_once 'includes/config.php';

$stmt = $conn->query("SELECT company_name, company_logo FROM employers WHERE company_logo IS NOT NULL");
$employers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>WORKLY - Trang chủ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
    </style>
</head>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="container-fluid text-white text-center py-5 mb-4"
        style="background: url('assets/images/pexels-vojtech-okenka-127162-392018.jpg') no-repeat center center / cover; min-height: 500px;">
        <div style="background-color: rgba(47, 138, 89, 0.5); padding: 40px;">
            <h1 class="display-4">Welcome to WORKLY!</h1>
            <p class="lead">opportunities are coming to you!</p>
        </div>
    </div>

    <div class="partner-banner">
        <h2>TỔ CHỨC/DOANH NGHIỆP ĐỐI TÁC</h2>
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php foreach ($employers as $emp): ?>
                    <div class="swiper-slide">
                        <img src="<?= htmlspecialchars($emp['company_logo']) ?>" alt="<?= htmlspecialchars($emp['company_name']) ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Nút điều hướng -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>


    <?php include 'includes/footer.php'; ?>

</body>

</html>

<script src="assets/js/swiper_init.js"></script>