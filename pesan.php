<?php
session_start();
require_once 'config/database.php';

// Proteksi Halaman: Wajib Login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// 1. Tangkap ID Produk dari URL (Default ke ID 1 jika tidak diset)
$id_produk = isset($_GET['id_produk']) ? intval($_GET['id_produk']) : 1;

// 2. Ambil data produk dari database
$query_produk = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = $id_produk");
$produk = mysqli_fetch_assoc($query_produk);

// Jika ID produk tidak ditemukan di database, fallback ke produk pertama
if (!$produk) {
    $query_fallback = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk ASC LIMIT 1");
    $produk = mysqli_fetch_assoc($query_fallback);
}

// Ambil data user yang sedang login
$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user");
$user = mysqli_fetch_assoc($query_user);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Produk - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --biru-rg: #0088cc;
            --merah-rg: #cc0000;
        }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg); }
        .btn-primer { background-color: var(--merah-rg); color: white; font-weight: bold; }
        .btn-primer:hover { background-color: #aa0000; color: white; }
        .card-produk-header {
            background-color: #f8f9fa;
            border-bottom: 3px solid var(--biru-rg);
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
                <span class="me-3 fw-semibold">Halo, <?= htmlspecialchars($user['nama']); ?>!</span>
                <a href="dashboard_pelanggan.php" class="btn btn-outline-secondary btn-sm me-2">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-header card-produk-header p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-secondary mb-2 text-capitalize">
                                    Tipe Harga: <?= htmlspecialchars($produk['tipe_harga']); ?>
                                </span>
                                <h3 class="fw-bold mb-1"><?= htmlspecialchars($produk['nama_produk']); ?></h3>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($produk['deskripsi']); ?></p>
                            </div>
                            <div class="text-end">
                                <span class="small text-muted d-block">Harga Dasar:</span>
                                <span class="fw-bold fs-4 text-merah">
                                    Rp <?= number_format($produk['harga_dasar'], 0, ',', '.'); ?>
                                </span>
                                <small class="text-muted d-block">
                                    <?= ($produk['tipe_harga'] == 'meteran') ? '/ m²' : '/ pcs'; ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="proses_pesanan.php" method="POST" enctype="multipart/form-data">
                            
                            <!-- Input Hidden Data Produk -->
                            <input type="hidden" name="id_produk" value="<?= $produk['id_produk']; ?>">
                            <input type="hidden" id="harga_dasar" value="<?= $produk['harga_dasar']; ?>">
                            <input type="hidden" id="tipe_harga" value="<?= $produk['tipe_harga']; ?>">

                            <h5 class="fw-bold mb-3">1. Ukuran & Jumlah Cetakan</h5>
                            
                            <?php if ($produk['tipe_harga'] == 'meteran') : ?>
                                <div class="row g-3 mb-3" id="box_ukuran_meteran">
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-bold">Panjang (Meter)</label>
                                        <input type="number" step="0.1" min="0.1" name="panjang" id="panjang" class="form-control" value="1" required oninput="hitungTotal()">
                                        <small class="text-muted">Misal: 1.5 untuk 1,5 meter</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-bold">Lebar (Meter)</label>
                                        <input type="number" step="0.1" min="0.1" name="lebar" id="lebar" class="form-control" value="1" required oninput="hitungTotal()">
                                        <small class="text-muted">Misal: 1 untuk 1 meter</small>
                                    </div>
                                </div>
                            <?php else : ?>
                                <!-- Jika tipe harga satuan, panjang dan lebar diset default 1 -->
                                <input type="hidden" name="panjang" id="panjang" value="1">
                                <input type="hidden" name="lebar" id="lebar" value="1">
                                <div class="alert alert-info py-2 small mb-3">
                                    ℹ️ Produk ini dihitung dengan sistem harga per satuan (pcs/box).
                                </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Jumlah (QTY)</label>
                                <input type="number" min="1" name="qty" id="qty" class="form-control" value="1" style="max-width: 200px;" required oninput="hitungTotal()">
                            </div>

                            <hr class="my-4">

                            <h5 class="fw-bold mb-3">2. Pilihan Desain & File Cetak</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Opsi Desain</label>
                                <select name="opsi_desain" id="opsi_desain" class="form-select" onchange="hitungTotal()">
                                    <option value="upload_mandiri">Saya Sudah Punya Desain Siap Cetak (Gratis)</option>
                                    <option value="custom_desain">Request Custom Desain dari Tim Kami (+ Rp 50.000)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload File Desain / Referensi (Opsional)</label>
                                <input type="file" name="file_desain" class="form-control" accept="image/*,.pdf,.zip,.rar,.psd,.ai">
                                <small class="text-muted">Format yang didukung: JPG, PNG, PDF, ZIP, PSD, AI (Maksimal 10MB)</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Catatan / Instruksi Cetak</label>
                                <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Tolong tambahkan mata ayam/lubang spanduk di tiap sudut..."></textarea>
                            </div>

                            <hr class="my-4">

                            <h5 class="fw-bold mb-3">3. Pengiriman</h5>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Metode Pengiriman</label>
                                <select name="pengiriman" class="form-select" required>
                                    <option value="ambil_sendiri">Ambil Sendiri di Toko Raster Grafik</option>
                                    <!-- Value di bawah ini diperbarui menjadi kirim_ojol -->
                                    <option value="kirim_ojol">Kirim via Ojek Online (Ongkir COD ke Driver)</option>
                                </select>
                            </div>

                            <!-- BOX RINCIAN HARGA -->
                            <div class="card bg-light border-0 p-3 mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal Cetak:</span>
                                    <span class="fw-bold" id="text_subtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Biaya Custom Desain:</span>
                                    <span class="fw-bold" id="text_biaya_desain">Rp 0</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">Total Estimated Tagihan:</span>
                                    <span class="fw-bold fs-3 text-merah" id="text_total_tagihan">Rp 0</span>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="persetujuan" required>
                                <label class="form-check-label small" for="persetujuan">
                                    Saya menyetujui bahwa data spesifikasi ukuran dan file yang saya kirimkan sudah benar.
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primer w-100 py-3 rounded-pill fs-5">
                                Buat Pesanan & Bayar Now
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- SCRIPT HITUNG HARGA OTOMATIS -->
    <script>
        function hitungTotal() {
            let hargaDasar = parseFloat(document.getElementById('harga_dasar').value) || 0;
            let tipeHarga = document.getElementById('tipe_harga').value;
            
            let panjang = parseFloat(document.getElementById('panjang').value) || 1;
            let lebar = parseFloat(document.getElementById('lebar').value) || 1;
            let qty = parseInt(document.getElementById('qty').value) || 1;
            let opsiDesain = document.getElementById('opsi_desain').value;

            let subtotal = 0;
            if (tipeHarga === 'meteran') {
                subtotal = panjang * lebar * qty * hargaDasar;
            } else {
                subtotal = qty * hargaDasar;
            }

            let biayaDesain = (opsiDesain === 'custom_desain') ? 50000 : 0;
            let totalTagihan = subtotal + biayaDesain;

            // Format ke IDR Rupiah
            document.getElementById('text_subtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('text_biaya_desain').innerText = 'Rp ' + biayaDesain.toLocaleString('id-ID');
            document.getElementById('text_total_tagihan').innerText = 'Rp ' + totalTagihan.toLocaleString('id-ID');
        }

        // Jalankan perhitungan awal saat halaman pertama kali dibuka
        window.onload = function() {
            hitungTotal();
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>