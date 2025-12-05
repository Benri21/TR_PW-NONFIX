<?php 
session_start();
// Pastikan path ke koneksi.php benar
require_once __DIR__ . "/../Backend/koneksi.php";

// --- 1. LOGIKA CEK USER ---
$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : "Pengunjung";

// --- 2. LOGIKA LINK NAVIGASI ---
$link_jadwal   = $isLoggedIn ? "Jadwal_film.php" : "login.php";
$link_rating   = $isLoggedIn ? "user/favorit.php" : "login.php";
$link_pesanan  = $isLoggedIn ? "user/pesanan.php" : "login.php";
$link_view_all = $isLoggedIn ? "Jadwal_film.php" : "login.php";

// --- 3. AMBIL DATA FILM ---
$query_terbaru = "SELECT * FROM film ORDER BY id_film DESC LIMIT 5";
$result_terbaru = mysqli_query($conn, $query_terbaru);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeknikTix - Home</title>
    
    <link rel="stylesheet" href="Style/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <h2>TekCinema</h2>
        <div class="nav-container">
            <ul class="nav-links">
                <div class="Menu">
                    <li><a href="landingpage.php" class="active">Home</a></li>
                    <li><a href="<?= $link_jadwal ?>" onclick="<?= !$isLoggedIn ? "alert('Silakan Login untuk melihat jadwal!');" : "" ?>">Jadwal Film</a></li>
                    <li><a href="<?= $link_rating ?>" onclick="<?= !$isLoggedIn ? "alert('Silakan Login untuk memberi rating!');" : "" ?>">Rating Film</a></li>
                    <li><a href="<?= $link_pesanan ?>" onclick="<?= !$isLoggedIn ? "alert('Silakan Login untuk melihat pesanan!');" : "" ?>">Pesanan Saya</a></li>
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
                <a href="login.php" class="btn-login" style="color: white; text-decoration: none; border: 1px solid white; padding: 5px 15px; border-radius: 5px;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </header>

    <main class="hero-wrapper">
        <section class="hero-section">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
                <p>Selamat Datang di Web Pemesanan Tiket TeknikTix. Nikmati kemudahan pengecekan jadwal film dan pemesanan tiket bioskop.</p>
                
                <div class="hero-badge">
                    <div class="circle-logo">
                        <h3>TeTix</h3>
                        <i class="fa-solid fa-clapperboard"></i>
                        <p>BIOSKOP TIKET</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="recommendation-section">
           <div class="section-header">
    <h2>Recommended Film</h2>
    
    <button class="lihat_semua" onclick="<?php 
        if(!$isLoggedIn) {
            // Jika Belum Login: Alert dulu, baru pindah ke login.php
            echo "alert('Silakan Login untuk melihat semua film!'); window.location.href='login.php';";
        } else {
            // Jika Sudah Login: Langsung pindah ke Jadwal_film.php
            echo "window.location.href='Jadwal_film.php';";
        }
    ?>">
        Lihat Semua &rarr;
    </button>
</div>

            <div class="movie-grid">
                <?php 
                if ($result_terbaru && mysqli_num_rows($result_terbaru) > 0) {
                    while($row = mysqli_fetch_assoc($result_terbaru)) { 
                        // --- LOGIKA PEMBERSIH PATH ---
                        $gambarRaw = $row['gambar']; 
                        $gambarRaw = str_replace('"', '', $gambarRaw); 
                        $namaFile = basename($gambarRaw); 
                        
                        // Gabungkan dengan folder Assets/img/
                        $path_gambar = "../Assets/img/" . $namaFile;

                        $judul = $row['judul_film'];
                        $rating = isset($row['rating']) ? $row['rating'] : 'N/A';
                        $durasi = isset($row['durasi']) ? $row['durasi'] : '-'; 
                ?>
                    <div class="movie-card">
                        <div class="card-image">
                            <div class="rating-badge">
                                <i class="fa-solid fa-star"></i> <?= $rating ?>
                            </div>
                            
                            <a href="<?= $link_jadwal ?>" onclick="<?= !$isLoggedIn ? "alert('Silakan Login!');" : "" ?>">
                                <img src="<?= $path_gambar ?>" 
                                     alt="<?= $judul ?>"
                                     onerror="this.src='https://via.placeholder.com/300x450?text=Gambar+Rusak';">
                            </a>
                        </div>
                        <div class="card-content">
                            <h3><?= $judul ?></h3>
                            <div class="card-tags">
                                <span class="tag">2D</span>
                                <span class="tag"><?= $durasi ?></span>
                            </div>
                        </div>
                    </div>
                <?php 
                    } 
                } else {
                    echo "<p style='color:white; text-align:center; width:100%;'>Belum ada data film.</p>";
                }
                ?>
            </div>
        </section>
        <section class="about-section">
        <div class="about-container">
            <div class="about-title-mobile">
                <h2>About TeknikTix</h2>
            </div>
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=2070&auto=format&fit=crop" alt="Cinema Hall">
            </div>
            <div class="about-content">
                <h2 class="about-title-desktop">About TekCinema</h2>
                <p>TekCinema adalah platform pemesanan tiket bioskop online terpercaya di Indonesia. Kami hadir untuk memberikan kemudahan bagi Anda dalam menikmati pengalaman menonton film favorit.</p>
                <p>Dengan teknologi terkini dan antarmuka yang user-friendly, kami memastikan proses pemesanan tiket menjadi cepat, mudah, dan aman. Dari pengecekan jadwal hingga pembayaran, semuanya dapat dilakukan dalam satu platform.</p>
                <p>Bergabunglah dengan ribuan pengguna yang telah mempercayai TekCinema untuk pengalaman menonton yang lebih praktis dan menyenangkan.</p>
            </div>
        </div>
    </section>

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
                <p><i class="fas fa-envelope"></i> info@tekniktix.com</p>
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
            &copy; 2025 TeknikTix. All Rights Reserved.
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
    </main>
</body>
</html>