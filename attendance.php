<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['user']['name'] ?? 'Admin';
$conn = new mysqli("localhost", "root", "", "employees");
date_default_timezone_set('Asia/Manila'); 

$view = $_GET['view'] ?? 'today'; 
$fromDate = $_GET['from_date'] ?? date('Y-m-d');
$toDate = $_GET['to_date'] ?? date('Y-m-d');
$logDate = date('Y-m-d');


if (isset($_POST['update_status'])) {
    $newStatus = $_POST['status_to_set'];
    $conn->query("UPDATE settings SET setting_value = '$newStatus' WHERE setting_name = 'barcode_status'");
    $_SESSION['status'] = "Scanner is now $newStatus";
    $_SESSION['status_code'] = "info";
    header("Location: attendance.php?view=$view&from_date=$fromDate&to_date=$toDate"); exit();
}

if (isset($_POST['start_today'])) {
    $currentDateTime = date('Y-m-d H:i:s');
    $archiveSql = "INSERT INTO attendance_history (employee_id, date, time_in, time_out, status)
                   SELECT employee_id, date, time_in, time_out, status FROM attendance WHERE time_out IS NOT NULL";
    if($conn->query($archiveSql)) {
        $conn->query("DELETE FROM attendance WHERE time_out IS NOT NULL");
        $conn->query("UPDATE settings SET setting_value = '$currentDateTime' WHERE setting_name = 'monitor_reset_time'");
        $conn->query("UPDATE settings SET setting_value = 'OPEN' WHERE setting_name = 'barcode_status'");
        $_SESSION['status'] = "Data Archived & Monitor Cleared!";
        $_SESSION['status_code'] = "success";
    }
    header("Location: attendance.php?view=today"); exit();
}

if (isset($_POST['delete_attendance'])) {
    $id = $conn->real_escape_string($_POST['attendance_id']);
    $table = ($view == 'history') ? 'attendance_history' : 'attendance';
    $conn->query("DELETE FROM $table WHERE id = '$id'");
    $_SESSION['status'] = "Record deleted!";
    $_SESSION['status_code'] = "success";
    header("Location: attendance.php?view=$view&from_date=$fromDate&to_date=$toDate"); exit();
}

$res = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'barcode_status'");
$currentStatus = $res->fetch_assoc()['setting_value'] ?? 'AUTO';
$manualOverride = ($currentStatus === 'OPEN');

if ($view == 'history') {
    $query = "SELECT h.id as att_id, e.firstName, e.lastName, e.position, h.time_in, h.time_out, h.date 
              FROM attendance_history h 
              JOIN employees e ON h.employee_id = e.id 
              WHERE h.date BETWEEN '$fromDate' AND '$toDate' 
              ORDER BY h.date DESC, h.time_in DESC";
} else {
    $query = "SELECT a.id as att_id, e.firstName, e.lastName, e.position, a.time_in, a.time_out, a.date 
              FROM attendance a 
              JOIN employees e ON a.employee_id = e.id 
              WHERE a.date = '$logDate' 
              ORDER BY a.time_in DESC";
}
$logResult = $conn->query($query);

$swiped = $conn->query("SELECT COUNT(*) as swiped FROM attendance WHERE date = '$logDate'");
$totalSwipedToday = $swiped->fetch_assoc()['swiped'] ?? 0;
$pending = $conn->query("SELECT COUNT(*) as pending FROM attendance WHERE date = '$logDate' AND time_out IS NULL");
$pendingCount = $pending->fetch_assoc()['pending'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Control | BCC</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root { --primary-bg: #f8f9fa; --sidebar-color: #0f172a; --accent: #6366f1; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--primary-bg); }

        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-color); position: fixed; left: 0; top: 0; color: white; display: flex; flex-direction: column; z-index: 1000; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .nav-link { color: #94a3b8; padding: 12px 20px; margin: 5px 15px; border-radius: 8px; display: flex; align-items: center; text-decoration: none; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .nav-link.active { background: var(--accent); }

        .control-card, .table-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .control-card { padding: 30px; }
        
        .view-pill { padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 0.85rem; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .view-pill.active { background: var(--accent); color: white; }
        .view-pill:not(.active) { color: #64748b; background: #fff; border: 1px solid #e2e8f0; }

        /* PRINT STYLES */
        @media print {
            .sidebar, .no-print, .btn-delete, .action-col, .view-pill-container, .search-container { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .table-card { box-shadow: none !important; border: none !important; }
            .attendance-row { border-bottom: 1px solid #eee !important; }
            body { background: white !important; }
            .print-header { display: block !important; text-align: center; margin-bottom: 30px; }
        }
        .print-header { display: none; }
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
        <a href="attendance.php" class="nav-link active"><i class="bi bi-calendar-check me-2"></i> Attendance</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reports</a>
    </div>
    <div class="pb-4"><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></div>
</div>

<div class="main-content">
    <div class="print-header">
        <h2 class="fw-bold">BARAS COCKPIT CENTER</h2>
        <h4>Attendance History Report</h4>
        <p>Period: <?= date('M d, Y', strtotime($fromDate)) ?> to <?= date('M d, Y', strtotime($toDate)) ?></p>
        <hr>
    </div>

    <div class="row g-4">
        <div class="col-lg-4 no-print">
            <div class="control-card text-center">
                <h6 class="text-muted fw-bold mb-3 small">SCANNER CONTROL</h6>
                <div class="mb-4">
                    <span class="badge bg-<?= $manualOverride ? 'success' : 'secondary' ?> rounded-pill px-4 py-2 mb-3">
                        <?= $manualOverride ? 'SCANNER OPEN' : 'SCANNER LOCKED' ?>
                    </span>
                    <form method="POST">
                        <input type="hidden" name="status_to_set" value="<?= $currentStatus == 'AUTO' ? 'OPEN' : 'AUTO' ?>">
                        <button type="submit" name="update_status" class="btn btn-<?= $currentStatus == 'AUTO' ? 'success' : 'danger' ?> w-100 rounded-pill py-2 fw-bold">
                            <?= $currentStatus == 'AUTO' ? 'FORCE OPEN SCANNER' : 'LOCK SCANNER (AUTO)' ?>
                        </button>
                    </form>
                </div>
                <hr>
                <button type="button" onclick="confirmStartToday()" class="btn btn-outline-primary w-100 py-3 rounded-4 fw-bold mt-3">
                    <i class="bi bi-play-circle-fill me-2"></i> START NEW SESSION
                </button>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2 view-pill-container no-print">
                    <a href="attendance.php?view=today" class="view-pill <?= $view == 'today' ? 'active' : '' ?>">Today</a>
                    <a href="attendance.php?view=history" class="view-pill <?= $view == 'history' ? 'active' : '' ?>">History</a>
                </div>

                <div class="d-flex gap-2 no-print">
                    <input type="text" id="attendanceSearch" class="form-control form-control-sm" style="width: 180px; border-radius: 10px;" placeholder="Search name...">
                    <?php if($view == 'history'): ?>
                        <button onclick="window.print()" class="btn btn-dark btn-sm rounded-pill px-3">
                            <i class="bi bi-printer me-1"></i> Print / PDF
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-card">
                <?php if($view == 'history'): ?>
                <div class="p-3 border-bottom bg-white no-print">
                    <form method="GET" class="row g-2 align-items-end">
                        <input type="hidden" name="view" value="history">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted">From</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="<?= $fromDate ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted">To</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="<?= $toDate ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Apply Date Filter</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="attendanceTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Employee</th>
                                <th>Date</th>
                                <th class="text-center">In</th>
                                <th class="text-center">Out</th>
                                <th class="text-center action-col no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($logResult->num_rows > 0): ?>
                                <?php while($row = $logResult->fetch_assoc()): ?>
                                <tr class="attendance-row">
                                    <td class="ps-4">
                                        <div class="fw-bold employee-name"><?= $row['firstName'].' '.$row['lastName'] ?></div>
                                        <small class="text-muted"><?= $row['position'] ?></small>
                                    </td>
                                    <td><small><?= date('M d, Y', strtotime($row['date'])) ?></small></td>
                                    <td class="text-center text-success fw-bold"><?= date('h:i A', strtotime($row['time_in'])) ?></td>
                                    <td class="text-center text-danger fw-bold"><?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '--:--' ?></td>
                                    <td class="text-center action-col no-print">
                                        <button class="btn text-danger p-0" onclick="confirmDelete(<?= $row['att_id'] ?>)"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="startTodayForm" method="POST" style="display:none;"><input type="hidden" name="start_today" value="1"></form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById('attendanceSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        document.querySelectorAll('.attendance-row').forEach(row => {
            let name = row.querySelector('.employee-name').textContent.toLowerCase();
            row.style.display = name.includes(filter) ? "" : "none";
        });
    });

    function confirmStartToday() {
        Swal.fire({
            title: 'Archive and Start New?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Start Session'
        }).then((result) => { if (result.isConfirmed) document.getElementById('startTodayForm').submit(); });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete'
        }).then((result) => { if (result.isConfirmed) {
            let f = document.createElement('form'); f.method='POST';
            f.innerHTML = `<input type='hidden' name='attendance_id' value='${id}'><input type='hidden' name='delete_attendance' value='1'>`;
            document.body.appendChild(f); f.submit();
        }});
    }

    <?php if(isset($_SESSION['status'])): ?>
        Swal.fire({ icon: '<?= $_SESSION['status_code'] ?>', title: '<?= $_SESSION['status'] ?>', showConfirmButton: false, timer: 2000 });
        <?php unset($_SESSION['status']); unset($_SESSION['status_code']); ?>
    <?php endif; ?>
</script>
</body>
</html>