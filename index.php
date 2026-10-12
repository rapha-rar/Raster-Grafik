<?php
// Memanggil file koneksi database
require_once 'config/database.php';

// Menarik seluruh data produk dari database
$query_produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raster Grafik - Digital Printing Cepat & Mudah</title>
    
    <!-- CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS Kustom Tema Merah-Biru Raster Grafik -->
    <style>
        :root {
            --biru-rg: #0088cc;
            --merah-rg: #cc0000;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
        }
        .text-biru { color: var(--biru-rg); }
        .text-merah { color: var(--merah-rg); }
        
        .btn-primer {
            background-color: var(--merah-rg);
            color: white;
            font-weight: bold;
        }
        .btn-primer:hover {
            background-color: #aa0000;
            color: white;
        }
        
        .btn-sekunder {
            background-color: var(--biru-rg);
            color: white;
            font-weight: bold;
        }
        .btn-sekunder:hover {
            background-color: #006699;
            color: white;
        }

        .hero-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 80px 0;
            border-bottom: 5px solid var(--merah-rg);
        }
        
        .hero-border-bottom {
            height: 5px;
            background-color: var(--biru-rg);
        }

        .fitur-box {
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .fitur-box:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: var(--biru-rg);
        }
    </style>
</head>
<body>

    <!-- 1. HEADER / NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto fw-semibold">
                    <li class="nav-item"><a class="nav-link text-primary" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#katalog">Katalog Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard_pelanggan.php">Pesanan Saya</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_pesanan.php">Admin Panel</a></li>
                </ul>
                <div class="ms-lg-3 mt-2 mt-lg-0">
                    <a href="dashboard_pelanggan.php" class="btn btn-sekunder px-4 rounded-pill">Dashboard Pelanggan</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 2. HERO BANNER -->
    <section class="hero-section text-center text-lg-start">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3">
                        CETAK <span class="text-merah">CEPAT</span>, MUDAH & <span class="text-biru">BERKUALITAS!</span>
                    </h1>
                    <p class="lead mb-4">
                        Solusi percetakan online Anda di Raster Grafik. Dari custom desain, pilih template, hingga siap kirim ke depan pintu rumah Anda (Ongkir COD)!
                    </p>
                    <div>
                        <a href="#katalog" class="btn btn-primer btn-lg px-4 me-2 rounded-pill">Pesan Sekarang</a>
                        <a href="#katalog" class="btn btn-outline-dark btn-lg px-4 rounded-pill">Lihat Katalog</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://via.placeholder.com/600x400?text=Raster+Grafik+Banner" alt="Banner Percetakan" class="img-fluid rounded shadow-lg">
                </div>
            </div>
        </div>
    </section>
    <div class="hero-border-bottom"></div>

    <!-- 3. BARIS FITUR UNGGULAN -->
    <section class="py-5 bg-white">
        <div class="container">
            <h3 class="text-center fw-bold mb-4">Layanan Unggulan Kami</h3>
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="fitur-box">
                        <h4 class="text-biru">🎨</h4>
                        <h5 class="fw-bold mt-2">3 Opsi Desain</h5>
                        <p class="small text-muted mb-0">Upload Mandiri, Pilih Template, atau Request Custom Desain.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="fitur-box">
                        <h4 class="text-merah">🧮</h4>
                        <h5 class="fw-bold mt-2">Kalkulator Harga</h5>
                        <p class="small text-muted mb-0">Hitung total tagihan otomatis sesuai ukuran cetakan Anda.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="fitur-box">
                        <h4 class="text-biru">🛵</h4>
                        <h5 class="fw-bold mt-2">Kirim via Ojol</h5>
                        <p class="small text-muted mb-0">Pesanan dikirim cepat, bayar ongkir langsung ke driver (COD).</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="fitur-box">
                        <h4 class="text-merah">🎯</h4>
                        <h5 class="fw-bold mt-2">Live Tracking</h5>
                        <p class="small text-muted mb-0">Pantau progres pesanan mulai dari desain hingga proses cetak.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. KATALOG PRODUK DINAMIS -->
    <section id="katalog" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Pilihan Produk Terlaris</h2>
            <div class="row g-4">
                
                <?php if (mysqli_num_rows($query_produk) > 0) : ?>
                    <?php while ($produk = mysqli_fetch_assoc($query_produk)) : ?>
                        <?php 
                            // Cek apakah gambar menggunakan URL luar (http) atau file foto lokal di folder uploads
                            $img_src = (strpos($produk['gambar_katalog'], 'http') === 0) 
                                ? $produk['gambar_katalog'] 
                                : 'public/uploads/produk/' . $produk['gambar_katalog'];
                        ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 shadow-sm border-0">
                                <img src="<?= $img_src; ?>" class="card-img-top" alt="<?= $produk['nama_produk']; ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body text-center d-flex flex-column">
                                    <h5 class="card-title fw-bold"><?= $produk['nama_produk']; ?></h5>
                                    <p class="card-text text-muted small flex-grow-1"><?= $produk['deskripsi']; ?></p>
                                    
                                    <h6 class="text-merah fw-bold mb-3">
                                        Mulai Rp <?= number_format($produk['harga_dasar'], 0, ',', '.'); ?>
                                        <?= ($produk['tipe_harga'] == 'meteran') ? '/ m²' : '/ pcs'; ?>
                                    </h6>
                                    
                                    <a href="pesan.php?id_produk=<?= $produk['id_produk']; ?>" class="btn btn-sekunder w-100 rounded-pill">
                                        Pilih Produk
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-12 text-center text-muted">
                        <p>Belum ada produk yang tersedia saat ini.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- 5. FOOTER -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center text-md-start">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5 class="fw-bold"><span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span></h5>
                    <p class="small text-secondary">Solusi cetak cepat dan berkualitas untuk kebutuhan promosi dan bisnis Anda.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5 class="fw-bold">Alamat Toko</h5>
                    <p class="small text-secondary mb-0">Jl. Contoh Percetakan No. 123</p>
                    <p class="small text-secondary">Buka: Senin - Sabtu (08:00 - 17:00)</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5 class="fw-bold">Bantuan</h5>
                    <p class="small text-secondary mb-0">WhatsApp: 0812-3456-7890</p>
                    <p class="small text-secondary">Email: halo@rastergrafik.id</p>
                </div>
            </div>
            <hr class="border-secondary">
            <p class="text-center small text-secondary mb-0">&copy; 2026 Raster Grafik. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>