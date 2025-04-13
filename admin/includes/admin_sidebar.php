<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="dashboard.php" class="logo text-white text-center" style="font-weight: bold; font-size: 18px; padding: 10px; display: block;">
                WORKLY ADMIN
            </a>
        </div>
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                ?>
                <li class="nav-item <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                    <a href="dashboard.php"><i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item <?php echo $current_page === 'admin_jobs.php' ? 'active' : ''; ?>">
                    <a href="admin_jobs.php"><i class="fas fa-briefcase"></i>
                        <p>Manage Jobs</p>
                    </a>
                </li>
                <li class="nav-item <?php echo $current_page === 'admin_employers.php' ? 'active' : ''; ?>">
                    <a href="admin_employers.php"><i class="fas fa-building"></i>
                        <p>Manage Employers</p>
                    </a>
                </li>
                <li class="nav-item <?php echo $current_page === 'admin_candidates.php' ? 'active' : ''; ?>">
                    <a href="admin_candidates.php"><i class="fas fa-users"></i>
                        <p>Manage Candidates</p>
                    </a>
                </li>
            </ul>
            <div class="text-center mt-5">
                <a href="login_admin.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</div>