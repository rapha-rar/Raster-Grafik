<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pelanggan') { header("Location: login.php"); exit(); }

$id_user = $_SESSION['id_user'];
$sukses = false;

if (isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $wa = mysqli_real_escape_string($koneksi, $_POST['no_whatsapp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap']);
    
    if(mysqli_query($koneksi, "UPDATE users SET nama='$nama', no_whatsapp='$wa', alamat_lengkap='$alamat' WHERE id_user=$id_user")){
        $_SESSION['nama'] = $nama; 
        $sukses = true;
    }
}
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Akun - Raster Grafik</title>
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
            min-height: 100vh;
            position: relative; overflow-x: hidden;
        }
        
        .bg-shape1 { position: fixed; top: -10%; left: -10%; width: max(350px, 45vw); height: max(350px, 45vw); border-radius: 50%; background: var(--color-1); filter: blur(120px); opacity: 0.15; z-index: -1; }
        .bg-shape2 { position: fixed; bottom: -10%; right: -10%; width: max(400px, 50vw); height: max(400px, 50vw); border-radius: 50%; background: var(--color-3); filter: blur(120px); opacity: 0.18; z-index: -1; }

        .navbar { background: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(25px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .navbar > .container { min-height: 52px; }
        .floating-wa { position: fixed; bottom: 25px; right: 25px; background: linear-gradient(135deg, #22c55e, #15803d); color: white; border-radius: 50px; padding: 14px 24px; box-shadow: 0 8px 25px rgba(34,197,94,0.4); z-index: 1000; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .floating-wa:hover { transform: translateY(-4px) scale(1.05); color: white; box-shadow: 0 12px 30px rgba(34,197,94,0.6); }

        .card-glass { background: #ffffff; border: 1px solid rgba(87, 142, 245, 0.2); box-shadow: 0 15px 40px rgba(15, 23, 42, 0.05); border-radius: 24px; padding: 2.5rem; position: relative; overflow: hidden; }
        
        .form-control { border-radius: 12px; border: 1px solid #cbd5e1; background: #f1f5f9; padding: 0.75rem 1rem; transition: 0.3s; font-weight: 600; color: #0f172a;}
        .form-control:focus { background: #ffffff; border-color: var(--color-2); box-shadow: 0 0 0 4px rgba(101, 208, 244, 0.3); }
        
        .btn-grad-primary { background: var(--grad-primary); color: white; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 15px rgba(35, 115, 244, 0.3); border: none; padding: 14px; transition: 0.3s; }
        .btn-grad-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(35, 115, 244, 0.5); color: white; }
    </style>
</head>
<body>
    <div class="bg-shape1"></div><div class="bg-shape2"></div>

    <nav class="navbar navbar-expand-lg py-3 sticky-top">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="dashboard_pelanggan.php" class="btn btn-light bg-white rounded-pill btn-sm px-4 fw-bold shadow-sm border" style="color: var(--color-1);"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        </div>
    </nav>

    <div class="container py-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                
                <div class="card-glass">
                    <i class="bi bi-person-bounding-box position-absolute" style="font-size: 8rem; color: var(--color-1); opacity: 0.03; top: 10px; right: 10px;"></i>
                    <div class="position-relative z-1">
                        <div class="mb-4">
                            <h3 class="fw-bold mb-1" style="color: var(--color-1);"><i class="bi bi-person-gear me-2"></i> Pengaturan Profil</h3>
                            <p class="text-muted small">Perbarui data diri dan alamat utama pengiriman Anda.</p>
                        </div>
                        <?php if($sukses): ?>
                            <div class="alert alert-success border border-success border-opacity-50 bg-success bg-opacity-20 text-success fw-bold rounded-4 px-4 py-3 mb-4"><i class="bi bi-check-circle-fill me-2"></i> Profil berhasil diperbarui!</div>
                        <?php endif; ?>
                        
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="small fw-bold mb-2 text-dark">Alamat Email Terdaftar</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-secondary bg-opacity-10 text-muted rounded-start-4"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control rounded-end-4 bg-secondary bg-opacity-10" value="<?= htmlspecialchars($user['email']); ?>" readonly disabled>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold mb-2 text-dark">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light text-primary rounded-start-4"><i class="bi bi-person"></i></span>
                                    <input type="text" name="nama" class="form-control rounded-end-4" value="<?= htmlspecialchars($user['nama']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold mb-2 text-dark">Nomor WhatsApp Aktif</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light text-success rounded-start-4"><i class="bi bi-whatsapp"></i></span>
                                    <input type="tel" name="no_whatsapp" class="form-control rounded-end-4" value="<?= htmlspecialchars($user['no_whatsapp']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="small fw-bold mb-2 text-dark">Alamat Pengiriman Utama (Default Ojol)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light text-danger align-items-start pt-3 rounded-start-4"><i class="bi bi-geo-alt-fill"></i></span>
                                    <textarea name="alamat_lengkap" class="form-control rounded-end-4" rows="3" required><?= htmlspecialchars($user['alamat_lengkap']); ?></textarea>
                                </div>
                            </div>
                            <button type="submit" name="update_profil" class="btn btn-grad-primary w-100 mt-2"><i class="bi bi-save me-2"></i> Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="https://wa.me/6281234567890?text=Halo%20Admin..." class="floating-wa" target="_blank"><i class="bi bi-whatsapp fs-4"></i><span class="d-none d-md-inline">Chat Admin</span></a>
</body>
</html>