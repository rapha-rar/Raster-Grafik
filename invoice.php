<?php
require_once 'config/database.php';

// Menangkap ID pesanan dari URL
$id_pesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Menarik data pesanan spesifik dari database
$query = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_pesanan = $id_pesanan");
$pesanan = mysqli_fetch_assoc($query);

// Jika pesanan tidak ditemukan, hentikan halaman
if (!$pesanan) {
    die("Pesanan tidak ditemukan!");
}

// LOGIKA UPLOAD BUKTI BAYAR
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_transfer'])) {
    $tmp_file = $_FILES['bukti_transfer']['tmp_name'];
    
    // Pastikan folder penyimpanan sudah ada
    $dir_upload = "public/uploads/bukti_transfer/";
    if(!is_dir($dir_upload)){
        mkdir($dir_upload, 0777, true);
    }

    // Membuat nama file unik (Contoh: BUKTI_INV-2026_16123456.jpg)
    $nama_file_baru = "BUKTI_" . $pesanan['nomor_invoice'] . "_" . time() . ".jpg";
    $path_simpan = $dir_upload . $nama_file_baru;

    // Memindahkan file foto dari browser ke folder server Laragon
    if (move_uploaded_file($tmp_file, $path_simpan)) {
        // 1. Update status pesanan di database
        mysqli_query($koneksi, "UPDATE pesanan SET bukti_bayar = '$nama_file_baru', status_pembayaran = 'menunggu_verifikasi' WHERE id_pesanan = $id_pesanan");
        
        // 2. Tambah catatan ke riwayat status
        mysqli_query($koneksi, "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) VALUES ($id_pesanan, 'Menunggu Verifikasi Pembayaran', 'Pelanggan telah mengunggah bukti pembayaran.', 1)");

        // 3. Munculkan notifikasi sukses
        echo "<script>alert('Bukti pembayaran berhasil diunggah! Menunggu konfirmasi admin Raster Grafik.'); window.location.href='dashboard_pelanggan.php';</script>";
    } else {
        echo "<script>alert('Gagal mengunggah file bukti pembayaran.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembayaran - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; }
        .bg-merah { background-color: var(--merah-rg); color: white; }
    </style>
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span style="color:var(--biru-rg);">RASTER</span><span style="color:var(--merah-rg);">GRAFIK</span>
            </a>
            <a href="dashboard_pelanggan.php" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 p-4">
                    <h4 class="fw-bold text-center mb-4">INVOICE PEMBAYARAN</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Nomor Invoice:</span>
                        <span class="fw-bold"><?= $pesanan['nomor_invoice'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 border-bottom pb-3">
                        <span class="text-muted">Total Tagihan:</span>
                        <span class="fw-bold text-danger fs-4">Rp <?= number_format($pesanan['total_tagihan'], 0, ',', '.') ?></span>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm text-center">
                        <p class="mb-1 small">Silakan transfer sesuai nominal di atas ke rekening berikut:</p>
                        <h5 class="fw-bold mt-2 mb-0" style="color:var(--biru-rg);">BCA - 1234 5678 90</h5>
                        <p class="small mb-0">a.n. Percetakan Raster Grafik</p>
                    </div>

                    <?php if($pesanan['status_pembayaran'] == 'belum_bayar'): ?>
                        <form action="" method="POST" enctype="multipart/form-data" class="mt-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload Bukti Transfer</label>
                                <input type="file" name="bukti_transfer" class="form-control" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG. Pastikan foto struk terlihat jelas.</small>
                            </div>
                            <button type="submit" class="btn bg-merah w-100 fw-bold py-2">Kirim Konfirmasi Pembayaran</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success mt-4 text-center">
                            ✅ Bukti pembayaran Anda sudah diterima dan sedang menunggu verifikasi Admin.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>