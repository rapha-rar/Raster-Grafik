<?php
session_start();
require_once 'config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ---------------------------------------------------------
// PROSES UPDATE STATUS PESANAN
// ---------------------------------------------------------
if (isset($_POST['update_status'])) {
    $id_pesanan = intval($_POST['id_pesanan']);
    $status_pembayaran = mysqli_real_escape_string($koneksi, $_POST['status_pembayaran']);
    $status_pesanan = mysqli_real_escape_string($koneksi, $_POST['status_pesanan']);

    $query = "UPDATE pesanan SET 
              status_pembayaran = '$status_pembayaran', 
              status_pesanan = '$status_pesanan' 
              WHERE id_pesanan = $id_pesanan";
              
    if(mysqli_query($koneksi, $query)){
        echo "<script>alert('Status pesanan berhasil diperbarui!'); window.location='admin_pesanan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status!'); window.history.back();</script>";
    }
}

// ---------------------------------------------------------
// HAPUS PESANAN (Opsional, gunakan dengan hati-hati)
// ---------------------------------------------------------
if(isset($_GET['hapus'])){
    $id_hapus = intval($_GET['hapus']);
    // Hapus detail keranjang pesanan terlebih dahulu (jika ada relasi foreign key)
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE id_pesanan = $id_hapus");
    header("Location: admin_pesanan.php");
    exit();
}

// ---------------------------------------------------------
// AMBIL DATA PESANAN KE DALAM ARRAY
// ---------------------------------------------------------
$query_pesanan = mysqli_query($koneksi, "
    SELECT p.*, u.nama, u.no_whatsapp 
    FROM pesanan p 
    JOIN users u ON p.id_user = u.id_user 
    ORDER BY p.id_pesanan DESC
");

$data_pesanan = [];
while($row = mysqli_fetch_assoc($query_pesanan)){
    $data_pesanan[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; }
        
        /* Navbar Admin Theme */
        .navbar-admin { background: #0f172a; padding: 15px 0; }
        .logo-raster { color: #2373F4; font-weight: 900; letter-spacing: -0.5px; }
        .logo-grafik { color: #ffffff; font-weight: 900; letter-spacing: -0.5px; }
        .nav-link-admin { color: #94a3b8; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: 0.3s; text-decoration: none;}
        .nav-link-admin:hover, .nav-link-admin.active { color: #ffffff; background: rgba(255,255,255,0.1); }
        
        /* Card & Table Theme */
        .card-admin { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .table-admin th { background: #f1f5f9; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem; border-bottom: 2px solid #e2e8f0;}
        .table-admin td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        
        /* Badges */
        .badge-soft { padding: 6px 12px; border-radius: 50px; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <!-- NAVBAR TOP -->
    <nav class="navbar-admin mb-4">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-4">
                <a href="admin_pesanan.php" class="text-decoration-none fs-4 me-3">
                    <span class="logo-raster">RASTER</span><span class="logo-grafik">GRAFIK</span>
                    <span class="text-secondary fs-6 fw-normal ms-2 border-start border-secondary ps-2">Admin Panel</span>
                </a>
                <!-- Link Active Berpindah ke Pesanan -->
                <a href="admin_pesanan.php" class="nav-link-admin active"><i class="bi bi-cart-check me-2"></i>Kelola Pesanan</a>
                <a href="admin_produk.php" class="nav-link-admin"><i class="bi bi-box-seam me-2"></i>Kelola Produk</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light fw-medium"><i class="bi bi-person-circle me-2 text-primary"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold px-3 border-danger border-opacity-50">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container-fluid px-4 pb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0"><i class="bi bi-cart-check text-primary me-2"></i>Daftar Pesanan Masuk</h3>
        </div>

        <!-- TABEL PESANAN -->
        <div class="card-admin p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr>
                            <th>Invoice & Waktu</th>
                            <th>Pelanggan</th>
                            <th>Total Tagihan</th>
                            <th>Status Pembayaran</th>
                            <th>Status Pesanan</th>
                            <th width="200" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_pesanan as $row) : ?>
                        <tr>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($row['nomor_invoice']); ?></strong>
                                <small class="text-muted fw-medium"><i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($row['waktu_pesan'])); ?></small>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($row['nama']); ?></strong>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['no_whatsapp']); ?>" target="_blank" class="text-success text-decoration-none small fw-bold">
                                    <i class="bi bi-whatsapp"></i> <?= htmlspecialchars($row['no_whatsapp']); ?>
                                </a>
                            </td>
                            <td class="fw-bold text-primary fs-6">Rp <?= number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if($row['status_pembayaran'] == 'belum_bayar'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 badge-soft">Belum Bayar</span>
                                <?php elseif($row['status_pembayaran'] == 'kedaluwarsa'): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 badge-soft">Kedaluwarsa</span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 badge-soft"><i class="bi bi-check-circle me-1"></i>Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $bg_color = 'bg-secondary'; $text_color = 'text-dark';
                                    if($row['status_pesanan'] == 'pending') { $bg_color = 'bg-warning bg-opacity-25'; $text_color = 'text-dark'; }
                                    if($row['status_pesanan'] == 'diproses') { $bg_color = 'bg-info bg-opacity-25'; $text_color = 'text-primary'; }
                                    if($row['status_pesanan'] == 'dikirim') { $bg_color = 'bg-primary bg-opacity-25'; $text_color = 'text-primary'; }
                                    if($row['status_pesanan'] == 'selesai') { $bg_color = 'bg-success bg-opacity-25'; $text_color = 'text-success'; }
                                    if($row['status_pesanan'] == 'dibatalkan') { $bg_color = 'bg-danger bg-opacity-25'; $text_color = 'text-danger'; }
                                ?>
                                <span class="badge <?= $bg_color ?> <?= $text_color ?> border badge-soft text-capitalize">
                                    <?= htmlspecialchars($row['status_pesanan']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <!-- Tombol Lihat Detail (Arahkan ke detail_pesanan.php) -->
                                <a href="detail_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn btn-sm btn-light border fw-bold rounded-pill px-3 me-1" title="Lihat Detail"><i class="bi bi-eye"></i></a>
                                
                                <!-- Tombol Edit Status (Memunculkan Modal) -->
                                <button class="btn btn-sm btn-primary fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalEditStatus<?= $row['id_pesanan']; ?>">Update</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_pesanan)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i> Belum ada pesanan masuk.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- Akhir Container -->

    <!-- =============================================================
         KUMPULAN MODAL EDIT STATUS DI LUAR TABEL (ANTI-BUG)
    ============================================================== -->
    <?php foreach ($data_pesanan as $row) : ?>
    <div class="modal fade" id="modalEditStatus<?= $row['id_pesanan']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
                <input type="hidden" name="id_pesanan" value="<?= $row['id_pesanan']; ?>">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Update Status Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="bg-light p-3 rounded-3 mb-4 border">
                        <small class="text-muted d-block fw-bold mb-1">Nomor Invoice:</small>
                        <span class="fs-5 fw-bold text-dark"><?= htmlspecialchars($row['nomor_invoice']); ?></span>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-dark">Status Pembayaran</label>
                        <select name="status_pembayaran" class="form-select fw-medium" required>
                            <option value="belum_bayar" <?= ($row['status_pembayaran'] == 'belum_bayar') ? 'selected' : ''; ?>>Belum Bayar</option>
                            <option value="lunas" <?= ($row['status_pembayaran'] == 'lunas') ? 'selected' : ''; ?>>Lunas</option>
                            <option value="kedaluwarsa" <?= ($row['status_pembayaran'] == 'kedaluwarsa') ? 'selected' : ''; ?>>Kedaluwarsa</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold mb-2 small text-dark">Status Pesanan</label>
                        <select name="status_pesanan" class="form-select fw-medium" required>
                            <option value="pending" <?= ($row['status_pesanan'] == 'pending') ? 'selected' : ''; ?>>Pending (Menunggu)</option>
                            <option value="diproses" <?= ($row['status_pesanan'] == 'diproses') ? 'selected' : ''; ?>>Diproses (Sedang Dicetak)</option>
                            <option value="dikirim" <?= ($row['status_pesanan'] == 'dikirim') ? 'selected' : ''; ?>>Dikirim (Dalam Perjalanan)</option>
                            <option value="selesai" <?= ($row['status_pesanan'] == 'selesai') ? 'selected' : ''; ?>>Selesai (Pesanan Diterima)</option>
                            <option value="dibatalkan" <?= ($row['status_pesanan'] == 'dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
                    <button type="button" class="btn btn-light border px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_status" class="btn btn-primary px-4 rounded-pill fw-bold"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>