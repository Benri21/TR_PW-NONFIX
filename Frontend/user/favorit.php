<?php
session_start();
// Pastikan path koneksi ini benar sesuai struktur folder Anda
// Asumsi favorit.php ada di folder Frontend/user/ atau sejenisnya
require_once __DIR__ . "/../../Backend/koneksi.php"; 

// --- BAGIAN API (BACKEND) ---
if (isset($_GET['action'])) {
    header("Content-Type: application/json; charset=UTF-8");
    $input = json_decode(file_get_contents("php://input"), true);
    
    if ($_GET['action'] == 'read') {
        $sql = "SELECT * FROM favorites ORDER BY id DESC";
        $result = $conn->query($sql);
        $movies = [];
        while($row = $result->fetch_assoc()) { $movies[] = $row; }
        echo json_encode($movies);
        exit();
    }

    if ($_GET['action'] == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $imdbID = $input['imdbID'];
        $check = $conn->query("SELECT id FROM favorites WHERE imdbID = '$imdbID'");
        if($check->num_rows > 0) {
            echo json_encode(["message" => "Film sudah ada di favorit!"]);
        } else {
            $stmt = $conn->prepare("INSERT INTO favorites (title, poster_url, rating, imdbID) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $input['title'], $input['poster'], $input['rating'], $input['imdbID']);
            if($stmt->execute()) echo json_encode(["message" => "Berhasil disimpan!"]);
            else echo json_encode(["error" => "Gagal menyimpan."]);
        }
        exit();
    }

    if ($_GET['action'] == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $stmt = $conn->prepare("UPDATE favorites SET rating = ? WHERE id = ?");
        $stmt->bind_param("si", $input['rating'], $input['id']);
        if($stmt->execute()) echo json_encode(["message" => "Rating berhasil diupdate!"]);
        else echo json_encode(["error" => "Gagal update."]);
        exit();
    }

    if ($_GET['action'] == 'delete') {
        $id = $_GET['id'];
        $conn->query("DELETE FROM favorites WHERE id = $id");
        echo json_encode(["message" => "Film dihapus."]);
        exit();
    }
}

// --- BAGIAN FRONTEND ---
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeknikTix - Favorite Collection</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../Style/style.css?v=<?= time(); ?>"> 

    <style>
        /* CSS KHUSUS HALAMAN INI */
        body {
            padding-top: 80px; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #0f4c9c;
        }

        .container {
            flex: 1; 
        }

        /* Styling Kartu Film Favorit */
        .movie-card-fav {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
            background: #16213e;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            height: 100%;
        }
        .movie-card-fav:hover {
            transform: scale(1.03);
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.5);
        }
        .movie-poster-fav {
            width: 100%;
            height: 320px;
            object-fit: cover;
        }
        .action-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.8);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .movie-card-fav:hover .action-overlay {
            display: flex;
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
                    <li><a href="favorit.php"class="active">Rating Film</a></li>
                    <li><a href="pesanan.php">Pesanan Saya</a></li>
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

    <div class="container pb-5">
        <div class="row align-items-center mb-5 p-4 rounded mt-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
            <div class="col-md-6">
                <h2 class="fw-bold mb-1 text-warning">Koleksi Favorit Saya</h2>
                <p class="text-white-50 m-0">Simpan film favoritmu dari OMDb Database disini.</p>
            </div>
            <div class="col-md-6 d-flex gap-2">
                <input type="text" id="searchInput" class="form-control bg-dark text-white border-secondary" placeholder="Cari judul film (Contoh: Avengers)...">
                <button class="btn btn-warning fw-bold" onclick="searchOMDb()">Cari</button>
                <button class="btn btn-outline-light" onclick="loadFavorites()">Koleksiku</button>
            </div>
        </div>

        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4" id="movieContainer">
            <div class="text-center w-100 mt-5">
                <div class="spinner-border text-warning" role="status"></div>
                <p class="text-white mt-2">Memuat data...</p>
            </div>
        </div>
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
                    <li><a href="../landingpage.php">Home</a></li>
                    <li><a href="#">Now Showing</a></li>
                    <li><a href="#">Upcoming Movies</a></li>
                    <li><a href="#">Promo</a></li>
                </ul>
            </div>
            <div class="footer-section contact">
                <h3>Kontak Kami</h3>
                <p><i class="fas fa-map-marker-alt"></i> Jl. Kemiri Barat No.47, Salatiga</p>
                <p><i class="fas fa-phone"></i> +62 812-1234-4321</p>
                <p><i class="fas fa-envelope"></i> info@teknikcinema.com</p>
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

    <div class="modal fade" id="movieDetailModal" tabindex="-1" style="color: black;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background: #1f1f1f; color: white;">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold" id="modalDetailTitle">Detail Film</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="modalDetailPoster" src="" class="img-fluid rounded shadow" style="max-height: 400px;">
                        </div>
                        <div class="col-md-8">
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>Rilis:</strong> <span id="modalReleased">-</span></li>
                                <li class="mb-2"><strong>Durasi:</strong> <span id="modalRuntime">-</span></li>
                                <li class="mb-2"><strong>Genre:</strong> <span id="modalGenre">-</span></li>
                                <li class="mb-2"><strong>Rating IMDB:</strong> <span class="text-warning">★ <span id="modalImdbRating">0</span></span></li>
                            </ul>
                            <hr class="border-secondary">
                            <h6 class="text-warning fw-bold">Sinopsis:</h6>
                            <p id="modalPlot" class="small" style="line-height: 1.6;">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" style="color: black;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Edit Rating Pribadi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Judul Film</label>
                        <input type="text" id="editTitle" class="form-control bg-secondary text-white border-0" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating Kamu (1-10)</label>
                        <input type="number" id="editRating" class="form-control" min="1" max="10">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="saveEdit()">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_KEY = 'd9ec1408'; 
        const CURRENT_FILE = 'favorit.php'; 

        const movieContainer = document.getElementById('movieContainer');
        const detailModal = new bootstrap.Modal(document.getElementById('movieDetailModal'));
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));

        async function loadFavorites() {
            document.getElementById('searchInput').value = ""; 
            movieContainer.innerHTML = '<div class="text-center w-100 mt-5"><div class="spinner-border text-light"></div></div>';
            try {
                const res = await fetch(`${CURRENT_FILE}?action=read`);
                const movies = await res.json();
                movieContainer.innerHTML = '';
                if(movies.length === 0) {
                    movieContainer.innerHTML = '<div class="text-center w-100 mt-5"><i class="fa-solid fa-film fa-3x mb-3 text-secondary"></i><p class="text-white-50">Belum ada koleksi film.</p></div>';
                    return;
                }
                movies.forEach(m => renderCard(m, true));
            } catch (error) { movieContainer.innerHTML = '<p class="text-center text-danger w-100">Gagal memuat data.</p>'; }
        }

        async function searchOMDb() {
            const query = document.getElementById('searchInput').value;
            if(!query) return alert("Ketik judul film!");
            movieContainer.innerHTML = '<div class="text-center w-100 mt-5"><div class="spinner-border text-light"></div></div>';
            try {
                const res = await fetch(`https://www.omdbapi.com/?s=${query}&apikey=${API_KEY}`);
                const data = await res.json();
                movieContainer.innerHTML = '';
                if(data.Response === "True") {
                    data.Search.forEach(m => {
                        const movieObj = {
                            id: null,
                            title: m.Title,
                            poster_url: m.Poster !== "N/A" ? m.Poster : "https://via.placeholder.com/300x450?text=No+Image",
                            rating: '?',
                            imdbID: m.imdbID
                        };
                        renderCard(movieObj, false);
                    });
                } else { movieContainer.innerHTML = '<p class="text-center w-100 mt-5 text-white">Film tidak ditemukan.</p>'; }
            } catch (err) { alert("Gagal koneksi internet."); }
        }

        function renderCard(m, isFavorite) {
            const safeTitle = m.title.replace(/'/g, "");
            let buttons = '';
            if (isFavorite) {
                buttons = `
                    <button class="btn btn-warning btn-sm w-75 mb-2 fw-bold" onclick="openEdit(${m.id}, '${safeTitle}', '${m.rating}')"><i class="fa-solid fa-pen"></i> Edit</button>
                    <button class="btn btn-danger btn-sm w-75 fw-bold" onclick="deleteFav(${m.id})"><i class="fa-solid fa-trash"></i> Hapus</button>`;
            } else {
                buttons = `<button class="btn btn-success btn-sm w-75 fw-bold" onclick="addFav('${safeTitle}', '${m.poster_url}', '${m.imdbID}')"><i class="fa-solid fa-heart"></i> Simpan</button>`;
            }

            const html = `
                <div class="col">
                    <div class="movie-card-fav h-100">
                        <img src="${m.poster_url}" class="movie-poster-fav" onclick="showDetail('${safeTitle}')">
                        <div class="action-overlay">${buttons}</div>
                        <div class="p-3 text-center">
                             <h6 class="fw-bold text-white text-truncate" title="${m.title}">${m.title}</h6>
                             ${isFavorite ? `<small class="text-warning"><i class="fa-solid fa-star"></i> ${m.rating}</small>` : ''}
                        </div>
                    </div>
                </div>`;
            movieContainer.innerHTML += html;
        }

        async function addFav(title, poster, imdbID) {
            const res = await fetch(`${CURRENT_FILE}?action=create`, { method: 'POST', body: JSON.stringify({ title, poster, rating: "8.0", imdbID }) });
            alert((await res.json()).message);
        }

        function openEdit(id, title, rating) {
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editRating').value = rating;
            editModal.show();
        }

        async function saveEdit() {
            const id = document.getElementById('editId').value;
            const rating = document.getElementById('editRating').value;
            const res = await fetch(`${CURRENT_FILE}?action=update`, { method: 'POST', body: JSON.stringify({ id, rating }) });
            alert((await res.json()).message);
            editModal.hide();
            loadFavorites();
        }

        async function deleteFav(id) {
            if(confirm("Hapus dari favorit?")) { await fetch(`${CURRENT_FILE}?action=delete&id=${id}`); loadFavorites(); }
        }

        async function showDetail(title) {
            detailModal.show();
            document.getElementById('modalDetailPoster').src = "https://via.placeholder.com/300x450?text=Loading...";
            const res = await fetch(`https://www.omdbapi.com/?t=${encodeURIComponent(title)}&apikey=${API_KEY}`);
            const data = await res.json();
            if(data.Response === "True") {
                document.getElementById('modalDetailTitle').innerText = data.Title;
                document.getElementById('modalDetailPoster').src = data.Poster !== "N/A" ? data.Poster : "https://via.placeholder.com/300x450";
                document.getElementById('modalReleased').innerText = data.Released;
                document.getElementById('modalRuntime').innerText = data.Runtime;
                document.getElementById('modalGenre').innerText = data.Genre;
                document.getElementById('modalImdbRating').innerText = data.imdbRating;
                document.getElementById('modalPlot').innerText = data.Plot;
            }
        }
        loadFavorites();
    </script>
</body>
</html>