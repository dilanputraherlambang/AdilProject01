<?php
session_start();

$login_message = "";

// Jika sudah login, arahkan ke dashboard
if (isset($_SESSION["is_login"])) {
    header("location: ../admin/dashboard.php");
    exit();
}

// Jika tombol login ditekan
if (isset($_POST['Login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek username dan password manual
    if ($username === "admin" && $password === "admin123") {
        $_SESSION["username"] = "admin";
        $_SESSION["is_login"] = true;
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        $login_message = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Manual</title>
    <link rel="stylesheet" href="../style.css?v=<?= time() ?>" />
</head>

<body>
    <?php include "../layout/header.html" ?>
    <main class="container">
        <div class="form-container animate-on-scroll">
            <h3>Masuk Akun</h3>

            <?php if (!empty($login_message)): ?>
                <p class="message message-error"><?= $login_message ?></p>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="form-group">
                    <input type="text" placeholder="Username" name="username" required>
                </div>
                <div class="form-group">
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <button type="submit" name="Login" class="btn" style="width: 100%;">Masuk</button>
            </form>
        </div>
    </main>

    <?php include "../layout/footer.html" ?>
    <script src="../script.js"></script>
</body>

</html>