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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pesan Produk - Raster Grafik</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; --bg-light: #f4f7f6; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--bg-light); 
            color: #333; 
            overflow-x: hidden; 
        }
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

        input[type="file"] {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        input[type="file"]::file-selector-button {
            padding: 0.4em 0.8em;
            font-size: 0.8rem;
            margin-right: 10px;
        }

        .option-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #fff;
        }
        .option-card:hover { border-color: var(--biru-rg); background-color: #f8fbfe; }
        .btn-check:checked + .option-card {
            border-color: var(--biru-rg);
            background-color: #e6f4fc;
            box-shadow: 0 4px 12px rgba(0,136,204,0.15);
        }

        @media (max-width: 767.98px) {
            .card-body { padding: 1.25rem !important; }
            .option-card { margin-bottom: 6px; }
            #qty { max-width: 100% !important; }
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

    <!-- NAVBAR BERSIH (TANPA NAMA USER) -->
    <nav class="navbar bg-white shadow-sm mb-4 py-3">
        <div class="container d-flex justify-content-center justify-content-md-start">
            <a class="navbar-brand fw-bold fs-4 mb-0" href="index.php">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span>
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- BARIS NAVIGASI SUB-HEADER -->
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 px-1">
                    <a href="index.php" class="btn btn-light border btn-sm px-3 rounded-pill text-muted shadow-sm text-nowrap">
                        <i class="bi bi-arrow-left me-1"></i> <span class="d-none d-sm-inline">Kembali ke Beranda</span><span class="d-inline d-sm-none">Batal</span>
                    </a>
                    <span class="text-muted small fw-medium text-nowrap">
                        <i class="bi bi-cart3 me-1"></i> Form Pesanan
                    </span>
                </div>

                <div class="card card-custom overflow-hidden mb-4">
                    
                    <!-- HEADER PRODUK -->
                    <div class="card-header card-produk-header p-3 p-md-4 pb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                            <div class="w-100">
                                <span class="badge bg-secondary rounded-pill mb-2 text-capitalize fw-normal px-3 py-2">
                                    <i class="bi bi-tag-fill me-1"></i> Tipe: <?= htmlspecialchars($produk['tipe_harga']); ?>
                                </span>
                                <h3 class="fw-bold mb-1 fs-5 fs-md-4"><?= htmlspecialchars($produk['nama_produk']); ?></h3>
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> <?= htmlspecialchars($produk['deskripsi']); ?></p>
                            </div>
                            
                            <div class="bg-light p-3 rounded-4 border w-100" style="max-width: 100%; flex-shrink: 0; flex-basis: auto;">
                                <div class="d-flex flex-md-column justify-content-between align-items-center align-items-md-end text-md-end">
                                    <span class="small text-muted fw-medium mb-0 mb-md-1" style="font-size:0.75rem;">Harga Dasar:</span>
                                    <div>
                                        <span class="fw-bold fs-5 text-merah">Rp <?= number_format($produk['harga_dasar'], 0, ',', '.'); ?></span>
                                        <small class="text-muted fw-medium ms-1 ms-md-0 d-md-block" style="font-size:0.75rem;"><?= ($produk['tipe_harga'] == 'meteran') ? '/ m²' : '/ pcs'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-5">
                        <form action="proses_pesanan.php" method="POST" enctype="multipart/form-data">
                            
                            <input type="hidden" name="id_produk" value="<?= $produk['id_produk']; ?>">
                            <input type="hidden" id="harga_dasar" value="<?= $produk['harga_dasar']; ?>">
                            <input type="hidden" id="tipe_harga" value="<?= $produk['tipe_harga']; ?>">

                            <h5 class="fw-bold mb-3 text-dark fs-6 fs-md-5"><i class="bi bi-rulers me-2 text-biru fs-5 align-middle"></i> 1. Ukuran & Jumlah Cetakan</h5>
                            
                            <?php if ($produk['tipe_harga'] == 'meteran') : ?>
                                <div class="row g-2 g-md-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-medium small">Panjang</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.1" min="0.1" name="panjang" id="panjang" class="form-control" value="1" required oninput="hitungTotal()">
                                            <span class="input-group-text bg-light text-muted px-2">m</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-medium small">Lebar</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.1" min="0.1" name="lebar" id="lebar" class="form-control" value="1" required oninput="hitungTotal()">
                                            <span class="input-group-text bg-light text-muted px-2">m</span>
                                        </div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <input type="hidden" name="panjang" id="panjang" value="1">
                                <input type="hidden" name="lebar" id="lebar" value="1">
                                <div class="alert alert-info py-2 small mb-3 border-0 bg-info bg-opacity-10 text-info-emphasis rounded-3" style="font-size:0.8rem;">
                                    <i class="bi bi-info-circle-fill me-1"></i> Produk ini dihitung per satuan (pcs/box).
                                </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <label class="form-label fw-medium small">Jumlah (QTY)</label>
                                <input type="number" min="1" name="qty" id="qty" class="form-control form-control-sm" value="1" style="max-width: 150px;" required oninput="hitungTotal()">
                            </div>

                            <hr class="my-4 border-light">

                            <h5 class="fw-bold mb-3 text-dark fs-6 fs-md-5"><i class="bi bi-image me-2 text-biru fs-5 align-middle"></i> 2. Desain & File Cetak</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-medium small">Pilih Opsi Desain</label>
                                <div class="row g-2">
                                    <div class="col-12 col-sm-6">
                                        <input type="radio" class="btn-check" name="opsi_desain" id="desain_mandiri" value="upload_mandiri" checked onchange="hitungTotal()">
                                        <label class="option-card d-block h-100" for="desain_mandiri">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge bg-success" style="font-size:0.65rem;">Gratis</span>
                                                <i class="bi bi-file-earmark-check text-success fs-5"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1 fs-6">Desain Mandiri</h6>
                                            <small class="text-muted d-block lh-sm" style="font-size:0.75rem;">Sudah punya file siap cetak</small>
                                        </label>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input type="radio" class="btn-check" name="opsi_desain" id="desain_custom" value="custom_desain" onchange="hitungTotal()">
                                        <label class="option-card d-block h-100" for="desain_custom">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge bg-info text-dark" style="font-size:0.65rem;">+ Rp 50.000</span>
                                                <i class="bi bi-palette text-info fs-5"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1 fs-6">Request Desain</h6>
                                            <small class="text-muted d-block lh-sm" style="font-size:0.75rem;">Dibuatkan oleh tim kami</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 w-100">
                                <label class="form-label fw-medium small">Upload File Desain (Opsional)</label>
                                <input type="file" name="file_desain" class="form-control form-control-sm w-100" accept="image/*,.pdf,.zip,.rar,.psd,.ai">
                                <small class="text-muted d-block mt-1" style="font-size:0.7rem;"><i class="bi bi-info-circle me-1"></i> JPG, PNG, PDF, ZIP, PSD, AI (Max 10MB)</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium small">Catatan Tambahan</label>
                                <textarea name="catatan" class="form-control form-control-sm" rows="3" placeholder="Contoh: Tambahkan mata ayam..."></textarea>
                            </div>

                            <hr class="my-4 border-light">

                            <h5 class="fw-bold mb-3 text-dark fs-6 fs-md-5"><i class="bi bi-truck me-2 text-biru fs-5 align-middle"></i> 3. Pengiriman</h5>
                            <div class="mb-4">
                                <label class="form-label fw-medium small">Metode Pengiriman</label>
                                <select name="pengiriman" class="form-select form-select-sm w-100" required>
                                    <option value="ambil_sendiri">Ambil Sendiri di Toko</option>
                                    <option value="kirim_ojol">Kirim via Ojol (Ongkir COD)</option>
                                </select>
                            </div>

                            <hr class="my-4 border-light">
 
                            <h5 class="fw-bold mb-3 text-dark fs-6 fs-md-5"><i class="bi bi-wallet2 me-2 text-biru fs-5 align-middle"></i> 4. Metode Pembayaran</h5>
                            <div class="row g-2 mb-4">
                                <div class="col-12 col-sm-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_qris" value="qris" checked>
                                    <label class="option-card d-block h-100" for="pay_qris">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="badge bg-danger" style="font-size:0.65rem;">Instant</span>
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg?utm_source=id.wikipedia.org&utm_campaign=imageinfo&utm_content=original" height="18" alt="QRIS">
                                        </div>
                                        <h6 class="fw-bold mb-1 fs-6">QRIS</h6>
                                        <small class="text-muted d-block lh-sm" style="font-size:0.7rem;">GoPay, OVO, DANA, m-Banking</small>
                                    </label>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_bca" value="bca">
                                    <label class="option-card d-block h-100" for="pay_bca">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="badge bg-primary" style="font-size:0.65rem;">Bank</span>
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg?utm_source=id.wikipedia.org&utm_campaign=index&utm_content=original" height="18" alt="BCA">
                                        </div>
                                        <h6 class="fw-bold mb-1 fs-6">Transfer BCA</h6>
                                        <small class="text-muted d-block lh-sm" style="font-size:0.7rem;">Rek 123 456 7890</small>
                                    </label>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_bri" value="bri">
                                    <label class="option-card d-block h-100" for="pay_bri">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="badge bg-primary" style="font-size:0.65rem;">Bank</span>
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/59/BRI_2025.svg?utm_source=id.wikipedia.org&utm_campaign=index&utm_content=original" height="18" alt="BRI">
                                        </div>
                                        <h6 class="fw-bold mb-1 fs-6">Transfer BRI</h6>
                                        <small class="text-muted d-block lh-sm" style="font-size:0.7rem;">Rek 0987 6543 2109 876</small>
                                    </label>
                                </div>
                            </div>

                            <div class="card bg-light border-0 rounded-4 p-3 p-md-4 mb-4">
                                <div class="d-flex justify-content-between mb-2 text-muted fw-medium small">
                                    <span>Subtotal Cetak:</span><span id="text_subtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 text-muted fw-medium small">
                                    <span>Biaya Custom Desain:</span><span id="text_biaya_desain">Rp 0</span>
                                </div>
                                <div class="border-top border-secondary opacity-25 mb-3"></div>
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
                                    <span class="fw-bold fs-6 text-dark mb-1 mb-sm-0">Total Tagihan:</span>
                                    <span class="fw-bold fs-3 text-merah" id="text_total_tagihan">Rp 0</span>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="persetujuan" required>
                                <label class="form-check-label text-muted lh-sm" for="persetujuan" style="font-size:0.75rem;">
                                    Saya menyetujui bahwa data spesifikasi ukuran, file, dan metode pembayaran yang saya pilih sudah benar.
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primer w-100 py-3 fs-6 shadow-sm">
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
            
            let opsiDesainElem = document.querySelector('input[name="opsi_desain"]:checked');
            let opsiDesain = opsiDesainElem ? opsiDesainElem.value : 'upload_mandiri';

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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>