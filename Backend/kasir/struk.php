<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

// Cek akses kasir
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../../Frontend/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id_transaksi = $_GET['id'];

// Ambil detail transaksi dengan JOIN ke tabel users dan film
$query = "SELECT t.*, u.username, f.judul_film, f.durasi 
          FROM transaksi t 
          JOIN users u ON t.id_user = u.id_user 
          JOIN film f ON t.id_film = f.id_film 
          WHERE t.id_transaksi = '$id_transaksi'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data transaksi tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?= $data['id_transaksi'] ?> - TekCinema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Font struk */
            background-color: #f0f2f5; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        /* Container Struk */
        .ticket {
            width: 320px; 
            background: white;
            padding: 20px 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }

        /* Efek kertas sobek di atas dan bawah  */
        .ticket::before, .ticket::after {
            content: "";
            position: absolute;
            left: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(135deg, transparent 5px, white 5px), 
                        linear-gradient(225deg, transparent 5px, white 5px);
            background-size: 10px 10px;
            background-repeat: repeat-x;
        }
        .ticket::before { top: -10px; transform: rotate(180deg); }
        .ticket::after { bottom: -10px; }

        /* Header Struk */
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { 
            margin: 0; 
            color: #0f4c9c; 
            font-family: sans-serif;
            font-weight: 900;
            letter-spacing: 1px;
        }
        .header p { margin: 2px 0; font-size: 12px; }

        
        .divider { border-bottom: 2px dashed #bbb; margin: 15px 0; }
        
        /* Konten */
        .info-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 5px; 
            font-size: 13px; 
        }
        
        .film-title { 
            font-weight: bold; 
            font-size: 16px; 
            margin: 15px 0 5px 0; 
            text-align: center; 
            text-transform: uppercase;
        }
        
        /* Kursi Box */
        .seats-box {
            text-align: center;
            background: #eee;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .seats-box span { display: block; font-size: 12px; color: #666; }
        .seats-box strong { font-size: 18px; color: #000; letter-spacing: 2px; }

        /* Total */
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }

        .footer { text-align: center; font-size: 11px; margin-top: 25px; color: #555; }

        /* Tombol Aksi (Tidak tercetak) */
        .actions {
            position: absolute;
            top: 0;
            right: -60px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn {
            width: 45px; height: 45px;
            border-radius: 50%;
            border: none;
            color: white;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        .btn-print { background: #0f4c9c; }
        .btn-print:hover { background: #0a3675; transform: scale(1.1); }
        
        .btn-back { background: #6c757d; text-decoration: none; }
        .btn-back:hover { background: #5a6268; transform: scale(1.1); }

        /* PENGATURAN SAAT DI-PRINT */
        @media print {
            body { background: white; }
            .ticket { box-shadow: none; width: 100%; border: none; padding: 0; }
            .ticket::before, .ticket::after { display: none; } 
            .actions { display: none; } 
            .no-print { display: none; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>

<div class="ticket">
    <div class="actions">
        <button onclick="window.print()" class="btn btn-print" title="Cetak"><i class="fa-solid fa-print"></i></button>
        <a href="transaksi.php" class="btn btn-back" title="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
    </div>

    <div class="header">
        <h2>TEKNIK-CINEMA</h2>
        <p>Jl. Kemiri Barat No.47, Salatiga</p>
        <p>021-555-0199</p>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span>Order ID</span>
        <span>#<?= $data['id_transaksi'] ?></span>
    </div>
    <div class="info-row">
        <span>Tanggal</span>
        <span><?= date('d/m/Y H:i') ?></span>
    </div>
    <div class="info-row">
        <span>Kasir</span>
        <span><?= $_SESSION['username'] ?></span>
    </div>
    <div class="info-row">
        <span>Customer</span>
        <span><?= substr($data['username'], 0, 15) ?></span>
    </div>

    <div class="divider"></div>

    <div class="film-title"><?= $data['judul_film'] ?></div>
    
    <div class="info-row">
        <span>Tayang</span>
        <span><?= date('d M Y', strtotime($data['tanggal_tayang'])) ?></span>
    </div>
    <div class="info-row">
        <span>Jam</span>
        <span><?= $data['jam_tayang'] ?> WIB</span>
    </div>
    <div class="info-row">
        <span>Durasi</span>
        <span><?= $data['durasi'] ?> Menit</span>
    </div>

    <div class="seats-box">
        <span>NOMOR KURSI</span>
        <strong><?= $data['kursi'] ?></strong>
    </div>

    <div class="divider"></div>

    <div class="total-row">
        <span>TOTAL</span>
        <span>Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></span>
    </div>
    
    <div class="info-row" style="margin-top: 5px;">
        <span>Pembayaran</span>
        <span>CASH</span>
    </div>

    <div class="footer">
        <p>-- TERIMA KASIH --</p>
        <p><i>Tiket yang sudah dibeli tidak dapat ditukar atau dikembalikan.</i></p>
        <br>
        <p>www.teknikcinema.com</p>
    </div>
</div>

<script>
    // window.onload = function() { window.print(); } //untuk pdf
</script>

</body>
</html>