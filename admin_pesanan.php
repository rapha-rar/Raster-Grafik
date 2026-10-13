<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id_admin = $_SESSION['id_user'];

// --- LOGIKA AKSI ADMIN ---
if (isset($_POST['aksi']) && $_POST['aksi'] == 'verifikasi_pembayaran') {
    $id_pesanan = intval($_POST['id_pesanan']);
    
    // Cek apakah pesanan ini memilih opsi request custom desain
    $q_check = mysqli_query($koneksi, "SELECT opsi_desain FROM detail_pesanan WHERE id_pesanan = $id_pesanan");
    $d_check = mysqli_fetch_assoc($q_check);
    
    if ($d_check && $d_check['opsi_desain'] == 'custom_desain') {
        $status_awal = 'proses_desain';
        $ket_awal = 'Pembayaran LUNAS. Pesanan masuk antrean pengerjaan Custom Desain oleh tim Raster Grafik.';
    } else {
        $status_awal = 'proses_cetak';
        $ket_awal = 'Pembayaran LUNAS. Pesanan masuk antrean proses cetak.';
    }

    mysqli_query($koneksi, "UPDATE pesanan SET status_pembayaran = 'lunas', status_pesanan = '$status_awal' WHERE id_pesanan = $id_pesanan");
    mysqli_query($koneksi, "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) VALUES ($id_pesanan, '$status_awal', '$ket_awal', $id_admin)");
    
    echo "<script>alert('Pembayaran berhasil diverifikasi! Status otomatis diset ke: " . strtoupper(str_replace('_', ' ', $status_awal)) . "'); window.location.href='admin_pesanan.php';</script>";
}

if (isset($_POST['aksi']) && $_POST['aksi'] == 'update_status_pesanan') {
    $id_pesanan = intval($_POST['id_pesanan']);
    $status_baru = $_POST['status_pesanan'];
    mysqli_query($koneksi, "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = $id_pesanan");
    $ket = "Status pesanan diperbarui menjadi: " . strtoupper(str_replace('_', ' ', $status_baru));
    mysqli_query($koneksi, "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) VALUES ($id_pesanan, '$status_baru', '$ket', $id_admin)");
    echo "<script>alert('Status pesanan berhasil diperbarui!'); window.location.href='admin_pesanan.php';</script>";
}

$query_pesanan = mysqli_query($koneksi, "
    SELECT pesanan.*, users.nama AS nama_pelanggan, users.no_whatsapp, 
           detail_pesanan.file_desain, detail_pesanan.catatan_desain, detail_pesanan.opsi_desain 
    FROM pesanan 
    JOIN users ON pesanan.id_user = users.id_user 
    LEFT JOIN detail_pesanan ON pesanan.id_pesanan = detail_pesanan.id_pesanan
    ORDER BY pesanan.waktu_pesan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Raster Grafik</title>
    
    <!-- CSS & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; --bg-light: #f4f7f6; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); color: #333; }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg) !important; }
        
        .admin-navbar { background-color: #1a1d20 !important; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        
        .table-custom th { background-color: #f8f9fa; font-weight: 600; color: #555; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border-bottom: 2px solid #eee; }
        .table-custom td { vertical-align: middle; font-size: 0.9rem; }
        .badge-custom { padding: 0.5em 0.8em; font-weight: 500; border-radius: 30px; }

        .catatan-box { transition: all 0.2s ease-in-out; border: 1px solid #dee2e6; }
        .catatan-box:hover { background-color: #e9ecef !important; border-color: var(--biru-rg) !important; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
    </style>
</head>
<body>

    <!-- Top Navigation Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar shadow-sm mb-4 py-3">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold fs-4" href="#">
                <span class="text-biru">RASTER</span><span class="text-danger">GRAFIK</span> 
                <span class="fs-6 text-secondary fw-normal ms-2 border-start border-secondary ps-2">Panel Admin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4 fw-medium">
                    <li class="nav-item">
                        <!-- UBAH WARNA DARI KUNING KE BIRU SAMA SEPERTI LOGO -->
                        <a class="nav-link active fw-bold" style="color: var(--biru-rg) !important;" href="admin_pesanan.php"><i class="bi bi-cart-check me-1"></i> Kelola Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_produk.php"><i class="bi bi-box-seam me-1"></i> Kelola Produk</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-light me-4 small"><i class="bi bi-person-badge me-1"></i> Halo, <?= htmlspecialchars($_SESSION['nama']); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-ui-checks-grid me-2 text-biru"></i> Kelola Antrean Pesanan</h3>
            <button class="btn btn-white border bg-white rounded-pill shadow-sm btn-sm px-3 text-muted" onclick="location.reload();">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
            </button>
        </div>

        <div class="card card-custom p-4">
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th>Pengiriman</th>
                            <th width="220px">Desain & Catatan</th>
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
                                    <td class="fw-bold text-dark"><?= $row['nomor_invoice']; ?></td>
                                    <td>
                                        <strong class="text-dark d-block"><?= htmlspecialchars($row['nama_pelanggan']); ?></strong>
                                        <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i> <?= htmlspecialchars($row['no_whatsapp']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light border text-dark text-capitalize badge-custom shadow-sm">
                                            <i class="bi bi-truck me-1"></i> <?= str_replace('_', ' ', $row['metode_pengiriman']); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php if (!empty($row['file_desain'])): ?>
                                            <a href="public/uploads/file_desain/<?= $row['file_desain']; ?>" target="_blank" download class="btn btn-sm btn-primary rounded-pill px-3 mb-2 shadow-sm" style="font-size:0.8rem;">
                                                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Download File
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-info bg-opacity-10 text-info badge-custom mb-2">
                                                <?= ($row['opsi_desain'] == 'custom_desain') ? '<i class="bi bi-palette-fill me-1"></i> Custom' : 'Tidak ada file'; ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($row['catatan_desain'])): ?>
                                            <div class="catatan-box bg-light p-2 rounded-3 text-start mt-1 cursor-pointer" 
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#modalCatatan<?= $row['id_pesanan']; ?>" 
                                                 style="max-width: 200px; cursor: pointer;"
                                                 title="Klik untuk melihat catatan lengkap">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <strong class="text-dark" style="font-size:0.75rem;"><i class="bi bi-chat-left-text text-primary me-1"></i> Catatan:</strong>
                                                    <i class="bi bi-arrows-angle-expand text-muted" style="font-size:0.7rem;"></i>
                                                </div>
                                                <div class="text-muted text-truncate" style="font-size:0.75rem; max-width: 180px;">
                                                    <?= htmlspecialchars($row['catatan_desain']); ?>
                                                </div>
                                                <span class="badge bg-primary bg-opacity-10 text-primary mt-1" style="font-size:0.65rem;">
                                                    <i class="bi bi-search me-1"></i> Baca Selengkapnya
                                                </span>
                                            </div>

                                            <div class="modal fade" id="modalCatatan<?= $row['id_pesanan']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                                                        <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                                                            <h5 class="modal-title fw-bold text-dark">
                                                                <i class="bi bi-chat-square-text-fill text-biru me-2"></i> Catatan Pesanan
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body px-4 pt-3 pb-4">
                                                            <div class="p-3 bg-light rounded-3 border text-dark" style="white-space: pre-line; word-break: break-word; font-size: 0.95rem; max-height: 350px; overflow-y: auto;">
                                                                <?= htmlspecialchars($row['catatan_desain']); ?>
                                                            </div>
                                                            <div class="mt-3 text-muted small d-flex justify-content-between">
                                                                <span><i class="bi bi-person me-1"></i> <strong>Pelanggan:</strong> <?= htmlspecialchars($row['nama_pelanggan']); ?></span>
                                                                <span><i class="bi bi-receipt me-1"></i> <strong>Invoice:</strong> <?= $row['nomor_invoice']; ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="fw-bold text-merah">Rp <?= number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                                    
                                    <td>
                                        <?php if ($row['bukti_bayar']): ?>
                                            <a href="public/uploads/bukti_transfer/<?= $row['bukti_bayar']; ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3" style="font-size:0.8rem;">
                                                <i class="bi bi-image me-1"></i> Lihat Foto
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Belum ada</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($row['status_pembayaran'] == 'belum_bayar'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger badge-custom"><i class="bi bi-x-circle me-1"></i> Belum Bayar</span>
                                        <?php elseif ($row['status_pembayaran'] == 'menunggu_verifikasi'): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning badge-custom"><i class="bi bi-hourglass-split me-1"></i> Menunggu ACC</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success badge-custom"><i class="bi bi-check-circle-fill me-1"></i> Lunas</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary badge-custom text-capitalize shadow-sm">
                                            <?= str_replace('_', ' ', $row['status_pesanan']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column gap-2 align-items-center">
                                            <?php if ($row['status_pembayaran'] == 'menunggu_verifikasi'): ?>
                                                <form action="" method="POST" onsubmit="return confirm('Konfirmasi bahwa uang transfer sudah masuk?');" class="w-100">
                                                    <input type="hidden" name="aksi" value="verifikasi_pembayaran">
                                                    <input type="hidden" name="id_pesanan" value="<?= $row['id_pesanan']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success fw-medium rounded-pill w-100 shadow-sm" style="font-size:0.8rem;">
                                                        <i class="bi bi-check2-all me-1"></i> ACC Bayar
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <form action="" method="POST" class="d-flex gap-1 w-100">
                                                <input type="hidden" name="aksi" value="update_status_pesanan">
                                                <input type="hidden" name="id_pesanan" value="<?= $row['id_pesanan']; ?>">
                                                <select name="status_pesanan" class="form-select form-select-sm rounded-start-pill" style="font-size:0.8rem;">
                                                    <option value="pending" <?= $row['status_pesanan'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="proses_desain" <?= $row['status_pesanan'] == 'proses_desain' ? 'selected' : ''; ?>>Proses Desain</option>
                                                    <option value="proses_cetak" <?= $row['status_pesanan'] == 'proses_cetak' ? 'selected' : ''; ?>>Proses Cetak</option>
                                                    <option value="selesai" <?= $row['status_pesanan'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                    <option value="dibatalkan" <?= $row['status_pesanan'] == 'dibatalkan' ? 'selected' : ''; ?>>Batal</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-dark rounded-end-pill px-3" style="font-size:0.8rem;">Set</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-light"></i>
                                    Belum ada pesanan masuk.
                                </td>
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