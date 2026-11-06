<?php
session_start();
require_once 'config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// ---------------------------------------------------------
// PROSES HAPUS ITEM DARI KERANJANG
// ---------------------------------------------------------
if (isset($_GET['hapus'])) {
    $id_keranjang = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM keranjang WHERE id_keranjang = $id_keranjang AND id_user = $id_user");
    echo "<script>alert('Item berhasil dihapus dari keranjang.'); window.location='keranjang.php';</script>";
    exit();
}

// ---------------------------------------------------------
// AMBIL DATA KERANJANG BESERTA DETAIL PRODUK
// ---------------------------------------------------------
$query_keranjang = mysqli_query($koneksi, "
    SELECT k.*, p.nama_produk, p.harga_dasar, p.tipe_harga, p.gambar_katalog 
    FROM keranjang k 
    JOIN produk p ON k.id_produk = p.id_produk 
    WHERE k.id_user = $id_user 
    ORDER BY k.id_keranjang DESC
");

$data_keranjang = [];
$total_keseluruhan = 0;
while ($row = mysqli_fetch_assoc($query_keranjang)) {
    $data_keranjang[] = $row;
    $total_keseluruhan += $row['total_harga'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; }
        .navbar-main { background: #0f172a; padding: 15px 0; }
        .logo-raster { color: #2373F4; font-weight: 900; letter-spacing: -0.5px; }
        .logo-grafik { color: #ffffff; font-weight: 900; letter-spacing: -0.5px; }
        .card-custom { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .btn-checkout { background: #2373F4; color: white; border-radius: 30px; font-weight: 700; transition: 0.3s; }
        .btn-checkout:hover { background: #1d5ec9; color: white; }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar-main mb-4 shadow-sm">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="index.php" class="text-decoration-none fs-4">
                <span class="logo-raster">RASTER</span><span class="logo-grafik">GRAFIK</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="text-light text-decoration-none fw-medium"><i class="bi bi-shop me-1"></i> Katalog</a>
                <a href="pesanan_saya.php" class="text-light text-decoration-none fw-medium"><i class="bi bi-bag-check me-1"></i> Pesanan Saya</a>
                <span class="text-light fw-medium ms-2 border-start border-secondary ps-3"><i class="bi bi-person-circle me-1 text-primary"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'Pelanggan'); ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold px-3 ms-2">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- CONTAINER UTAMA -->
    <div class="container pb-5">
        <h3 class="fw-bold mb-4"><i class="bi bi-cart3 text-primary me-2"></i>Keranjang Belanja</h3>

        <?php if (empty($data_keranjang)): ?>
            <!-- KERANJANG KOSONG -->
            <div class="card-custom p-5 text-center">
                <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                <h5 class="fw-bold mt-3 text-secondary">Keranjang Belanja Anda Kosong</h5>
                <p class="text-muted">Anda belum menambahkan produk cetakan apapun ke dalam keranjang.</p>
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-arrow-left me-2"></i>Mulai Belanja</a>
                </div>
            </div>
        <?php else: ?>
            <form action="proses_keranjang_checkout.php" method="POST">
                <div class="row g-4">
                    <!-- LIST ITEM KERANJANG -->
                    <div class="col-lg-8">
                        <div class="card-custom p-4">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Daftar Produk (<?= count($data_keranjang); ?> Item)</h6>

                            <?php foreach ($data_keranjang as $item): 
                                // Gambar katalog thumbnail
                                $gbr_raw = $item['gambar_katalog'];
                                $dec = json_decode($gbr_raw, true);
                                $thumb = is_array($dec) ? ($dec[0] ?? 'default') : $gbr_raw;
                                $img_src = (strpos(trim($thumb), 'http') === 0) ? trim($thumb) : 'public/uploads/produk/' . trim($thumb);
                            ?>
                                <div class="card p-3 mb-3 border rounded-3 bg-light">
                                    <div class="row align-items-center g-3">
                                        <!-- Foto Thumbnail -->
                                        <div class="col-3 col-sm-2">
                                            <img src="<?= $img_src; ?>" class="rounded w-100" style="aspect-ratio: 1/1; object-fit: cover;" onerror="this.src='https://via.placeholder.com/100?text=No+Img'">
                                        </div>

                                        <!-- Detail Rincian -->
                                        <div class="col-9 col-sm-6">
                                            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($item['nama_produk']); ?></h6>
                                            
                                            <!-- Rincian Dimensi / Jumlah -->
                                            <div class="small text-secondary mb-1">
                                                <?php if ($item['tipe_harga'] == 'meteran'): ?>
                                                    <span><i class="bi bi-aspect-ratio me-1"></i> Ukuran: <b><?= $item['panjang']; ?>m × <?= $item['lebar']; ?>m</b> (<?= $item['panjang'] * $item['lebar']; ?> m²)</span>
                                                <?php else: ?>
                                                    <span><i class="bi bi-box me-1"></i> Jumlah: <b><?= $item['jumlah']; ?> Pcs</b></span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Rincian Spesifikasi Pilihan -->
                                            <?php 
                                                $spek_json = json_decode($item['spesifikasi_pilihan'], true);
                                                if (!empty($spek_json) && is_array($spek_json)):
                                            ?>
                                                <div class="bg-white p-2 rounded border small mb-2">
                                                    <?php foreach ($spek_json as $kategori => $nilai): ?>
                                                        <span class="d-block"><b><?= htmlspecialchars($kategori); ?>:</b> <?= htmlspecialchars($nilai); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php elseif (!empty($item['spesifikasi_pilihan'])): ?>
                                                <div class="small text-muted mb-1"><b>Spesifikasi:</b> <?= htmlspecialchars($item['spesifikasi_pilihan']); ?></div>
                                            <?php endif; ?>

                                            <!-- File Desain & Catatan -->
                                            <?php if (!empty($item['file_desain'])): ?>
                                                <small class="d-block text-primary"><i class="bi bi-file-earmark-arrow-up me-1"></i> Desain: <?= htmlspecialchars($item['file_desain']); ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($item['catatan'])): ?>
                                                <small class="d-block text-muted fst-italic">"<?= htmlspecialchars($item['catatan']); ?>"</small>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Harga & Tombol Hapus -->
                                        <div class="col-12 col-sm-4 text-sm-end border-top border-sm-0 pt-2 pt-sm-0 d-flex d-sm-block justify-content-between align-items-center">
                                            <div class="mb-sm-2">
                                                <span class="small text-muted d-block d-sm-none">Subtotal:</span>
                                                <strong class="text-primary fs-5">Rp <?= number_format($item['total_harga'], 0, ',', '.'); ?></strong>
                                            </div>
                                            <a href="?hapus=<?= $item['id_keranjang']; ?>" onclick="return confirm('Hapus item ini dari keranjang?');" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="mt-3">
                                <a href="index.php" class="text-decoration-none fw-bold small text-primary"><i class="bi bi-plus-circle me-1"></i> Tambah Produk Lain</a>
                            </div>
                        </div>
                    </div>

                    <!-- RINGKASAN PESANAN & CHECKOUT -->
                    <div class="col-lg-4">
                        <div class="card-custom p-4 sticky-top" style="top: 20px;">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Ringkasan Pembayaran</h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Subtotal</span>
                                <strong>Rp <?= number_format($total_keseluruhan, 0, ',', '.'); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Biaya Pengiriman</span>
                                <span class="text-success fw-bold">GRATIS / COD</span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fw-bold text-dark fs-6">Total Pembayaran</span>
                                <span class="fw-extrabold text-primary fs-4">Rp <?= number_format($total_keseluruhan, 0, ',', '.'); ?></span>
                            </div>

                            <!-- FORM INFORMASI PENGIRIMAN & PEMBAYARAN -->
                            <div class="mb-3">
                                <label class="fw-bold small mb-2 text-dark">Alamat Pengiriman</label>
                                <textarea name="alamat" class="form-control form-control-sm" rows="3" placeholder="Masukkan alamat lengkap pengiriman..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold small mb-2 text-dark">Metode Pembayaran</label>
                                <select name="metode_pembayaran" class="form-select form-select-sm" required>
                                    <option value="Transfer Bank (BCA / Mandiri)">Transfer Bank (BCA / Mandiri)</option>
                                    <option value="E-Wallet (Dana / OVO / QRIS)">E-Wallet (DANA / QRIS)</option>
                                    <option value="Bayar di Toko / COD">Bayar di Toko / COD</option>
                                </select>
                            </div>

                            <!-- TOMBOL SUBMIT KE PROSES CHECKOUT -->
                            <button type="submit" class="btn btn-checkout w-100 py-3 shadow-sm">
                                <i class="bi bi-credit-card me-2"></i>Buat Pesanan Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>