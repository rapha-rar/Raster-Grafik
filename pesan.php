<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }

$id_produk = intval($_GET['id_produk'] ?? 1);
$produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = $id_produk"));
if (!$produk) { header("Location: index.php"); exit(); }

$id_user = $_SESSION['id_user'];
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user"));

// DECODE JSON SPESIFIKASI
$spesifikasi = json_decode($produk['spesifikasi_json'] ?? '{}', true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Produk - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { 
            --color-1: #2373F4; --color-2: #578EF5; --color-3: #65D0F4;
            --grad-primary: linear-gradient(135deg, var(--color-1) 0%, var(--color-2) 100%); 
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #0f172a; 
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #ffffff 100%);
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
        }
        
        .bg-shape1 { position: fixed; top: -10%; left: -10%; width: max(350px, 45vw); height: max(350px, 45vw); border-radius: 50%; background: var(--color-1); filter: blur(120px); opacity: 0.15; z-index: -1; }
        .bg-shape2 { position: fixed; bottom: -10%; right: -10%; width: max(400px, 50vw); height: max(400px, 50vw); border-radius: 50%; background: var(--color-3); filter: blur(120px); opacity: 0.18; z-index: -1; }

        .card-glass { background: #ffffff; border: 1px solid rgba(87, 142, 245, 0.25); box-shadow: 0 15px 35px rgba(15, 23, 42, 0.05); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; }
        
        .form-control, .form-select { background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 12px; padding: 0.75rem 1rem; color: #0f172a; font-weight: 600; transition: 0.3s; }
        .form-control:focus, .form-select:focus { background: #ffffff; border-color: var(--color-2); box-shadow: 0 0 0 4px rgba(101, 208, 244, 0.3); }
        
        .btn-grad-primary { background: var(--grad-primary); color: white; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 15px rgba(35, 115, 244, 0.3); border: none; transition: 0.3s; }
        .btn-grad-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(35, 115, 244, 0.5); color: white; }
        
        .btn-outline-grad { background: #ffffff; color: var(--color-1); border: 2px solid var(--color-2); border-radius: 50px; font-weight: 700; transition: 0.3s; }
        .btn-outline-grad:hover { background: var(--grad-primary); color: white; border-color: transparent; }

        .section-title { font-size: 1.1rem; color: var(--color-1); font-weight: 800; border-bottom: 2px dashed rgba(87, 142, 245, 0.3); padding-bottom: 0.8rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="bg-shape1"></div><div class="bg-shape2"></div>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4"><a href="index.php" class="btn btn-light bg-white rounded-pill border shadow-sm px-4 fw-bold" style="color: var(--color-1);"><i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda</a></div>
                
                <div class="card-glass">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3">
                        <div>
                            <h3 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($produk['nama_produk']); ?></h3>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($produk['deskripsi']); ?></p>
                        </div>
                        <div class="text-end bg-light border p-3 rounded-4 shadow-sm">
                            <span class="text-muted small fw-bold">Harga Dasar</span><br>
                            <span class="fw-bold fs-4" style="color: var(--color-1);">Rp <?= number_format($produk['harga_dasar'],0,',','.'); ?></span><small class="fw-medium">/ <?= $produk['tipe_harga']=='meteran'?'m²':'pcs'; ?></small>
                        </div>
                    </div>

                    <form action="proses_keranjang_checkout.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_produk" value="<?= $produk['id_produk']; ?>">
                        <input type="hidden" name="tipe_harga" value="<?= $produk['tipe_harga']; ?>">
                        <input type="hidden" name="harga_dasar" value="<?= $produk['harga_dasar']; ?>">

                        <!-- 1. BAGIAN SPESIFIKASI DINAMIS -->
                        <h6 class="fw-bold section-title"><i class="bi bi-sliders me-2"></i>1. Spesifikasi Cetak</h6>
                        <div class="row g-3 mb-4">
                            
                            <!-- Looping Data JSON Spesifikasi -->
                            <?php if (!empty($spesifikasi) && is_array($spesifikasi)): ?>
                                <?php foreach ($spesifikasi as $kategori => $pilihan_list): ?>
                                    <div class="col-md-6">
                                        <label class="small fw-bold mb-2 text-capitalize"><?= htmlspecialchars($kategori); ?></label>
                                        <!-- Nama select dikirim sebagai array spesifikasi[Nama Kategori] -->
                                        <select name="spesifikasi[<?= htmlspecialchars($kategori); ?>]" class="form-select" required>
                                            <?php foreach ($pilihan_list as $opsi): ?>
                                                <option value="<?= htmlspecialchars($opsi); ?>"><?= htmlspecialchars($opsi); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Jika Produk Lama (Belum pakai JSON) -->
                                <div class="col-md-12">
                                    <div class="alert alert-warning py-2 mb-0 small fw-bold">Tidak ada spesifikasi khusus untuk produk ini.</div>
                                </div>
                            <?php endif; ?>

                            <!-- Ukuran (Tergantung Tipe Harga) -->
                            <?php if ($produk['tipe_harga'] == 'meteran') : ?>
                                <div class="col-6 col-md-4 mt-4"><label class="small fw-bold mb-2 text-danger">Panjang (meter) *</label><input type="number" step="0.1" name="panjang" class="form-control" value="1" min="0.1" required></div>
                                <div class="col-6 col-md-4 mt-4"><label class="small fw-bold mb-2 text-danger">Lebar (meter) *</label><input type="number" step="0.1" name="lebar" class="form-control" value="1" min="0.1" required></div>
                            <?php else : ?>
                                <input type="hidden" name="panjang" value="1"><input type="hidden" name="lebar" value="1">
                            <?php endif; ?>
                            
                            <div class="col-12 col-md-4 mt-4"><label class="small fw-bold mb-2 text-primary">Jumlah Cetak (Qty) *</label><input type="number" name="qty" class="form-control border-primary" value="1" min="1" required></div>
                        </div>

                        <!-- 2. FILE & DESAIN -->
                        <h6 class="fw-bold section-title"><i class="bi bi-palette me-2"></i>2. File & Desain</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><label class="small fw-bold mb-2">Opsi Desain</label>
                                <select name="opsi_desain" class="form-select"><option value="upload_mandiri">Desain Mandiri</option><option value="custom_desain">Request Custom (+Rp 50rb)</option></select>
                            </div>
                            <div class="col-md-6"><label class="small fw-bold mb-2">Upload File</label><input type="file" name="file_desain" class="form-control bg-white"></div>
                            <div class="col-12"><label class="small fw-bold mb-2">Catatan Khusus (Opsional)</label><textarea name="catatan" class="form-control" rows="2" placeholder="Tuliskan catatan tambahan untuk admin..."></textarea></div>
                        </div>

                        <!-- 3. PENGIRIMAN -->
                        <h6 class="fw-bold section-title"><i class="bi bi-truck me-2"></i>3. Pengiriman</h6>
                        <select name="pengiriman" id="pengiriman" class="form-select mb-3" onchange="toggleAlamat()" required>
                            <option value="ambil_sendiri">Ambil Sendiri di Toko</option>
                            <option value="kirim_ojol">Kirim via Ojek Online (Ongkir COD)</option>
                        </select>
                        <div id="alamat_box" style="display:none;" class="p-3 bg-light rounded-4 border mb-4 shadow-sm">
                            <label class="small fw-bold mb-2" style="color: var(--color-1);"><i class="bi bi-geo-alt-fill me-1"></i> Alamat Tujuan (Ojol)</label>
                            <textarea name="alamat_pengiriman" class="form-control bg-white" rows="3" placeholder="Masukkan alamat lengkap Anda..."><?= htmlspecialchars($user['alamat_lengkap'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-3 mt-5">
                            <button type="submit" name="action" value="keranjang" class="btn-outline-grad w-100 py-3"><i class="bi bi-cart-plus me-2"></i>Masuk Keranjang</button>
                            <button type="submit" name="action" value="checkout" class="btn-grad-primary w-100 py-3"><i class="bi bi-wallet2 me-2"></i>Beli Langsung</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleAlamat() {
            let opsi = document.getElementById('pengiriman').value;
            let box = document.getElementById('alamat_box');
            box.style.display = (opsi === 'kirim_ojol') ? 'block' : 'none';
            box.querySelector('textarea').required = (opsi === 'kirim_ojol');
        }
        window.onload = toggleAlamat;
    </script>
</body>
</html>