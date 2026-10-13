<?php
session_start();
require_once 'config/database.php';

// Proteksi: Wajib Login sebagai Pelanggan
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_pesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pesanan == 0) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location.href='dashboard_pelanggan.php';</script>";
    exit();
}

// --- LOGIKA UPLOAD BUKTI PEMBAYARAN ---
if (isset($_POST['upload_bukti'])) {
    $dir_upload = "public/uploads/bukti_transfer/";
    if (!is_dir($dir_upload)) mkdir($dir_upload, 0777, true);

    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
        $ext = pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION);
        $nama_bukti = "BUKTI_" . $id_pesanan . "_" . time() . "." . $ext;
        
        move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $dir_upload . $nama_bukti);

        // Update database
        mysqli_query($koneksi, "UPDATE pesanan SET bukti_bayar = '$nama_bukti', status_pembayaran = 'menunggu_verifikasi' WHERE id_pesanan = $id_pesanan");
        
        // Catat di riwayat
        mysqli_query($koneksi, "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) VALUES ($id_pesanan, 'Bukti Bayar Diunggah', 'Pelanggan telah mengunggah bukti pembayaran. Menunggu verifikasi dari Admin.', $id_user)");
        
        echo "<script>alert('Bukti pembayaran berhasil diunggah! Mohon tunggu verifikasi.'); window.location.href='detail_pesanan.php?id=$id_pesanan';</script>";
    }
}

// Menarik data pesanan dan produk secara lengkap
$query_pesanan = "SELECT p.*, d.*, pr.nama_produk, pr.tipe_harga, pr.gambar_katalog 
                  FROM pesanan p 
                  JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan 
                  JOIN produk pr ON d.id_produk = pr.id_produk 
                  WHERE p.id_pesanan = $id_pesanan AND p.id_user = $id_user";
$result = mysqli_query($koneksi, $query_pesanan);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Akses Ditolak. Pesanan ini bukan milik Anda.'); window.location.href='dashboard_pelanggan.php';</script>";
    exit();
}
$pesanan = mysqli_fetch_assoc($result);

// Menarik Riwayat Tracking Status
$query_riwayat = mysqli_query($koneksi, "SELECT * FROM riwayat_status WHERE id_pesanan = $id_pesanan ORDER BY waktu_update ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg); }
        
        /* CSS Untuk Garis Waktu (Timeline) Tracking */
        .timeline {
            border-left: 3px solid var(--biru-rg);
            padding-left: 20px;
            margin-left: 10px;
            position: relative;
        }
        .timeline-item {
            margin-bottom: 25px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -29.5px; /* Menyesuaikan agar tepat di tengah garis */
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: var(--merah-rg);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--biru-rg);
        }
        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 2px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span>
            </a>
            <div class="d-flex align-items-center">
                <a href="dashboard_pelanggan.php" class="btn btn-outline-secondary btn-sm me-2">⬅️ Kembali ke Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row g-4">
            
            <!-- KOLOM KIRI: RINCIAN INVOICE & PRODUK -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Rincian Pesanan</h4>
                        <span class="badge bg-dark fs-6"><?= $pesanan['nomor_invoice']; ?></span>
                    </div>

                    <div class="d-flex align-items-center mb-4 border p-3 rounded bg-light">
                        <?php $img_src = (strpos($pesanan['gambar_katalog'], 'http') === 0) ? $pesanan['gambar_katalog'] : 'public/uploads/produk/' . $pesanan['gambar_katalog']; ?>
                        <img src="<?= $img_src; ?>" width="80" class="rounded me-3 border" alt="Produk">
                        <div>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($pesanan['nama_produk']); ?></h5>
                            <span class="badge bg-info text-capitalize"><?= str_replace('_', ' ', $pesanan['metode_pengiriman']); ?></span>
                        </div>
                    </div>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Dimensi / Ukuran</span>
                            <span class="fw-semibold">
                                <?= ($pesanan['tipe_harga'] == 'meteran') ? $pesanan['panjang']."m x ".$pesanan['lebar']."m" : "Satuan (Pcs)"; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Jumlah Cetak (QTY)</span>
                            <span class="fw-semibold"><?= $pesanan['jumlah']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Desain & File</span>
                            <span class="fw-semibold text-end">
                                <?= ($pesanan['opsi_desain'] == 'custom_desain') ? "🎨 Request Custom Desain<br>" : ""; ?>
                                <?= (!empty($pesanan['file_desain'])) ? "📄 File Terlampir" : "Belum ada file"; ?>
                            </span>
                        </li>
                        <li class="list-group-item px-0">
                            <span class="text-muted d-block mb-1">Catatan Tambahan:</span>
                            <div class="p-2 bg-light border rounded small">
                                <?= (!empty($pesanan['catatan_desain'])) ? htmlspecialchars($pesanan['catatan_desain']) : "Tidak ada catatan."; ?>
                            </div>
                        </li>
                    </ul>

                    <div class="alert alert-secondary mb-0">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal Produk:</span>
                            <span class="fw-bold">Rp <?= number_format($pesanan['subtotal'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Biaya Desain Custom:</span>
                            <span class="fw-bold">Rp <?= number_format($pesanan['biaya_desain_custom'], 0, ',', '.'); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">TOTAL TAGIHAN:</span>
                            <span class="fw-bold fs-4 text-merah">Rp <?= number_format($pesanan['total_tagihan'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: LIVE TRACKING & PEMBAYARAN -->
            <div class="col-lg-5">
                
                <!-- BOKS PEMBAYARAN (MUNCUL JIKA BELUM LUNAS) -->
                <?php if ($pesanan['status_pembayaran'] == 'belum_bayar'): ?>
                    <div class="card border-danger shadow-sm p-4 mb-4">
                        <h5 class="fw-bold text-danger mb-3">Selesaikan Pembayaran</h5>
                        <p class="small text-muted mb-3">Silakan transfer sebesar <strong>Rp <?= number_format($pesanan['total_tagihan'], 0, ',', '.'); ?></strong> ke rekening berikut:</p>
                        
                        <div class="bg-light border rounded p-3 mb-3 text-center">
                            <h6 class="fw-bold mb-1">BCA - 1234567890</h6>
                            <span class="small">a.n PT Raster Grafik Indonesia</span>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Upload Bukti Transfer</label>
                                <input type="file" name="bukti_bayar" class="form-control form-control-sm" accept="image/*" required>
                            </div>
                            <button type="submit" name="upload_bukti" class="btn btn-danger w-100 fw-bold">Konfirmasi Pembayaran</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- BOKS TRACKING / TIMELINE PESANAN -->
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h5 class="fw-bold mb-4">Live Tracking Pesanan</h5>
                    
                    <div class="timeline">
                        <?php while($riwayat = mysqli_fetch_assoc($query_riwayat)): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?= date('d M Y, H:i', strtotime($riwayat['waktu_update'])); ?></div>
                                <h6 class="fw-bold mb-1 text-capitalize"><?= str_replace('_', ' ', $riwayat['status']); ?></h6>
                                <p class="small text-muted mb-0"><?= htmlspecialchars($riwayat['keterangan']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 