<?php
session_start();
// Cek Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../Frontend/login.php");
    exit();
}

require_once(__DIR__ . "/../koneksi.php");

// ==============================
// 1. STATISTIK TOTAL (ANGKA)
// ==============================
$q_users = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$users = mysqli_fetch_assoc($q_users)['total_users'] ?? 0; 

$q_film_count = mysqli_query($conn, "SELECT COUNT(*) AS total_film FROM film");
$film_count = mysqli_fetch_assoc($q_film_count)['total_film'] ?? 0; 

$q_transaksi = mysqli_query($conn, "SELECT COUNT(*) AS total_transaksi FROM transaksi");
$transaksi = mysqli_fetch_assoc($q_transaksi)['total_transaksi'] ?? 0; 

// ==============================
// 2. DATA LIST FILM
// ==============================
$result_film = mysqli_query($conn, "SELECT * FROM film ORDER BY id_film DESC LIMIT 5");

// ==============================
// 3. DATA PEMESANAN TERBARU
// ==============================
$query_detail = "
    SELECT 
        t.id_transaksi, t.tanggal_pesan, t.tanggal_tayang, t.jam_tayang,
        t.jumlah_tiket, t.kursi, t.total_harga, t.status,
        f.judul_film, j.studio, u.username
    FROM transaksi t
    LEFT JOIN film f ON t.id_film = f.id_film
    LEFT JOIN jadwal j ON t.id_film = j.id_film AND t.tanggal_tayang = j.tanggal AND t.jam_tayang = j.jam_mulai
    LEFT JOIN users u ON t.id_user = u.id_user
    ORDER BY t.tanggal_pesan DESC LIMIT 10
";
$data_detail = mysqli_query($conn, $query_detail);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard - TeknikTix</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body { background-color: #0f4c9c; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        /* NAVBAR */
        .navbar-custom {
            background: #173d79;
            padding: 15px 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }
        .navbar-custom a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
            transition: 0.3s;
        }
        .navbar-custom a:hover { color: #ffcc00; }
        .navbar-custom .logout { color: #ffcc00 !important; font-weight: bold; }

        .title { color: #ffcc00; text-shadow: 0px 2px 4px rgba(0,0,0,0.6); margin-top: 20px; font-weight: 800; }

        .container-box {
            background: #1f293a;
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.4);
        }

        /* Card Statistik */
        .stat-card {
            background: #2b394e;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-bottom: 4px solid #ffcc00;
        }
        .stat-card h3 { font-size: 2.5rem; font-weight: bold; margin: 0; }
        .stat-card p { margin: 0; opacity: 0.8; }

        /* Tabel */
        .table-dark-custom {
            --bs-table-bg: #2b394e;
            --bs-table-color: white;
            --bs-table-border-color: #444;
        }
        .table-dark-custom th { background: #173d79; color: #ffcc00; }
        
        .status-badge {
            padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase;
        }
        .status-pending { background: #ffc107; color: black; }
        .status-dibayar { background: #0d6efd; color: white; }
        .status-terverifikasi { background: #198754; color: white; }
        .status-batal { background: #dc3545; color: white; }
    </style>
</head>

<body>

<nav class="navbar-custom d-flex justify-content-between align-items-center">
    <div style="color:#ffcc00; font-size:22px; font-weight:bold;">
        <i class="fa-solid fa-film"></i> TEKNIKTIX ADMIN
    </div>

    <div>
        <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="admin_film.php"><i class="fa-solid fa-clapperboard"></i> Kelola Film</a>
        <a href="admin_jadwal.php"><i class="fa-regular fa-calendar-days"></i> Kelola Jadwal</a>
        <a href="admin_users.php"><i class="fa-solid fa-users-gear"></i> Kelola User</a>
        
        <a href="../../Frontend/logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <h2 class="text-center title">ADMIN DASHBOARD</h2>

    <div class="row mt-4 g-4">
        <div class="col-md-4">
            <div class="stat-card">
                <h3><?= htmlspecialchars($users) ?></h3>
                <p><i class="fa-solid fa-users"></i> Total User Terdaftar</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h3><?= htmlspecialchars($film_count) ?></h3>
                <p><i class="fa-solid fa-film"></i> Total Film Tayang</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h3><?= htmlspecialchars($transaksi) ?></h3>
                <p><i class="fa-solid fa-receipt"></i> Total Transaksi Masuk</p>
            </div>
        </div>
    </div>

    <div class="container-box mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold"><i class="fa-solid fa-video"></i> Daftar Film Terbaru</h4>
            <a href="admin_film.php" class="btn btn-sm btn-outline-warning">Lihat Semua</a>
        </div>

        <?php if (mysqli_num_rows($result_film) > 0): ?>
        <table class="table table-bordered table-dark-custom">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul Film</th>
                    <th>Genre</th>
                    <th>Durasi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($film = mysqli_fetch_assoc($result_film)) { ?>
                <tr>
                    <td><?= htmlspecialchars($film['id_film']) ?></td>
                    <td><?= htmlspecialchars($film['judul_film']) ?></td>
                    <td><?= htmlspecialchars($film['genre']) ?></td>
                    <td><?= htmlspecialchars($film['durasi']) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-center text-muted">Belum ada data film.</p>
        <?php endif; ?>
    </div>

    <div class="container-box mt-4">
        <h4 class="fw-bold mb-3"><i class="fa-solid fa-money-bill-wave"></i> Transaksi Terbaru</h4>

        <?php if (mysqli_num_rows($data_detail) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-dark-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Film</th>
                        <th>Jadwal</th>
                        <th>Kursi</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($data_detail)) { 
                        $statusClass = 'status-pending';
                        if($p['status'] == 'dibayar') $statusClass = 'status-dibayar';
                        if($p['status'] == 'terverifikasi') $statusClass = 'status-terverifikasi';
                        if($p['status'] == 'batal') $statusClass = 'status-batal';
                    ?>
                    <tr>
                        <td>#<?= htmlspecialchars($p['id_transaksi']) ?></td>
                        <td><?= htmlspecialchars($p['username'] ?? 'User Hapus') ?></td>
                        <td><?= htmlspecialchars($p['judul_film']) ?></td>
                        <td>
                            <?= date('d/m', strtotime($p['tanggal_tayang'])) ?> 
                            <?= date('H:i', strtotime($p['jam_tayang'])) ?>
                        </td>
                        <td><?= htmlspecialchars($p['kursi']) ?></td>
                        <td>Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-center text-muted">Belum ada transaksi masuk.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>