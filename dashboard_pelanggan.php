<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pelanggan') { header("Location: login.php"); exit(); }

$id_user = $_SESSION['id_user'];
mysqli_query($koneksi, "UPDATE pesanan SET status_pesanan = 'dibatalkan', status_pembayaran = 'kedaluwarsa' WHERE status_pembayaran = 'belum_bayar' AND status_pesanan = 'pending' AND waktu_pesan < (NOW() - INTERVAL 24 HOUR)");

$query_pesanan = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_user = $id_user ORDER BY waktu_pesan DESC");
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - Raster Grafik</title>
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
        .section-title-black { color: #0f172a !important; font-weight: 800; }
        .card-glass { background: #ffffff; border: 1px solid rgba(87, 142, 245, 0.2); box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05); border-radius: 24px; overflow: hidden; }
        .btn-grad-primary { background: var(--grad-primary); color: white; border: none; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 15px rgba(35, 115, 244, 0.3); transition: 0.3s; }
        .btn-grad-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(35, 115, 244, 0.5); color: white; }
        .floating-wa { position: fixed; bottom: 25px; right: 25px; background: linear-gradient(135deg, #22c55e, #15803d); color: white; border-radius: 50px; padding: 14px 24px; box-shadow: 0 8px 25px rgba(34,197,94,0.4); z-index: 1000; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .floating-wa:hover { transform: translateY(-4px) scale(1.05); color: white; box-shadow: 0 12px 30px rgba(34,197,94,0.6); }
        
        .badge-soft { padding: 8px 14px; border-radius: 50px; font-weight: 700; letter-spacing: 0.5px; font-size: 0.75rem;}
        .table th { background: #f8fafc; color: #0f172a; font-weight: 800; border-bottom: 2px solid #e2e8f0; }
        .table td { vertical-align: middle; border-bottom: 1px solid #f1f5f9; padding: 1rem 0.5rem; }
    </style>
</head>
<body>
    <div class="bg-shape1"></div><div class="bg-shape2"></div>
    <nav class="navbar navbar-expand-lg py-3 sticky-top">
        <div class="container d-flex justify-content-between">
            <a href="index.php" class="btn btn-light bg-white rounded-pill btn-sm px-4 fw-bold shadow-sm border" style="color: var(--color-1);"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
            <div class="dropdown">
                <button class="btn btn-light bg-white rounded-pill btn-sm border fw-bold px-4 shadow-sm" data-bs-toggle="dropdown" style="color: var(--color-1);">
                    <i class="bi bi-person-circle me-2"></i> <?= htmlspecialchars($user['nama']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2">
                    <li><a class="dropdown-item py-2 fw-bold" href="profil_pelanggan.php"><i class="bi bi-gear text-secondary me-2"></i>Edit Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 fw-bold text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar Akun</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4 pb-5">
        <div class="d-flex align-items-center mb-4">
            <h4 class="section-title-black m-0 fs-5 fs-md-4"><i class="bi bi-clock-history me-2" style="color: var(--color-1);"></i>Riwayat Pesanan Anda</h4>
            <div class="ms-4 flex-grow-1 rounded-pill" style="height: 3px; background: linear-gradient(90deg, var(--color-2) 0%, transparent 100%); opacity: 0.5;"></div>
        </div>
        <div class="card-glass p-4">
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Invoice & Tanggal</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_pesanan) == 0): ?>
                            <tr><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-box-seam display-4 d-block mb-3"></i><span class="fw-medium">Belum ada pesanan.</span></td></tr>
                        <?php endif; ?>
                        <?php while($row = mysqli_fetch_assoc($query_pesanan)): ?>
                            <tr>
                                <td>
                                    <strong class="d-block text-dark"><?= $row['nomor_invoice'] ?></strong>
                                    <small class="text-muted fw-medium"><?= date('d M Y, H:i', strtotime($row['waktu_pesan'])) ?></small>
                                </td>
                                <td class="fw-bold" style="color: var(--color-1);">Rp <?= number_format($row['total_tagihan'],0,',','.') ?></td>
                                <td>
                                    <?php if($row['status_pembayaran'] == 'belum_bayar'): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 badge-soft">Belum Bayar</span>
                                    <?php elseif($row['status_pembayaran'] == 'kedaluwarsa'): ?>
                                        <span class="badge bg-secondary bg-opacity-25 text-dark border border-secondary border-opacity-50 badge-soft">Kedaluwarsa</span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-50 badge-soft">Lunas / Menunggu</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info bg-opacity-20 text-dark border border-info border-opacity-50 badge-soft text-capitalize"><?= str_replace('_',' ',$row['status_pesanan']) ?></span></td>
                                <td><a href="detail_pesanan.php?id=<?= $row['id_pesanan'] ?>" class="btn btn-grad-primary btn-sm px-4">Lihat Detail</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <a href="https://wa.me/6281234567890?text=Halo%20Admin..." class="floating-wa" target="_blank"><i class="bi bi-whatsapp fs-4"></i><span class="d-none d-md-inline">Chat Admin</span></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>