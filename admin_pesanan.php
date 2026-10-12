<?php
session_start();
require_once 'config/database.php';

// Proteksi Halaman: Wajib Login & Role Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id_admin = $_SESSION['id_user'];

// --- LOGIKA AKSI ADMIN ---

// 1. Verifikasi Pembayaran (Ubah ke Lunas)
if (isset($_POST['aksi']) && $_POST['aksi'] == 'verifikasi_pembayaran') {
    $id_pesanan = intval($_POST['id_pesanan']);
    
    // Update status pembayaran & status cetak
    mysqli_query($koneksi, "UPDATE pesanan SET status_pembayaran = 'lunas', status_pesanan = 'proses_cetak' WHERE id_pesanan = $id_pesanan");
    
    // Catat ke riwayat status
    mysqli_query($koneksi, "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) VALUES ($id_pesanan, 'Pembayaran Diverifikasi', 'Pembayaran telah dikonfirmasi LUNAS oleh Admin. Pesanan masuk antrean cetak.', $id_admin)");
    
    echo "<script>alert('Pembayaran berhasil diverifikasi! Pesanan lanjut ke Proses Cetak.'); window.location.href='admin_pesanan.php';</script>";
}

// 2. Update Status Pengerjaan Cetak
if (isset($_POST['aksi']) && $_POST['aksi'] == 'update_status_pesanan') {
    $id_pesanan = intval($_POST['id_pesanan']);
    $status_baru = $_POST['status_pesanan'];
    
    mysqli_query($koneksi, "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = $id_pesanan");
    
    $ket = "Status pesanan diperbarui menjadi: " . strtoupper(str_replace('_', ' ', $status_baru));
    mysqli_query($koneksi, "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) VALUES ($id_pesanan, '$status_baru', '$ket', $id_admin)");
    
    echo "<script>alert('Status pesanan berhasil diperbarui!'); window.location.href='admin_pesanan.php';</script>";
}

// Menarik semua data pesanan + nama pelanggan dari database
$query_pesanan = mysqli_query($koneksi, "
    SELECT pesanan.*, users.nama AS nama_pelanggan, users.no_whatsapp 
    FROM pesanan 
    JOIN users ON pesanan.id_user = users.id_user 
    ORDER BY pesanan.waktu_pesan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg); }
    </style>
</head>
<body class="bg-light">

    <!-- Top Navigation Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span> <small class="fs-6 text-muted">| Panel Admin</small>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-warning" href="admin_pesanan.php">Kelola Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_produk.php">Kelola Produk</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text text-white me-3">Halo, <?= htmlspecialchars($_SESSION['nama']); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Kelola Antrean Pesanan</h3>
            <button class="btn btn-outline-secondary btn-sm" onclick="location.reload();">🔄 Refresh Data</button>
        </div>

        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th>Pengiriman</th>
                            <th>Total Tagihan</th>
                            <th>Bukti Bayar</th>
                            <th>Status Bayar</th>
                            <th>Status Pesanan</th>
                            <th class="text-center">Aksi / Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_pesanan) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($query_pesanan)): ?>
                                <tr>
                                    <td class="fw-bold"><?= $row['nomor_invoice']; ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_pelanggan']); ?></strong><br>
                                        <small class="text-muted">WA: <?= htmlspecialchars($row['no_whatsapp']); ?></small>
                                    </td>
                                    <td><span class="badge bg-outline-dark border text-dark"><?= str_replace('_', ' ', $row['metode_pengiriman']); ?></span></td>
                                    <td class="fw-bold text-danger">Rp <?= number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                                    
                                    <!-- Bukti Transfer -->
                                    <td>
                                        <?php if ($row['bukti_bayar']): ?>
                                            <a href="public/uploads/bukti_transfer/<?= $row['bukti_bayar']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                🖼️ Lihat Foto
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Belum Upload</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Status Pembayaran -->
                                    <td>
                                        <?php if ($row['status_pembayaran'] == 'belum_bayar'): ?>
                                            <span class="badge bg-danger">Belum Bayar</span>
                                        <?php elseif ($row['status_pembayaran'] == 'menunggu_verifikasi'): ?>
                                            <span class="badge bg-warning text-dark">Menunggu ACC</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Lunas</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Status Pesanan -->
                                    <td>
                                        <span class="badge bg-primary text-capitalize">
                                            <?= str_replace('_', ' ', $row['status_pesanan']); ?>
                                        </span>
                                    </td>

                                    <!-- Form Aksi Admin -->
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <!-- Tombol ACC Bayar -->
                                            <?php if ($row['status_pembayaran'] == 'menunggu_verifikasi'): ?>
                                                <form action="" method="POST" onsubmit="return confirm('Konfirmasi bahwa uang transfer sudah masuk?');">
                                                    <input type="hidden" name="aksi" value="verifikasi_pembayaran">
                                                    <input type="hidden" name="id_pesanan" value="<?= $row['id_pesanan']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold">✓ ACC Bayar</button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Dropdown Update Status Cetak -->
                                            <form action="" method="POST" class="d-flex gap-1">
                                                <input type="hidden" name="aksi" value="update_status_pesanan">
                                                <input type="hidden" name="id_pesanan" value="<?= $row['id_pesanan']; ?>">
                                                <select name="status_pesanan" class="form-select form-select-sm" style="width: 130px;">
                                                    <option value="pending" <?= $row['status_pesanan'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="proses_cetak" <?= $row['status_pesanan'] == 'proses_cetak' ? 'selected' : ''; ?>>Proses Cetak</option>
                                                    <option value="selesai" <?= $row['status_pesanan'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                    <option value="dibatalkan" <?= $row['status_pesanan'] == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Set</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada pesanan masuk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>