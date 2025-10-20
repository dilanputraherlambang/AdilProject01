<?php
    include "service/database.php";
    session_start();

    $register_message = "";

    if(isset($_SESSION["is_login"])) {
        header("location: dashboard.php");

}
    
    if(isset($_POST['register'])) {
        $username = $_POST['username']; 
        $password = $_POST['password'];

        // 1. Hash password untuk keamanan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 2. Gunakan prepared statement untuk mencegah SQL Injection
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $db->prepare($sql);

        if ($stmt) {
            // "ss" berarti kedua variabel adalah string
            $stmt->bind_param("ss", $username, $hashed_password);

            if($stmt->execute()) {
                $register_message = "DATA BERHASIL MASUK, SILAHKAN LOGIN";
            } else {
                // Cek jika username sudah ada (error code 1062 untuk duplicate entry)
                if ($db->errno === 1062) {
                    $register_message = "USERNAME SUDAH DIGUNAKAN, SILAHKAN COBA LAGI";
                } else {
                    // Menambahkan detail error untuk debugging
                    $register_message = "DAFTAR AKUN GAGAL: " . $db->error;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <?php include "layout/header.html"?>
    <main class="container">
        <div class="form-container">
            <h3>Daftar Akun</h3>
            
            <?php if(!empty($register_message)): ?>
                <p class="message <?= ($db->errno === 1062) ? 'message-error' : 'message-success' ?>">
                    <?= $register_message ?>
                </p>
            <?php endif; ?>
    
            <form action="register.php" method="POST">
                <div class="form-group">
                    <input type="text" placeholder="Username" name="username" required>
                </div>
                <div class="form-group">
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <button type="submit" name="register" class="btn" style="width: 100%;">Daftar Sekarang</button>
            </form>
        </div>
    </main>

    <?php include "layout/footer.html"?>
</body>
</html>