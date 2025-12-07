<?php
session_start();

// 1. ACCESS CONTROL: Only allow 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../Frontend/login.php");
    exit();
}

require_once(__DIR__ . "/../koneksi.php");

// 2. HANDLE ACTIONS (Add / Delete)
$action = $_GET['action'] ?? 'list';
$msg = "";

// ADD USER
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password']; // Password input
    $role = $_POST['role'];

    // Basic Validation
    if (empty($username) || empty($password) || empty($role)) {
        $msg = "Semua field harus diisi!";
    } else {
        // Check if username already exists
        $check = mysqli_query($conn, "SELECT id_user FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $msg = "Username sudah digunakan!";
        } else {
           
            $hashed_password = $password; 

            // Insert into database
            $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
            if (mysqli_query($conn, $query)) {
                header("Location: admin_users.php?msg=added");
                exit();
            } else {
                $msg = "Gagal menambah user: " . mysqli_error($conn);
            }
        }
    }
}

// DELETE USER
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Prevent deleting yourself
    if ($id == $_SESSION['id_user']) {
        echo "<script>alert('Anda tidak bisa menghapus akun sendiri!'); window.location='admin_users.php';</script>";
        exit();
    }

    mysqli_query($conn, "DELETE FROM users WHERE id_user = $id");
    header("Location: admin_users.php?msg=deleted");
    exit();
}

// 3. GET DATA: List all users (excluding regular users if you only want to manage staff)
// Or show all. Let's show Admins and Cashiers.
$users_res = mysqli_query($conn, "SELECT * FROM users WHERE role IN ('admin', 'kasir') ORDER BY role ASC, username ASC");
$users = mysqli_fetch_all($users_res, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kelola User Admin & Kasir</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background:#0f4c9c; font-family:Arial; color:white; margin:0; padding:0; }

    /* NAVBAR */
    .navbar-custom {
        background:#173d79; padding:15px 25px; margin-bottom:20px;
        display:flex; justify-content:space-between; align-items:center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    .navbar-custom a { color:#fff; margin-left:18px; text-decoration:none; font-weight:600; }
    .navbar-custom .brand { color:#ffcc00; font-weight:800; font-size: 20px; }
    .navbar-custom .logout { color: #ffcc00; }

    .container { width:90%; max-width: 1000px; margin:auto; }

    .box {
        background:#1f293a; padding:25px; border-radius:12px;
        box-shadow:0 6px 15px rgba(0,0,0,0.4); margin-bottom:25px;
    }

    h2, h4 { color:#ffcc00; font-weight: 700; }

    /* TABLE */
    table { width:100%; border-collapse:collapse; margin-top:15px; color: white; }
    th { background:#173d79; padding:12px; border: 1px solid #2b394e; }
    td { background:#2b394e; padding:12px; border: 1px solid #1f293a; }

    /* BUTTONS */
    .btn-custom { padding:8px 15px; border-radius:6px; text-decoration:none; font-weight:bold; border: none; cursor: pointer; }
    .add { background:#ffcc00; color:black; }
    .add:hover { background:#e6b800; }
    .del { background:#dc3545; color:white; font-size: 14px; }
    .del:hover { background:#c82333; }

    /* FORM ELEMENTS */
    input, select {
        width:100%; padding:10px; margin-top:5px; margin-bottom: 15px;
        border-radius:6px; border:1px solid #444; background: #2b394e; color: white;
    }
    input:focus, select:focus { border-color: #ffcc00; outline: none; }
    label { font-weight: 600; color: #ccc; }

    .alert-custom { background: #dc3545; color: white; padding: 10px; border-radius: 6px; margin-bottom: 20px; }
</style>
</head>
<body>

<div class="navbar-custom">
    <div class="brand">TEKNIK-CINEMA - ADMIN</div>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="admin_film.php">Kelola Film</a>
        <a href="admin_jadwal.php">Kelola Jadwal</a>
        <a href="admin_users.php" style="color:#ffcc00;">Kelola User</a> <a href="../../Frontend/logout.php" class="logout">Logout</a>
    </div>
</div>

<div class="container">

    <div class="box">
        <h4><i class="fa-solid fa-user-plus"></i> Tambah Akun Baru</h4>
        <p class="text-white-50 mb-4">Buat akun untuk Admin baru atau Kasir.</p>

        <?php if ($msg): ?>
            <div class="alert-custom"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_users.php?action=add">
            <div class="row">
                <div class="col-md-4">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Masukkan username">
                </div>
                <div class="col-md-4">
                    <label>Password</label>
                    <input type="text" name="password" required placeholder="Masukkan password">
                </div>
                <div class="col-md-4">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-custom add w-100 mt-2">Buat Akun</button>
        </form>
    </div>

    <div class="box">
        <h4><i class="fa-solid fa-users"></i> Daftar Staff (Admin & Kasir)</h4>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
            <div class="alert alert-success py-2">User berhasil ditambahkan!</div>
        <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success py-2">User berhasil dihapus!</div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Password (Encrypted/Plain)</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php $no = 1; foreach ($users as $u): ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td>
                            <?php if($u['role'] == 'admin'): ?>
                                <span class="badge bg-warning text-dark">ADMIN</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark">KASIR</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-family: monospace; color: #aaa;">
                            <?= strlen($u['password']) > 20 ? substr($u['password'], 0, 20) . '...' : $u['password'] ?>
                        </td>
                        <td align="center">
                            <?php if ($u['username'] !== $_SESSION['username']): // Cannot delete self ?>
                                <a href="admin_users.php?action=delete&id=<?= $u['id_user'] ?>" 
                                   class="btn-custom del" 
                                   onclick="return confirm('Yakin ingin menghapus user <?= $u['username'] ?>?');">
                                   Hapus
                                </a>
                            <?php else: ?>
                                <span class="text-white-50 small">(Anda)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" align="center">Belum ada data staff lain.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</body>
</html>