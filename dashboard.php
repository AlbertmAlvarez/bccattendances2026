<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['user']['name'] ?? 'Admin';


$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

date_default_timezone_set('Asia/Manila');
$todayDate = date('Y-m-d');


$totalEmployees = $conn->query("SELECT COUNT(*) as total FROM employees")->fetch_assoc()['total'];


$maleCount = $conn->query("SELECT COUNT(*) as total FROM employees WHERE email = 'Male'")->fetch_assoc()['total'] ?? 0;


$femaleCount = $conn->query("SELECT COUNT(*) as total FROM employees WHERE email = 'Female'")->fetch_assoc()['total'] ?? 0;

$activeQuery = "SELECT COUNT(DISTINCT employee_id) as total FROM attendance WHERE date = '$todayDate' AND time_out IS NULL";
$activeToday = $conn->query($activeQuery)->fetch_assoc()['total'] ?? 0;

$attendanceTodayCount = $conn->query("SELECT COUNT(DISTINCT employee_id) as total FROM attendance WHERE date = '$todayDate'")->fetch_assoc()['total'];
$notYetIn = $totalEmployees - $attendanceTodayCount;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-color: #0f172a; --accent-color: #6366f1; --bg-light: #f8f9fa; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-light); }
     
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-color); position: fixed; left: 0; top: 0; color: white; display: flex; flex-direction: column; z-index: 1000; }
        .main-content { margin-left: 260px; padding: 30px; }
        .nav-link { color: #94a3b8; padding: 12px 20px; margin: 5px 15px; border-radius: 8px; display: flex; align-items: center; text-decoration: none; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: var(--accent-color); color: white; }

        .stat-card { background: white; border-radius: 20px; padding: 25px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.3s ease; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; }

        .bg-male { background-color: #e0f2fe; color: #0ea5e9; }
        .bg-female { background-color: #fce7f3; color: #ec4899; }
        
        .chart-container { background: white; padding: 20px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary mb-3">
        <img src="img.jpg" class="rounded-circle mb-2 border border-2 border-primary" style="width: 60px; height: 60px; object-fit: cover;" alt="Admin">
        <h6 class="mb-0 fw-bold">Admin Portal</h6>
        <small class="text-muted"><?= htmlspecialchars($adminName) ?></small>
    </div>
    <div style="flex-grow: 1;">
        <a href="dashboard.php" class="nav-link active"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
        <a href="employees.php" class="nav-link"><i class="bi bi-people me-2"></i> Employees</a>
        <a href="attendance.php" class="nav-link "><i class="bi bi-calendar-check me-2"></i> Attendance</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reports</a>
    </div>
    <div class="pb-4">
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </div>
</div>
</div>


<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="logoutModalLabel">Ready to Leave?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-muted">
                Select "Logout" below if you are ready to end your current session.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-danger rounded-pill px-4">Logout</a>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>





<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Overview</h3>
            <p class="text-muted">Monitoring employee attendance.</p>
        </div>
        <div class="text-end">
            <h5 id="liveClock" class="fw-bold mb-0">00:00:00 AM</h5>
            <small class="text-muted"><?= date('F d, Y') ?></small>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-primary-subtle text-primary"><i class="bi bi-people-fill"></i></div>
                <h6 class="text-muted small">Total Employees</h6>
                <h2 class="fw-bold"><?= $totalEmployees ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-male"><i class="bi bi-gender-male"></i></div>
                <h6 class="text-muted small">Male</h6>
                <h2 class="fw-bold"><?= $maleCount ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-female"><i class="bi bi-gender-female"></i></div>
                <h6 class="text-muted small">Female</h6>
                <h2 class="fw-bold"><?= $femaleCount ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                <h6 class="text-muted small">Not Yet In</h6>
                <h2 class="fw-bold"><?= $notYetIn ?></h2>
                <small class="text-muted" style="font-size: 12px">Waiting for Time-in</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-container">
                <h6 class="fw-bold mb-4">Attendance Trends</h6>
                <canvas id="attendanceChart" height="150"></canvas>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="chart-container h-100">
                <h6 class="fw-bold mb-4">Recent Activity (Today)</h6>
                <div class="d-flex flex-column gap-3">
                    <?php
                    
                    $sqlRecent = "SELECT e.firstName, e.lastName, e.position, a.time_in, a.time_out 
                                  FROM attendance a 
                                  JOIN employees e ON a.employee_id = e.id 
                                  WHERE a.date = '$todayDate' 
                                  ORDER BY a.time_in DESC 
                                  LIMIT 5";
                    
                    $recentResult = $conn->query($sqlRecent);

                    if ($recentResult && $recentResult->num_rows > 0):
                        while ($row = $recentResult->fetch_assoc()):
                            $timeIn = date('h:i A', strtotime($row['time_in']));
                           
                            $timeOut = !empty($row['time_out']) ? date('h:i A', strtotime($row['time_out'])) : '--:--';
                            
                            $outClass = !empty($row['time_out']) ? 'text-danger' : 'text-muted';
                    ?>
                    <div class="d-flex align-items-center gap-3 border-bottom pb-2">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-person-fill text-primary"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <div class="fw-bold small"><?= htmlspecialchars($row['firstName']) ?></div>
                            <small class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($row['position']) ?></small>
                        </div>
                        
                        <div class="text-end" style="min-width: 80px;">
                            <div class="d-flex align-items-center justify-content-end mb-1">
                                <span class="badge bg-success-subtle text-success p-1 me-2" style="font-size: 0.6rem; width: 30px;">IN</span>
                                <span class="fw-bold small text-dark"><?= $timeIn ?></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-end">
                                <span class="badge bg-secondary-subtle text-secondary p-1 me-2" style="font-size: 0.6rem; width: 30px;">OUT</span>
                                <span class="fw-bold small <?= $outClass ?>"><?= $timeOut ?></span>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-clock fs-4 mb-2 d-block"></i>
                            <small>No attendance activity yet.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function updateClock() { document.getElementById('liveClock').innerText = new Date().toLocaleTimeString(); }
    setInterval(updateClock, 1000); updateClock();

    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            datasets: [{
                label: 'Employees Present',
                data: [40, 45, 42, 48, 45, 38], 
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }
    });
</script>
</body>
</html>