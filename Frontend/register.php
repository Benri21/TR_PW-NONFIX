<?php
session_start();
// Pastikan path ke koneksi.php benar
require_once __DIR__ . "/../Backend/koneksi.php";

$message = "";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if ($password !== $confirm_password) {
        $message = "Password tidak cocok!";
    } else {
        // Cek username duplikat
        $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek_user) > 0) {
            $message = "Username sudah digunakan!";
        } else {
            // --- BAGIAN YANG DIUBAH ---
            // Kita TIDAK LAGI menggunakan password_hash.
            // Password langsung disimpan apa adanya.
            
            // Default role adalah 'user'
            $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')";
            
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location.href='login.php';</script>";
            } else {
                $message = "Registrasi Gagal: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TeknikTix</title>
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
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            color: white;
        }
        .form-control {
            background: #2b394e;
            border: 1px solid #444;
            color: white;
        }
        .form-control:focus {
            background: #2b394e;
            color: white;
            box-shadow: 0 0 5px #ffcc00;
            border-color: #ffcc00;
        }
        .btn-primary {
            background-color: #ffcc00;
            border: none;
            color: black;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #e6b800;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Register</h2>
        <p class="text-white-50">Buat akun TeknikTix baru</p>
    </div>

    <?php if($message): ?>
        <div class="alert alert-danger py-2"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Buat password">
        </div>
        <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="confirm_password" class="form-control" required placeholder="Ulangi password">
        </div>
        <button type="submit" name="register" class="btn btn-primary w-100 py-2">Daftar Sekarang</button>
    </form>

    <div class="text-center mt-3">
        <small>Sudah punya akun? <a href="login.php" class="text-warning text-decoration-none">Login disini</a></small>
    </div>
</div>

</body>
</html>