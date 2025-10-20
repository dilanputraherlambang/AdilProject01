<?php
session_start();
include "../service/database.php";

if(!isset($_SESSION['is_login'])){
    header("Location: ../login.php");
    exit();
}

// 📅 Filter tanggal
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date("Y-m-d");

// 🔍 Ambil data presensi + siswa
$query = $db->query("
    SELECT p.*, s.nama 
    FROM presensi p
    INNER JOIN siswa s ON p.nisn = s.nisn
    WHERE p.tanggal = '$tanggal'
    ORDER BY p.jam_masuk ASC
");
if(!$query) die("Query error: ".$db->error);

$markers = [];
while($row = $query->fetch_assoc()){
    // Hitung keterlambatan pulang
    $terlambat_pulang = "-";
    if (!empty($row['jam_pulang'])) {
        $jam_pulang = strtotime($row['jam_pulang']);
        $batas_pulang = strtotime("15:10:00");
        $terlambat_pulang = ($jam_pulang > $batas_pulang) ? "Terlambat" : "Tepat Waktu";
    }

    $markers[] = [
        'nisn' => $row['nisn'],
        'nama' => $row['nama'],
        'lat' => $row['lat'] ?? -7.600997778892902,
        'lng' => $row['lng'] ?? 111.88932534325842,
        'status' => $row['status_izin'] ?? 'Hadir',
        'jam_masuk' => $row['jam_masuk'] ?? null,
        'jam_pulang' => $row['jam_pulang'] ?? null,
        'terlambat_pulang' => $terlambat_pulang,
        'bukti_izin' => $row['bukti_izin'] ?? null
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin - Presensi Siswa</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
body {
    font-family: "Poppins", Arial, sans-serif;
    background: #e3f2fd;
    margin: 0;
    padding: 20px;
}
.container {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(33,150,243,0.2);
    max-width: 1300px;
    margin: auto;
}
h3 {
    color: #0d47a1;
    text-align: center;
    font-size: 22px;
}
.controls {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    gap: 10px;
}
.controls input, .controls button {
    border-radius: 8px;
    border: 1px solid #90caf9;
    padding: 8px 12px;
    outline: none;
}
.controls button {
    background: #1976d2;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
}
.controls button:hover { background: #0d47a1; }

.btn-danger {
    background: #e53935;
    color: white;
    padding: 8px 14px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
.btn-danger:hover { background: #b71c1c; }

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 14px;
}
th, td {
    border: 1px solid #bbdefb;
    padding: 10px;
    text-align: center;
}
th {
    background: #1976d2;
    color: white;
    font-weight: 600;
}
tr:nth-child(even){background:#e3f2fd;}
tr:hover{background:#bbdefb;}

#map {
    width: 100%;
    height: 460px;
    margin-top: 25px;
    border-radius: 12px;
    border: 3px solid #64b5f6;
    box-shadow: 0 0 10px rgba(33,150,243,0.2);
}

.name-link {
    cursor: pointer;
    color: #1565c0;
    text-decoration: underline;
}
.name-link:hover { color: #0d47a1; text-decoration: none; }

@media (max-width: 768px) {
    .controls { flex-direction: column; align-items: flex-start; }
    table { font-size: 13px; }
}
</style>
</head>

<body>
<div class="container">
<h3>📋 Dashboard Presensi Siswa</h3>

<div class="controls">
    <form method="GET" style="display:flex; align-items:center; gap:10px;">
        <label for="tanggal">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
        <button type="submit">Tampilkan</button>
    </form>

    <input type="text" id="searchInput" placeholder="Cari nama atau NISN...">
    <button onclick="exportTableToExcel('dataTable', 'Presensi_<?= $tanggal ?>')">📊 Export Excel</button>
</div>

<table id="dataTable">
<tr>
<th>No</th>
<th>NISN</th>
<th>Nama</th>
<th>Status</th>
<th>Jam Masuk</th>
<th>Jam Pulang</th>
<th>Keterangan Pulang</th>
<th>Bukti Izin</th>
</tr>

<?php 
$no = 1;
if (count($markers) > 0):
foreach($markers as $row): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['nisn']) ?></td>
<td><span class="name-link" data-nisn="<?= $row['nisn'] ?>"><?= htmlspecialchars($row['nama']) ?></span></td>
<td><?= htmlspecialchars($row['status']) ?></td>
<td><?= $row['jam_masuk'] ?? '-' ?></td>
<td><?= $row['jam_pulang'] ?? '-' ?></td>
<td>
<?php 
if($row['jam_pulang']){
    echo ($row['terlambat_pulang'] == "Terlambat") 
        ? "<span style='color:red;'>Terlambat</span>" 
        : "<span style='color:green;'>Tepat Waktu</span>";
} else {
    echo "-";
}
?>
</td>
<td>
<?php 
if($row['status']!='Hadir' && $row['bukti_izin']){
    echo "<a href='../uploads/".$row['bukti_izin']."' target='_blank'>Lihat</a>";
} else {
    echo "-";
}
?>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="8">Tidak ada data presensi untuk tanggal ini.</td></tr>
<?php endif; ?>
</table>

<div id="map"></div>

<div style="text-align:center; margin-top:15px;">
    <a href="logout.php" class="btn-danger">Logout</a>
</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// --- PETA ---
const map = L.map('map').setView([-7.600997778892902, 111.88932534325842], 17);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.circle([-7.600997778892902, 111.88932534325842], {radius: 90, color: 'blue', fillOpacity: 0.1}).addTo(map);

const siswaMarkers = <?= json_encode($markers) ?>;
const markerObjects = {};

siswaMarkers.forEach(s => {
    let iconColor;
    switch(s.status){
        case 'Hadir': iconColor = 'green'; break;
        case 'Sakit': iconColor = 'yellow'; break;
        case 'Izin': iconColor = 'orange'; break;
        default: iconColor = 'blue';
    }

    const customIcon = L.icon({
        iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${iconColor}.png`,
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const marker = L.marker([s.lat, s.lng], {icon: customIcon}).addTo(map)
        .bindPopup(`<b>${s.nama}</b><br>NISN: ${s.nisn}<br>Status: ${s.status}<br>Jam Masuk: ${s.jam_masuk ?? '-'}<br>Jam Pulang: ${s.jam_pulang ?? '-'}<br>Keterangan: ${s.terlambat_pulang}<br>${s.bukti_izin ? `<a href='../uploads/${s.bukti_izin}' target='_blank'>Lihat Bukti</a>` : ''}`);
    markerObjects[s.nisn] = marker;
});

// Klik nama di tabel buka popup di peta
document.querySelectorAll('.name-link').forEach(el => {
    el.addEventListener('click', () => {
        const nisn = el.dataset.nisn;
        if(markerObjects[nisn]){
            markerObjects[nisn].openPopup();
            map.setView(markerObjects[nisn].getLatLng(), 18);
        }
    });
});

// 🔍 Pencarian
document.getElementById("searchInput").addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("#dataTable tr");
    rows.forEach((row, index) => {
        if (index === 0) return;
        const nama = row.cells[2].textContent.toLowerCase();
        const nisn = row.cells[1].textContent.toLowerCase();
        row.style.display = (nama.includes(filter) || nisn.includes(filter)) ? "" : "none";
    });
});

// 📤 Export Excel
function exportTableToExcel(tableID, filename = ''){
    let downloadLink;
    const dataType = 'application/vnd.ms-excel';
    const tableSelect = document.getElementById(tableID);
    const tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    filename = filename?filename+'.xls':'presensi_data.xls';
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);
    if(navigator.msSaveOrOpenBlob){
        const blob = new Blob(['\ufeff', tableHTML], { type: dataType });
        navigator.msSaveOrOpenBlob(blob, filename);
    }else{
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}
</script>
</body>
</html>
