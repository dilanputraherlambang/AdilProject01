<?php
session_start();
include "service/database.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

// 1️⃣ Cek NISN
if (isset($_POST['nisn'])) {
    $nisn = trim($_POST['nisn']);
    $cek = $db->query("SELECT * FROM siswa WHERE nisn='$nisn'");
    if ($cek->num_rows > 0) {
        $siswa = $cek->fetch_assoc();
        $_SESSION['nisn'] = $nisn;
        $_SESSION['nama'] = $siswa['nama'];
    } else {
        $message = "❌ NISN tidak terdaftar!";
    }
}

// 2️⃣ Proses presensi
if (isset($_SESSION['nisn'])) {
    $nisn = $_SESSION['nisn'];
    $nama = $_SESSION['nama'];
    $tanggal = date("Y-m-d");

    // pastikan kolom ada
    $cekKolom = $db->query("SHOW COLUMNS FROM presensi LIKE 'keterangan'");
    if ($cekKolom->num_rows == 0) {
        $db->query("ALTER TABLE presensi ADD COLUMN keterangan VARCHAR(255) NULL AFTER jam_pulang");
    }

    $cek = $db->query("SELECT * FROM presensi WHERE nisn='$nisn' AND tanggal='$tanggal'");
    $presensi = $cek->fetch_assoc() ?? [];

    // 3️⃣ Presensi Hadir
    if (isset($_POST['hadir']) && !$presensi) {
        $jamSekarang = date("H:i:s");
        $status = (strtotime($jamSekarang) > strtotime("07:10:00")) ? "Terlambat Masuk" : "Hadir Tepat Waktu";

        $db->query("INSERT INTO presensi (nisn, tanggal, jam_masuk, status_izin, bukti_izin, keterangan)
                    VALUES ('$nisn','$tanggal',NOW(),'Hadir','','$status')");
        $message = "✅ Presensi Hadir berhasil dicatat ($status)";
    }

    // 4️⃣ Presensi Izin
    if (isset($_POST['izin']) && !$presensi) {
        $status = $_POST['status_izin'];
        if (!isset($_FILES['bukti_surat']) || $_FILES['bukti_surat']['error'] != 0) {
            $message = "❌ Upload bukti surat wajib!";
        } else {
            $filename = time() . '_' . basename($_FILES['bukti_surat']['name']);
            move_uploaded_file($_FILES['bukti_surat']['tmp_name'], "uploads/$filename");
            $db->query("INSERT INTO presensi (nisn, tanggal, status_izin, bukti_izin, keterangan)
                        VALUES ('$nisn','$tanggal','$status','$filename','Izin $status')");
            $message = "✅ Presensi Izin berhasil dicatat!";
        }
    }

    // 5️⃣ Presensi Pulang
    if (isset($_POST['pulang']) && $presensi && $presensi['status_izin'] == 'Hadir' && !$presensi['jam_pulang']) {
        $jamSekarang = date("H:i:s");

        if (strtotime($jamSekarang) < strtotime("15:10:00")) {
            $message = "⚠️ Belum bisa absen pulang sebelum jam 15:10.";
        } else {
            $statusPulang = (strtotime($jamSekarang) < strtotime("15:20:00")) ? "Pulang Cepat" : "Pulang Tepat";
            $db->query("UPDATE presensi 
                        SET jam_pulang=NOW(), keterangan=CONCAT(IFNULL(keterangan,''), ' - $statusPulang') 
                        WHERE id=" . $presensi['id']);
            $message = "✅ Presensi Pulang berhasil dicatat ($statusPulang)";
        }
    }

    // refresh data
    $cek = $db->query("SELECT * FROM presensi WHERE nisn='$nisn' AND tanggal='$tanggal'");
    $presensi = $cek->fetch_assoc() ?? [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Presensi Siswa</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
body {
  font-family: "Poppins", sans-serif;
  background: linear-gradient(135deg, #eef2f3, #dfe6e9);
  color: #222;
  margin: 0;
  padding: 0;
}
.container {
  max-width: 600px;
  margin: 40px auto;
  padding: 20px;
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
h3 { text-align: center; color: #007bff; margin-bottom: 20px; }
.btn {
  padding: 12px;
  font-size: 1rem;
  border: none;
  border-radius: 8px;
  width: 100%;
  margin: 8px 0;
  cursor: pointer;
  transition: 0.3s;
}
.btn-enabled { background:#007bff; color:white; }
.btn-enabled:hover { background:#0056b3; }
.btn-disabled { background:#ccc; color:#555; cursor:not-allowed; }
input, select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-bottom: 10px;
}
.message { font-weight:bold; padding:10px; border-radius:8px; text-align:center; margin-bottom:10px; }
.message:empty { display:none; }
#map { width:100%; height:300px; border-radius:10px; margin-top:15px; }
.success { background:#d4edda; color:#155724; }
.warning { background:#fff3cd; color:#856404; }
.error { background:#f8d7da; color:#721c24; }
</style>
</head>
<body>

<main class="container">
    <h3>Presensi Siswa</h3>
    <?php if($message): ?>
        <p class="message 
        <?php 
        echo (strpos($message,'✅')!==false)?'success':
             ((strpos($message,'⚠️')!==false)?'warning':'error');
        ?>">
        <?= $message ?>
        </p>
    <?php endif; ?>

    <?php if(!isset($_SESSION['nisn'])): ?>
        <form method="POST">
            <input type="text" name="nisn" placeholder="Masukkan NISN" required>
            <button type="submit" class="btn btn-enabled">Lanjut</button>
        </form>

    <?php else: ?>
        <p>Halo, <b><?= htmlspecialchars($nama) ?></b></p>

        <?php if(!$presensi): ?>
            <form method="POST">
                <button type="submit" name="hadir" class="btn btn-enabled">Presensi Hadir</button>
            </form>
            <hr>
            <form method="POST" enctype="multipart/form-data">
                <label>Status Izin:</label>
                <select name="status_izin" required>
                    <option value="Sakit">Sakit</option>
                    <option value="Acara Keluarga">Acara Keluarga</option>
                </select><br>
                <label>Upload Bukti (foto/surat):</label>
                <input type="file" name="bukti_surat" required><br>
                <button type="submit" name="izin" class="btn btn-enabled">Kirim Izin</button>
            </form>

        <?php else: ?>
            <p>✅ Kamu sudah presensi hari ini</p>
            <p>Status: <b><?= $presensi['status_izin'] ?></b></p>
            <?php if($presensi['jam_masuk']) echo "<p>Jam Masuk: ".$presensi['jam_masuk']."</p>"; ?>
            <?php if($presensi['jam_pulang']) echo "<p>Jam Pulang: ".$presensi['jam_pulang']."</p>"; ?>
            <?php if($presensi['keterangan']) echo "<p>Keterangan: ".$presensi['keterangan']."</p>"; ?>
            <?php if($presensi['bukti_izin']) echo "<p>Bukti: <a href='uploads/".$presensi['bukti_izin']."' target='_blank'>Lihat</a></p>"; ?>
            <?php if($presensi['status_izin']=='Hadir' && !$presensi['jam_pulang']): ?>
                <form method="POST">
                    <button type="submit" name="pulang" class="btn btn-enabled">Presensi Pulang</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <div id="map"></div>
    <?php endif; ?>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const latSekolah = -7.600997778892902;
const lngSekolah = 111.88932534325842;
const radiusSekolah = 90;

const map = L.map('map').setView([latSekolah, lngSekolah], 17);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.circle([latSekolah, lngSekolah], {radius:radiusSekolah, color:'red', fillOpacity:0.1}).addTo(map);

navigator.geolocation.getCurrentPosition(pos=>{
    const latSiswa = pos.coords.latitude;
    const lngSiswa = pos.coords.longitude;

    const jarak = getDistance(latSiswa, lngSiswa, latSekolah, lngSekolah);
    L.marker([latSiswa, lngSiswa]).addTo(map).bindPopup("Lokasi Anda").openPopup();

    const tombol = document.querySelectorAll(".btn");
    if(jarak <= radiusSekolah){
        tombol.forEach(b=>{ b.classList.add("btn-enabled"); b.classList.remove("btn-disabled"); });
    } else {
        tombol.forEach(b=>{ b.classList.add("btn-disabled"); b.classList.remove("btn-enabled"); });
        alert(`⚠️ Kamu di luar radius sekolah (${Math.round(jarak)}m).`);
    }
});

function getDistance(lat1, lon1, lat2, lon2){
    const R = 6371000;
    const dLat = (lat2-lat1)*Math.PI/180;
    const dLon = (lon2-lon1)*Math.PI/180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}
</script>
</body>
</html>
