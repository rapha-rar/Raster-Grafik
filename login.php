<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_pesanan.php");
    } else {
        // PERUBAHAN: Pelanggan yang sudah login diarahkan ke index.php
        header("Location: index.php"); 
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);
        
        // Verifikasi password hash (atau plain text jika data dummy lama)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin_pesanan.php");
            } else {
                // PERUBAHAN: Pelanggan yang baru berhasil login diarahkan ke index.php
                header("Location: index.php"); 
            }
            exit();
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; }
        .bg-merah { background-color: var(--merah-rg); color: white; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-sm p-4 mt-4">
                    <h3 class="fw-bold text-center mb-1"><span style="color:var(--biru-rg);">RASTER</span><span style="color:var(--merah-rg);">GRAFIK</span></h3>
                    <p class="text-center text-muted small mb-4">Masuk ke akun Anda</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?= $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn bg-merah w-100 fw-bold py-2 mb-3">Login</button>
                    </form>

                    <div class="text-center text-muted small">
                        Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold">Daftar sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>