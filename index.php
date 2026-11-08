<?php
session_start();
require_once 'config/database.php';

$search = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';
$id_kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
$semua = isset($_GET['semua']) ? intval($_GET['semua']) : 0;

$mode = 'kategori';
if ($search != '') {
    $mode = 'produk';
    $sql = "SELECT * FROM produk WHERE nama_produk LIKE '%$search%' OR deskripsi LIKE '%$search%'";
    $query_produk = mysqli_query($koneksi, $sql);
} elseif ($id_kategori > 0) {
    $mode = 'produk';
    $sql = "SELECT * FROM produk WHERE id_kategori = $id_kategori";
    $query_produk = mysqli_query($koneksi, $sql);
    $kat_info = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_kategori FROM kategori WHERE id_kategori = $id_kategori"));
} elseif ($semua == 1) {
    $mode = 'produk';
    $sql = "SELECT * FROM produk ORDER BY id_produk DESC";
    $query_produk = mysqli_query($koneksi, $sql);
} else {
    $query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori DESC");
}

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
    <title>Raster Grafik - Digital Printing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@75..100,700..900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --color-1: #2373F4; --color-2: #578EF5; --color-3: #65D0F4; --grad-primary: linear-gradient(135deg, var(--color-1) 0%, var(--color-2) 100%); }
        html { scroll-behavior: smooth; }
        #katalog { scroll-margin-top: 90px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #ffffff 100%); background-attachment: fixed; min-height: 100vh; position: relative; overflow-x: hidden; }
        .bg-shape1 { position: fixed; top: -10%; left: -10%; width: max(350px, 45vw); height: max(350px, 45vw); border-radius: 50%; background: var(--color-1); filter: blur(120px); opacity: 0.15; z-index: -1; }
        .bg-shape2 { position: fixed; bottom: -10%; right: -10%; width: max(400px, 50vw); height: max(400px, 50vw); border-radius: 50%; background: var(--color-3); filter: blur(120px); opacity: 0.18; z-index: -1; }
        .logo-raster { color: var(--color-1); font-weight: 900; letter-spacing: -0.5px; }
        .logo-grafik { color: #0f172a; font-weight: 900; letter-spacing: -0.5px; }
        .section-title-black { color: #0f172a !important; font-weight: 800; }
        .card-glass { background: #ffffff; border: 1px solid rgba(87, 142, 245, 0.2); box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04); border-radius: 16px; transition: border-color 0.3s ease, box-shadow 0.3s ease; overflow: hidden; }
        .card-glass:hover { box-shadow: 0 8px 24px rgba(35, 115, 244, 0.10); border-color: var(--color-2); }
        .img-wrap { position: relative; width: 100%; aspect-ratio: 1 / 1; overflow: hidden; border-radius: 14px 14px 0 0; background: #f1f5f9; }
        .img-katalog { width: 100%; height: 100%; object-fit: cover; object-position: center; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); -webkit-backface-visibility: hidden; backface-visibility: hidden; transform: translateZ(0); }
        .card-glass:hover .img-katalog { transform: scale(1.04) translateZ(0); }
        .carousel-control-prev, .carousel-control-next { opacity: 0; width: 45px; transition: all 0.4s ease; }
        .img-wrap:hover .carousel-control-prev, .img-wrap:hover .carousel-control-next { opacity: 1; }
        .carousel-control-prev-icon, .carousel-control-next-icon { width: 32px; height: 32px; background-color: rgba(255, 255, 255, 0.95); border-radius: 50%; background-size: 50%; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .carousel-control-prev-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232373F4'%3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z'/%3e%3c/svg%3e"); }
        .carousel-control-next-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232373F4'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e"); }
        .carousel-control-prev:hover .carousel-control-prev-icon, .carousel-control-next:hover .carousel-control-next-icon { background-color: var(--color-1); transform: scale(1.15); box-shadow: 0 6px 20px rgba(35, 115, 244, 0.4); }
        .carousel-control-prev:hover .carousel-control-prev-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z'/%3e%3c/svg%3e"); }
        .carousel-control-next:hover .carousel-control-next-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e"); }
        .carousel-indicators { bottom: 8px; background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(4px); padding: 5px 8px; border-radius: 20px; margin-left: auto; margin-right: auto; width: fit-content; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .carousel-indicators [data-bs-target] { width: 6px; height: 6px; border-radius: 50%; margin: 0 4px; border: none; background-color: #cbd5e1; transition: all 0.3s ease; }
        .carousel-indicators .active { background-color: var(--color-1); width: 16px; border-radius: 10px; }
        .navbar { background: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(25px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .search-box { border-radius: 50px 0 0 50px; background: #f1f5f9; border: 1px solid #cbd5e1; padding-left: 20px; transition: 0.3s; color: #0f172a; font-weight: 600;}
        .search-box:focus { background: #ffffff; border-color: var(--color-2); box-shadow: 0 0 0 4px rgba(101, 208, 244, 0.3); }
        .btn-search { border-radius: 0 50px 50px 0; background: var(--grad-primary); border: none; color: white; padding: 0 24px; }
        .btn-grad-primary { background: var(--grad-primary); color: white; border: none; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 15px rgba(35, 115, 244, 0.3); transition: 0.3s; }
        .btn-grad-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(35, 115, 244, 0.5); color: white; }
        .btn-grad-secondary { background: #ffffff; color: var(--color-1); border: 1px solid rgba(87, 142, 245, 0.25); border-radius: 50px; font-weight: 700; box-shadow: 0 4px 12px rgba(35, 115, 244, 0.12); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-grad-secondary:hover, .btn-grad-secondary:focus-visible { background: var(--grad-primary); color: #ffffff; border-color: transparent; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(35, 115, 244, 0.35); }
        .btn-grad-secondary:focus-visible { outline: 3px solid rgba(101, 208, 244, 0.6); outline-offset: 2px; }
        .icon-btn { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #ffffff; color: var(--color-1); font-size: 1.2rem; text-decoration: none; border: 1px solid rgba(87, 142, 245, 0.25); box-shadow: 0 4px 12px rgba(35, 115, 244, 0.12); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .icon-btn:hover { background: var(--grad-primary); color: #ffffff; border-color: transparent; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(35, 115, 244, 0.35); }
        .icon-btn:focus-visible { outline: 3px solid rgba(101, 208, 244, 0.6); outline-offset: 2px; }
        .icon-btn .badge { border: 2px solid #ffffff; font-size: 0.6rem; background: var(--color-1); }
        .icon-btn:hover .badge { background: #0f172a; }
        .font-display, .banner-title, .katalog-title { font-family: 'Archivo', 'Plus Jakarta Sans', sans-serif; font-stretch: 80%; font-weight: 800; letter-spacing: -0.01em; }
        .banner-slider { position: relative; border-radius: 28px; overflow: hidden; border: 1px solid rgba(87, 142, 245, 0.25); box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05); margin-top: 24px; }
        .banner-slide { --tint: 15, 23, 42; position: relative; height: 360px; display: flex; align-items: center; padding: 40px 72px 64px; overflow: hidden; }
        .banner-1 { background: #ffffff; color: #0f172a; }
        .banner-2 { --tint: 255, 255, 255; background: #1a5bd6; color: #ffffff; }
        .banner-3 { --tint: 255, 255, 255; background: #0a2a66; color: #ffffff; }
        .banner-slide::before { content: ''; position: absolute; inset: 16px; pointer-events: none; color: rgba(var(--tint), 0.35); background: linear-gradient(currentColor, currentColor) left top / 20px 1px, linear-gradient(currentColor, currentColor) left top / 1px 20px, linear-gradient(currentColor, currentColor) right top / 20px 1px, linear-gradient(currentColor, currentColor) right top / 1px 20px, linear-gradient(currentColor, currentColor) left bottom / 20px 1px, linear-gradient(currentColor, currentColor) left bottom / 1px 20px, linear-gradient(currentColor, currentColor) right bottom / 20px 1px, linear-gradient(currentColor, currentColor) right bottom / 1px 20px; background-repeat: no-repeat; }
        .banner-slide::after, .ruler { content: ''; pointer-events: none; color: rgba(var(--tint, 35, 115, 244), 0.4); background: repeating-linear-gradient(90deg, currentColor 0 1px, transparent 1px 10px) left bottom / 100% 6px no-repeat, repeating-linear-gradient(90deg, currentColor 0 1px, transparent 1px 50px) left bottom / 100% 12px no-repeat; }
        .banner-slide::after { position: absolute; left: 40px; right: 40px; bottom: 20px; height: 12px; }
        .cmyk { position: absolute; top: 24px; left: 72px; display: flex; gap: 3px; z-index: 3; }
        .cmyk i { width: 10px; height: 10px; box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.12); }
        .cmyk i:nth-child(1) { background: #00aeef; } .cmyk i:nth-child(2) { background: #ec008c; }
        .cmyk i:nth-child(3) { background: #fff200; } .cmyk i:nth-child(4) { background: #1f2937; }
        .banner-title { font-size: clamp(2rem, 5vw, 3.4rem); line-height: 1.02; }
        .banner-text { font-weight: 500; font-size: clamp(0.9rem, 1.6vw, 1.1rem); opacity: 0.88; max-width: 440px; }
        .banner-img { width: 100%; max-height: 250px; object-fit: contain; mix-blend-mode: multiply; }
        .banner-art { width: 100%; max-height: 250px; color: #ffffff; }
        .banner-slider .carousel-control-prev, .banner-slider .carousel-control-next { opacity: 1; width: 64px; }
        .banner-slider .carousel-indicators { bottom: 34px; }
        .katalog-head { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
        .katalog-title { font-size: clamp(1.6rem, 3.2vw, 2.2rem); line-height: 1; color: #0f172a; }
        .katalog-head .ruler { flex: 1; height: 12px; min-width: 100px; }
        .floating-wa { position: fixed; bottom: 25px; right: 25px; background: linear-gradient(135deg, #22c55e, #15803d); color: white; border-radius: 50px; padding: 14px 24px; box-shadow: 0 8px 25px rgba(34,197,94,0.4); z-index: 1000; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .floating-wa:hover { transform: translateY(-4px) scale(1.05); color: white; box-shadow: 0 12px 30px rgba(34,197,94,0.6); }
        .btn-wa { background: linear-gradient(135deg, #22c55e, #15803d); color: white; border: none; border-radius: 50px; font-weight: 700; box-shadow: 0 8px 25px rgba(34,197,94,0.4); transition: 0.3s; }
        .btn-wa:hover { transform: translateY(-4px) scale(1.05); color: white; box-shadow: 0 12px 30px rgba(34,197,94,0.6); }
        .navbar > .container { min-height: 52px; }
        @media (max-width: 991.98px) { .banner-slide { height: 340px; padding: 36px 56px 60px; } .banner-slider .carousel-control-prev, .banner-slider .carousel-control-next { width: 52px; } .cmyk { left: 56px; } }
        @media (max-width: 767.98px) { .navbar-brand.fs-3 { font-size: 1.4rem !important; } .icon-btn { width: 40px; height: 40px; font-size: 1.1rem; } .banner-slider { border-radius: 22px; } .banner-slide { height: 340px; padding: 40px 22px 58px; } .banner-slide::before { inset: 10px; } .banner-slide::after { left: 32px; right: 32px; bottom: 14px; } .cmyk { left: 22px; top: 18px; } .banner-slider .carousel-control-prev, .banner-slider .carousel-control-next { display: none; } .banner-slider .carousel-indicators { bottom: 28px; } .banner-text { max-width: 100%; } .card-glass { border-radius: 14px; } .img-wrap { border-radius: 12px 12px 0 0; } .card-glass .p-3 { padding: 0.85rem !important; } .card-glass h5 { font-size: 1rem; } .card-glass .btn-grad-primary.btn-sm { font-size: 0.8rem; } }
        @media (max-width: 380px) { .banner-slide { height: 350px; } .banner-title { font-size: 1.75rem; } .banner-text { font-size: 0.82rem; } }
        @media (hover: none) { .carousel-control-prev, .carousel-control-next { opacity: 1; } .carousel-control-prev-icon, .carousel-control-next-icon { width: 28px; height: 28px; } .card-glass:hover .img-katalog { transform: translateZ(0); } }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } * { transition: none !important; } }
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
    
    <?php if ($mode == 'kategori' && $search == '' && $semua == 0): ?>
    <div class="container">
        <div id="bannerSlider" class="banner-slider carousel slide" data-bs-ride="carousel" data-bs-interval="5000" aria-label="Banner promosi">
            <div class="cmyk" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#bannerSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Banner 1"></button>
                <button type="button" data-bs-target="#bannerSlider" data-bs-slide-to="1" aria-label="Banner 2"></button>
                <button type="button" data-bs-target="#bannerSlider" data-bs-slide-to="2" aria-label="Banner 3"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="banner-slide banner-1">
                        <div class="row align-items-center w-100 g-3">
                            <div class="col-md-6">
                                <h2 class="banner-title mb-3">Cetak banner dan spanduk yang tajam</h2>
                                <p class="banner-text mb-4">Dicetak dengan mesin digital printing modern, siap untuk promosi usaha Anda.</p>
                                <a href="#katalog" class="btn btn-grad-primary px-4 py-2">Lihat katalog</a>
                            </div>
                            <div class="col-md-6 d-none d-md-block text-center">
                                <img src="public/images/banner.jpg" class="banner-img" alt="Mesin digital printing" onerror="this.style.display='none'">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="banner-slide banner-2">
                        <div class="row align-items-center w-100 g-3">
                            <div class="col-md-6">
                                <h2 class="banner-title mb-3">Ukuran bebas, harga per meter</h2>
                                <p class="banner-text mb-4">Isi panjang dan lebar sendiri. Harga dihitung otomatis.</p>
                                <a href="#katalog" class="btn btn-light rounded-pill fw-bold px-4 py-2" style="color: var(--color-1);">Mulai pesan</a>
                            </div>
                            <div class="col-md-6 d-none d-md-block">
                                <svg class="banner-art" viewBox="0 0 360 230" fill="none" stroke="currentColor" stroke-width="1.5" font-family="Plus Jakarta Sans, sans-serif" aria-hidden="true">
                                    <rect x="56" y="52" width="270" height="130" fill="rgba(255,255,255,.14)" stroke-width="2"/>
                                    <path d="M56 30H326M56 24V36M326 24V36"/>
                                    <text x="191" y="20" text-anchor="middle" fill="currentColor" stroke="none" font-size="13" font-weight="600">panjang</text>
                                    <path d="M34 52V182M28 52H40M28 182H40"/>
                                    <text transform="rotate(-90 18 117)" x="18" y="117" text-anchor="middle" fill="currentColor" stroke="none" font-size="13" font-weight="600">lebar</text>
                                    <text x="191" y="122" text-anchor="middle" fill="currentColor" stroke="none" font-size="15" font-weight="700">p   l = luas (m )</text>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="banner-slide banner-3">
                        <div class="row align-items-center w-100 g-3">
                            <div class="col-md-6">
                                <h2 class="banner-title mb-3">Pesanan diantar ke alamat Anda</h2>
                                <p class="banner-text mb-4">Selesai dicetak, langsung dikirim via ojol. Tanpa perlu ke toko.</p>
                                <a href="https://wa.me/6281234567890?text=Halo%20Admin..." target="_blank" class="btn btn-wa px-4 py-2"><i class="bi bi-whatsapp me-2"></i>Tanya admin</a>
                            </div>
                            <div class="col-md-6 d-none d-md-block">
                                <svg class="banner-art" viewBox="0 0 360 230" fill="none" stroke="currentColor" stroke-width="1.5" font-family="Plus Jakarta Sans, sans-serif" aria-hidden="true">
                                    <path d="M60 170C130 60 210 200 292 70" stroke-width="2" stroke-dasharray="6 7"/>
                                    <circle cx="60" cy="170" r="7" fill="currentColor" stroke="none"/>
                                    <circle cx="292" cy="70" r="11" fill="#65D0F4" stroke="none"/>
                                    <circle cx="292" cy="70" r="4" fill="#0a2a66" stroke="none"/>
                                    <text x="60" y="199" text-anchor="middle" fill="currentColor" stroke="none" font-size="13" font-weight="600">Toko</text>
                                    <text x="292" y="44" text-anchor="middle" fill="currentColor" stroke="none" font-size="13" font-weight="600">Alamat Anda</text>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerSlider" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Sebelumnya</span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerSlider" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Berikutnya</span></button>
        </div>
    </div>
    <?php endif; ?>

    <section id="katalog" class="py-4 container pb-5">
        <div class="katalog-head mb-4">
            <h2 class="katalog-title m-0">
                <?php 
                if ($mode == 'produk') {
                    if ($search != '') echo 'Hasil pencarian: ' . htmlspecialchars($_GET['q']);
                    elseif ($semua == 1) echo 'Semua Produk';
                    else echo htmlspecialchars($kat_info['nama_kategori']);
                } else {
                    echo 'Katalog Kategori';
                }
                ?>
            </h2>
            <div class="ruler" aria-hidden="true"></div>
            <?php if($mode == 'kategori'): ?>
                <a href="?semua=1#katalog" class="btn btn-grad-secondary px-4 py-2"><i class="bi bi-grid-fill me-2"></i>Tampilkan Semua Produk</a>
            <?php endif; ?>
            <?php if($mode == 'produk' && $search == ''): ?>
                <a href="index.php#katalog" class="btn btn-grad-secondary px-4 py-2"><i class="bi bi-arrow-left me-2"></i>Kembali ke Kategori</a>
            <?php endif; ?>
        </div>
        
        <div class="row g-3 g-md-4">
            <?php if ($mode == 'kategori') : ?>
                <?php if (mysqli_num_rows($query_kategori) > 0) : ?>
                    <?php while ($kat = mysqli_fetch_assoc($query_kategori)) : 
                        $img_kat = 'public/uploads/kategori/' . $kat['gambar_kategori'];
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="?kategori=<?= $kat['id_kategori'] ?>#katalog" class="text-decoration-none">
                                <div class="card-glass h-100 d-flex flex-column hoverable">
                                    <div class="img-wrap">
                                        <img src="<?= $img_kat ?>" class="img-katalog" alt="Kategori" onerror="this.src='https://via.placeholder.com/400x400/f1f5f9/0f172a?text=No+Img'">
                                    </div>
                                    <div class="p-3 text-center">
                                        <h6 class="fw-bold m-0 text-dark"><?= htmlspecialchars($kat['nama_kategori']); ?></h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-5"><p class="fw-medium">Belum ada kategori.</p></div>
                <?php endif; ?>
            <?php else : ?>
                <?php if (mysqli_num_rows($query_produk) > 0) : ?>
                    <?php while ($produk = mysqli_fetch_assoc($query_produk)) : ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card-glass h-100 d-flex flex-column">
                                <div id="carouselProduk<?= $produk['id_produk'] ?>" class="img-wrap carousel slide" data-bs-ride="false" data-bs-interval="false">
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
                                    <?php if($indicator_count > 1): ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduk<?= $produk['id_produk'] ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselProduk<?= $produk['id_produk'] ?>" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
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
                                        <h5 class="fw-bold mb-0 text-truncate" style="color: var(--color-1);">Rp <?= number_format($produk['harga_dasar'], 0, ',', '.'); ?> <span class="text-muted fw-normal" style="font-size:0.7rem;">/ <?= ($produk['tipe_harga'] == 'meteran') ? 'm ' : 'pcs'; ?></span></h5>
                                    </div>
                                    <a href="pesan.php?id_produk=<?= $produk['id_produk']; ?>" class="btn btn-grad-primary btn-sm w-100 py-2"><i class="bi bi-bag-plus me-1"></i> Pesan</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-12 text-center text-muted py-5"><i class="bi bi-box-seam display-4 d-block mb-3"></i><p class="fw-medium">Produk tidak ditemukan.</p></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
    <a href="https://wa.me/6281234567890?text=Halo%20Admin..." class="floating-wa" target="_blank"><i class="bi bi-whatsapp fs-4"></i><span class="d-none d-md-inline">Chat Admin</span></a>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>