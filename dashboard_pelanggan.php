<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

if (isset($_POST['update_profil'])) {
    $nama_baru = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_wa_baru = mysqli_real_escape_string($koneksi, $_POST['no_whatsapp']);
    $alamat_baru = mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap']);

    $query_update = "UPDATE users SET nama = '$nama_baru', no_whatsapp = '$no_wa_baru', alamat_lengkap = '$alamat_baru' WHERE id_user = $id_user";
    if (mysqli_query($koneksi, $query_update)) {
        $_SESSION['nama'] = $nama_baru;
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
                    title: 'Profil Diperbarui!',
                    text: 'Data profil Anda telah berhasil disimpan.',
                    icon: 'success',
                    confirmButtonColor: '#0088cc',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'dashboard_pelanggan.php';
                });
            </script>
        </body>
        </html>";
        exit();
    }
}

$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user");
$user = mysqli_fetch_assoc($query_user);

$query_pesanan = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_user = $id_user ORDER BY waktu_pesan DESC");
$data_pesanan = [];
if ($query_pesanan) {
    while ($row = mysqli_fetch_assoc($query_pesanan)) {
        $data_pesanan[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - Raster Grafik</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; --bg-light: #f4f7f6; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); color: #333; }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg); }
        
        #page-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center; z-index: 99999;
            opacity: 1; visibility: visible; transition: opacity 0.4s ease, visibility 0.4s ease;
        }
        #page-loader.fade-out { opacity: 0; visibility: hidden; pointer-events: none; }
        .spinner-container { text-align: center; animation: pulseLoader 1.5s infinite ease-in-out; }
        @keyframes pulseLoader { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } }

        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .btn-primer { background-color: var(--merah-rg); color: white; font-weight: 500; border-radius: 8px; }
        .btn-primer:hover { background-color: #aa0000; color: white; }
        .table-custom th { background-color: #f8f9fa; font-weight: 600; color: #555; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border-bottom: 2px solid #eee; }
        .table-custom td { vertical-align: middle; font-size: 0.9rem; }
        .badge-custom { padding: 0.5em 0.8em; font-weight: 500; border-radius: 30px; font-size: 0.75rem; }

        .item-order-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 14px;
            background-color: #ffffff;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

    <!-- OVERLAY LOADER -->
    <div id="page-loader">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em; color: #0088cc !important;"></div>
            <div class="loader-logo mt-3 fw-bold fs-5">
                <span style="color: #0088cc;">RASTER</span><span style="color: #cc0000;">GRAFIK</span>
            </div>
            <small class="text-muted d-block mt-1">Memuat halaman...</small>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="index.php">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="me-2 text-muted d-none d-md-inline small"><i class="bi bi-person-circle me-1"></i> Halo, <strong class="text-dark"><?= htmlspecialchars($user['nama']); ?></strong></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-3 rounded-pill">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row g-4">
            
            <!-- PROFIL USER -->
            <div class="col-12 col-lg-4 col-xl-3">
                <div class="card card-custom p-3">
                    <div class="card-body text-center">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama']); ?>&background=0088cc&color=fff&size=100" class="rounded-circle shadow-sm mb-3" alt="Avatar">
                        <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user['nama']); ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']); ?></p>
                        
                        <div class="text-start bg-light p-3 rounded-3 mb-3">
                            <div class="d-flex mb-2 align-items-start">
                                <i class="bi bi-whatsapp text-success me-2 mt-1"></i>
                                <span class="small text-break"><?= htmlspecialchars($user['no_whatsapp']); ?></span>
                            </div>
                            <div class="d-flex align-items-start">
                                <i class="bi bi-geo-alt-fill text-danger me-2 mt-1"></i>
                                <span class="small text-break"><?= htmlspecialchars($user['alamat_lengkap']); ?></span>
                            </div>
                        </div>

                        <button class="btn btn-outline-primary w-100 rounded-pill btn-sm fw-medium" data-bs-toggle="modal" data-bs-target="#modalEditProfil">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profil
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIWAYAT PESANAN -->
            <div class="col-12 col-lg-8 col-xl-9">
                <div class="card card-custom p-3 p-md-4">
                    
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4"><i class="bi bi-bag-check me-2 text-biru"></i>Riwayat Pesanan</h4>
                        <a href="index.php" class="btn btn-light border btn-sm px-3 rounded-pill text-muted">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                        </a>
                    </div>

                    <?php if (count($data_pesanan) > 0) : ?>
                        
                        <!-- TAMPILAN MOBILE (KARTU) -->
                        <div class="d-block d-md-none">
                            <?php foreach ($data_pesanan as $row) : ?>
                                <div class="item-order-card shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= $row['nomor_invoice']; ?></span>
                                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($row['waktu_pesan'])); ?></small>
                                        </div>
                                        <span class="fw-bold text-merah fs-6">Rp <?= number_format($row['total_tagihan'], 0, ',', '.'); ?></span>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-1 align-items-center my-2">
                                        <?php if ($row['status_pembayaran'] == 'belum_bayar'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger badge-custom"><i class="bi bi-exclamation-circle me-1"></i> Belum Bayar</span>
                                        <?php elseif ($row['status_pembayaran'] == 'menunggu_verifikasi'): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning badge-custom"><i class="bi bi-hourglass-split me-1"></i> Menunggu Cek</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success badge-custom"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                                        <?php endif; ?>

                                        <span class="badge bg-secondary badge-custom text-capitalize">
                                            <?= str_replace('_', ' ', $row['status_pesanan']); ?>
                                        </span>
                                    </div>

                                    <div class="mt-3 pt-2 border-top text-end">
                                        <?php if ($row['status_pembayaran'] == 'belum_bayar'): ?>
                                            <a href="detail_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn btn-sm btn-primary rounded-pill w-100 shadow-sm">
                                                <i class="bi bi-wallet2 me-1"></i> Bayar Sekarang
                                            </a>
                                        <?php else: ?>
                                            <a href="detail_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                                                <i class="bi bi-eye me-1"></i> Lihat Detail
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- TAMPILAN DESKTOP (TABEL) -->
                        <div class="d-none d-md-block table-responsive">
                            <table class="table table-hover table-custom align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No. Invoice</th>
                                        <th>Tanggal</th>
                                        <th>Total Tagihan</th>
                                        <th>Pembayaran</th>
                                        <th>Status Pesanan</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_pesanan as $row) : ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?= $row['nomor_invoice']; ?></td>
                                            <td class="text-muted small"><?= date('d M Y, H:i', strtotime($row['waktu_pesan'])); ?></td>
                                            <td class="fw-bold text-merah">Rp <?= number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php if ($row['status_pembayaran'] == 'belum_bayar'): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger badge-custom"><i class="bi bi-exclamation-circle me-1"></i> Belum Bayar</span>
                                                <?php elseif ($row['status_pembayaran'] == 'menunggu_verifikasi'): ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning badge-custom"><i class="bi bi-hourglass-split me-1"></i> Menunggu Cek</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success badge-custom"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary badge-custom text-capitalize">
                                                    <?= str_replace('_', ' ', $row['status_pesanan']); ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($row['status_pembayaran'] == 'belum_bayar'): ?>
                                                    <a href="detail_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                                        <i class="bi bi-wallet2 me-1"></i> Bayar
                                                    </a>
                                                <?php else: ?>
                                                    <a href="detail_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bi bi-eye me-1"></i> Detail
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else : ?>
                        <div class="text-center py-5 text-muted px-2">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <p class="mb-0">Belum ada pesanan. Yuk mulai pesan sekarang!</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT PROFIL -->
    <div class="modal fade" id="modalEditProfil" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                <form action="" method="POST">
                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill me-2 text-biru"></i>Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 pt-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">Email Akun</label>
                            <input type="email" class="form-control bg-light border-0 text-muted" value="<?= htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">No. WhatsApp</label>
                            <input type="text" name="no_whatsapp" class="form-control" value="<?= htmlspecialchars($user['no_whatsapp']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Alamat Lengkap</label>
                            <textarea name="alamat_lengkap" class="form-control" rows="3" required><?= htmlspecialchars($user['alamat_lengkap']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_profil" class="btn btn-primer rounded-pill px-4 shadow-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('page-loader');
            if (loader) loader.classList.add('fade-out');
        });

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