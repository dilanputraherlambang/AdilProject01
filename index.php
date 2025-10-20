<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Selamat Datang di Sistem Presensi</title>
<link rel="stylesheet" href="style.css?v=<?= time() ?>" />
<style>
body { background-color: #ffffff; font-family: sans-serif; margin:0; padding:0; }
.container { max-width:600px; margin:auto; padding:20px; text-align:center; }
.form-container { 
    padding:25px; 
    border-radius:10px; 
    background:#ffffff; 
    box-shadow:0 4px 12px rgba(0,0,0,0.2); 
    opacity:0; 
    transform:translateY(20px); 
    animation:fadeIn 0.6s forwards; 
}
h1 { color:#007BFF; } /* tulisan biru */
.btn { display:inline-block; margin:10px; padding:15px 25px; font-size:1.1rem; text-decoration:none; color:white; border-radius:8px; transition:all 0.3s; }
.btn-masuk { background-color:#4CAF50; }
.btn-masuk:hover { background-color:#45a049; }
.btn-pulang { background-color:#f39c12; }
.btn-pulang:hover { background-color:#e67e22; }

@keyframes fadeIn { to { opacity:1; transform:translateY(0); } }

@media screen and (max-width:480px){ 
    .form-container{padding:15px;} 
    .btn{font-size:1rem; padding:12px 20px;} 
}
</style>
</head>
<body>
<?php include "layout/header.html" ?>
<main class="container">
    <div class="form-container">
        <h1>Selamat Datang di Sistem Presensi</h1>
        <h2 style="color: #555; font-size: 1.1rem;">Platform presensi digital untuk SMAN 1 Nganjuk. Klik tombol di bawah untuk presensi.</h2>
        <br>
        <a href="presensi.php?jenis=masuk" class="btn btn-masuk">Presensi Masuk</a>
        <a href="presensi.php?jenis=pulang" class="btn btn-pulang">Presensi Pulang</a>
    </div>
</main>
<?php include "layout/footer.html" ?>
<script src="script.js"></script>
<script>
// Animasi tombol muncul saat load halaman
document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".form-container");
    container.style.opacity = 1;
    container.style.transform = "translateY(0)";
});
</script>
</body>
</html>
