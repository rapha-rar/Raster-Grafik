<?php
session_start();
require_once 'config/database.php';
$query_produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Raster Grafik - Digital Printing Cepat & Mudah</title>
    
    <!-- CSS & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --biru-rg: #0088cc;
            --merah-rg: #cc0000;
            --bg-light: #f4f7f6;
        }
        body { font-family: 'Poppins', sans-serif; color: #333; overflow-x: hidden; }
        .text-biru { color: var(--biru-rg); }
        .text-merah { color: var(--merah-rg); }
        
        /* PAGE LOADER STYLING */
        #page-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center; z-index: 99999;
            opacity: 1; visibility: visible; transition: opacity 0.4s ease, visibility 0.4s ease;
        }
        #page-loader.fade-out { opacity: 0; visibility: hidden; pointer-events: none; }
        .spinner-container { text-align: center; animation: pulseLoader 1.5s infinite ease-in-out; }
        @keyframes pulseLoader { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } }

        .navbar-brand { font-weight: 800; font-size: 1.5rem; letter-spacing: -0.5px; }
        .btn-primer { background-color: var(--merah-rg); color: white; font-weight: 500; transition: all 0.3s ease; }
        .btn-primer:hover { background-color: #aa0000; color: white; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(204,0,0,0.2); }
        .btn-sekunder { background-color: var(--biru-rg); color: white; font-weight: 500; transition: all 0.3s ease; }
        .btn-sekunder:hover { background-color: #006699; color: white; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,136,204,0.2); }
        .hero-section { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 80px 0; border-bottom: 5px solid var(--merah-rg); }
        .hero-border-bottom { height: 5px; background-color: var(--biru-rg); }
        
        .fitur-box { text-align: center; padding: 30px 20px; border: none; border-radius: 16px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: 0.3s; height: 100%; }
        .fitur-box:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); transition: transform 0.3s ease, box-shadow 0.3s ease; overflow: hidden; }
        .card-custom:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }

        /* RESPONSIVE CSS & NAVBAR MOBILE FIX */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background-color: #ffffff;
                padding: 1.5rem;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                margin-top: 15px;
                position: absolute;
                width: 100%;
                left: 0;
                top: 100%;
                z-index: 1000;
            }
            .nav-item { margin-bottom: 8px; text-align: center; }
            .nav-link { padding: 10px !important; border-radius: 8px; background-color: #f8f9fa; }
            .nav-link.text-primary { background-color: rgba(0,136,204,0.1); font-weight: 600; }
        }
        @media (max-width: 767.98px) {
            .hero-section { padding: 40px 0 !important; text-align: center !important; }
            .hero-section h1 { font-size: 1.8rem !important; line-height: 1.3; }
            .hero-section .lead { font-size: 0.95rem !important; }
            .card-body { padding: 1.25rem !important; }
            .navbar-brand { font-size: 1.2rem !important; }
        }
        @media (max-width: 575.98px) {
            .btn-mobile-full { width: 100% !important; margin-bottom: 8px; }
        }
    </style>
</head>
<body class="bg-light">

    <!-- OVERLAY PAGE LOADER -->
    <div id="page-loader">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em; color: #0088cc !important;"></div>
            <div class="loader-logo mt-3 fw-bold fs-5">
                <span style="color: #0088cc;">RASTER</span><span style="color: #cc0000;">GRAFIK</span>
            </div>
            <small class="text-muted d-block mt-1">Memuat halaman...</small>
        </div>
    </div>

    <!-- NAVBAR (SUDAH DIPERBAIKI UNTUK MOBILE) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-3 position-relative">
        <div class="container position-relative">
            <a class="navbar-brand" href="index.php">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none focus-ring focus-ring-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto fw-medium mb-3 mb-lg-0 w-100 justify-content-lg-end">
                    <li class="nav-item"><a class="nav-link text-primary" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#katalog">Katalog Produk</a></li>
                </ul>
                <div class="ms-lg-4 mt-2 mt-lg-0 text-center text-lg-start w-100" style="max-width: 250px; margin: 0 auto;">
                    <?php if(isset($_SESSION['id_user'])): ?>
                        <?php if($_SESSION['role'] == 'admin'): ?>
                            <a href="admin_pesanan.php" class="btn btn-sekunder px-4 rounded-pill w-100 btn-mobile-full"><i class="bi bi-shield-lock me-1"></i> Panel Admin</a>
                        <?php else: ?>
                            <a href="dashboard_pelanggan.php" class="btn btn-sekunder px-4 rounded-pill w-100 btn-mobile-full"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-sekunder px-4 rounded-pill w-100 btn-mobile-full"><i class="bi bi-box-arrow-in-right me-1"></i> Login / Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO BANNER -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 text-center text-lg-start pe-lg-5">
                    <h1 class="display-4 fw-bold mb-3" style="letter-spacing: -1px;">
                        CETAK <span class="text-merah">CEPAT</span>, MUDAH & <span class="text-biru">BERKUALITAS!</span>
                    </h1>
                    <p class="lead mb-4 text-muted">
                        Solusi percetakan online Anda di Raster Grafik. Dari custom desain, pilih template, hingga siap kirim ke depan pintu rumah Anda (Ongkir COD)!
                    </p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
                        <a href="#katalog" class="btn btn-primer btn-lg px-4 rounded-pill btn-mobile-full shadow-sm"><i class="bi bi-cart me-1"></i> Pesan Sekarang</a>
                        <a href="#katalog" class="btn btn-outline-dark btn-lg px-4 rounded-pill btn-mobile-full">Lihat Katalog</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="public/images/banner.jpg" alt="Banner" class="img-fluid rounded-4 shadow-lg" onerror="this.src='https://via.placeholder.com/600x450?text=Raster+Grafik+Banner'">
                </div>
            </div>
        </div>
    </section>
    <div class="hero-border-bottom"></div>

    <!-- MENGAPA MEMILIH KAMI -->
    <section class="py-5 bg-white">
        <div class="container my-4">
            <div class="text-center mb-5">
                <h6 class="text-merah fw-bold text-uppercase tracking-wide">Mengapa Memilih Kami?</h6>
                <h3 class="fw-bold">Keunggulan Percetakan Raster Grafik</h3>
            </div>
            <div class="row g-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="fitur-box">
                        <i class="bi bi-printer text-biru display-4 mb-3 d-block"></i>
                        <h5 class="fw-bold">Hasil Cetak Tajam</h5>
                        <p class="small text-muted mb-0">Menggunakan mesin percetakan modern dengan hasil cetak warna tajam, jernih, dan presisi tinggi.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="fitur-box">
                        <i class="bi bi-clock-history text-merah display-4 mb-3 d-block"></i>
                        <h5 class="fw-bold">Pengerjaan Cepat</h5>
                        <p class="small text-muted mb-0">Proses pengerjaan terstruktur, rapi, cepat, dan tepat waktu sesuai kebutuhan Anda.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="fitur-box">
                        <i class="bi bi-tags text-biru display-4 mb-3 d-block"></i>
                        <h5 class="fw-bold">Harga Bersaing</h5>
                        <p class="small text-muted mb-0">Harga terjangkau dengan transparansi perhitungan biaya yang jelas tanpa biaya tersembunyi.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="fitur-box">
                        <i class="bi bi-palette text-merah display-4 mb-3 d-block"></i>
                        <h5 class="fw-bold">Layanan Custom Desain</h5>
                        <p class="small text-muted mb-0">Tim desainer siap membantu proses pembuatan dan penyesuaian desain sesuai keinginan Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- KATALOG PRODUK -->
    <section id="katalog" class="py-5" style="background-color: var(--bg-light);">
        <div class="container my-4">
            <div class="text-center mb-5">
                <h6 class="text-biru fw-bold text-uppercase tracking-wide">Katalog Kami</h6>
                <h2 class="fw-bold">Pilihan Produk Terlaris</h2>
            </div>
            <div class="row g-3 g-md-4">
                <?php if (mysqli_num_rows($query_produk) > 0) : ?>
                    <?php while ($produk = mysqli_fetch_assoc($query_produk)) : ?>
                        <?php 
                            $img_src = (strpos($produk['gambar_katalog'], 'http') === 0) 
                                ? $produk['gambar_katalog'] : 'public/uploads/produk/' . $produk['gambar_katalog'];
                        ?>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card card-custom h-100">
                                <img src="<?= $img_src; ?>" class="card-img-top" alt="<?= htmlspecialchars($produk['nama_produk']); ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body text-center d-flex flex-column p-4">
                                    <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($produk['nama_produk']); ?></h5>
                                    <p class="card-text text-muted small flex-grow-1 mb-3"><?= htmlspecialchars($produk['deskripsi']); ?></p>
                                    <h6 class="text-merah fw-bold fs-5 mb-4">
                                        Rp <?= number_format($produk['harga_dasar'], 0, ',', '.'); ?>
                                        <span class="fs-6 text-muted fw-normal"><?= ($produk['tipe_harga'] == 'meteran') ? '/ m²' : '/ pcs'; ?></span>
                                    </h6>
                                    <a href="pesan.php?id_produk=<?= $produk['id_produk']; ?>" class="btn btn-sekunder w-100 rounded-pill py-2">
                                        Pilih Produk <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-12 text-center text-muted py-5">
                        <i class="bi bi-inbox display-1 mb-3 d-block text-secondary"></i>
                        <p>Belum ada produk yang tersedia saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="small text-secondary mb-0">&copy; 2026 Raster Grafik. All Rights Reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('page-loader');
            if (loader) loader.classList.add('fade-out');
        });

        // Tutup menu collapse saat klik di luar area
        document.addEventListener('click', function(event) {
            const navbar = document.getElementById('navbarNav');
            const toggler = document.querySelector('.navbar-toggler');
            if (navbar.classList.contains('show') && !navbar.contains(event.target) && !toggler.contains(event.target)) {
                bootstrap.Collapse.getInstance(navbar).hide();
            }
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
        });
    </script>
</body>
</html>