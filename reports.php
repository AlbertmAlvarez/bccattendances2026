<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['user']['name'] ?? 'Admin';

$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// SQL Query - Pinagsama ang attendance at employees table
$sql = "SELECT e.firstName, e.lastName, e.position, a.date, a.time_in, a.time_out 
        FROM attendance a 
        JOIN employees e ON a.employee_id = e.id 
        WHERE a.date BETWEEN '$fromDate' AND '$toDate' 
        ORDER BY a.date DESC, a.time_in ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Admin Panel</title>
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

        .report-card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 25px; border: 1px solid #e2e8f0; }
        .table thead th { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 15px; border-bottom: 2px solid #e2e8f0; }
        .table tbody td { padding: 15px; vertical-align: middle; color: #334155; border-bottom: 1px solid #f1f5f9; }

        /* Search Bar Styling */
        .search-group { position: relative; width: 300px; }
        .search-group i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; z-index: 5; }
        .search-input { padding-left: 35px; border-radius: 10px; }

        @media print {
            .sidebar, .no-print, .search-group { display: none !important; }
            .main-content { margin: 0; padding: 0; }
            body { background: white; }
            .report-card { box-shadow: none; border: none; padding: 0; }
        }
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
        <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
        <a href="employees.php" class="nav-link"><i class="bi bi-people me-2"></i> Employees</a>
        <a href="attendance.php" class="nav-link"><i class="bi bi-calendar-check me-2"></i> Attendance</a>
        <a href="reports.php" class="nav-link active"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reports</a>
    </div>
    <div class="pb-4">
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold mb-0">Attendance Reports</h3>
            <p class="text-muted">View and print employee logs.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="search-group">
                <i class="bi bi-search"></i>
                <input type="text" id="reportSearch" class="form-control search-input" placeholder="Search name or position...">
            </div>
            <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm"><i class="bi bi-printer-fill me-2"></i> Print Report</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body p-4 bg-white rounded-3">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $fromDate ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $toDate ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-filter me-2"></i> Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="report-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0">Attendance Summary</h5>
                <small class="text-muted">Period: <?= date('M d, Y', strtotime($fromDate)) ?> - <?= date('M d, Y', strtotime($toDate)) ?></small>
            </div>
            <div class="d-none d-print-block text-end">
                <h4 class="fw-bold mb-0">BARAS COCKPIT CENTER</h4>
                <small>Generated on <?= date('M d, Y h:i A') ?></small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" id="reportTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee Name</th>
                        <th>Position</th>
                        <th class="text-center">Time In</th>
                        <th class="text-center">Time Out</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center no-print">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            $timeIn = strtotime($row['time_in']);
                            $timeOut = !empty($row['time_out']) ? strtotime($row['time_out']) : null;
                            
                            $displayDate = date('M d, Y', strtotime($row['date']));
                            $displayIn = date('h:i A', $timeIn);
                            $displayOut = $timeOut ? date('h:i A', $timeOut) : '--:--';
                            
                            $duration = '--';
                            if ($timeOut) {
                                $diff = $timeOut - $timeIn;
                                $hours = floor($diff / 3600);
                                $minutes = floor(($diff % 3600) / 60);
                                $duration = "{$hours}h {$minutes}m";
                            }

                            $statusBadge = $timeOut ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Completed</span>' : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Ongoing</span>';
                        ?>
                        <tr class="report-row">
                            <td><?= $displayDate ?></td>
                            <td>
                                <div class="fw-bold name-cell"><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></div>
                            </td>
                            <td class="small text-muted position-cell"><?= htmlspecialchars($row['position']) ?></td>
                            <td class="text-center fw-bold text-success"><?= $displayIn ?></td>
                            <td class="text-center fw-bold text-secondary"><?= $displayOut ?></td>
                            <td class="text-center fw-bold"><?= $duration ?></td>
                            <td class="text-center no-print"><?= $statusBadge ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                                No records found for this date range.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="7" class="text-center py-5 text-muted">No matching records found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // LIVE SEARCH SCRIPT
    document.getElementById('reportSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('.report-row');
        let visibleCount = 0;

        rows.forEach(row => {
            let name = row.querySelector('.name-cell').textContent.toLowerCase();
            let position = row.querySelector('.position-cell').textContent.toLowerCase();
            
            if (name.includes(filter) || position.includes(filter)) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        // Show/Hide "No Results" row
        document.getElementById('noResultsRow').style.display = (visibleCount === 0 && filter !== "") ? "" : "none";
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>