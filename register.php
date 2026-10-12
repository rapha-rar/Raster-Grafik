<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['id_user'])) {
    header("Location: dashboard_pelanggan.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $no_wa = mysqli_real_escape_string($koneksi, $_POST['no_whatsapp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap']);

    // Cek apakah email sudah terdaftar
    $check_email = mysqli_query($koneksi, "SELECT id_user FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $error = "Email sudah terdaftar! Gunakan email lain.";
    } else {
        // Hash password demi keamanan
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, email, password, role, no_whatsapp, alamat_lengkap) 
                  VALUES ('$nama', '$email', '$password_hash', 'pelanggan', '$no_wa', '$alamat')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location.href='login.php';</script>";
        } else {
            $error = "Gagal mendaftar: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; }
        .bg-merah { background-color: var(--merah-rg); color: white; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-center mb-1"><span style="color:var(--biru-rg);">RASTER</span><span style="color:var(--merah-rg);">GRAFIK</span></h3>
                    <p class="text-center text-muted small mb-4">Buat akun untuk mulai memesan</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?= $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WhatsApp</label>
                            <input type="text" name="no_whatsapp" class="form-control" placeholder="08123456789" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="alamat_lengkap" class="form-control" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn bg-merah w-100 fw-bold py-2 mb-3">Daftar Akun</button>
                    </form>
                    
                    <div class="text-center text-muted small">
                        Sudah punya akun? <a href="login.php" class="text-decoration-none fw-bold">Login di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>