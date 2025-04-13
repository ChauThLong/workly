<?php
session_start();
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../admin/login_admin.php');
  exit;
}

// Count statistics
$jobCount = $conn->query("SELECT COUNT(*) FROM job_posts")->fetchColumn();
$employerCount = $conn->query("SELECT COUNT(*) FROM employers")->fetchColumn();
$candidateCount = $conn->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/css/plugins.min.css">
  <link rel="stylesheet" href="/assets/css/kaiadmin.min.css">
  <link rel="stylesheet" href="/assets/css/demo.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <div class="wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>

    <!-- Main Panel -->
    <div class="main-panel">
      <div class="content">
        <div class="page-inner">
          <div class="page-header">
            <h4 class="page-title">Admin Dashboard</h4>
            <ul class="breadcrumbs">
              <li class="nav-home"><a href="#"><i class="fas fa-home"></i></a></li>
              <li class="separator"><i class="fas fa-chevron-right"></i></li>
              <li class="nav-item">Dashboard</li>
            </ul>
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-primary bubble-shadow-small">
                        <i class="fas fa-briefcase"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3">
                      <div class="numbers">
                        <p class="card-category">Job Posts</p>
                        <h4 class="card-title"><?php echo $jobCount; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-info bubble-shadow-small">
                        <i class="fas fa-building"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3">
                      <div class="numbers">
                        <p class="card-category">Employers</p>
                        <h4 class="card-title"><?php echo $employerCount; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-success bubble-shadow-small">
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3">
                      <div class="numbers">
                        <p class="card-category">Candidates</p>
                        <h4 class="card-title"><?php echo $candidateCount; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="/assets/js/core/jquery-3.7.1.min.js"></script>
  <script src="/assets/js/core/popper.min.js"></script>
  <script src="/assets/js/core/bootstrap.min.js"></script>
  <script src="/assets/js/plugin/chart.js/chart.min.js"></script>
  <script src="/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
  <script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
  <script src="/assets/js/kaiadmin.min.js"></script>
</body>

</html>