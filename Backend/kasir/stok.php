<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

// Cek akses kasir
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../../Frontend/login.php");
    exit();
}

$total_kursi_per_studio = 40; 

// Ambil Data Jadwal Film
$query = "SELECT j.*, f.judul_film, f.durasi 
          FROM jadwal j 
          JOIN film f ON j.id_film = f.id_film 
          WHERE j.tanggal >= CURDATE()
          ORDER BY j.tanggal ASC, j.jam_mulai ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Tiket - Teknik-Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0f4c9c;
            overflow-x: hidden;
        }
        .sidebar {
            background: #0f4c9c;
            min-height: 100vh;
            color: white;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 16.666667%;
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
            color: #ffcc00;
            padding-left: 25px;
        }
        .sidebar a.active {
            background: linear-gradient(90deg, rgba(255,204,0,0.2) 0%, rgba(255,255,255,0) 100%);
            color: #ffcc00;
            border-left: 4px solid #ffcc00;
        }
        .main-content {
            margin-left: 16.666667%;
            padding: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: white;
            overflow: hidden;
        }
        .table thead {
            background-color: #0f4c9c;
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f7ff;
        }
        .progress {
            height: 25px;
            border-radius: 15px;
            background-color: #e9ecef;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }
        .progress-bar {
            font-weight: bold;
            line-height: 25px;
        }
        .status-badge {
            font-size: 0.85em;
            padding: 5px 10px;
            border-radius: 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            color: #555;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-2 sidebar">
            <h4><i class="fa-solid fa-film"></i> TEKNIK-CINEMA</h4>
            <p class="small text-white-50 px-3 mb-2">MENU UTAMA</p>
            <a href="transaksi.php"><i class="fa-solid fa-ticket me-2"></i> Verifikasi Tiket</a>
            <a href="stok.php" class="active"><i class="fa-solid fa-chair me-2"></i> Cek Stok Kursi</a>
            
            <div style="margin-top: 50px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <p class="small text-white-50 px-3 mb-2">AKUN</p>
                <div class="px-3 mb-3 text-warning small"><i class="fa-solid fa-user-circle"></i> Halo, Kasir</div>
                <a href="../../Frontend/logout.php" class="text-danger"><i class="fa-solid fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </div>

        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-light">Ketersediaan Kursi</h2>
                    <p class="text-light">Pantau okupansi studio secara real-time.</p>
                </div>
                <div class="text-end">
                    <div class="card p-2 px-3 d-inline-block text-start">
                        <small class="text-muted d-block">Kapasitas Studio</small>
                        <span class="fw-bold text-primary fs-5"><?= $total_kursi_per_studio ?> Kursi</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Judul Film</th>
                                    <th>Jadwal Tayang</th>
                                    <th class="text-center">Terjual</th>
                                    <th class="text-center">Sisa</th>
                                    <th style="width: 35%;">Status Okupansi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($result) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <?php
                                            // LOGIKA HITUNG KURSI
                                            $id_film = $row['id_film'];
                                            $tanggal = $row['tanggal'];
                                            
                                            
                                            // format jam dari '13:00:00' menjadi '13:00' agar cocok dengan tabel transaksi
                                            $jam = date('H:i', strtotime($row['jam_mulai']));

                                            // Query hitung kursi yang statusnya TIDAK batal
                                            $q_stok = "SELECT kursi FROM transaksi 
                                                       WHERE id_film = '$id_film' 
                                                       AND tanggal_tayang = '$tanggal' 
                                                       AND jam_tayang = '$jam' 
                                                       AND status != 'batal'";
                                            
                                            $r_stok = mysqli_query($conn, $q_stok);
                                            
                                            $total_terjual = 0;
                                            while($t = mysqli_fetch_assoc($r_stok)){
                                                $kursi_array = explode(",", $t['kursi']);
                                                $total_terjual += count($kursi_array);
                                            }

                                            $sisa = $total_kursi_per_studio - $total_terjual;
                                            $persen = ($total_terjual / $total_kursi_per_studio) * 100;
                                            
                                            // Visualisasi Progress Bar
                                            $bg_color = "bg-success";
                                            $status_text = "Tersedia";
                                            
                                            if($persen > 50) { 
                                                $bg_color = "bg-warning"; 
                                                $status_text = "Mulai Penuh";
                                            }
                                            if($persen > 85) { 
                                                $bg_color = "bg-danger"; 
                                                $status_text = "Hampir Habis";
                                            }
                                            if($sisa == 0) {
                                                $status_text = "SOLD OUT";
                                                $bg_color = "bg-secondary";
                                            }
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-primary"><?= $row['judul_film'] ?></div>
                                                <small class="text-muted"><i class="fa-solid fa-clock"></i> <?= $row['durasi'] ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3 text-center bg-light border rounded p-1 px-2">
                                                        <small class="d-block text-muted text-uppercase"><?= date('M', strtotime($row['tanggal'])) ?></small>
                                                        <strong class="fs-5"><?= date('d', strtotime($row['tanggal'])) ?></strong>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-dark"><?= $jam ?> WIB</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold fs-5"><?= $total_terjual ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold fs-5 text-success"><?= $sisa ?></span>
                                            </td>
                                            <td class="pe-4">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <small class="fw-bold text-muted"><?= round($persen) ?>% Terisi</small>
                                                    <small class="status-badge"><?= $status_text ?></small>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar <?= $bg_color ?>" role="progressbar" 
                                                         style="width: <?= $persen ?>%">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada jadwal tayang.</td></tr>
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