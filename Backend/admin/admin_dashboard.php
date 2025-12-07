<?php
require_once("../koneksi.php");

// ==============================
// 1. STATISTIK TOTAL (ANGKA)
// ==============================
$q_users = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
if (!$q_users) {
    die("Query users error: " . mysqli_error($conn));
}
$users = mysqli_fetch_assoc($q_users)['total_users'] ?? 0; 

$q_film_count = mysqli_query($conn, "SELECT COUNT(*) AS total_film FROM film");
if (!$q_film_count) {
    die("Query film count error: " . mysqli_error($conn));
}
$film_count = mysqli_fetch_assoc($q_film_count)['total_film'] ?? 0; 

$q_transaksi = mysqli_query($conn, "SELECT COUNT(*) AS total_transaksi FROM transaksi");
if (!$q_transaksi) {
    die("Query transaksi count error: " . mysqli_error($conn));
}
$transaksi = mysqli_fetch_assoc($q_transaksi)['total_transaksi'] ?? 0; 

// ==============================
// 2. DATA LIST FILM
// ==============================
$result_film = mysqli_query($conn, "SELECT * FROM film");
if ($result_film === false) {
    die("Query daftar film error: " . mysqli_error($conn));
}

// ==============================
// 3. DATA PEMESANAN
// ==============================
// Perbaikan: join berdasarkan tanggal_tayang (tanggal penayangan pada transaksi)
// dan jam_tayang = jam_mulai pada jadwal
$query_detail = "
    SELECT 
        t.id_transaksi,
        t.tanggal_pesan,
        t.tanggal_tayang,
        t.jam_tayang,
        t.jumlah_tiket,
        t.kursi,
        t.total_harga,
        t.status,
        f.judul_film,
        j.studio,
        u.username
    FROM transaksi t
    LEFT JOIN film f ON t.id_film = f.id_film
    LEFT JOIN jadwal j 
        ON t.id_film = j.id_film
        AND t.tanggal_tayang = j.tanggal
        AND t.jam_tayang = j.jam_mulai
    LEFT JOIN users u ON t.id_user = u.id_user
    ORDER BY t.tanggal_pesan DESC
";
$data_detail = mysqli_query($conn, $query_detail);
if ($data_detail === false) {
    die("Query detail transaksi error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Teknik-Cinema</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #0f4c9c;
            min-height: 100vh;
        }

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
        }
        .navbar-custom .logout {
            color: #ffcc00 !important;
            font-weight: bold;
        }

        .title {
            color: #ffcc00;
            text-shadow: 0px 2px 4px rgba(0,0,0,0.6);
            margin-top: 20px;
        }

        .container-box {
            background: #1f293a;
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.4);
        }

        th {
            background: #173d79;
            color: #fff;
        }

        td {
            background: #2b394e;
            color: #fff;
        }

        .small-note {
            color: #d1d5db;
            padding: 18px;
            text-align: center;
        }
    </style>
</head>

<body>

<!-- ============================== -->
<!--          NAVBAR ADMIN         -->
<!-- ============================== -->
<nav class="navbar-custom d-flex justify-content-between">
    <div style="color:#ffcc00; font-size:22px; font-weight:bold;">
        TEKNIK-CINEMA ADMIN PANEL
    </div>

    <div>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_film.php">Kelola Film</a>
        <a href="admin_jadwal.php">Kelola Jadwal</a>
        <a href="admin_users.php"><i class="fa-solid fa-users-gear"></i> Kelola User</a>
        <a href="../../Frontend/logout.php" class="menu-item logout">Logout</a>

    </div>
</nav>


<div class="container mt-4">
    <h2 class="text-center fw-bold title">ADMIN DASHBOARD</h2>

    <!-- Statistik -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="container-box text-center">
                <h3><?= htmlspecialchars($users) ?></h3>
                <p>Total User</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="container-box text-center">
                <h3><?= htmlspecialchars($film_count) ?></h3>
                <p>Total Film</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="container-box text-center">
                <h3><?= htmlspecialchars($transaksi) ?></h3>
                <p>Total Transaksi</p>
            </div>
        </div>
    </div>

    <!-- Daftar Film -->
    <div class="container-box mt-4">
        <h4 class="fw-bold">🎬 Daftar Film</h4>
        <a href="admin_film.php?action=add" class="btn btn-warning mb-3">+ Tambah Film</a>

        <?php if (mysqli_num_rows($result_film) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Film</th>
                    <th>Judul</th>
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
                    <td><?= htmlspecialchars($film['durasi']) ?> menit</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="small-note">Belum ada data film.</div>
        <?php endif; ?>
    </div>

    <!-- Daftar Pemesanan -->
    <div class="container-box mt-4">
        <h4 class="fw-bold">🧾 Daftar Pemesanan Tiket</h4>

        <?php if (mysqli_num_rows($data_detail) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>User</th>
                    <th>Film</th>
                    <th>Jadwal</th>
                    <th>Tiket</th>
                    <th>Kursi</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = mysqli_fetch_assoc($data_detail)) { ?>
                <tr>
                    <td><?= htmlspecialchars($p['id_transaksi']) ?></td>
                    <td><?= htmlspecialchars($p['username'] ?? 'User Dihapus') ?></td>
                    <td><?= htmlspecialchars($p['judul_film']) ?></td>
                    <td><?= htmlspecialchars($p['tanggal_tayang'] ?? '') ?> <?= htmlspecialchars($p['jam_tayang'] ?? '') ?> (Studio: <?= htmlspecialchars($p['studio'] ?? '-') ?>)</td>
                    <td><?= htmlspecialchars($p['jumlah_tiket']) ?></td>
                    <td><?= htmlspecialchars($p['kursi']) ?></td>
                    <td>Rp <?= number_format((float)$p['total_harga'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($p['status']) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="small-note">Belum ada pemesanan.</div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
