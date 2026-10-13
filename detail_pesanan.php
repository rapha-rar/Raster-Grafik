<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pelanggan') { header("Location: login.php"); exit(); }

$id_user = $_SESSION['id_user'];
$id_pesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_pesanan == 0) { echo "<script>window.location.href='dashboard_pelanggan.php';</script>"; exit(); }

if (isset($_POST['upload_bukti'])) {
    $dir_upload = "public/uploads/bukti_transfer/";
    if (!is_dir($dir_upload)) mkdir($dir_upload, 0777, true);
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
        $ext = pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION);
        $nama_bukti = "BUKTI_" . $id_pesanan . "_" . time() . "." . $ext;
        move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $dir_upload . $nama_bukti);
        mysqli_query($koneksi, "UPDATE pesanan SET bukti_bayar = '$nama_bukti', status_pembayaran = 'menunggu_verifikasi' WHERE id_pesanan = $id_pesanan");
        mysqli_query($koneksi, "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) VALUES ($id_pesanan, 'Bukti Bayar Diunggah', 'Pelanggan telah mengunggah bukti pembayaran. Menunggu verifikasi dari Admin.', $id_user)");
        echo "<script>alert('Bukti berhasil diunggah!'); window.location.href='detail_pesanan.php?id=$id_pesanan';</script>";
    }
}

$query_pesanan = "SELECT p.*, d.*, pr.nama_produk, pr.tipe_harga, pr.gambar_katalog FROM pesanan p JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan JOIN produk pr ON d.id_produk = pr.id_produk WHERE p.id_pesanan = $id_pesanan AND p.id_user = $id_user";
$result = mysqli_query($koneksi, $query_pesanan);
if (mysqli_num_rows($result) == 0) { echo "<script>window.location.href='dashboard_pelanggan.php';</script>"; exit(); }
$pesanan = mysqli_fetch_assoc($result);

$query_riwayat = mysqli_query($koneksi, "SELECT * FROM riwayat_status WHERE id_pesanan = $id_pesanan ORDER BY waktu_update ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Raster Grafik</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; --bg-light: #f4f7f6; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); color: #333; }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg); }
        
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .btn-primer { background-color: var(--merah-rg); color: white; font-weight: 500; }
        .btn-primer:hover { background-color: #aa0000; color: white; }
        
        /* Modern Timeline */
        .timeline { border-left: 2px dashed #dee2e6; padding-left: 20px; margin-left: 15px; position: relative; }
        .timeline-item { margin-bottom: 25px; position: relative; }
        .timeline-item::before { content: ''; position: absolute; left: -27px; top: 5px; width: 14px; height: 14px; border-radius: 50%; background-color: var(--biru-rg); border: 2px solid white; box-shadow: 0 0 0 3px rgba(0,136,204,0.2); }
        .timeline-item:last-child::before { background-color: var(--merah-rg); box-shadow: 0 0 0 3px rgba(204,0,0,0.2); }
        .timeline-date { font-size: 0.8rem; font-weight: 500; color: #888; margin-bottom: 4px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-5 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="index.php">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span>
            </a>
            <div class="d-flex align-items-center">
                <a href="dashboard_pelanggan.php" class="btn btn-light border btn-sm px-3 rounded-pill text-muted shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row g-4">
            
            <!-- KOLOM KIRI -->
            <div class="col-lg-7">
                <div class="card card-custom p-4 p-md-5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-biru"></i> Rincian Pesanan</h4>
                        <span class="badge bg-dark rounded-pill fw-medium px-3 py-2 fs-6 shadow-sm"><i class="bi bi-upc-scan me-1"></i> <?= $pesanan['nomor_invoice']; ?></span>
                    </div>

                    <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-light border border-light-subtle">
                        <?php $img_src = (strpos($pesanan['gambar_katalog'], 'http') === 0) ? $pesanan['gambar_katalog'] : 'public/uploads/produk/' . $pesanan['gambar_katalog']; ?>
                        <img src="<?= $img_src; ?>" width="90" height="90" style="object-fit:cover;" class="rounded-3 shadow-sm me-3" alt="Produk">
                        <div>
                            <h5 class="fw-bold mb-2 text-dark"><?= htmlspecialchars($pesanan['nama_produk']); ?></h5>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill text-capitalize fw-medium px-3">
                                <i class="bi bi-truck me-1"></i> <?= str_replace('_', ' ', $pesanan['metode_pengiriman']); ?>
                            </span>
                        </div>
                    </div>

                    <ul class="list-group list-group-flush mb-4 text-muted fw-medium">
                        <li class="list-group-item d-flex justify-content-between px-2 py-3 border-light">
                            <span><i class="bi bi-aspect-ratio me-2"></i>Dimensi / Ukuran</span>
                            <span class="fw-bold text-dark"><?= ($pesanan['tipe_harga'] == 'meteran') ? $pesanan['panjang']."m x ".$pesanan['lebar']."m" : "Satuan (Pcs)"; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-2 py-3 border-light">
                            <span><i class="bi bi-box-seam me-2"></i>Jumlah Cetak (QTY)</span>
                            <span class="fw-bold text-dark"><?= $pesanan['jumlah']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-2 py-3 border-light">
                            <span><i class="bi bi-palette me-2"></i>Desain & File</span>
                            <span class="text-end text-dark">
                                <?= ($pesanan['opsi_desain'] == 'custom_desain') ? "<span class='badge bg-info bg-opacity-10 text-info fw-semibold rounded-pill'><i class='bi bi-brush'></i> Custom Desain</span><br>" : ""; ?>
                                <?= (!empty($pesanan['file_desain'])) ? "<i class='bi bi-file-earmark-check text-success'></i> File Terlampir" : "<span class='text-muted small'>Tidak ada file</span>"; ?>
                            </span>
                        </li>
                        <li class="list-group-item px-2 py-3 border-light">
                            <span class="d-block mb-2"><i class="bi bi-journal-text me-2"></i>Catatan Tambahan:</span>
                            <div class="p-3 bg-light border border-light-subtle rounded-3 small text-dark">
                                <?= (!empty($pesanan['catatan_desain'])) ? htmlspecialchars($pesanan['catatan_desain']) : "Tidak ada instruksi khusus."; ?>
                            </div>
                        </li>
                    </ul>

                    <div class="bg-light p-4 rounded-4 border border-light-subtle">
                        <div class="d-flex justify-content-between mb-2 text-muted fw-medium">
                            <span>Subtotal Produk:</span>
                            <span class="fw-bold text-dark">Rp <?= number_format($pesanan['subtotal'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted fw-medium">
                            <span>Biaya Desain Custom:</span>
                            <span class="fw-bold text-dark">Rp <?= number_format($pesanan['biaya_desain_custom'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="border-top border-secondary opacity-25 mb-3"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5 text-dark">TOTAL TAGIHAN:</span>
                            <span class="fw-bold fs-3 text-merah">Rp <?= number_format($pesanan['total_tagihan'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN -->
            <div class="col-lg-5">
                
                <?php if ($pesanan['status_pembayaran'] == 'belum_bayar'): ?>
                    <div class="card card-custom border border-danger border-opacity-25 p-4 mb-4" style="background-color: #fff9f9;">
                        <h5 class="fw-bold text-danger mb-3"><i class="bi bi-wallet2 me-2"></i> Selesaikan Pembayaran</h5>
                        <p class="small text-muted mb-3">Silakan transfer sebesar <strong class="text-dark">Rp <?= number_format($pesanan['total_tagihan'], 0, ',', '.'); ?></strong> ke rekening berikut:</p>
                        
                        <div class="bg-white border rounded-4 p-3 mb-4 text-center shadow-sm">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/2560px-Bank_Central_Asia.svg.png" height="25" class="mb-2" alt="BCA">
                            <h5 class="fw-bold mb-0 text-dark">123 456 7890</h5>
                            <span class="small text-muted">a.n PT Raster Grafik Indonesia</span>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-medium small text-dark"><i class="bi bi-cloud-upload me-1"></i> Upload Bukti Transfer</label>
                                <input type="file" name="bukti_bayar" class="form-control" accept="image/*" required>
                            </div>
                            <button type="submit" name="upload_bukti" class="btn btn-danger w-100 fw-bold rounded-pill shadow-sm">Kirim Bukti Pembayaran</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="card card-custom p-4 h-100">
                    <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-signpost-split me-2 text-biru"></i> Live Tracking</h5>
                    <div class="timeline mt-2">
                        <?php while($riwayat = mysqli_fetch_assoc($query_riwayat)): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><i class="bi bi-clock me-1"></i> <?= date('d M Y, H:i', strtotime($riwayat['waktu_update'])); ?></div>
                                <h6 class="fw-bold mb-1 text-dark text-capitalize"><?= str_replace('_', ' ', $riwayat['status']); ?></h6>
                                <p class="small text-muted mb-0 lh-sm"><?= htmlspecialchars($riwayat['keterangan']); ?></p>
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