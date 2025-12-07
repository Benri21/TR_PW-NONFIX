<?php
require '../koneksi.php';
$pemesanan = $koneksi->query("
SELECT pemesanan.*, film.judul 
FROM pemesanan 
JOIN film ON pemesanan.film_id = film.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pemesanan</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

<div class="sidebar">
    <h2>ADMIN</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_film.php">Kelola Film</a>
    <a href="admin_jadwal.php">Kelola Jadwal</a>
    <a href="admin_pemesanan.php" class="active">Daftar Pemesanan</a>
    <a href="../Frontend/logout.php" class="logout">Logout</a>
</div>

<div class="content">
    <h1>Daftar Pemesanan</h1>

    <table>
        <tr>
            <th>Film</th>
            <th>Nama</th>
            <th>Tiket</th>
            <th>Total</th>
            <th>Tanggal</th>
        </tr>

        <?php while ($row = $pemesanan->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['judul'] ?></td>
            <td><?= $row['nama'] ?></td>
            <td><?= $row['jumlah_tiket'] ?></td>
            <td><?= $row['total_harga'] ?></td>
            <td><?= $row['tanggal'] ?></td>
        </tr>
        <?php } ?>
    </table>

</div>

</body>
</html>
