<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Asia/Manila');
$adminName = $_SESSION['user']['name'] ?? 'Admin';
$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];
    $today = date('Y-m-d');
    $now = date('H:i:s');

    $emp = $conn->prepare("SELECT id, firstName, lastName, position FROM employees WHERE barcode=?");
    $emp->bind_param("s", $barcode);
    $emp->execute();
    $employee = $emp->get_result()->fetch_assoc();

    if (!$employee) {
        $message = "<div class='alert alert-danger'>❌ Invalid barcode</div>";
    } else {
        $employee_id = $employee['id'];

       
        $check = $conn->prepare("SELECT * FROM attendance WHERE employee_id=? AND date=?");
        $check->bind_param("is", $employee_id, $today);
        $check->execute();
        $att = $check->get_result()->fetch_assoc();

        if (!$att) {
        
            $insert = $conn->prepare("INSERT INTO attendance (employee_id, date, time_in) VALUES (?, ?, ?)");
            $insert->bind_param("iss", $employee_id, $today, $now);
            $insert->execute();
            $message = "<div class='alert alert-success'>✅ Time In: {$employee['firstName']} {$employee['lastName']} ({$employee['position']})<br>⏰ " . date('h:i:s A', strtotime($now)) . "</div>";
        } elseif ($att['time_out'] === NULL) {
          
            $update = $conn->prepare("UPDATE attendance SET time_out=? WHERE id=?");
            $update->bind_param("si", $now, $att['id']);
            $update->execute();
            $message = "<div class='alert alert-warning'>⏱ Time Out: {$employee['firstName']} {$employee['lastName']} ({$employee['position']})<br>⏰ " . date('h:i:s A', strtotime($now)) . "</div>";
        } else {
          
            $message = "<div class='alert alert-info'>ℹ️ Already recorded:<br>Time In: " . date('h:i:s A', strtotime($att['time_in'])) . "<br>Time Out: " . date('h:i:s A', strtotime($att['time_out'])) . "</div>";
        }
    }
}

$timeInResults = $conn->query("
    SELECT e.firstName, e.lastName, e.position, a.time_in
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    WHERE a.date = '".date('Y-m-d')."'
    ORDER BY a.time_in ASC
");

$timeOutResults = $conn->query("
    SELECT e.firstName, e.lastName, e.position, a.time_out
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    WHERE a.date = '".date('Y-m-d')."' AND a.time_out IS NOT NULL
    ORDER BY a.time_out ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Registration</title>
<link rel="stylesheet" href="Style/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>

</style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="login-card">
        <img src="img.jpg" class="logo" alt="Logo">
        <h5><?= htmlspecialchars($adminName) ?></h5>
    </div>
    <div class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></div>
    <ul class="nav flex-column mt-3">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house"></i> <span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="employees.php"><i class="bi bi-people"></i> <span>Employees</span></a></li>
        <li class="nav-item"><a class="nav-link" href="attendance.php"><i class="bi bi-clock"></i> <span>Attendance</span></a></li>
        <li class="nav-item"><a class="nav-link active" href="registration.php"><i class="bi bi-pencil-square"></i> <span>Registration</span></a></li>
    </ul>
</div>

<div class="content-area">
    <div class="content container text-center">
        <h3>Attendance Registration</h3>


        <div class="mb-3">
            <h5>Current Date & Time: <span id="currentDateTime"></span></h5>
        </div>

  
        <form id="barcodeForm" method="POST" class="mb-3">
            <input type="text"
                   name="barcode"
                   id="barcodeInput"
                   class="form-control form-control-lg text-center"
                   placeholder="Scan Barcode Here"
                   autofocus
                   autocomplete="off"
                   required>
        </form>

        <div class="mt-2"><?php if(isset($message)) echo $message; ?></div>

       
        <div class="mb-3">
            <form method="POST" action="export_excel.php" style="display:inline-block;">
                <button type="submit" class="btn btn-success"><i class="bi bi-file-earmark-spreadsheet"></i> Export to Excel</button>
            </form>
            <form method="POST" action="export_pdf.php" style="display:inline-block;">
                <button type="submit" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> Export to PDF</button>
            </form>
        </div>


        <div class="table-wrapper">
         
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th colspan="3">Time In</th></tr>
                    <tr><th>Name</th><th>Position</th><th>Time</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $timeInResults->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['firstName'].' '.$row['lastName']) ?></td>
                            <td><?= htmlspecialchars($row['position']) ?></td>
                            <td><?= date('h:i:s A', strtotime($row['time_in'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

   
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th colspan="3">Time Out</th></tr>
                    <tr><th>Name</th><th>Position</th><th>Time</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $timeOutResults->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['firstName'].' '.$row['lastName']) ?></td>
                            <td><?= htmlspecialchars($row['position']) ?></td>
                            <td><?= date('h:i:s A', strtotime($row['time_out'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<audio id="beep" src="beep.mp3"></audio>

<script>

function updateCurrentTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateStr = now.toLocaleDateString('en-US', options);
    const timeStr = now.toLocaleTimeString('en-US', { hour12: true });
    document.getElementById('currentDateTime').textContent = `${dateStr} ${timeStr}`;
}
setInterval(updateCurrentTime, 1000);
updateCurrentTime();


document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('barcodeInput');
    const form = document.getElementById('barcodeForm');
    input.focus();
    input.addEventListener('keypress', function(e){
        if(e.key === 'Enter'){
            e.preventDefault();
            form.submit();
            input.value = '';
        }
    });
    document.addEventListener('click', () => input.focus());
});


function toggleSidebar() { document.getElementById('sidebar').classList.toggle('collapsed'); }

<?php if(isset($message)): ?>
document.getElementById('beep').play();
setTimeout(()=>{ window.location.href = "registration.php"; }, 1500);
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
