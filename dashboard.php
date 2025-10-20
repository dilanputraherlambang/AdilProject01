<?php
session_start();
if (!isset($_SESSION["is_login"])) {
    header("location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Presensi</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>" />
</head>

<body>
    <?php include "layout/header.html" ?>
    <main class="container">
        <div class="form-container animate-on-scroll" style="text-align: center;">
            <h3>Presensi Berhasil ✅</h3>
            <a href="pulang.php" class="btn">Presensi Pulang</a>

            <div style="text-align: left; margin: 2rem 0; font-size: 1.1rem; line-height: 1.8;">
                <p><b style="color: var(--color-primary);">NISN:</b> <?= htmlspecialchars($_SESSION["nisn"]) ?></p>
                <p><b style="color: var(--color-primary);">Nama:</b> <?= htmlspecialchars($_SESSION["nama"]) ?></p>
                <p><b style="color: var(--color-primary);">Waktu Hadir:</b> <?= htmlspecialchars($_SESSION["waktu_presensi"]) ?></p>
            </div>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </main>
    <?php include "layout/footer.html" ?>
    <script src="script.js"></script>
</body>

</html>