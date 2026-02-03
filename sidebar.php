<div class="sidebar" id="sidebar">
    <div class="p-4 text-center">
        <img src="img.jpg" class="rounded-circle mb-2 border border-2 border-primary" style="width: 60px; height: 60px; object-fit: cover;" alt="Admin">
        <h6 class="mb-0 fw-bold">Admin Portal</h6>
        <small class="text-muted"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></small>
    </div>
    <div class="nav-links-container flex-grow-1">
        <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
        <a href="employees.php" class="nav-link active"><i class="bi bi-people me-2"></i> Employees</a>
        <a href="attendance.php" class="nav-link"><i class="bi bi-calendar-check me-2"></i> Attendance</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reports</a>
    </div>
    <div class="pb-4">
        <a href="logout.php" class="nav-link text-danger" onclick="return confirm('Sigurado ka bang gusto mong mag-logout?')"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </div>
</div>