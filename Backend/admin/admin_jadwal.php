<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    header("Location: ../../Frontend/login.php");
    exit();
}

require_once(__DIR__ . "/../koneksi.php");

// ACTION
$action = $_GET['action'] ?? 'list';

// ADD
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_film = (int)$_POST['id_film'];
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $studio = mysqli_real_escape_string($conn, $_POST['studio'] ?? 'Studio 1');

    $ins = mysqli_query($conn, "INSERT INTO jadwal (id_film, tanggal, jam_mulai, studio) 
                                VALUES ($id_film, '$tanggal', '$jam_mulai', '$studio')");
    header("Location: admin_jadwal.php");
    exit;
}

// EDIT SAVE
if ($action === 'edit' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_GET['id'];
    $id_film = (int)$_POST['id_film'];
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $studio = mysqli_real_escape_string($conn, $_POST['studio'] ?? 'Studio 1');

    $upd = mysqli_query($conn, "UPDATE jadwal SET 
                id_film=$id_film, tanggal='$tanggal', jam_mulai='$jam_mulai', studio='$studio'
                WHERE id_jadwal = $id");
    header("Location: admin_jadwal.php");
    exit;
}

// DELETE
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM jadwal WHERE id_jadwal = $id");
    header("Location: admin_jadwal.php");
    exit;
}

// GET DATA
$jadwals_res = mysqli_query($conn, 
    "SELECT j.*, f.judul_film 
     FROM jadwal j 
     JOIN film f ON j.id_film = f.id_film 
     ORDER BY j.tanggal DESC, j.jam_mulai DESC");
$jadwals = mysqli_fetch_all($jadwals_res, MYSQLI_ASSOC);

$films_res = mysqli_query($conn, "SELECT id_film, judul_film FROM film ORDER BY judul_film ASC");
$films = mysqli_fetch_all($films_res, MYSQLI_ASSOC);

$editJ = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $get = mysqli_query($conn, "SELECT * FROM jadwal WHERE id_jadwal = $id");
    $editJ = mysqli_fetch_assoc($get);
}

function e($v){ return htmlspecialchars($v, ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kelola Jadwal</title>
<style>
    body { background:#0f4c9c; font-family:Arial; color:white; margin:0; padding:0; }

    .navbar {
        background:#173d79; padding:12px 18px; margin-bottom:18px;
        display:flex; justify-content:space-between; align-items:center;
    }
    .navbar a{ color:#fff; margin-left:18px; text-decoration:none; font-weight:600; }
    .navbar .brand{ color:#ffcc00; font-weight:800; }

    .container { width:90%; margin:auto; margin-top:20px; }

    .box {
        background:#1f293a; padding:20px; border-radius:12px;
        box-shadow:0 6px 15px rgba(0,0,0,0.4); margin-bottom:20px;
    }

    h2, h3 { color:#ffcc00; }

    table { width:100%; border-collapse:collapse; margin-top:15px; }
    th { background:#173d79; padding:10px; }
    td { background:#2b394e; padding:10px; }

    a.btn {
        padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:bold;
    }
    .add { background:#ffcc00; color:black; }
    .edit { background:#007bff; color:white; }
    .del { background:#dc3545; color:white; }

    input, select {
        width:100%; padding:8px; margin-top:5px; border-radius:6px; border:none;
    }
    .btn-save { margin-top:10px; background:#ffcc00; padding:10px; border:none; font-weight:bold; }
</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="brand">TEKNIKTIX - ADMIN</div>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="admin_film.php">Kelola Film</a>
        <a href="admin_jadwal.php">Kelola Jadwal</a>
        <a href="../../Frontend/logout.php" style="color:#ffcc00;">Logout</a>
    </div>
</div>

<div class="container">

<?php if ($action === 'edit' && $editJ): ?>
<div class="box">
<h2>Edit Jadwal</h2>

<form method="POST" action="admin_jadwal.php?action=edit&id=<?= (int)$editJ['id_jadwal'] ?>">

Film:
<select name="id_film" required>
<?php foreach ($films as $f): ?>
<option value="<?= $f['id_film'] ?>" <?= ($f['id_film'] == $editJ['id_film'])?'selected':'' ?>>
    <?= e($f['judul_film']) ?>
</option>
<?php endforeach; ?>
</select>

Tanggal:
<input type="date" name="tanggal" value="<?= e($editJ['tanggal']) ?>" required>

Jam Mulai:
<input type="time" name="jam_mulai" value="<?= e($editJ['jam_mulai']) ?>" required>

Studio:
<input type="text" name="studio" value="<?= e($editJ['studio']) ?>">

<button class="btn-save">Update</button>
<a href="admin_jadwal.php" class="btn del">Batal</a>

</form>
</div>
<?php else: ?>

<div class="box">
<h2>Tambah Jadwal</h2>

<form method="POST" action="admin_jadwal.php?action=add">

Film:
<select name="id_film" required>
    <option value="">-- Pilih Film --</option>
    <?php foreach ($films as $f): ?>
        <option value="<?= $f['id_film'] ?>"><?= e($f['judul_film']) ?></option>
    <?php endforeach; ?>
</select>

Tanggal:
<input type="date" name="tanggal" required>

Jam Mulai:
<input type="time" name="jam_mulai" required>

Studio:
<input type="text" name="studio" placeholder="Studio 1">

<button class="btn-save">+ Tambah Jadwal</button>

</form>
</div>

<?php endif; ?>

<!-- LIST JADWAL -->
<div class="box">
<h2>Daftar Jadwal</h2>

<a href="admin_jadwal.php" class="btn add">Refresh</a>

<table border="1">
<tr>
    <th>Film</th>
    <th>Tanggal</th>
    <th>Jam Mulai</th>
    <th>Studio</th>
    <th>Aksi</th>
</tr>

<?php if (count($jadwals) === 0): ?>
<tr><td colspan="5" style="text-align:center;">Belum ada jadwal.</td></tr>
<?php else: ?>
<?php foreach ($jadwals as $j): ?>
<tr>
    <td><?= e($j['judul_film']) ?></td>
    <td><?= e($j['tanggal']) ?></td>
    <td><?= e($j['jam_mulai']) ?></td>
    <td><?= e($j['studio']) ?></td>
    <td>
        <a href="admin_jadwal.php?action=edit&id=<?= $j['id_jadwal'] ?>" class="btn edit">Edit</a>
        <a href="admin_jadwal.php?action=delete&id=<?= $j['id_jadwal'] ?>" class="btn del"
           onclick="return confirm('Hapus jadwal ini?');">Hapus</a>
    </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</table>
</div>

</div>

</body>
</html>
