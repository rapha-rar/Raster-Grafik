<?php
session_start();
require_once 'config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = intval($_SESSION['id_user']);

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
$jumlah_keranjang = count($data_keranjang);
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
        :root {
            --color-1: #2373F4; --color-2: #578EF5; --color-3: #65D0F4;
            --grad-primary: linear-gradient(135deg, var(--color-1) 0%, var(--color-2) 100%);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #ffffff 100%);
            background-attachment: fixed; min-height: 100vh; position: relative; overflow-x: hidden;
        }

        .bg-shape1 { position: fixed; top: -10%; left: -10%; width: max(350px, 45vw); height: max(350px, 45vw); border-radius: 50%; background: var(--color-1); filter: blur(120px); opacity: 0.15; z-index: -1; }
        .bg-shape2 { position: fixed; bottom: -10%; right: -10%; width: max(400px, 50vw); height: max(400px, 50vw); border-radius: 50%; background: var(--color-3); filter: blur(120px); opacity: 0.18; z-index: -1; }

        .logo-raster { color: var(--color-1); font-weight: 900; letter-spacing: -0.5px; }
        .logo-grafik { color: #0f172a; font-weight: 900; letter-spacing: -0.5px; }
        .section-title-black { color: #0f172a !important; font-weight: 800; }

        /* NAVBAR (sama dengan index.php) */
        .navbar { background: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(25px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        /* Tinggi header disamakan dengan index.php (tinggi baris logo fs-3 = 52px) */
        .navbar > .container { min-height: 52px; }

        /* TOMBOL */
        .btn-grad-primary { background: var(--grad-primary); color: white; border: none; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 15px rgba(35, 115, 244, 0.3); transition: 0.3s; }
        .btn-grad-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(35, 115, 244, 0.5); color: white; }

        /* KARTU (sama dengan .card-glass di index.php) */
        .card-glass { background: #ffffff; border: 1px solid rgba(87, 142, 245, 0.2); box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05); border-radius: 24px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; }
        .card-glass.hoverable:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(35, 115, 244, 0.15); border-color: var(--color-2); }

        .item-img { width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 18px; background: #f1f5f9; }
        .spek-box { background: #f8fafc; border: 1px solid rgba(87, 142, 245, 0.2); border-radius: 12px; }
        .harga-biru { color: var(--color-1); }
        .btn-hapus { border-radius: 50px; font-weight: 700; }

        .floating-wa { position: fixed; bottom: 25px; right: 25px; background: linear-gradient(135deg, #22c55e, #15803d); color: white; border-radius: 50px; padding: 14px 24px; box-shadow: 0 8px 25px rgba(34,197,94,0.4); z-index: 1000; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .floating-wa:hover { transform: translateY(-4px) scale(1.05); color: white; box-shadow: 0 12px 30px rgba(34,197,94,0.6); }
    </style>
</head>
<body>

    <div class="bg-shape1"></div><div class="bg-shape2"></div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg py-3 sticky-top">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="index.php" class="btn btn-light bg-white rounded-pill btn-sm px-4 fw-bold shadow-sm border" style="color: var(--color-1);"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a href="dashboard_pelanggan.php" class="btn btn-light bg-white rounded-pill btn-sm px-4 fw-bold shadow-sm border" style="color: var(--color-1);">Dashboard</a>
            </div>
        </div>
    </nav>

    <!-- CONTAINER UTAMA -->
    <div class="container py-4 pb-5">
        <div class="d-flex align-items-center mb-4">
            <h4 class="section-title-black m-0 fs-5 fs-md-4">
                <i class="bi bi-cart3 me-2" style="color: var(--color-1);"></i>Keranjang Belanja
            </h4>
            <div class="ms-4 flex-grow-1 rounded-pill" style="height: 3px; background: linear-gradient(90deg, var(--color-2) 0%, transparent 100%); opacity: 0.5;"></div>
        </div>

        <?php if (empty($data_keranjang)): ?>
            <!-- KERANJANG KOSONG -->
            <div class="card-glass p-5 text-center">
                <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                <h5 class="fw-bold mt-3 text-secondary">Keranjang Belanja Anda Kosong</h5>
                <p class="text-muted fw-medium">Anda belum menambahkan produk cetakan apapun ke dalam keranjang.</p>
                <div class="mt-4">
                    <a href="index.php" class="btn btn-grad-primary px-4 py-2"><i class="bi bi-arrow-left me-2"></i>Mulai Belanja</a>
                </div>
            </div>
        <?php else: ?>
            <form action="proses_keranjang_checkout.php" method="POST">
                <div class="row g-4">
                    <!-- LIST ITEM KERANJANG -->
                    <div class="col-lg-8">
                        <div class="card-glass p-4">
                            <h6 class="fw-bold mb-3 pb-2" style="border-bottom: 1px solid rgba(87,142,245,0.2);">Daftar Produk (<?= $jumlah_keranjang; ?> Item)</h6>

                            <?php foreach ($data_keranjang as $item):
                                $gbr_raw = $item['gambar_katalog'];
                                $dec = json_decode($gbr_raw, true);
                                $thumb = is_array($dec) ? ($dec[0] ?? 'default') : $gbr_raw;
                                $img_src = (strpos(trim($thumb), 'http') === 0) ? trim($thumb) : 'public/uploads/produk/' . trim($thumb);
                            ?>
                                <div class="card-glass hoverable p-3 mb-3">
                                    <div class="row align-items-center g-3">
                                        <!-- Foto -->
                                        <div class="col-3 col-sm-2">
                                            <img src="<?= htmlspecialchars($img_src); ?>" class="item-img" alt="Produk" onerror="this.src='https://via.placeholder.com/100/f1f5f9/0f172a?text=No+Img'">
                                        </div>

                                        <!-- Detail -->
                                        <div class="col-9 col-sm-6">
                                            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($item['nama_produk']); ?></h6>

                                            <div class="small text-secondary mb-2 fw-medium">
                                                <?php if ($item['tipe_harga'] == 'meteran'): ?>
                                                    <span><i class="bi bi-aspect-ratio me-1"></i> Ukuran: <b><?= $item['panjang']; ?>m × <?= $item['lebar']; ?>m</b> (<?= $item['panjang'] * $item['lebar']; ?> m²)</span>
                                                <?php else: ?>
                                                    <span><i class="bi bi-box me-1"></i> Jumlah: <b><?= $item['jumlah']; ?> Pcs</b></span>
                                                <?php endif; ?>
                                            </div>

                                            <?php
                                                $spek_json = json_decode($item['spesifikasi_pilihan'], true);
                                                if (!empty($spek_json) && is_array($spek_json)):
                                            ?>
                                                <div class="spek-box p-2 small mb-2">
                                                    <?php foreach ($spek_json as $kategori => $nilai): ?>
                                                        <span class="d-block"><b><?= htmlspecialchars($kategori); ?>:</b> <?= htmlspecialchars($nilai); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php elseif (!empty($item['spesifikasi_pilihan'])): ?>
                                                <div class="small text-muted mb-1"><b>Spesifikasi:</b> <?= htmlspecialchars($item['spesifikasi_pilihan']); ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($item['file_desain'])): ?>
                                                <small class="d-block harga-biru fw-medium"><i class="bi bi-file-earmark-arrow-up me-1"></i> Desain: <?= htmlspecialchars($item['file_desain']); ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($item['catatan'])): ?>
                                                <small class="d-block text-muted fst-italic">"<?= htmlspecialchars($item['catatan']); ?>"</small>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Harga & Hapus -->
                                        <div class="col-12 col-sm-4 text-sm-end pt-2 pt-sm-0 d-flex d-sm-block justify-content-between align-items-center">
                                            <div class="mb-sm-2">
                                                <span class="small text-muted d-block d-sm-none">Subtotal:</span>
                                                <strong class="harga-biru fs-5">Rp <?= number_format($item['total_harga'], 0, ',', '.'); ?></strong>
                                            </div>
                                            <a href="?hapus=<?= $item['id_keranjang']; ?>" onclick="return confirm('Hapus item ini dari keranjang?');" class="btn btn-sm btn-outline-danger btn-hapus px-3">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="mt-3">
                                <a href="index.php" class="text-decoration-none fw-bold small harga-biru"><i class="bi bi-plus-circle me-1"></i> Tambah Produk Lain</a>
                            </div>
                        </div>
                    </div>

                    <!-- RINGKASAN & CHECKOUT -->
                    <div class="col-lg-4">
                        <div class="card-glass p-4 sticky-top" style="top: 100px;">
                            <h6 class="fw-bold mb-3 pb-2" style="border-bottom: 1px solid rgba(87,142,245,0.2);">Ringkasan Pembayaran</h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted fw-medium">Total Subtotal</span>
                                <strong>Rp <?= number_format($total_keseluruhan, 0, ',', '.'); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted fw-medium">Biaya Pengiriman</span>
                                <span class="text-success fw-bold">GRATIS / COD</span>
                            </div>

                            <hr style="border-color: rgba(87,142,245,0.3);">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fw-bold text-dark fs-6">Total Pembayaran</span>
                                <span class="fw-bold harga-biru fs-4">Rp <?= number_format($total_keseluruhan, 0, ',', '.'); ?></span>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold small mb-2 text-dark">Alamat Pengiriman</label>
                                <textarea name="alamat" class="form-control form-control-sm" style="border-radius: 12px;" rows="3" placeholder="Masukkan alamat lengkap pengiriman..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold small mb-2 text-dark">Metode Pembayaran</label>
                                <select name="metode_pembayaran" class="form-select form-select-sm" style="border-radius: 12px;" required>
                                    <option value="Transfer Bank (BCA / Mandiri)">Transfer Bank (BCA / Mandiri)</option>
                                    <option value="E-Wallet (Dana / OVO / QRIS)">E-Wallet (DANA / QRIS)</option>
                                    <option value="Bayar di Toko / COD">Bayar di Toko / COD</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-grad-primary w-100 py-3">
                                <i class="bi bi-credit-card me-2"></i>Buat Pesanan Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <a href="https://wa.me/6281234567890?text=Halo%20Admin..." class="floating-wa" target="_blank"><i class="bi bi-whatsapp fs-4"></i><span class="d-none d-md-inline">Chat Admin</span></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>