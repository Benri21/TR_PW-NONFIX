<?php
session_start();
require_once __DIR__ . "/../../Backend/koneksi.php";

// 1. KEAMANAN & ID FILM
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='../login.php';</script>";
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../Jadwal_film.php");
    exit();
}

$id_film = $_GET['id'];
$id_user = $_SESSION['id_user']; 

// 2. AMBIL DATA FILM
$query_film = "SELECT * FROM film WHERE id_film = '$id_film'";
$result_film = mysqli_query($conn, $query_film);
$film = mysqli_fetch_assoc($result_film);
$harga_per_tiket = isset($film['harga']) ? $film['harga'] : 35000;

// 3. AMBIL DATA JADWAL
$query_jadwal = "SELECT * FROM jadwal WHERE id_film = '$id_film' AND tanggal >= CURDATE() ORDER BY tanggal ASC, jam_mulai ASC";
$result_jadwal = mysqli_query($conn, $query_jadwal);

$schedule_data = [];
if (mysqli_num_rows($result_jadwal) > 0) {
    while($row = mysqli_fetch_assoc($result_jadwal)) {
        $tgl = $row['tanggal'];
        $jam = date('H:i', strtotime($row['jam_mulai']));
        $schedule_data[$tgl][] = $jam;
    }
}

// 4. API UNTUK CEK KURSI (Dipanggil via AJAX)
if (isset($_GET['action']) && $_GET['action'] == 'check_seats') {
    $date = $_GET['date'];
    $time = $_GET['time'];
    
    // Ambil kursi yang statusnya BUKAN 'batal'
    $q_seats = "SELECT kursi FROM transaksi 
                WHERE id_film = '$id_film' 
                AND tanggal_tayang = '$date' 
                AND jam_tayang = '$time' 
                AND status != 'batal'";
    
    $res_seats = mysqli_query($conn, $q_seats);
    $booked_seats = [];
    
    while($row = mysqli_fetch_assoc($res_seats)) {
        // Pecah string "A1,A2" menjadi array
        $seats = explode(',', $row['kursi']);
        foreach($seats as $s) {
            $booked_seats[] = trim($s);
        }
    }
    
    echo json_encode($booked_seats);
    exit(); // Stop eksekusi agar tidak render HTML
}

// 5. PROSES TRANSAKSI
if (isset($_POST['submit_order'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam     = mysqli_real_escape_string($conn, $_POST['jam']);
    $kursi   = mysqli_real_escape_string($conn, $_POST['selected_seats']); 
    $total   = mysqli_real_escape_string($conn, $_POST['total_price']);
    
    $seat_array = explode(",", $kursi);
    $jumlah_tiket = count($seat_array);

    // Validasi Ganda (Server Side Check)
    $cek_booked = "SELECT * FROM transaksi 
                   WHERE id_film = '$id_film' AND tanggal_tayang = '$tanggal' 
                   AND jam_tayang = '$jam' AND status != 'batal'";
    $res_cek = mysqli_query($conn, $cek_booked);
    $is_taken = false;
    
    while($r = mysqli_fetch_assoc($res_cek)) {
        $taken_seats = explode(',', $r['kursi']);
        foreach($seat_array as $my_seat) {
            if(in_array(trim($my_seat), $taken_seats)) {
                $is_taken = true; break;
            }
        }
    }

    if ($is_taken) {
        echo "<script>alert('Maaf, salah satu kursi yang Anda pilih baru saja dipesan orang lain!');</script>";
    } elseif (empty($kursi) || empty($tanggal) || empty($jam)) {
        echo "<script>alert('Mohon lengkapi jadwal dan kursi!');</script>";
    } else {
        $insert = "INSERT INTO transaksi (id_user, id_film, tanggal_tayang, jam_tayang, jumlah_tiket, kursi, total_harga, status, tanggal_pesan) 
                   VALUES ('$id_user', '$id_film', '$tanggal', '$jam', '$jumlah_tiket', '$kursi', '$total', 'pending', NOW())";
        
        if (mysqli_query($conn, $insert)) {
            echo "<script>alert('Pesanan Berhasil! Silakan bayar.'); window.location.href='pesanan.php';</script>";
        } else {
            echo "<script>alert('Gagal memesan: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket - <?= $film['judul_film'] ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../Frontend/Style/style.css?v=<?= time(); ?>">

    <style>
        body { background-color: #0f172a; color: white; padding-top: 100px; }
        .order-container { max-width: 1000px; margin: 0 auto; display: flex; gap: 30px; flex-wrap: wrap; padding: 20px; }
        .info-panel { flex: 1; min-width: 300px; background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); }
        .seat-panel { flex: 2; min-width: 300px; background: #1e293b; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
        .screen { background: #cbd5e1; height: 30px; width: 80%; margin: 0 auto 30px; border-radius: 50% 50% 0 0 / 100% 100% 0 0; box-shadow: 0 10px 20px rgba(255, 255, 255, 0.2); color: #333; font-size: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .seat-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 10px; max-width: 400px; margin: 0 auto; }
        .seat { height: 35px; background: #334155; border-radius: 5px; cursor: pointer; transition: 0.2s; font-size: 10px; display: flex; align-items: center; justify-content: center; color: #94a3b8; }
        .seat:hover { background: #64748b; color: white; }
        .seat.selected { background: #ffc107; color: black; font-weight: bold; box-shadow: 0 0 10px #ffc107; }
        /* Style Kursi Terisi */
        .seat.occupied { background: #ef4444 !important; cursor: not-allowed; color: white; opacity: 0.6; pointer-events: none; }
        
        .form-select, .form-control { background: #0f172a; color: white; border: 1px solid #475569; }
        .form-select:focus { border-color: #ffc107; box-shadow: none; }
        .total-box { background: #0f172a; padding: 15px; border-radius: 8px; margin-top: 20px; border: 1px solid #ffc107; }
        .btn-confirm { width: 100%; background: #ffc107; color: black; font-weight: bold; padding: 12px; margin-top: 15px; border: none; }
        .btn-confirm:hover { background: #e0a800; }
    </style>
</head>
<body>

    <header style="background: #0f4c9c; padding: 15px 5%; display: flex; align-items: center; justify-content: space-between; position: fixed; top: 0; width: 100%; z-index: 999;">
        <h2 style="margin:0; font-weight:800;">TekCinema</h2>
        <a href="../Jadwal_film.php" class="btn btn-sm btn-outline-light">Kembali</a>
    </header>

    <div class="order-container">
        <div class="info-panel">
            <h3 class="text-warning mb-3"><?= $film['judul_film'] ?></h3>
            <p class="text-white-50 small mb-4">Rating: <?= $film['rating'] ?> | Durasi: <?= $film['durasi'] ?></p>

            <form method="POST" id="bookingForm">
                <div class="mb-3">
                    <label class="form-label text-warning">Pilih Tanggal</label>
                    <select name="tanggal" id="selectTanggal" class="form-select" required onchange="updateJam()">
                        <option value="">-- Pilih Tanggal --</option>
                        <?php 
                        if (!empty($schedule_data)) {
                            foreach ($schedule_data as $tgl => $jamList) {
                                $displayDate = date('d M Y', strtotime($tgl));
                                echo "<option value='$tgl'>$displayDate</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-warning">Pilih Jam</label>
                    <select name="jam" id="selectJam" class="form-select" required disabled onchange="loadBookedSeats()">
                        <option value="">-- Pilih Tanggal Dulu --</option>
                    </select>
                </div>

                <input type="hidden" name="selected_seats" id="inputSeats">
                <input type="hidden" name="total_price" id="inputTotal">

                <div class="total-box">
                    <div class="d-flex justify-content-between"><span>Kursi:</span><span id="displaySeats" class="text-warning fw-bold">-</span></div>
                    <div class="d-flex justify-content-between mt-2"><span>Harga:</span><span>Rp <?= number_format($harga_per_tiket, 0, ',', '.') ?></span></div>
                    <hr style="border-color: #555;">
                    <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total:</span><span class="text-warning" id="displayTotal">Rp 0</span></div>
                </div>

                <button type="submit" name="submit_order" class="btn btn-confirm">Pesan Tiket</button>
            </form>
        </div>

        <div class="seat-panel">
            <h4 class="mb-4">Pilih Kursi</h4>
            <div class="screen">LAYAR BIOSKOP</div>
            <div class="seat-grid">
                <?php
                $rows = ['A', 'B', 'C', 'D', 'E'];
                $cols = 8;
                foreach ($rows as $row) {
                    for ($c = 1; $c <= $cols; $c++) {
                        $seatNum = $row . $c;
                        echo "<div class='seat' id='seat-$seatNum' data-seat='$seatNum'>$seatNum</div>";
                    }
                }
                ?>
            </div>
            <div class="mt-4 d-flex justify-content-center gap-4 small text-white-50">
                <div class="d-flex align-items-center gap-2"><div style="width:15px; height:15px; background:#334155;"></div> Kosong</div>
                <div class="d-flex align-items-center gap-2"><div style="width:15px; height:15px; background:#ffc107;"></div> Dipilih</div>
                <div class="d-flex align-items-center gap-2"><div style="width:15px; height:15px; background:#ef4444;"></div> Terisi</div>
            </div>
        </div>
    </div>

    <script>
        const schedules = <?= json_encode($schedule_data) ?>;
        const selectTanggal = document.getElementById('selectTanggal');
        const selectJam = document.getElementById('selectJam');
        const idFilm = "<?= $id_film ?>";

        function updateJam() {
            const tgl = selectTanggal.value;
            selectJam.innerHTML = '<option value="">-- Pilih Jam --</option>';
            // Reset kursi saat ganti tanggal
            resetSeats();
            
            if (tgl && schedules[tgl]) {
                selectJam.disabled = false;
                schedules[tgl].forEach(jam => {
                    const option = document.createElement('option');
                    option.value = jam;
                    option.text = jam;
                    selectJam.appendChild(option);
                });
            } else {
                selectJam.disabled = true;
            }
        }

        // --- FUNGSI UTAMA: CEK KURSI TERISI ---
        function loadBookedSeats() {
            const tgl = selectTanggal.value;
            const jam = selectJam.value;
            
            if(!tgl || !jam) return;

            // Reset dulu sebelum load baru
            resetSeats();

            // Panggil PHP via Fetch API
            fetch(`order.php?action=check_seats&id=${idFilm}&date=${tgl}&time=${jam}`)
                .then(response => response.json())
                .then(bookedSeats => {
                    bookedSeats.forEach(seatNum => {
                        const seatElem = document.getElementById(`seat-${seatNum}`);
                        if(seatElem) {
                            seatElem.classList.add('occupied');
                            seatElem.title = "Sudah Dipesan";
                        }
                    });
                })
                .catch(err => console.error("Gagal memuat kursi:", err));
        }

        function resetSeats() {
            document.querySelectorAll('.seat').forEach(s => {
                s.classList.remove('occupied', 'selected');
            });
            updateTotal(); // Reset harga
        }

        // Logic Pilih Kursi
        const seatContainer = document.querySelector('.seat-grid');
        const displaySeats = document.getElementById('displaySeats');
        const displayTotal = document.getElementById('displayTotal');
        const inputSeats = document.getElementById('inputSeats');
        const inputTotal = document.getElementById('inputTotal');
        const ticketPrice = <?= $harga_per_tiket ?>;

        seatContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('seat') && !e.target.classList.contains('occupied')) {
                e.target.classList.toggle('selected');
                updateTotal();
            }
        });

        function updateTotal() {
            const selectedSeats = document.querySelectorAll('.seat.selected');
            const seatsIndex = [...selectedSeats].map(seat => seat.getAttribute('data-seat'));
            displaySeats.innerText = seatsIndex.length > 0 ? seatsIndex.join(', ') : '-';
            const totalPrice = seatsIndex.length * ticketPrice;
            displayTotal.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            inputSeats.value = seatsIndex.join(',');
            inputTotal.value = totalPrice;
        }
    </script>

</body>
</html>