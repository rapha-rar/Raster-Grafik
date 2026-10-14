<?php
session_start();
require_once 'config/database.php';

// Jika pengguna sudah login, langsung arahkan sesuai role
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_pesanan.php");
    } else {
        header("Location: dashboard_pelanggan.php");
    }
    exit();
}

$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Memeriksa password (mendukung password_hash maupun teks biasa)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            $redirect_target = ($user['role'] === 'admin') ? 'admin_pesanan.php' : 'dashboard_pelanggan.php';

            echo "
            <!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet'>
                <style>body { font-family: 'Poppins', sans-serif; }</style>
            </head>
            <body style='background-color:#f4f7f6;'>
                <script>
                    Swal.fire({
                        title: 'Login Berhasil!',
                        text: 'Selamat datang kembali, " . addslashes($user['nama']) . "',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '$redirect_target';
                    });
                </script>
            </body>
            </html>";
            exit();
        } else {
            $pesan_error = "Password yang Anda masukkan salah!";
        }
    } else {
        $pesan_error = "Email tidak ditemukan!";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --biru-rg: #0088cc;
            --merah-rg: #cc0000;
            --bg-light: #f4f7f6;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .text-biru { color: var(--biru-rg); }
        .text-merah { color: var(--merah-rg); }

        /* OVERLAY PAGE LOADER */
        #page-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center; z-index: 99999;
            opacity: 1; visibility: visible; transition: opacity 0.4s ease, visibility 0.4s ease;
        }
        #page-loader.fade-out { opacity: 0; visibility: hidden; pointer-events: none; }
        .spinner-container { text-align: center; animation: pulseLoader 1.5s infinite ease-in-out; }
        @keyframes pulseLoader { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } }

        /* CARD LOGIN STYLING */
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            background: #ffffff;
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            background-color: #ffffff;
            border-bottom: 4px solid var(--biru-rg);
            padding: 35px 30px 20px 30px;
            text-align: center;
        }
        .brand-logo {
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
            text-decoration: none;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: var(--biru-rg);
            box-shadow: 0 0 0 0.25rem rgba(0,136,204,0.2);
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #6c757d;
        }
        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
        .btn-primer {
            background-color: var(--biru-rg);
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-primer:hover {
            background-color: #006699;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,136,204,0.3);
        }
    </style>
</head>
<body>

    <!-- OVERLAY PAGE LOADER -->
    <div id="page-loader">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em; color: #0088cc !important;"></div>
            <div class="loader-logo mt-3 fw-bold fs-5">
                <span style="color: #0088cc;">RASTER</span><span style="color: #cc0000;">GRAFIK</span>
            </div>
            <small class="text-muted d-block mt-1">Memuat halaman...</small>
        </div>
    </div>

    <div class="container d-flex flex-column align-items-center justify-content-center">
        
        <!-- CARD LOGIN -->
        <div class="card login-card">
            
            <div class="login-header">
                <a href="index.php" class="brand-logo d-block mb-1">
                    <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span>
                </a>
                <p class="text-muted small mb-0">Silakan masuk ke akun Anda</p>
            </div>

            <div class="card-body p-4 p-md-5">

                <form action="" method="POST" id="formLogin">
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium small text-dark">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium small text-dark d-flex justify-content-between">
                            <span>Password</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Masukkan password Anda" required style="border-radius: 0;">
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword" style="border-radius: 0 10px 10px 0; border-color: #dee2e6;">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primer w-100 mb-3 shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>

                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="small text-muted mb-2">Belum punya akun?</p>
                    <a href="register.php" class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-medium">
                        <i class="bi bi-person-plus me-1"></i> Daftar Akun Baru
                    </a>
                </div>

            </div>

            <div class="card-footer bg-light border-top-0 py-3 text-center">
                <a href="index.php" class="small text-decoration-none text-muted fw-medium">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                </a>
            </div>

        </div>

        <p class="small text-muted mt-4 mb-0">&copy; 2026 Raster Grafik. All Rights Reserved.</p>
    </div>

    <!-- NOTIFIKASI ERROR DENGAN SWEETALERT2 -->
    <?php if (!empty($pesan_error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Gagal Login!',
                text: '<?= addslashes($pesan_error); ?>',
                icon: 'error',
                confirmButtonColor: '#cc0000',
                confirmButtonText: 'Coba Lagi'
            });
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sembunyikan Page Loader setelah halaman selesai dimuat
        window.addEventListener('load', function() {
            const loader = document.getElementById('page-loader');
            if (loader) loader.classList.add('fade-out');
        });

        // Fitur Toggle Password (Lihat/Sembunyikan)
        const togglePassword = document.getElementById('togglePassword');
        const inputPassword = document.getElementById('inputPassword');
        const eyeIcon = document.getElementById('eyeIcon');

        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = inputPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                inputPassword.setAttribute('type', type);
                eyeIcon.classList.toggle('bi-eye');
                eyeIcon.classList.toggle('bi-eye-slash');
            });
        }

        // Tampilkan loader saat klik tautan atau submit form
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not([download])');
            const loader = document.getElementById('page-loader');

            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (e.ctrlKey || e.metaKey) return;
                    if (loader) loader.classList.remove('fade-out');
                });
            });

            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    if (loader) loader.classList.remove('fade-out');
                });
            });
        });
    </script>
</body>
</html>