<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

// Cek apakah yang login adalah kasir
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../../Frontend/login.php");
    exit();
}

// Logika Verifikasi Tiket
if (isset($_GET['verifikasi_id'])) {
    $id_transaksi = $_GET['verifikasi_id'];
    $id_kasir = $_SESSION['id_user'];
    
    $query_update = "UPDATE transaksi SET status = 'terverifikasi', id_kasir = '$id_kasir' WHERE id_transaksi = '$id_transaksi'";
    mysqli_query($conn, $query_update);
    header("Location: transaksi.php?msg=sukses");
    exit();
}

// Fitur Pencarian
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT t.*, u.username, f.judul_film 
              FROM transaksi t 
              JOIN users u ON t.id_user = u.id_user 
              JOIN film f ON t.id_film = f.id_film 
              WHERE t.id_transaksi LIKE '%$search%' OR u.username LIKE '%$search%'
              ORDER BY t.tanggal_pesan DESC";
} else {
    $query = "SELECT t.*, u.username, f.judul_film 
              FROM transaksi t 
              JOIN users u ON t.id_user = u.id_user 
              JOIN film f ON t.id_film = f.id_film 
              ORDER BY t.tanggal_pesan DESC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Dashboard - Teknik-Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #07336dff;
            overflow-x: hidden;
        }

        /* SIDEBAR STYLING - Mengikuti Warna Landing Page */
        .sidebar {
            background: #0f4c9c; /* Warna Biru Utama TeknikTix */
            min-height: 100vh;
            color: white;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 16.666667%; /* Sesuai col-md-2 */
            top: 0; left: 0;
            padding-top: 20px;
            z-index: 1000;
        }

        .sidebar h4 {
            color: white;
            font-weight: 800;
            letter-spacing: 1px;
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 20px;
        }

        .sidebar a {
            color: rgba(255,255,255, 0.8);
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffcc00; /* Warna Kuning Emas saat Hover */
            padding-left: 25px; /* Efek geser sedikit */
        }

        .sidebar a.active {
            background: linear-gradient(90deg, rgba(255,204,0,0.2) 0%, rgba(255,255,255,0) 100%);
            color: #ffcc00;
            border-left: 4px solid #ffcc00;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 16.666667%; /* Geser konten ke kanan agar tidak tertutup sidebar */
            padding: 30px;
        }

        /* CARD STYLING */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: white;
            overflow: hidden;
        }

        /* FORM PENCARIAN */
        .form-control {
            border-radius: 20px 0 0 20px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0f4c9c;
        }
        .btn-cari {
            border-radius: 0 20px 20px 0;
            background-color: #0f4c9c;
            border: 1px solid #0f4c9c;
            color: white;
        }
        .btn-cari:hover {
            background-color: #0a3675;
            color: white;
        }

        /* TABLE STYLING */
        .table thead {
            background-color: #0f4c9c;
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f7ff;
        }
        
        .badge {
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
        }

        /* BUTTONS */
        .btn-verifikasi {
            background-color: #198754;
            color: white;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 14px;
        }
        .btn-verifikasi:hover {
            background-color: #146c43;
            color: white;
            box-shadow: 0 3px 10px rgba(25, 135, 84, 0.3);
        }
        
        .btn-cetak {
            background-color: #0f4c9c;
            color: white;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 14px;
        }
        .btn-cetak:hover {
            background-color: #0a3675;
            color: #ffcc00;
        }

    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-2 sidebar">
            <h4><i class="fa-solid fa-film"></i> TEKNIK-CINEMA</h4>
            
            <p class="small text-white-50 px-3 mb-2">MENU UTAMA</p>
            
            <a href="transaksi.php" class="active">
                <i class="fa-solid fa-ticket me-2"></i> Verifikasi Tiket
            </a>
            <a href="stok.php">
                <i class="fa-solid fa-chair me-2"></i> Cek Stok Kursi
            </a>
            
            <div style="margin-top: 50px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <p class="small text-white-50 px-3 mb-2">AKUN</p>
                <div class="px-3 mb-3 text-warning small">
                    <i class="fa-solid fa-user-circle"></i> Halo, Kasir
                </div>
                <a href="../../Frontend/logout.php" class="text-danger">
                    <i class="fa-solid fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>

        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-light">Dashboard Kasir</h2>
                    <p class="text-light">Kelola transaksi dan verifikasi tiket pengunjung.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary fs-6"><?= date('l, d F Y') ?></span>
                </div>
            </div>

            <div class="card mb-4 p-3">
                <form method="GET" class="d-flex w-50">
                    <input type="text" name="search" class="form-control" placeholder="Cari ID Transaksi / Username..." value="<?= $search ?>">
                    <button type="submit" class="btn btn-cari"><i class="fa-solid fa-search"></i> Cari</button>
                    <a href="transaksi.php" class="btn btn-light border ms-2 rounded-circle" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                </form>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID Order</th>
                                    <th>Pelanggan</th>
                                    <th>Detail Film</th>
                                    <th>Jadwal</th>
                                    <th>Kursi</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($result) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">#<?= $row['id_transaksi'] ?></td>
                                        <td>
                                            <div class="fw-bold"><?= $row['username'] ?></div>
                                            <small class="text-muted">User ID: <?= $row['id_user'] ?></small>
                                        </td>
                                        <td><?= $row['judul_film'] ?></td>
                                        <td>
                                            <i class="fa-regular fa-calendar-alt text-muted"></i> <?= date('d M', strtotime($row['tanggal_tayang'])) ?>
                                            <br>
                                            <span class="badge bg-light text-dark border"><?= $row['jam_tayang'] ?></span>
                                        </td>
                                        <td class="text-break" style="max-width: 150px;">
                                            <small><?= $row['kursi'] ?></small>
                                        </td>
                                        <td class="fw-bold text-success">
                                            Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <?php if($row['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock"></i> Menunggu</span>
                                            <?php elseif($row['status'] == 'terverifikasi'): ?>
                                                <span class="badge bg-success"><i class="fa-solid fa-check-circle"></i> Selesai</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Batal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if($row['status'] == 'pending'): ?>
                                                <a href="transaksi.php?verifikasi_id=<?= $row['id_transaksi'] ?>" 
                                                   class="btn btn-verifikasi" 
                                                   onclick="return confirm('Apakah pembayaran tunai sudah diterima? Verifikasi sekarang?')">
                                                   <i class="fa-solid fa-check"></i> Verifikasi
                                                </a>
                                            <?php elseif($row['status'] == 'terverifikasi'): ?>
                                                <a href="struk.php?id=<?= $row['id_transaksi'] ?>" 
                                                   class="btn btn-cetak" target="_blank">
                                                   <i class="fa-solid fa-print"></i> Struk
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-box-open fa-3x mb-3"></i><br>
                                            Data transaksi tidak ditemukan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>