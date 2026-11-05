<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] == 'admin') header("Location: admin_pesanan.php"); 
    else header("Location: index.php");
    exit();
}
$error = '';
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $user = mysqli_fetch_assoc($cek);
        if (password_verify($password, $user['password']) || $password == $user['password']) {
            $_SESSION['id_user'] = $user['id_user']; 
            $_SESSION['role'] = $user['role']; 
            $_SESSION['nama'] = $user['nama'];
            if ($user['role'] == 'admin') header("Location: admin_pesanan.php"); 
            else header("Location: index.php");
            exit();
        } else { $error = "Password yang Anda masukkan salah!"; }
    } else { $error = "Email tidak terdaftar di sistem kami!"; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { 
            --color-1: #2373F4; --color-2: #578EF5; --color-3: #65D0F4;
            --grad-primary: linear-gradient(135deg, var(--color-1) 0%, var(--color-2) 100%); 
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #0f172a; 
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #ffffff 100%);
            background-attachment: fixed;
            height: 100vh; 
            display: flex; 
            align-items: center; 
            position: relative; 
            overflow: hidden; 
        }
        
        .bg-shape1 { position: fixed; top: -10%; left: -10%; width: max(350px, 45vw); height: max(350px, 45vw); border-radius: 50%; background: var(--color-1); filter: blur(120px); opacity: 0.15; z-index: -1; }
        .bg-shape2 { position: fixed; bottom: -10%; right: -10%; width: max(400px, 50vw); height: max(400px, 50vw); border-radius: 50%; background: var(--color-3); filter: blur(120px); opacity: 0.18; z-index: -1; }

        .logo-raster { color: var(--color-1); font-weight: 900; letter-spacing: -0.5px; }
        .logo-grafik { color: #0f172a; font-weight: 900; letter-spacing: -0.5px; }
        
        .card-glass { 
            background: #ffffff; 
            border: 1px solid rgba(87, 142, 245, 0.25); 
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.05); 
            border-radius: 24px; 
            padding: 2.5rem; 
            position: relative; 
            overflow: hidden;
        }
        
        .form-control { background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 12px; padding: 0.8rem 1rem; color: #0f172a; font-weight: 600; transition: 0.3s; }
        .form-control:focus { background: #ffffff; border-color: var(--color-2); box-shadow: 0 0 0 4px rgba(101, 208, 244, 0.3); }
        .btn-grad-primary { background: var(--grad-primary); color: white; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 15px rgba(35, 115, 244, 0.3); border: none; padding: 14px; transition: 0.3s; }
        .btn-grad-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(35, 115, 244, 0.5); color: white; }
    </style>
</head>
<body>
    <div class="bg-shape1"></div><div class="bg-shape2"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="text-center mb-4">
                    <a href="index.php" class="text-decoration-none fs-1 fw-bold">
                        <span class="logo-raster">RASTER</span><span class="logo-grafik">GRAFIK</span>
                    </a>
                </div>

                <div class="card-glass">
                    <i class="bi bi-person-fill position-absolute" style="font-size: 15rem; color: var(--color-1); opacity: 0.03; bottom: -30px; right: -20px; z-index: 0; transform: rotate(-10deg);"></i>
                    
                    <div class="position-relative z-1">
                        <h4 class="fw-bold mb-4" style="color: var(--color-1);">Selamat Datang!</h4>
                        
                        <?php if ($error != ''): ?>
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger fw-bold rounded-4 py-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error; ?></div>
                        <?php endif; ?>
                        
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="small fw-bold mb-2 text-dark">Alamat Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                            </div>
                            <div class="mb-4">
                                <label class="small fw-bold mb-2 text-dark">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-grad-primary w-100 mb-3">Masuk ke Akun</button>
                        </form>
                        <div class="text-center mt-3"><span class="text-dark fw-medium small">Belum punya akun? <a href="register.php" class="fw-bold text-decoration-none" style="color: var(--color-1);">Daftar Sekarang</a></span></div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>