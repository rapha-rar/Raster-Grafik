<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }

$id_produk = isset($_GET['id_produk']) ? intval($_GET['id_produk']) : 1;
$query_produk = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = $id_produk");
$produk = mysqli_fetch_assoc($query_produk);

if (!$produk) {
    $query_fallback = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk ASC LIMIT 1");
    $produk = mysqli_fetch_assoc($query_fallback);
}

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
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
        .card-produk-header { background-color: white; border-bottom: 4px solid var(--biru-rg); border-radius: 16px 16px 0 0 !important; }
        .btn-primer { background-color: var(--merah-rg); color: white; font-weight: 500; border-radius: 30px; transition: all 0.3s ease; }
        .btn-primer:hover { background-color: #aa0000; color: white; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(204,0,0,0.2); }
        .form-control, .form-select { border-radius: 8px; padding: 0.6rem 1rem; border: 1px solid #dee2e6; }
        .form-control:focus, .form-select:focus { border-color: var(--biru-rg); box-shadow: 0 0 0 0.25rem rgba(0,136,204,0.25); }

        /* Card Radio Payment Selection */
        .payment-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .payment-card:hover {
            border-color: var(--biru-rg);
            background-color: #f8fbfe;
        }
        .btn-check:checked + .payment-card {
            border-color: var(--biru-rg);
            background-color: #e6f4fc;
            box-shadow: 0 4px 12px rgba(0,136,204,0.15);
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
                <span class="me-2 text-muted d-none d-md-block"><i class="bi bi-person-circle me-1"></i> Halo, <?= htmlspecialchars($user['nama']); ?></span>
                <a href="index.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i> Beranda
                </a>
                <a href="dashboard_pelanggan.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="index.php" class="btn btn-light border btn-sm px-3 rounded-pill text-muted shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                    </a>
                    <span class="text-muted small"><i class="bi bi-cart3 me-1"></i> Form Pemesanan Produk</span>
                </div>

                <div class="card card-custom overflow-hidden mb-4">
                    <div class="card-header card-produk-header p-4 pb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-secondary rounded-pill mb-2 text-capitalize fw-normal px-3 py-2">
                                    <i class="bi bi-tag-fill me-1"></i> Tipe: <?= htmlspecialchars($produk['tipe_harga']); ?>
                                </span>
                                <h3 class="fw-bold mb-1"><?= htmlspecialchars($produk['nama_produk']); ?></h3>
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> <?= htmlspecialchars($produk['deskripsi']); ?></p>
                            </div>
                            <div class="text-end bg-light p-3 rounded-4 border">
                                <span class="small text-muted d-block fw-medium">Harga Dasar:</span>
                                <span class="fw-bold fs-3 text-merah">Rp <?= number_format($produk['harga_dasar'], 0, ',', '.'); ?></span>
                                <small class="text-muted d-block fw-medium"><?= ($produk['tipe_harga'] == 'meteran') ? '/ m²' : '/ pcs'; ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="proses_pesanan.php" method="POST" enctype="multipart/form-data">
                            
                            <input type="hidden" name="id_produk" value="<?= $produk['id_produk']; ?>">
                            <input type="hidden" id="harga_dasar" value="<?= $produk['harga_dasar']; ?>">
                            <input type="hidden" id="tipe_harga" value="<?= $produk['tipe_harga']; ?>">

                            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-rulers me-2 text-biru fs-4 align-middle"></i> 1. Ukuran & Jumlah Cetakan</h5>
                            
                            <?php if ($produk['tipe_harga'] == 'meteran') : ?>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Panjang (Meter)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.1" min="0.1" name="panjang" id="panjang" class="form-control" value="1" required oninput="hitungTotal()">
                                            <span class="input-group-text bg-light text-muted">m</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Lebar (Meter)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.1" min="0.1" name="lebar" id="lebar" class="form-control" value="1" required oninput="hitungTotal()">
                                            <span class="input-group-text bg-light text-muted">m</span>
                                        </div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <input type="hidden" name="panjang" id="panjang" value="1">
                                <input type="hidden" name="lebar" id="lebar" value="1">
                                <div class="alert alert-info py-2 small mb-3 border-0 bg-info bg-opacity-10 text-info-emphasis rounded-3">
                                    <i class="bi bi-info-circle-fill me-1"></i> Produk ini dihitung dengan sistem harga per satuan (pcs/box).
                                </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <label class="form-label fw-medium">Jumlah (QTY)</label>
                                <input type="number" min="1" name="qty" id="qty" class="form-control" value="1" style="max-width: 150px;" required oninput="hitungTotal()">
                            </div>

                            <hr class="my-5 border-light">

                            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-image me-2 text-biru fs-4 align-middle"></i> 2. Desain & File Cetak</h5>
                            
                            <div class="mb-4">
                                <label class="form-label fw-medium">Opsi Desain</label>
                                <select name="opsi_desain" id="opsi_desain" class="form-select" onchange="hitungTotal()">
                                    <option value="upload_mandiri">Saya Sudah Punya Desain Siap Cetak (Gratis)</option>
                                    <option value="custom_desain">Request Custom Desain dari Tim Kami (+ Rp 50.000)</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Upload File Desain / Referensi (Opsional)</label>
                                <input type="file" name="file_desain" class="form-control" accept="image/*,.pdf,.zip,.rar,.psd,.ai">
                                <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i> Format: JPG, PNG, PDF, ZIP, PSD, AI (Max 10MB)</small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Catatan / Instruksi Cetak</label>
                                <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Tolong tambahkan mata ayam di tiap sudut..."></textarea>
                            </div>

                            <hr class="my-5 border-light">

                            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-truck me-2 text-biru fs-4 align-middle"></i> 3. Pengiriman</h5>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Metode Pengiriman</label>
                                <select name="pengiriman" class="form-select" required>
                                    <option value="ambil_sendiri">Ambil Sendiri di Toko Raster Grafik</option>
                                    <option value="kirim_ojol">Kirim via Ojek Online (Ongkir COD ke Driver)</option>
                                </select>
                            </div>

                            <hr class="my-5 border-light">

                            <!-- OPSI PEMBAYARAN BARU -->
                            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-wallet2 me-2 text-biru fs-4 align-middle"></i> 4. Metode Pembayaran</h5>
                            <div class="row g-3 mb-5">
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_qris" value="qris" checked>
                                    <label class="payment-card d-block h-100" for="pay_qris">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-danger">Scan QR</span>
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg?utm_source=id.wikipedia.org&utm_campaign=index&utm_content=original" height="16" alt="QRIS">
                                        </div>
                                        <h6 class="fw-bold mb-1">QRIS</h6>
                                        <small class="text-muted d-block" style="font-size:0.75rem;">Scan via GoPay, OVO, DANA, ShopeePay, m-Banking</small>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_bca" value="bca">
                                    <label class="payment-card d-block h-100" for="pay_bca">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-primary">Bank</span>
                                            <img src="https://thumb.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/1920px-Bank_Central_Asia.svg.png?utm_source=id.wikipedia.org&utm_campaign=index&utm_content=thumbnail" height="16" alt="BCA">
                                        </div>
                                        <h6 class="fw-bold mb-1">Transfer BCA</h6>
                                        <small class="text-muted d-block" style="font-size:0.75rem;">Rekening 123 456 7890 a.n PT Raster Grafik</small>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_bri" value="bri">
                                    <label class="payment-card d-block h-100" for="pay_bri">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-primary">Bank</span>
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/68/BANK_BRI_logo.svg?utm_source=commons.wikimedia.org&utm_campaign=imageinfo&utm_content=original" height="16" alt="BRI">
                                        </div>
                                        <h6 class="fw-bold mb-1">Transfer BRI</h6>
                                        <small class="text-muted d-block" style="font-size:0.75rem;">Rekening 0987 6543 2109 876 a.n PT Raster Grafik</small>
                                    </label>
                                </div>
                            </div>

                            <!-- BOX RINCIAN HARGA -->
                            <div class="card bg-light border-0 rounded-4 p-4 mb-4">
                                <div class="d-flex justify-content-between mb-2 text-muted fw-medium">
                                    <span>Subtotal Cetak:</span><span id="text_subtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 text-muted fw-medium">
                                    <span>Biaya Custom Desain:</span><span id="text_biaya_desain">Rp 0</span>
                                </div>
                                <div class="border-top border-secondary opacity-25 mb-3"></div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5 text-dark">Total Tagihan:</span>
                                    <span class="fw-bold fs-2 text-merah" id="text_total_tagihan">Rp 0</span>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="persetujuan" required>
                                <label class="form-check-label small text-muted" for="persetujuan">
                                    Saya menyetujui bahwa data spesifikasi ukuran, file, dan metode pembayaran yang saya pilih sudah benar.
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primer w-100 py-3 fs-5 shadow-sm">
                                <i class="bi bi-cart-check-fill me-1"></i> Buat Pesanan Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function hitungTotal() {
            let hargaDasar = parseFloat(document.getElementById('harga_dasar').value) || 0;
            let tipeHarga = document.getElementById('tipe_harga').value;
            let panjang = parseFloat(document.getElementById('panjang').value) || 1;
            let lebar = parseFloat(document.getElementById('lebar').value) || 1;
            let qty = parseInt(document.getElementById('qty').value) || 1;
            let opsiDesain = document.getElementById('opsi_desain').value;

            let subtotal = (tipeHarga === 'meteran') ? (panjang * lebar * qty * hargaDasar) : (qty * hargaDasar);
            let biayaDesain = (opsiDesain === 'custom_desain') ? 50000 : 0;
            let totalTagihan = subtotal + biayaDesain;

            document.getElementById('text_subtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('text_biaya_desain').innerText = 'Rp ' + biayaDesain.toLocaleString('id-ID');
            document.getElementById('text_total_tagihan').innerText = 'Rp ' + totalTagihan.toLocaleString('id-ID');
        }
        window.onload = hitungTotal;

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>