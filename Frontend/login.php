<?php
session_start();
require_once __DIR__ . "/../Backend/koneksi.php";

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Ambil data user berdasarkan username
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        
        if ($password == $row['password']) {
            // Set Session
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['id_user'] = $row['id_user'];

            if ($row['role'] == 'admin') {
                header("Location: ../Backend/admin/dashboard.php"); // Arahkan ke folder admin
            } else if ($row['role'] == 'kasir') {
                header("Location: ../Backend/kasir/transaksi.php"); // Arahkan ke folder kasir
            } else {
                header("Location: landingpage.php"); // User biasa ke home
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TeknikTix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Style/style.css">
    <style>
        body {
            background-color: #0f4c9c;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: #1f293a;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            color: white;
            text-align: center;
        }
        .circle-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            color: #0f4c9c;
            font-weight: bold;
            font-size: 24px;
        }
        .form-control {
            background: #2b394e;
            border: 1px solid #444;
            color: white;
            margin-bottom: 15px;
        }
        .form-control:focus {
            background: #2b394e;
            color: white;
            box-shadow: 0 0 5px #ffcc00;
            border-color: #ffcc00;
        }
        .btn-login {
            background-color: #ffcc00;
            border: none;
            color: black;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: #e6b800;
            transform: scale(1.02);
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="circle-logo">
        <i class="fa-solid fa-user"></i>
    </div>
    <h3 class="fw-bold mb-4">Welcome Back!</h3>

    <?php if($error): ?>
        <div class="alert alert-danger py-2 text-start"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="text-start">
            <label class="form-label small text-white-50">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="text-start">
            <label class="form-label small text-white-50">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        
        <button type="submit" name="login" class="btn btn-login w-100 py-2 mt-3">LOGIN</button>
    </form>

    <div class="mt-4">
        <small>Belum punya akun? <a href="register.php" class="text-warning text-decoration-none">Daftar Disini</a></small>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>