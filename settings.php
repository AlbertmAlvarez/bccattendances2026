<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Gamitin ang name mula sa session (Inayos base sa login structure mo)
$adminName = $_SESSION['user']['name'] ?? 'Admin';
$adminId = $_SESSION['user_id'] ?? 1; // Siguraduhing may user_id ka sa session

$conn = new mysqli("localhost", "root", "", "employees");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$message = "";
$msgType = "";

// LOGIC: UPDATE ACCOUNT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_account'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        // I-hash ang password para sa security
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET email = '$email', password = '$hashed_pass' WHERE id = '$adminId'";
    } else {
        $sql = "UPDATE users SET email = '$email' WHERE id = '$adminId'";
    }

    if ($conn->query($sql)) {
        $_SESSION['status'] = "Account updated successfully!";
        $_SESSION['status_code'] = "success";
    } else {
        $_SESSION['status'] = "Error updating account.";
        $_SESSION['status_code'] = "error";
    }
    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | Baras Cockpit</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --primary-bg: #f8f9fa; --sidebar-color: #0f172a; --accent-color: #6366f1; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--primary-bg); }
        
        /* SIDEBAR STYLE (Pareho sa Attendance) */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-color); position: fixed; left: 0; top: 0; color: white; display: flex; flex-direction: column; z-index: 1000; }
        .main-content { margin-left: 260px; padding: 40px; min-height: 100vh; }
        .nav-link { color: #94a3b8; padding: 12px 20px; margin: 5px 15px; border-radius: 8px; display: flex; align-items: center; text-decoration: none; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .nav-link.active { background: var(--accent-color); }

        /* SETTINGS CARD */
        .settings-card { background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; padding: 40px; max-width: 700px; }
        .form-label { font-weight: 600; color: #334155; }
        .btn-save { background: var(--accent-color); color: white; border: none; border-radius: 12px; padding: 12px 30px; font-weight: 700; transition: 0.3s; }
        .btn-save:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary mb-3">
        <img src="img.jpg" class="rounded-circle mb-2 border border-2 border-primary" style="width: 60px; height: 60px; object-fit: cover;">
        <h6 class="mb-0 fw-bold">Admin Portal</h6>
        <small class="text-muted"><?= htmlspecialchars($adminName) ?></small>
    </div>
    <div style="flex-grow: 1;">
        <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
        <a href="employees.php" class="nav-link"><i class="bi bi-people me-2"></i> Employees</a>
        <a href="attendance.php" class="nav-link"><i class="bi bi-calendar-check me-2"></i> Attendance</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reports</a>
        <a href="settings.php" class="nav-link active"><i class="bi bi-gear me-2"></i> Settings</a>
    </div>
    <div class="pb-4">
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="mb-4">
        <h4 class="fw-bold">Account Settings</h4>
        <p class="text-muted small">Update your administrative credentials here.</p>
    </div>

    <div class="settings-card">
        <form method="POST" autocomplete="off">
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control border-start-0" placeholder="admin@barascockpit.com" required>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" placeholder="Enter new password (optional)">
                    </div>
                    <small class="text-muted mt-2 d-block">Leave blank if you don't want to change your password.</small>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" name="update_account" class="btn-save">
                        <i class="bi bi-check2-circle me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // SweetAlert para sa Successful update
    <?php if(isset($_SESSION['status'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['status_code'] ?>',
            title: '<?= $_SESSION['status'] ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        <?php unset($_SESSION['status']); unset($_SESSION['status_code']); ?>
    <?php endif; ?>
</script>

</body>
</html>