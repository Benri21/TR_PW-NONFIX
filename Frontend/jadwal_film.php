<?php
session_start();
require_once __DIR__ . "/../Backend/koneksi.php";

// 1. CEK LOGIN
$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : "Pengunjung";

// 2. AMBIL DATA FILM
$query = "SELECT * FROM film ORDER BY id_film DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Film - Teknik Cineam</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="Style/style.css?v=<?= time(); ?>">
    
    <style>
        
        /* 1. Wrapper Konten */
        .jadwal-wrapper {
            padding: 40px 5%;
            max-width: 1280px;
            margin: 0 auto;
            flex: 1; /* Agar footer selalu di bawah */
        }

        .section-header {
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 10px;
        }

        /* 2. Style Khusus Harga & Tombol di Card */
        .price-section {
            margin-top: auto; /* Mendorong ke bagian bawah card */
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 10px;
        }

        .price-tag {
            color: #ffc107; /* Warna Kuning */
            font-weight: bold;
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }

        .btn-pesan-card {
            display: block;
            width: 100%;
            background-color: #ffc107;
            color: black;
            text-align: center;
            padding: 8px 0;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-pesan-card:hover {
            background-color: #e0a800; /* Kuning lebih gelap */
        }
    </style>
</head>
<body>

    <header>
        <h2>TekCinema</h2>
        <div class="nav-container">
            <ul class="nav-links">
                <div class="Menu">
                    <li><a href="landingpage.php">Home</a></li>
                    <li><a href="Jadwal_film.php" class="active">Jadwal Film</a></li>
                    <li><a href="user/favorit.php">Rating Film</a></li>
                    <li><a href="user/pesanan.php">Pesanan Saya</a></li>
                </div>
            </ul>
        </div>
        
        <div class="header-right">
            <?php if ($isLoggedIn): ?>
                <div class="user-info">
                    <i class="fa-solid fa-user"></i> <span><?= htmlspecialchars($username) ?></span>
                </div>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="jadwal-wrapper">
        <div class="section-header">
            <h2>Film Sedang Tayang</h2>
            
        </div>

        <div class="movie-grid">
            <?php 
            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    // --- 1. LOGIKA GAMBAR ---
                    $gambarRaw = trim($row['gambar']);
                    $gambarRaw = str_replace('"', '', $gambarRaw);
                    $namaFile = basename($gambarRaw);
                    $path_gambar = "Assets/img/" . $namaFile;
                    
                    // Cek ketersediaan file (Smart Check)
                    if (!file_exists("../" . $path_gambar)) {
                        if (file_exists($path_gambar)) {
                            // Path sudah benar relative
                        } elseif (file_exists("../Frontend/" . $path_gambar)) {
                            $path_gambar = "../Frontend/" . $path_gambar;
                        } else {
                            $path_gambar = "https://via.placeholder.com/300x450?text=No+Image";
                        }
                    } else {
                        $path_gambar = "../" . $path_gambar;
                    }

                    // --- 2. AMBIL DATA ---
                    $judul = $row['judul_film'];
                    $durasi = isset($row['durasi']) ? $row['durasi'] : '-';
                    $rating = isset($row['rating']) ? $row['rating'] : 'N/A';
                    
                    // Format Harga dari Database (Rp 35.000)
                    $hargaDB = isset($row['harga']) ? $row['harga'] : 35000;
                    $hargaFormatted = "Rp " . number_format($hargaDB, 0, ',', '.');
            ?>
            
            <div class="movie-card">
                <div class="card-image">
                    <div class="rating-badge">
                        <i class="fa-solid fa-star"></i> <?= $rating ?>
                    </div>
                    <a href="user/order.php?id=<?= $row['id_film'] ?>">
                        <img src="<?= $path_gambar ?>" alt="<?= $judul ?>">
                    </a>
                </div>
                
                <div class="card-content">
                    <div>
                        <h3><?= $judul ?></h3>
                        <div class="card-tags">
                            <span class="tag">2D</span>
                            <span class="tag"><?= $durasi ?></span>
                        </div>
                    </div>

                    <div class="price-section">
                        <span class="price-tag"><?= $hargaFormatted ?></span>
                        
                        <button class="btn-pesan-card" onclick="<?php 
                            if(!$isLoggedIn) {
                                // Jika belum login -> Alert & Redirect Login
                                echo "alert('Silakan Login untuk memesan tiket!'); window.location.href='login.php';";
                            } else {
                                // Jika sudah login -> Pindah ke Order.php bawa ID Film
                                echo "window.location.href='user/order.php?id=" . $row['id_film'] . "';";
                            }
                        ?>">
                            Pesan Tiket
                        </button>
                    </div>
                </div>
            </div>

            <?php 
                } 
            } else {
                echo "<p style='color:white; text-align:center; width:100%; padding:50px;'>Tidak ada film yang sedang tayang.</p>";
            }
            ?>
        </div>
    </main>

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