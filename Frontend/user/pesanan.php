<?php
session_start();
// Mundur 2 folder untuk ke Backend (Frontend/user/ -> Frontend/ -> Root -> Backend)
require_once __DIR__ . "/../../Backend/koneksi.php";

// 1. CEK LOGIN
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='../login.php';</script>";
    exit();
}

$id_user = $_SESSION['id_user'];
$username = $_SESSION['username'];

// 2. AMBIL DATA PESANAN USER
// Kita join tabel transaksi dengan tabel film untuk mengambil judul & gambar
$query = "SELECT t.*, f.judul_film, f.gambar 
          FROM transaksi t 
          JOIN film f ON t.id_film = f.id_film 
          WHERE t.id_user = '$id_user' 
          ORDER BY t.tanggal_pesan DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Teknik Cineam</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../Style/style.css?v=<?= time(); ?>">

    <style>
        /* CSS KHUSUS HALAMAN PESANAN */
        body {
            background-color: #0f172a;
            color: white;
            padding-top: 100px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container-pesanan {
            max-width: 900px;
            margin: 0 auto;
            flex: 1;
            padding: 0 20px;
        }

        .page-title {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-weight: 800;
            color: #ffc107;
        }

        /* CARD PESANAN (Horizontal) */
        .order-card {
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            display: flex;
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
        }
        .order-card:hover {
            border-color: #ffc107;
            transform: translateY(-2px);
        }

        .order-img {
            width: 120px;
            height: 160px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .order-details {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .film-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .order-date {
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .info-row {
            display: flex;
            gap: 20px;
            font-size: 0.9rem;
            color: #cbd5e1;
            margin-bottom: 5px;
        }
        .info-row i { color: #ffc107; width: 20px; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #f59e0b; color: black; }
        .status-dibayar { background: #3b82f6; color: white; }
        .status-terverifikasi { background: #10b981; color: white; } /* HIJAU */
        .status-batal { background: #ef4444; color: white; }

        .price-total {
            font-size: 1.1rem;
            font-weight: 800;
            color: #ffc107;
            text-align: right;
            margin-top: auto;
        }

        /* RESPONSIVE HP */
        @media (max-width: 576px) {
            .order-card { flex-direction: column; }
            .order-img { width: 100%; height: 150px; }
            .order-header { flex-direction: column; gap: 5px; }
            .price-total { text-align: left; margin-top: 15px; }
        }
    </style>
</head>
<body>
    <header>
        <h2>TekCinema</h2>
        <div class="nav-container">
            <ul class="nav-links">
                <div class="Menu">
                    <li><a href="../landingpage.php">Home</a></li>
                    <li><a href="../Jadwal_film.php">Jadwal Film</a></li>
                    <li><a href="favorit.php">Rating Film</a></li>
                    <li><a href="pesanan.php" class="active">Pesanan Saya</a></li>
                </div>
            </ul>
        </div>
        
        <div class="header-right">
            <div class="user-info">
                <i class="fa-solid fa-user"></i> <span><?= htmlspecialchars($username) ?></span>
            </div>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </header>

    <div class="container-pesanan">
        <h2 class="page-title"><i class="fa-solid fa-ticket"></i> Riwayat Pesanan Saya</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                // Logika Path Gambar
                $gambarRaw = trim($row['gambar']);
                $gambarRaw = str_replace('"', '', $gambarRaw);
                $namaFile = basename($gambarRaw);
                // Mundur satu folder lalu masuk Assets
                $path_gambar = "../Assets/img/" . $namaFile;
                
                if (!file_exists("../" . $path_gambar)) { 
                    // Cek path alternatif jika perlu
                    $path_gambar = "https://via.placeholder.com/300x450?text=No+Image"; 
                } else {
                    $path_gambar = "../" . $path_gambar; // Agar sesuai struktur folder
                }

                // Format Tanggal
                $tanggalTayang = date('d M Y', strtotime($row['tanggal_tayang']));
                $jamTayang = date('H:i', strtotime($row['jam_tayang']));
                $tglPesan = date('d/m/Y H:i', strtotime($row['tanggal_pesan']));

                // Status Badge Color
                $statusClass = 'status-pending';
                $statusLabel = $row['status'];
                if ($row['status'] == 'dibayar') $statusClass = 'status-dibayar';
                if ($row['status'] == 'terverifikasi') $statusClass = 'status-terverifikasi'; // Verifikasi Kasir
                if ($row['status'] == 'batal') $statusClass = 'status-batal';
            ?>

            <div class="order-card">
                <img src="<?= $path_gambar ?>" alt="Poster" class="order-img">
                
                <div class="order-details">
                    <div class="order-header">
                        <div>
                            <h4 class="film-title"><?= $row['judul_film'] ?></h4>
                            <span class="order-date">Dipesan pada: <?= $tglPesan ?></span>
                        </div>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </div>

                    <div class="info-row">
                        <i class="fa-regular fa-calendar"></i> <?= $tanggalTayang ?>
                    </div>
                    <div class="info-row">
                        <i class="fa-regular fa-clock"></i> <?= $jamTayang ?> WIB
                    </div>
                    <div class="info-row">
                        <i class="fa-solid fa-couch"></i> Kursi: <b><?= $row['kursi'] ?></b>
                    </div>

                    <div class="price-total">
                        Total: Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                    </div>
                </div>
            </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center mt-5">
                <i class="fa-solid fa-receipt fa-4x text-muted mb-3"></i>
                <p class="text-white-50">Belum ada riwayat pesanan.</p>
                <a href="../Jadwal_film.php" class="btn btn-warning fw-bold">Pesan Tiket Sekarang</a>
            </div>
        <?php endif; ?>

    </div>

     <footer>
        <div class="footer-content">
            <div class="footer-section about">
                <h3>TekCinema</h3>
                <p>Platform pemesanan tiket bioskop terpercaya dengan layanan terbaik untuk pengalaman menonton yang tak terlupakan.</p>
            </div>

            <div class="footer-section links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="landingpage.php">Home</a></li>
                    <li><a href="#">Now Showing</a></li>
                    <li><a href="#">Upcoming Movies</a></li>
                    <li><a href="#">Promo</a></li>
                </ul>
            </div>

            <div class="footer-section contact">
                <h3>Kontak Kami</h3>
                <p><i class="fas fa-map-marker-alt"></i> Jl. Kemiri Barat No.47, Salatiga</p>
                <p><i class="fas fa-phone"></i> +62 812-1234-4321</p>
                <p><i class="fas fa-envelope"></i> info@teknikcineam.com</p>
            </div>

            <div class="footer-section social">
                <h3>Ikuti Kami</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
                <a href="#" class="btn-tentang-footer">Tentang Kami</a>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; 2025 Teknik Cinema. All Rights Reserved.
        </div>
    </footer>

</body>
</html>