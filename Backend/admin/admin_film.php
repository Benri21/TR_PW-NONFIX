<?php
// admin_film.php - CRUD Film (fixed)
require_once(__DIR__ . "/../koneksi.php");
session_start();

function e($v){ return htmlspecialchars($v, ENT_QUOTES); }

// === Perbaikan PATH gambar (sesuai struktur dashboard) ===
$uploadDir = __DIR__ . "/../../img/";
$webUploadPrefix = "../../img/";
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

// ----------------------------------------------------
$action = $_GET['action'] ?? 'list';

// ===== HAPUS FILM =====
if ($action === "delete" && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $res = mysqli_query($conn, "SELECT gambar FROM film WHERE id_film = $id");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        if (!empty($row['gambar']) && strpos($row['gambar'], $webUploadPrefix) === 0) {
            $filePath = $uploadDir . basename($row['gambar']);
            if (file_exists($filePath)) @unlink($filePath);
        }
    }

    $delete = mysqli_query($conn, "DELETE FROM film WHERE id_film = $id");
    if (!$delete) die("Gagal menghapus film: " . mysqli_error($conn));

    header("Location: admin_film.php?msg=deleted");
    exit();
}

// ===== TAMBAH FILM =====
if ($action === "add_save" && $_SERVER["REQUEST_METHOD"] === "POST") {

    $judul = mysqli_real_escape_string($conn, trim($_POST['judul_film'] ?? ''));
    $genre = mysqli_real_escape_string($conn, trim($_POST['genre'] ?? ''));
    $durasi = mysqli_real_escape_string($conn, trim($_POST['durasi'] ?? ''));
    $rating = mysqli_real_escape_string($conn, trim($_POST['rating'] ?? ''));
    $saran_umur = mysqli_real_escape_string($conn, trim($_POST['saran_umur'] ?? ''));
    $tanggal_rilis = mysqli_real_escape_string($conn, trim($_POST['tanggal_rilis'] ?? '0000-00-00'));
    $harga = (float)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $sinopsis = mysqli_real_escape_string($conn, trim($_POST['sinopsis'] ?? ''));

    $gambarPath = '';
    if (!empty($_FILES['gambar']['name'])) {
        $fname = basename($_FILES['gambar']['name']);
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed)) {
            $newName = time() . '_' . preg_replace('/[^a-zA-Z0-9\-_\.]/','', $fname);
            $target = $uploadDir . $newName;
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
                $gambarPath = $webUploadPrefix . $newName;
            }
        }
    } else {
        $tmp = trim($_POST['gambar_manual'] ?? '');
        if ($tmp !== '') $gambarPath = mysqli_real_escape_string($conn, $tmp);
    }

    $sql = "INSERT INTO film (judul_film, genre, durasi, rating, gambar, saran_umur, tanggal_rilis, harga, sinopsis, stok)
            VALUES ('$judul','$genre','$durasi','$rating','$gambarPath','$saran_umur','$tanggal_rilis',$harga,'$sinopsis',$stok)";
    $insert = mysqli_query($conn, $sql);

    if (!$insert) die("Insert film gagal: " . mysqli_error($conn));

    header("Location: admin_film.php?msg=added");
    exit();
}

// ===== EDIT FILM =====
if ($action === "edit_save" && $_SERVER["REQUEST_METHOD"] === "POST") {

    $id = (int)($_POST['id_film'] ?? 0);
    $judul = mysqli_real_escape_string($conn, trim($_POST['judul_film'] ?? ''));
    $genre = mysqli_real_escape_string($conn, trim($_POST['genre'] ?? ''));
    $durasi = mysqli_real_escape_string($conn, trim($_POST['durasi'] ?? ''));
    $rating = mysqli_real_escape_string($conn, trim($_POST['rating'] ?? ''));
    $saran_umur = mysqli_real_escape_string($conn, trim($_POST['saran_umur'] ?? ''));
    $tanggal_rilis = mysqli_real_escape_string($conn, trim($_POST['tanggal_rilis'] ?? '0000-00-00'));
    $harga = (float)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $sinopsis = mysqli_real_escape_string($conn, trim($_POST['sinopsis'] ?? ''));

    $oldG = '';
    $resOld = mysqli_query($conn, "SELECT gambar FROM film WHERE id_film = $id");
    if ($resOld && mysqli_num_rows($resOld) > 0) {
        $oldG = mysqli_fetch_assoc($resOld)['gambar'] ?? '';
    }

    $gambarPath = $oldG;

    if (!empty($_FILES['gambar']['name'])) {
        $fname = basename($_FILES['gambar']['name']);
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed)) {
            $newName = time() . '_' . preg_replace('/[^a-zA-Z0-9\-_\.]/','', $fname);
            $target = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
                $gambarPath = $webUploadPrefix . $newName;

                if ($oldG && strpos($oldG, $webUploadPrefix) === 0) {
                    $oldFile = $uploadDir . basename($oldG);
                    if (file_exists($oldFile)) @unlink($oldFile);
                }
            }
        }
    } else {
        $tmp = trim($_POST['gambar_manual'] ?? '');
        if ($tmp !== '') $gambarPath = mysqli_real_escape_string($conn, $tmp);
    }

    $sql = "UPDATE film SET
            judul_film='$judul',
            genre='$genre',
            durasi='$durasi',
            rating='$rating',
            gambar='" . mysqli_real_escape_string($conn, $gambarPath) . "',
            saran_umur='$saran_umur',
            tanggal_rilis='$tanggal_rilis',
            harga=$harga,
            sinopsis='$sinopsis',
            stok=$stok
            WHERE id_film=$id";

    $update = mysqli_query($conn, $sql);

    if (!$update) die("Update film gagal: " . mysqli_error($conn));

    header("Location: admin_film.php?msg=updated");
    exit();
}

$data_film = mysqli_query($conn, "SELECT * FROM film ORDER BY id_film DESC");
if ($data_film === false) die("Query film error: " . mysqli_error($conn));
?>
<!DOCTYPE html>
<html>
<head>
<title>Kelola Film</title>
<style>
    body { background:#0f4c9c; font-family:Arial; color:white; }
    .container { width:90%; margin:auto; margin-top:30px; }
    .box { background:#1f293a; padding:20px; border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.4); }
    h2 { color:#ffcc00; }
    table { width:100%; border-collapse:collapse; margin-top:20px; }
    th { background:#173d79; padding:10px; }
    td { background:#2b394e; padding:10px; }
    a.btn { padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:bold; }
    .add { background:#ffcc00; color:black; }
    .edit { background:#007bff; color:white; }
    .del { background:#dc3545; color:white; }
    input, select, textarea { width:100%; padding:8px; margin:5px 0; border-radius:6px; border:none; }
    .btn-save { background:#ffcc00; padding:10px; border:none; font-weight:bold; }
    .navbar { background:#173d79; padding:12px 18px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center;}
    .navbar a{ color:#fff; margin-left:18px; text-decoration:none; font-weight:600;}
    .navbar .brand{ color:#ffcc00; font-weight:800;}
    .small-note{ color:#d1d5db; padding:10px; text-align:center;}
</style>
</head>
<body>

<div class="navbar">
    <div class="brand">TEKNIK-CINEMA - ADMIN</div>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="admin_film.php">Kelola Film</a>
        <a href="admin_jadwal.php">Kelola Jadwal</a>
        <a href="admin_users.php"><i class="fa-solid fa-users-gear"></i> Kelola User</a>

       
        <a href="../../Frontend/logout.php" style="color:#ffcc00;">Logout</a>
    </div>
</div>

<div class="container">


<div class="container">

<?php
// HALAMAN TAMBAH FILM
if ($action === "add") {
?>
<div class="box">
<h2>Tambah Film</h2>

<form action="admin_film.php?action=add_save" method="POST" enctype="multipart/form-data">

Judul Film:
<input type="text" name="judul_film" required>

Genre:
<input type="text" name="genre" required>

Durasi (contoh: 120 menit):
<input type="text" name="durasi" required>

Rating:
<input type="text" name="rating">

Saran Umur:
<input type="text" name="saran_umur">

Tanggal Rilis:
<input type="date" name="tanggal_rilis">

Harga Tiket:
<input type="number" name="harga" required>

Stok:
<input type="number" name="stok" required>

Path Gambar (opsional):
<input type="text" name="gambar_manual" placeholder="mis: ../img/file.jpg">

atau Upload Gambar:
<input type="file" name="gambar" accept="image/*">

Sinopsis:
<textarea name="sinopsis" rows="4"></textarea>

<button class="btn-save">Simpan</button>
</form>

</div>
<?php 
exit(); 
} 
?>

<?php
// HALAMAN EDIT FILM
if ($action === "edit" && isset($_GET['id'])) {

    $id = (int)$_GET['id'];
    $qf = mysqli_query($conn, "SELECT * FROM film WHERE id_film=$id");
    if (!$qf || mysqli_num_rows($qf) === 0) {
        echo "<div class='box'><div class='small-note'>Data film tidak ditemukan.</div></div>";
        exit();
    }
    $f = mysqli_fetch_assoc($qf);
?>
<div class="box">
<h2>Edit Film</h2>

<form action="admin_film.php?action=edit_save" method="POST" enctype="multipart/form-data">
<input type="hidden" name="id_film" value="<?= e($f['id_film']) ?>">

Judul Film:
<input type="text" name="judul_film" value="<?= e($f['judul_film']) ?>">

Genre:
<input type="text" name="genre" value="<?= e($f['genre']) ?>">

Durasi:
<input type="text" name="durasi" value="<?= e($f['durasi']) ?>">

Rating:
<input type="text" name="rating" value="<?= e($f['rating']) ?>">

Saran Umur:
<input type="text" name="saran_umur" value="<?= e($f['saran_umur']) ?>">

Tanggal Rilis:
<input type="date" name="tanggal_rilis" value="<?= e($f['tanggal_rilis']) ?>">

Harga Tiket:
<input type="number" name="harga" value="<?= e($f['harga']) ?>">

Stok:
<input type="number" name="stok" value="<?= e($f['stok']) ?>">

Path Gambar (opsional):
<input type="text" name="gambar_manual" value="<?= e($f['gambar']) ?>">

atau Upload Gambar Baru:
<input type="file" name="gambar" accept="image/*">

Sinopsis:
<textarea name="sinopsis" rows="4"><?= e($f['sinopsis']) ?></textarea>

<button class="btn-save">Update</button>
</form>

</div>
<?php 
exit(); 
}
?>

<!-- LIST FILM -->
<div class="box">
<h2>Daftar Film</h2>

<a href="admin_film.php?action=add" class="btn add">+ Tambah Film</a>

<?php if (mysqli_num_rows($data_film) > 0): ?>
<table border="1">
<tr>
    <th>ID</th>
    <th>Judul</th>
    <th>Genre</th>
    <th>Durasi</th>
    <th>Harga</th>
    <th>Stok</th>
    <th>Aksi</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($data_film)) { ?>
<tr>
    <td><?= e($row['id_film']) ?></td>
    <td><?= e($row['judul_film']) ?></td>
    <td><?= e($row['genre']) ?></td>
    <td><?= e($row['durasi']) ?></td>
    <td>Rp <?= number_format((float)$row['harga'],0,',','.') ?></td>
    <td><?= (int)$row['stok'] ?></td>
    <td>
        <a href="admin_film.php?action=edit&id=<?= (int)$row['id_film'] ?>" class="btn edit">Edit</a>
        <a href="admin_film.php?action=delete&id=<?= (int)$row['id_film'] ?>"
           onclick="return confirm('Hapus film ini?')" class="btn del">Hapus</a>
    </td>
</tr>
<?php } ?>
</table>
<?php else: ?>
    <div class="small-note">Belum ada film.</div>
<?php endif; ?>
</div>

</div>

</body>
</html>
