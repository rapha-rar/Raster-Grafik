<?php
session_start();
require_once 'config/database.php';
$search = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';
$sql = "SELECT * FROM produk";
if ($search != '') $sql .= " WHERE nama_produk LIKE '%$search%' OR deskripsi LIKE '%$search%'";
$query_produk = mysqli_query($koneksi, $sql);

$jumlah_keranjang = 0;
if(isset($_SESSION['id_user']) && $_SESSION['role'] == 'pelanggan'){
    $id_user = $_SESSION['id_user'];
    $q_cart = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = $id_user");
    $jumlah_keranjang = mysqli_fetch_assoc($q_cart)['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raster Grafik - Digital Printing Modern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { 
            --color-1: #2373F4; --color-2: #578EF5; --color-3: #65D0F4;
            --grad-primary: linear-gradient(135deg, var(--color-1) 0%, var(--color-2) 100%); 
        }
        html { scroll-behavior: smooth; }
        #katalog { scroll-margin-top: 90px; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; 
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #ffffff 100%);
            background-attachment: fixed; min-height: 100vh; position: relative; overflow-x: hidden;
        }
        
        .bg-shape1 { position: fixed; top: -10%; left: -10%; width: max(350px, 45vw); height: max(350px, 45vw); border-radius: 50%; background: var(--color-1); filter: blur(120px); opacity: 0.15; z-index: -1; }
        .bg-shape2 { position: fixed; bottom: -10%; right: -10%; width: max(400px, 50vw); height: max(400px, 50vw); border-radius: 50%; background: var(--color-3); filter: blur(120px); opacity: 0.18; z-index: -1; }

        .logo-raster { color: var(--color-1); font-weight: 900; letter-spacing: -0.5px; }
        .logo-grafik { color: #0f172a; font-weight: 900; letter-spacing: -0.5px; }
        .hero-title-main { color: #0f172a; font-weight: 800; }
        .hero-title-accent { background: var(--grad-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; }
        .section-title-black { color: #0f172a !important; font-weight: 800; }

        /* KARD KATALOG */
        .card-glass { background: #ffffff; border: 1px solid rgba(87, 142, 245, 0.2); box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05); border-radius: 24px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; }
        .card-glass:hover { transform: translateY(-6px); box-shadow: 0 15px 35px rgba(35, 115, 244, 0.15); border-color: var(--color-2); }
        
        .img-wrap { position: relative; width: 100%; aspect-ratio: 1 / 1; overflow: hidden; border-radius: 18px 18px 0 0; background: #f1f5f9; }
        .img-katalog { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-glass:hover .img-katalog { transform: scale(1.08); } 
        .badge-katalog { position: absolute; top: 12px; right: 12px; background: var(--grad-primary); color: #ffffff; font-size: 0.65rem; font-weight: 800; padding: 5px 12px; border-radius: 50px; box-shadow: 0 4px 10px rgba(35, 115, 244, 0.3); z-index: 10; }
        
        /* -------------------------------------------------------------
           DESAIN BARU CAROUSEL CONTROL (SLIDER PREMIUM)
           ------------------------------------------------------------- */
        .carousel-control-prev, .carousel-control-next {
            opacity: 0; /* Disembunyikan secara default */
            width: 45px; 
            transition: all 0.4s ease;
        }
        /* Muncul dengan elegan saat area gambar disentuh mouse (hover) */
        .img-wrap:hover .carousel-control-prev, 
        .img-wrap:hover .carousel-control-next {
            opacity: 1; 
        }
        
        /* Tombol Bulat Putih dengan Ikon Biru */
        .carousel-control-prev-icon, .carousel-control-next-icon { 
            width: 32px; 
            height: 32px; 
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 50%; 
            background-size: 50%; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        /* Ganti icon SVG Bootstrap menjadi warna biru hex #2373F4 */
        .carousel-control-prev-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232373F4'%3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z'/%3e%3c/svg%3e"); }
        .carousel-control-next-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232373F4'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e"); }

        /* Efek saat tombol di klik atau disorot (Membesar & Jadi Biru) */
        .carousel-control-prev:hover .carousel-control-prev-icon,
        .carousel-control-next:hover .carousel-control-next-icon {
            background-color: var(--color-1);
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(35, 115, 244, 0.4);
        }
        .carousel-control-prev:hover .carousel-control-prev-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z'/%3e%3c/svg%3e"); }
        .carousel-control-next:hover .carousel-control-next-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e"); }

        /* TITIK INDIKATOR GLASSMORPHISM */
        .carousel-indicators {
            bottom: 8px;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(4px);
            padding: 5px 8px;
            border-radius: 20px;
            margin-left: auto;
            margin-right: auto;
            width: fit-content;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .carousel-indicators [data-bs-target] { 
            width: 6px; height: 6px; border-radius: 50%; margin: 0 4px; border: none; 
            background-color: #cbd5e1; transition: all 0.3s ease;
        }
        .carousel-indicators .active { 
            background-color: var(--color-1); width: 16px; border-radius: 10px; 
        }

        .navbar { background: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(25px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .search-box { border-radius: 50px 0 0 50px; background: #f1f5f9; border: 1px solid #cbd5e1; padding-left: 20px; transition: 0.3s; color: #0f172a; font-weight: 600;}
        .search-box:focus { background: #ffffff; border-color: var(--color-2); box-shadow: 0 0 0 4px rgba(101, 208, 244, 0.3); }
        .btn-search { border-radius: 0 50px 50px 0; background: var(--grad-primary); border: none; color: white; padding: 0 24px; }
        
        .btn-grad-primary { background: var(--grad-primary); color: white; border: none; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 15px rgba(35, 115, 244, 0.3); transition: 0.3s; }
        .btn-grad-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(35, 115, 244, 0.5); color: white; }

        /* TOMBOL IKON HEADER (keranjang & akun) */
        .icon-btn { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #ffffff; color: var(--color-1); font-size: 1.2rem; text-decoration: none; border: 1px solid rgba(87, 142, 245, 0.25); box-shadow: 0 4px 12px rgba(35, 115, 244, 0.12); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .icon-btn:hover { background: var(--grad-primary); color: #ffffff; border-color: transparent; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(35, 115, 244, 0.35); }
        .icon-btn:focus-visible { outline: 3px solid rgba(101, 208, 244, 0.6); outline-offset: 2px; }
        .icon-btn .badge { border: 2px solid #ffffff; font-size: 0.6rem; background: var(--color-1); }
        .icon-btn:hover .badge { background: #0f172a; }

        /* BANNER SLIDER */
        .banner-slider { border-radius: 32px; overflow: hidden; border: 1px solid rgba(87, 142, 245, 0.25); box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06); margin-top: 24px; }
        .banner-slide { height: 360px; display: flex; align-items: center; padding: 40px 64px 56px; position: relative; overflow: hidden; }
        .banner-1 { background: linear-gradient(135deg, #ffffff 0%, #eaf2ff 100%); color: #0f172a; }
        .banner-2 { background: var(--grad-primary); color: #ffffff; }
        .banner-3 { background: linear-gradient(135deg, var(--color-3) 0%, var(--color-1) 100%); color: #ffffff; }
        .banner-title { font-weight: 800; letter-spacing: -0.5px; font-size: clamp(1.5rem, 3.2vw, 2.4rem); line-height: 1.2; }
        .banner-text { font-weight: 500; font-size: clamp(0.9rem, 1.6vw, 1.1rem); opacity: 0.85; max-width: 480px; }
        .banner-img { width: 100%; max-height: 260px; object-fit: contain; mix-blend-mode: multiply; }
        .banner-icon { position: absolute; right: 6%; top: 50%; transform: translateY(-50%); font-size: clamp(8rem, 20vw, 14rem); opacity: 0.18; line-height: 1; pointer-events: none; }
        .banner-slider .carousel-control-prev, .banner-slider .carousel-control-next { opacity: 1; width: 64px; }
        .banner-slider .carousel-indicators { bottom: 14px; }
        @media (max-width: 767.98px) {
            .banner-slide { height: 360px; padding: 28px 24px 52px; }
            .banner-slider { border-radius: 24px; }
            .banner-slider .carousel-control-prev, .banner-slider .carousel-control-next { display: none; }
        }

        .floating-wa { position: fixed; bottom: 25px; right: 25px; background: linear-gradient(135deg, #22c55e, #15803d); color: white; border-radius: 50px; padding: 14px 24px; box-shadow: 0 8px 25px rgba(34,197,94,0.4); z-index: 1000; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .floating-wa:hover { transform: translateY(-4px) scale(1.05); color: white; box-shadow: 0 12px 30px rgba(34,197,94,0.6); }
        .btn-wa { background: linear-gradient(135deg, #22c55e, #15803d); color: white; border: none; border-radius: 50px; font-weight: 700; box-shadow: 0 8px 25px rgba(34,197,94,0.4); transition: 0.3s; }
        .btn-wa:hover { transform: translateY(-4px) scale(1.05); color: white; box-shadow: 0 12px 30px rgba(34,197,94,0.6); }

        /* ================= RESPONSIVE ================= */
        .navbar > .container { min-height: 52px; }

        /* Tablet */
        @media (max-width: 991.98px) {
            .banner-slide { height: 340px; padding: 36px 48px 52px; }
            .banner-slider .carousel-control-prev, .banner-slider .carousel-control-next { width: 52px; }
        }

        /* HP */
        @media (max-width: 767.98px) {
            .navbar-brand.fs-3 { font-size: 1.4rem !important; }
            .icon-btn { width: 40px; height: 40px; font-size: 1.1rem; }

            .banner-slide { height: 330px; padding: 26px 22px 50px; }
            .banner-text { max-width: 100%; }


            .card-glass { border-radius: 20px; }
            .img-wrap { border-radius: 16px 16px 0 0; }
            .card-glass .p-3 { padding: 0.85rem !important; }
            .card-glass h5 { font-size: 1rem; }
            .card-glass .btn-grad-primary.btn-sm { font-size: 0.8rem; }

        }

        /* HP kecil (<= 380px) */
        @media (max-width: 380px) {
            .banner-slide { height: 340px; }
            .banner-title { font-size: 1.3rem; }
            .banner-text { font-size: 0.82rem; }
        }

        /* Layar sentuh: tidak ada hover, jadi panah slider foto produk selalu tampil */
        @media (hover: none) {
            .carousel-control-prev, .carousel-control-next { opacity: 1; }
            .carousel-control-prev-icon, .carousel-control-next-icon { width: 28px; height: 28px; }
            .card-glass:hover { transform: none; }
            .card-glass:hover .img-katalog { transform: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            * { transition: none !important; }
        }
    </style>
</head>
<body>

    <div class="bg-shape1"></div><div class="bg-shape2"></div>

    <nav class="navbar navbar-expand-lg py-3 sticky-top">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="navbar-brand fs-3 m-0" href="index.php"><span class="logo-raster">RASTER</span><span class="logo-grafik">GRAFIK</span></a>
            <form action="index.php" method="GET" class="d-none d-md-flex mx-auto" style="width: 45%;">
                <input type="search" name="q" class="form-control search-box" placeholder="Cari produk cetak..." value="<?= htmlspecialchars($search); ?>">
                <button class="btn-search" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex align-items-center gap-3">
                <?php if(isset($_SESSION['id_user']) && $_SESSION['role'] == 'pelanggan'): ?>
                    <a href="keranjang.php" class="icon-btn position-relative" title="Keranjang" aria-label="Keranjang Belanja">
                        <i class="bi bi-cart3"></i>
                        <?php if($jumlah_keranjang > 0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"><?= $jumlah_keranjang; ?></span><?php endif; ?>
                    </a>
                    <a href="dashboard_pelanggan.php" class="icon-btn" title="Dashboard" aria-label="Dashboard"><i class="bi bi-person-fill"></i></a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-grad-primary btn-sm px-4">Masuk / Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container d-md-none mt-3">
        <form action="index.php" method="GET" class="d-flex w-100">
            <input type="search" name="q" class="form-control search-box" placeholder="Cari produk cetak..." value="<?= htmlspecialchars($search); ?>">
            <button class="btn-search" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="container">
        <div id="bannerSlider" class="banner-slider carousel slide" data-bs-ride="carousel" data-bs-interval="5000" aria-label="Banner promosi">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#bannerSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Banner 1"></button>
                <button type="button" data-bs-target="#bannerSlider" data-bs-slide-to="1" aria-label="Banner 2"></button>
                <button type="button" data-bs-target="#bannerSlider" data-bs-slide-to="2" aria-label="Banner 3"></button>
            </div>

            <div class="carousel-inner">
                <!-- SLIDE 1 -->
                <div class="carousel-item active">
                    <div class="banner-slide banner-1">
                        <div class="row align-items-center w-100 g-3">
                            <div class="col-md-6">
                                <h2 class="banner-title mb-3">Cetak Banner &amp; Spanduk <span class="hero-title-accent">Berkualitas</span></h2>
                                <p class="banner-text mb-4">Hasil tajam untuk promosi usaha Anda, dicetak dengan mesin digital printing modern.</p>
                                <a href="#katalog" class="btn btn-grad-primary px-4 py-2">Lihat Katalog <i class="bi bi-arrow-down ms-1"></i></a>
                            </div>
                            <div class="col-md-6 d-none d-md-block text-center">
                                <img src="public/images/banner.jpg" class="banner-img" alt="Mesin digital printing" onerror="this.style.display='none'">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2 -->
                <div class="carousel-item">
                    <div class="banner-slide banner-2">
                        <i class="bi bi-aspect-ratio banner-icon" aria-hidden="true"></i>
                        <div class="position-relative">
                            <h2 class="banner-title mb-3">Custom Ukuran<br>Sesuai Kebutuhan</h2>
                            <p class="banner-text mb-4">Tentukan panjang dan lebar sendiri, harga dihitung otomatis per meter.</p>
                            <a href="#katalog" class="btn btn-light rounded-pill fw-bold px-4 py-2" style="color: var(--color-1);">Mulai Pesan</a>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 3 -->
                <div class="carousel-item">
                    <div class="banner-slide banner-3">
                        <i class="bi bi-truck banner-icon" aria-hidden="true"></i>
                        <div class="position-relative">
                            <h2 class="banner-title mb-3">Siap Kirim via Ojol</h2>
                            <p class="banner-text mb-4">Pesanan selesai langsung diantar ke alamat Anda, praktis tanpa perlu ke toko.</p>
                            <a href="https://wa.me/6281234567890?text=Halo%20Admin..." target="_blank" class="btn btn-wa px-4 py-2"><i class="bi bi-whatsapp me-2"></i>Tanya Admin</a>
                        </div>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#bannerSlider" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sebelumnya</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerSlider" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Berikutnya</span>
            </button>
        </div>
    </div>

    <section id="katalog" class="py-4 container pb-5">
        <div class="d-flex align-items-center mb-4">
            <h4 class="section-title-black m-0 fs-5 fs-md-4">
                <i class="bi bi-stars me-2" style="color: var(--color-1);"></i><?= ($search != '') ? "Hasil pencarian: '$search'" : "Katalog Unggulan"; ?>
            </h4>
            <div class="ms-4 flex-grow-1 rounded-pill" style="height: 3px; background: linear-gradient(90deg, var(--color-2) 0%, transparent 100%); opacity: 0.5;"></div>
        </div>
        
        <div class="row g-3 g-md-4">
            <?php if (mysqli_num_rows($query_produk) > 0) : ?>
                <?php while ($produk = mysqli_fetch_assoc($query_produk)) : ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card-glass h-100 d-flex flex-column">
                            
                            <div id="carouselProduk<?= $produk['id_produk'] ?>" class="img-wrap carousel slide" data-bs-ride="false" data-bs-interval="false">
                                <span class="badge-katalog"><i class="bi bi-tag-fill me-1"></i> Custom</span>
                                
                                <div class="carousel-inner h-100">
                                    <?php 
                                        $gambar_raw = $produk['gambar_katalog'];
                                        $gambar_list = [];

                                        if (!empty($gambar_raw)) {
                                            $decoded = json_decode($gambar_raw, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $gambar_list = $decoded;
                                            } else if (strpos($gambar_raw, ',') !== false) {
                                                $gambar_list = explode(',', $gambar_raw);
                                            } else {
                                                $gambar_list = [$gambar_raw];
                                            }
                                        }

                                        $gambar_list = array_filter(array_map('trim', $gambar_list));
                                        if (empty($gambar_list)) { $gambar_list = ['default']; }

                                        $indicator_count = 0;
                                        foreach ($gambar_list as $index => $gbr) : 
                                            if (empty($gbr)) continue;
                                            $active = ($indicator_count === 0) ? 'active' : '';
                                            $img_url = (strpos($gbr, 'http') === 0) ? $gbr : 'public/uploads/produk/' . $gbr;
                                            
                                            $placeholder = "https://via.placeholder.com/400x400/f1f5f9/0f172a?text=Gambar+Tidak+Ditemukan";
                                            if ($gbr === 'default') { $img_url = $placeholder; }
                                    ?>
                                        <div class="carousel-item h-100 <?= $active; ?>">
                                            <img src="<?= $img_url; ?>" class="img-katalog" alt="Produk" onerror="this.src='<?= $placeholder; ?>'">
                                        </div>
                                    <?php 
                                        $indicator_count++;
                                        endforeach; 
                                    ?>
                                </div>
                                
                                <!-- NAVIGASI CAROUSEL (DIPERBARUI) -->
                                <?php if($indicator_count > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduk<?= $produk['id_produk'] ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselProduk<?= $produk['id_produk'] ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                    <div class="carousel-indicators">
                                        <?php for ($i = 0; $i < $indicator_count; $i++) : ?>
                                            <button type="button" data-bs-target="#carouselProduk<?= $produk['id_produk'] ?>" data-bs-slide-to="<?= $i ?>" class="<?= ($i === 0) ? 'active' : '' ?>" aria-current="true"></button>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold mb-1 text-truncate text-dark fs-6"><?= htmlspecialchars($produk['nama_produk']); ?></h6>
                                <p class="text-muted small mb-3 fw-medium" style="font-size:0.75rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($produk['deskripsi']); ?></p>
                                
                                <div class="mt-auto mb-3">
                                    <span class="text-muted small fw-bold d-block mb-1" style="font-size:0.65rem;">Mulai dari</span>
                                    <h5 class="fw-bold mb-0 text-truncate" style="color: var(--color-1);">Rp <?= number_format($produk['harga_dasar'], 0, ',', '.'); ?> <span class="text-muted fw-normal" style="font-size:0.7rem;">/ <?= ($produk['tipe_harga'] == 'meteran') ? 'm²' : 'pcs'; ?></span></h5>
                                </div>
                                
                                <a href="pesan.php?id_produk=<?= $produk['id_produk']; ?>" class="btn btn-grad-primary btn-sm w-100 py-2"><i class="bi bi-bag-plus me-1"></i> Pesan</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center text-muted py-5"><i class="bi bi-box-seam display-4 d-block mb-3"></i><p class="fw-medium">Produk tidak ditemukan.</p></div>
            <?php endif; ?>
        </div>
    </section>

    <a href="https://wa.me/6282249021569?text=Halo Admin..." class="floating-wa" target="_blank"><i class="bi bi-whatsapp fs-4"></i><span class="d-none d-md-inline">Chat Admin</span></a>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>