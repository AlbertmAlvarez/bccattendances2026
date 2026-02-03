<?php
session_start();
$conn = new mysqli("localhost", "root", "", "employees");
date_default_timezone_set('Asia/Manila'); 

$logDate = date('Y-m-d');

// --- 1. SYNC SETTINGS ---
$settingsRes = $conn->query("SELECT setting_name, setting_value FROM settings");
$settings = [];
while($sRow = $settingsRes->fetch_assoc()){
    $settings[$sRow['setting_name']] = $sRow['setting_value'];
}

$manualOverride = ($settings['barcode_status'] === 'OPEN');
$lastReset = $settings['monitor_reset_time'] ?? $logDate . ' 00:00:00';

// --- 2. SCANNING LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['barcode'])) {
    $barcode = $conn->real_escape_string($_POST['barcode']);
    $currentTime = date('H:i:s');

    $empResult = $conn->query("SELECT * FROM employees WHERE barcode = '$barcode'");

    if ($empResult->num_rows > 0) {
        $employee = $empResult->fetch_assoc();
        $empId = $employee['id'];
        $fullName = $employee['firstName'] . ' ' . $employee['lastName'];

        $attResult = $conn->query("SELECT * FROM attendance WHERE employee_id = '$empId' AND date = '$logDate'");

        if ($attResult->num_rows == 0) {
            // INSERT TIME IN: Status is 'Present' initially
            $conn->query("INSERT INTO attendance (employee_id, date, time_in, status) VALUES ('$empId', '$logDate', '$currentTime', 'Present')");
            $_SESSION['status'] = "TIME IN: $fullName";
            $_SESSION['status_code'] = "success";
        } else {
            $attendance = $attResult->fetch_assoc();
            if ($attendance['time_out'] == NULL) {
                // UPDATE TIME OUT: Change status to 'Finished'
                $conn->query("UPDATE attendance SET 
                              time_out = '$currentTime', 
                              status = 'Finished' 
                              WHERE id = '{$attendance['id']}'");
                
                $_SESSION['status'] = "TIME OUT: $fullName";
                $_SESSION['status_code'] = "warning";
            } else {
                $_SESSION['status'] = "ALREADY COMPLETED FOR TODAY";
                $_SESSION['status_code'] = "error";
            }
        }
    } else {
        $_SESSION['status'] = "UNKNOWN ID";
        $_SESSION['status_code'] = "error";
    }
    header("Location: monitor.php");
    exit();
}

// --- 3. DATA FETCHING ---
$allEmp = $conn->query("SELECT COUNT(*) as total FROM employees");
$totalEmployees = $allEmp->fetch_assoc()['total'] ?? 0;

$swiped = $conn->query("SELECT COUNT(*) as swiped FROM attendance WHERE date = '$logDate' AND CONCAT(date, ' ', time_in) > '$lastReset'");
$totalSwipedToday = $swiped->fetch_assoc()['swiped'] ?? 0;

$pending = $conn->query("SELECT COUNT(*) as pending FROM attendance WHERE date = '$logDate' AND time_out IS NULL AND CONCAT(date, ' ', time_in) > '$lastReset'");
$pendingCount = $pending->fetch_assoc()['pending'] ?? 0;

// Fetch Recent Scans
$logResult = $conn->query("SELECT a.id, e.firstName, e.lastName, e.position, a.time_in, a.time_out, a.status 
                            FROM attendance a 
                            JOIN employees e ON a.employee_id = e.id 
                            WHERE a.date = '$logDate' 
                            AND CONCAT(a.date, ' ', a.time_in) > '$lastReset'
                            ORDER BY a.time_in DESC LIMIT 10");

$barcodeOpen = ($manualOverride || ($totalSwipedToday < $totalEmployees));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Monitor | Baras Cockpit</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --bg: #0f172a; --card-bg: #1e293b; --accent: #6366f1; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); color: white; overflow: hidden; }
        .monitor-container { height: 100vh; padding: 20px; display: flex; flex-direction: column; }
        .clock { font-size: 5rem; font-weight: 800; letter-spacing: -3px; text-align: center; margin-top: 20px; }
        .scanner-box { background: var(--card-bg); border-radius: 24px; padding: 40px; border: 1px solid rgba(255,255,255,0.1); }
        .scan-input { width: 100%; background: #0f172a; border: 2px solid #334155; border-radius: 16px; padding: 15px; color: white; font-size: 1.5rem; text-align: center; outline: none; transition: 0.3s; }
        .scan-input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2); }
        .stat-card { background: rgba(255,255,255,0.05); padding: 15px; border-radius: 15px; text-align: center; }
        .feed-card { background: var(--card-bg); border-radius: 24px; height: 100%; border: 1px solid rgba(255,255,255,0.1); overflow: hidden; }
        .status-badge { padding: 8px 20px; border-radius: 50px; font-weight: 800; }
    </style>
</head>
<body>

<div class="monitor-container">
    <div class="row g-4 flex-grow-1">
        <div class="col-lg-5">
            <div id="liveClock" class="clock">00:00:00</div>
            <p class="text-center text-muted fw-bold"><?= date('l, F d, Y') ?></p>

            <div class="scanner-box mt-4" id="scanner-ui">
                <center>
                    <div class="status-badge mb-3 <?= $barcodeOpen ? 'bg-success' : 'bg-danger' ?>">
                        <i class="bi <?= $barcodeOpen ? 'bi-unlock-fill' : 'bi-lock-fill' ?>"></i>
                        SCANNER <?= $barcodeOpen ? 'OPEN' : 'LOCKED' ?>
                    </div>
                </center>

                <?php if ($barcodeOpen): ?>
                    <form method="POST" autocomplete="off">
                        <input type="text" name="barcode" class="scan-input" placeholder="SCAN YOUR ID HERE" autofocus onblur="this.focus()" required>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <h5 class="text-danger fw-bold">SYSTEM CLOSED</h5>
                        <p class="small text-muted">Please wait for the administrator to start the session.</p>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mt-4">
                    <div class="col-4"><div class="stat-card"><small class="text-muted d-block">TOTAL</small><b><?= $totalEmployees ?></b></div></div>
                    <div class="col-4"><div class="stat-card text-success"><small class="text-muted d-block">PRESENT</small><b><?= $totalSwipedToday ?></b></div></div>
                    <div class="col-4"><div class="stat-card text-danger"><small class="text-muted d-block">OUT</small><b><?= $totalEmployees - $pendingCount ?></b></div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="feed-card p-4">
                <h5 class="fw-bold mb-4"><i class="bi bi-activity me-2"></i>Recent Activity</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-center">In</th>
                                <th class="text-center">Out</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($logResult->num_rows > 0): ?>
                                <?php while($row = $logResult->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= $row['firstName'] . ' ' . $row['lastName'] ?></div>
                                            <small class="text-muted"><?= $row['position'] ?></small>
                                        </td>
                                        <td class="text-center text-success fw-bold"><?= date('h:i A', strtotime($row['time_in'])) ?></td>
                                        <td class="text-center text-danger fw-bold"><?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '--:--' ?></td>
                                        <td class="text-center">
                                            <?php if($row['time_out']): ?>
                                                <span class="badge bg-success rounded-pill px-3">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Done
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-primary rounded-pill px-3">
                                                    <i class="bi bi-geo-alt-fill me-1"></i> Inside
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">Screen is cleared. Start scanning!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function updateClock() {
        document.getElementById('liveClock').textContent = new Date().toLocaleTimeString('en-US', { hour12: false });
    }
    setInterval(updateClock, 1000); updateClock();

    // AUTO SYNC: Monitors changes from Admin Panel
    setInterval(function(){
        fetch(window.location.href)
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.getElementById('scanner-ui').innerHTML = doc.getElementById('scanner-ui').innerHTML;
            document.querySelector('.table-responsive').innerHTML = doc.querySelector('.table-responsive').innerHTML;
            const input = document.querySelector('.scan-input');
            if(input) input.focus();
        });
    }, 3000);

    // Toast Notification
    <?php if(isset($_SESSION['status'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['status_code'] ?>',
            title: '<?= $_SESSION['status'] ?>',
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, background: '#1e293b', color: '#fff'
        });
        <?php unset($_SESSION['status']); ?>
    <?php endif; ?>
</script>
</body>
</html>