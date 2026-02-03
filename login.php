<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Route 1: monitor.php
    if ($username === "admin" && $password === "monitor") {
        $_SESSION['user'] = "admin";
        header("Location: monitor.php");
        exit();
    } 
    // Route 2: dashboard.php
    elseif ($username === "admin" && $password === "dashboard") {
        $_SESSION['user'] = "admin";
        header("Location: dashboard.php");
        exit();
    } 
    else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baras Cockpit Center | Login</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            color: #fff;
            text-align: center;
        }

       
        .logo-container {
            margin-bottom: 20px;
        }

        .logo {
            width: 100px; 
            height: 100px;
            object-fit: cover;
            border-radius: 50%; 
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .login-title {
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: 1px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .login-subtitle {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
        }

        .input-group {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .input-group:focus-within {
            border-color: #ff512f;
            background: rgba(255, 255, 255, 0.15);
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            padding-left: 15px;
        }

        .form-control {
            background: transparent;
            border: none;
            color: #fff;
            padding: 12px;
        }

        .form-control:focus {
            background: transparent;
            box-shadow: none;
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .btn-login {
            background: linear-gradient(135deg, #ff512f, #dd2476);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: 0.3s;
        }

        .btn-login:hover {
            opacity: 0.9;
            transform: scale(1.02);
            color: #fff;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #ff8c94;
            font-size: 0.85rem;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .toggle-password {
            cursor: pointer;
            padding-right: 15px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-container">
        <img src="img.jpg" class="logo" alt="Baras Cockpit Logo">
    </div>

    <h4 class="login-title">BARAS COCKPIT CENTER</h4>
    <p class="login-subtitle">Admin Access Panel</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <div class="toggle-password" onclick="togglePassword()">
                <i class="bi bi-eye" id="toggleIcon"></i>
            </div>
        </div>

        <button type="submit" class="btn btn-login mt-2">
            LOGIN
        </button>
    </form>

    <div class="mt-4 small opacity-50">
        © <?= date("Y") ?> Admin System
    </div>
</div>

<script>
    function togglePassword() {
        const pass = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");
        if (pass.type === "password") {
            pass.type = "text";
            icon.classList.replace("bi-eye", "bi-eye-slash");
        } else {
            pass.type = "password";
            icon.classList.replace("bi-eye-slash", "bi-eye");
        }
    }
</script>

</body>
</html>