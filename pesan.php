<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }

$id_produk = intval($_GET['id_produk'] ?? 1);
$produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = $id_produk"));
if (!$produk) { header("Location: index.php"); exit(); }

$id_user = intval($_SESSION['id_user']);
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user"));

// DECODE JSON SPESIFIKASI
$spesifikasi = json_decode($produk['spesifikasi_json'] ?? '{}', true);
$meteran = ($produk['tipe_harga'] == 'meteran');
$satuan = $meteran ? 'm²' : 'pcs';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan <?= htmlspecialchars($produk['nama_produk']); ?> - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@75..100,700..900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            overflow-x: hidden;
        }

        .bg-shape1 { position: fixed; top: -10%; left: -10%; width: max(350px, 45vw); height: max(350px, 45vw); border-radius: 50%; background: var(--color-1); filter: blur(120px); opacity: 0.15; z-index: -1; }
        .bg-shape2 { position: fixed; bottom: -10%; right: -10%; width: max(400px, 50vw); height: max(400px, 50vw); border-radius: 50%; background: var(--color-3); filter: blur(120px); opacity: 0.18; z-index: -1; }

        .navbar { background: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(25px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .navbar > .container { min-height: 52px; }

        .btn-nav { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-nav:hover, .btn-nav:focus-visible { background: var(--grad-primary) !important; color: #ffffff !important; border-color: transparent !important; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(35, 115, 244, 0.35) !important; }
        .btn-nav:focus-visible { outline: 3px solid rgba(101, 208, 244, 0.6); outline-offset: 2px; }

        .font-display, .page-title, .step-title, .summary-total { font-family: 'Archivo', 'Plus Jakarta Sans', sans-serif; font-stretch: 80%; font-weight: 800; letter-spacing: -0.01em; }
        .page-title { font-size: clamp(1.8rem, 4vw, 2.6rem); line-height: 1.02; color: #0f172a; }
        .page-head { display: flex; align-items: flex-end; gap: 24px; }
        .ruler { flex: 1; height: 12px; margin-bottom: 4px; pointer-events: none; color: rgba(35, 115, 244, 0.4);
            background:
                repeating-linear-gradient(90deg, currentColor 0 1px, transparent 1px 10px) left bottom / 100% 6px no-repeat,
                repeating-linear-gradient(90deg, currentColor 0 1px, transparent 1px 50px) left bottom / 100% 12px no-repeat; }

        .card-glass { background: #ffffff; border: 1px solid rgba(87, 142, 245, 0.2); box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04); border-radius: 16px; }

        .step + .step { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0; }
        .step-head { display: flex; align-items: center; gap: 12px; margin-bottom: 1.25rem; }
        .step-no { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border: 1.5px solid var(--color-1); border-radius: 6px; color: var(--color-1); font-weight: 800; font-size: 0.85rem; }
        .step-title { font-size: 1.4rem; line-height: 1; margin: 0; }

        .field-label { display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem; text-transform: capitalize; }
        .form-control, .form-select { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 0.7rem 0.9rem; color: #0f172a; font-weight: 600; transition: 0.3s; }
        .form-control:focus, .form-select:focus { background: #ffffff; border-color: var(--color-2); box-shadow: 0 0 0 4px rgba(101, 208, 244, 0.3); }
        .form-control[type="file"] { background: #ffffff; }

        .btn-grad-primary { background: var(--grad-primary); color: white; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 15px rgba(35, 115, 244, 0.3); border: none; transition: 0.3s; }
        .btn-grad-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(35, 115, 244, 0.5); color: white; }
        .btn-outline-grad { background: #ffffff; color: var(--color-1); border: 2px solid var(--color-2); border-radius: 50px; font-weight: 700; transition: 0.3s; }
        .btn-outline-grad:hover { background: var(--grad-primary); color: white; border-color: transparent; transform: translateY(-3px); }

        .alamat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; }

        .summary-title { font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem; }
        .dim-box { background: #f8fafc; border: 1px solid rgba(87, 142, 245, 0.2); border-radius: 12px; padding: 8px 8px 12px; margin-bottom: 1rem; }
        .dim-box svg { display: block; width: 100%; height: auto; color: var(--color-1); }
        .summary-row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; font-weight: 500; }
        .summary-row span:last-child { font-weight: 700; text-align: right; font-variant-numeric: tabular-nums; }
        .summary-total-wrap { padding-top: 14px; }
        .summary-total { font-size: 2rem; line-height: 1.1; color: var(--color-1); font-variant-numeric: tabular-nums; }

        .deskripsi-wrap { margin-bottom: 1.5rem; }
        .deskripsi { width: 100%; color: #64748b; font-weight: 500; font-size: clamp(0.92rem, 1.4vw, 1rem); line-height: 1.65; overflow-wrap: anywhere; }
        .deskripsi-toggle { background: none; border: 0; padding: 0; color: var(--color-1); font-weight: 700; font-size: 0.9rem; }
        .deskripsi-toggle:hover { text-decoration: underline; }
        .deskripsi-toggle:focus-visible { outline: 3px solid rgba(101, 208, 244, 0.6); outline-offset: 2px; border-radius: 4px; }

        .select-wrap { position: relative; }
        .select-wrap .form-select { background-image: none; padding-right: 3.4rem; cursor: pointer; }
        .select-wrap .form-select:hover { border-color: var(--color-2); }
        .select-chip { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: rgba(35, 115, 244, 0.12); color: var(--color-1); font-size: 0.85rem; pointer-events: none; transition: background 0.3s, color 0.3s; }
        .select-wrap:hover .select-chip, .select-wrap:focus-within .select-chip { background: var(--color-1); color: #ffffff; }

        .floating-wa { position: fixed; bottom: 25px; right: 25px; background: linear-gradient(135deg, #22c55e, #15803d); color: white; border-radius: 50px; padding: 14px 24px; box-shadow: 0 8px 25px rgba(34,197,94,0.4); z-index: 1000; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .floating-wa:hover { transform: translateY(-4px) scale(1.05); color: white; box-shadow: 0 12px 30px rgba(34,197,94,0.6); }
        .floating-wa { cursor: grab; touch-action: none; user-select: none; -webkit-user-drag: none; }
        .floating-wa.dragging { transition: none; cursor: grabbing; transform: scale(1.05); box-shadow: 0 14px 34px rgba(34,197,94,0.55); }

        @media (max-width: 991.98px) {
            .summary-total { font-size: 1.7rem; }
        }
        @media (max-width: 767.98px) {
            .deskripsi.collapsed { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
            .deskripsi-wrap { margin-bottom: 1.25rem; }
            .card-glass { border-radius: 14px; }
            .card-glass.p-4 { padding: 1.1rem !important; }
            .page-head { gap: 14px; }
            .step + .step { margin-top: 1.5rem; padding-top: 1.5rem; }
            .step-title { font-size: 1.2rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>
    <div class="bg-shape1"></div><div class="bg-shape2"></div>

    <nav class="navbar navbar-expand-lg py-3 sticky-top">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="index.php" class="btn btn-nav btn-light bg-white rounded-pill btn-sm px-4 fw-bold shadow-sm border" style="color: var(--color-1);"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        </div>
    </nav>

    <div class="container py-4 pb-5">
        <div class="page-head mb-4">
            <h1 class="page-title m-0"><?= htmlspecialchars($produk['nama_produk']); ?></h1>
            <div class="ruler" aria-hidden="true"></div>
        </div>

        <form action="proses_keranjang_checkout.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_produk" value="<?= intval($produk['id_produk']); ?>">
            <input type="hidden" name="tipe_harga" value="<?= htmlspecialchars($produk['tipe_harga']); ?>">
            <input type="hidden" name="harga_dasar" value="<?= htmlspecialchars($produk['harga_dasar']); ?>">

            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <div class="deskripsi-wrap">
                        <p class="deskripsi mb-1" id="deskripsi"><?= htmlspecialchars($produk['deskripsi']); ?></p>
                        <button type="button" class="deskripsi-toggle" id="deskripsiToggle" aria-expanded="false" aria-controls="deskripsi" hidden>Selengkapnya</button>
                    </div>
                    <div class="card-glass p-4 p-md-5">
                        <section class="step">
                            <div class="step-head"><span class="step-no">1</span><h2 class="step-title">Spesifikasi cetak</h2></div>
                            <div class="row g-3">
                                <?php if (!empty($spesifikasi) && is_array($spesifikasi)): ?>
                                    <?php foreach ($spesifikasi as $kategori => $pilihan_list): ?>
                                        <div class="col-md-6">
                                            <label class="field-label"><?= htmlspecialchars($kategori); ?></label>
                                            <div class="select-wrap"><select name="spesifikasi[<?= htmlspecialchars($kategori); ?>]" class="form-select" required>
                                                <?php foreach ($pilihan_list as $opsi): ?>
                                                    <option value="<?= htmlspecialchars($opsi); ?>"><?= htmlspecialchars($opsi); ?></option>
                                                <?php endforeach; ?>
                                            </select><span class="select-chip" aria-hidden="true"><i class="bi bi-chevron-expand"></i></span></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12"><p class="text-muted fw-medium small mb-0">Produk ini tidak punya spesifikasi tambahan.</p></div>
                                <?php endif; ?>

                                <?php if ($meteran): ?>
                                    <div class="col-6 col-md-4"><label class="field-label" for="panjang">Panjang (meter)</label><input type="number" step="0.1" name="panjang" id="panjang" class="form-control" value="1" min="0.1" required></div>
                                    <div class="col-6 col-md-4"><label class="field-label" for="lebar">Lebar (meter)</label><input type="number" step="0.1" name="lebar" id="lebar" class="form-control" value="1" min="0.1" required></div>
                                <?php else: ?>
                                    <input type="hidden" name="panjang" value="1"><input type="hidden" name="lebar" value="1">
                                <?php endif; ?>

                                <div class="col-12 col-md-4"><label class="field-label" for="qty">Jumlah cetak</label><input type="number" name="qty" id="qty" class="form-control" value="1" min="1" required></div>
                            </div>
                        </section>

                        <section class="step">
                            <div class="step-head"><span class="step-no">2</span><h2 class="step-title">File dan desain</h2></div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="field-label" for="opsi_desain">Opsi desain</label>
                                    <div class="select-wrap"><select name="opsi_desain" id="opsi_desain" class="form-select">
                                        <option value="upload_mandiri">Desain mandiri</option>
                                        <option value="custom_desain">Request custom (+Rp 50rb)</option>
                                    </select><span class="select-chip" aria-hidden="true"><i class="bi bi-chevron-expand"></i></span></div>
                                </div>
                                <div class="col-md-6"><label class="field-label" for="file_desain">Upload file</label><input type="file" name="file_desain" id="file_desain" class="form-control"></div>
                                <div class="col-12"><label class="field-label" for="catatan">Catatan khusus (opsional)</label><textarea name="catatan" id="catatan" class="form-control" rows="2" placeholder="Tulis catatan tambahan untuk admin"></textarea></div>
                            </div>
                        </section>

                        <section class="step">
                            <div class="step-head"><span class="step-no">3</span><h2 class="step-title">Pengiriman</h2></div>
                            <div class="select-wrap"><select name="pengiriman" id="pengiriman" class="form-select" onchange="toggleAlamat()" required>
                                <option value="ambil_sendiri">Ambil sendiri di toko</option>
                                <option value="kirim_ojol">Kirim via ojek online (ongkir COD)</option>
                            </select><span class="select-chip" aria-hidden="true"><i class="bi bi-chevron-expand"></i></span></div>
                            <div id="alamat_box" class="alamat-box mt-3" style="display:none;">
                                <label class="field-label" for="alamat_pengiriman"><i class="bi bi-geo-alt-fill me-1" style="color: var(--color-1);"></i>Alamat tujuan</label>
                                <textarea name="alamat_pengiriman" id="alamat_pengiriman" class="form-control" rows="3" placeholder="Tulis alamat lengkap Anda"><?= htmlspecialchars($user['alamat_lengkap'] ?? ''); ?></textarea>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-glass p-4 sticky-lg-top" style="top: 90px;">
                        <h2 class="summary-title">Ringkasan pesanan</h2>

                        <?php if ($meteran): ?>
                            <div class="dim-box">
                                <svg viewBox="0 0 320 190" fill="none" stroke="currentColor" stroke-width="1.5" font-family="Plus Jakarta Sans, sans-serif" role="img" aria-label="Gambar ukuran cetak">
                                    <rect id="dimRect" x="50" y="48" width="220" height="110" fill="rgba(35,115,244,0.08)" stroke-width="2"/>
                                    <path id="dimTop" d=""/>
                                    <text id="dimTopText" x="160" y="24" text-anchor="middle" fill="currentColor" stroke="none" font-size="12" font-weight="600"></text>
                                    <path id="dimLeft" d=""/>
                                    <text id="dimLeftText" x="24" y="103" text-anchor="middle" fill="currentColor" stroke="none" font-size="12" font-weight="600"></text>
                                </svg>
                            </div>
                        <?php endif; ?>

                        <div class="summary-row"><span>Harga dasar</span><span>Rp <?= number_format($produk['harga_dasar'], 0, ',', '.'); ?> / <?= $satuan; ?></span></div>
                        <?php if ($meteran): ?>
                            <div class="summary-row"><span>Luas</span><span id="sumLuas">1 m²</span></div>
                        <?php endif; ?>
                        <div class="summary-row"><span>Jumlah</span><span id="sumQty">1</span></div>
                        <div class="summary-row" id="sumDesain" style="display:none;"><span>Desain custom</span><span>Rp 50.000</span></div>

                        <div class="summary-total-wrap">
                            <span class="text-muted small fw-bold d-block mb-1">Perkiraan harga</span>
                            <div class="summary-total" id="sumTotal">Rp 0</div>
                            <p class="text-muted small fw-medium mt-2 mb-0">Harga akhir dihitung saat pesanan dibuat.</p>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="action" value="checkout" class="btn-grad-primary w-100 py-3"><i class="bi bi-wallet2 me-2"></i>Beli langsung</button>
                            <button type="submit" name="action" value="keranjang" class="btn-outline-grad w-100 py-3"><i class="bi bi-cart-plus me-2"></i>Masuk keranjang</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <a href="https://wa.me/6281234567890?text=Halo%20Admin..." class="floating-wa" target="_blank" draggable="false" title="Tarik untuk memindahkan"><i class="bi bi-whatsapp fs-4"></i><span class="d-none d-md-inline">Chat Admin</span></a>

    <script>
        const hargaDasar = <?= (float) $produk['harga_dasar']; ?>;
        const meteran = <?= $meteran ? 'true' : 'false'; ?>;
        const rupiah = new Intl.NumberFormat('id-ID');
        const el = id => document.getElementById(id);
        const fmt = n => String(Math.round(n * 100) / 100).replace('.', ',');

        function toggleAlamat() {
            const opsi = el('pengiriman').value;
            const box = el('alamat_box');
            box.style.display = (opsi === 'kirim_ojol') ? 'block' : 'none';
            box.querySelector('textarea').required = (opsi === 'kirim_ojol');
        }

        function gambar(p, l) {
            const maxW = 220, maxH = 110, cx = 160, cy = 103;
            let w = maxW, h = maxH;
            if (p > 0 && l > 0) {
                const r = p / l;
                if (r >= maxW / maxH) { h = maxW / r; } else { w = maxH * r; }
            }
            w = Math.max(w, 24);
            h = Math.max(h, 24);
            const x = cx - w / 2, y = cy - h / 2;
            const rect = el('dimRect');
            rect.setAttribute('x', x);
            rect.setAttribute('y', y);
            rect.setAttribute('width', w);
            rect.setAttribute('height', h);
            el('dimTop').setAttribute('d', `M${x} ${y - 14}H${x + w}M${x} ${y - 20}V${y - 8}M${x + w} ${y - 20}V${y - 8}`);
            const tt = el('dimTopText');
            tt.setAttribute('x', cx);
            tt.setAttribute('y', y - 24);
            tt.textContent = 'panjang ' + fmt(p) + ' m';
            el('dimLeft').setAttribute('d', `M${x - 14} ${y}V${y + h}M${x - 20} ${y}H${x - 8}M${x - 20} ${y + h}H${x - 8}`);
            const lt = el('dimLeftText');
            const tx = x - 26;
            lt.setAttribute('x', tx);
            lt.setAttribute('y', cy);
            lt.setAttribute('transform', `rotate(-90 ${tx} ${cy})`);
            lt.textContent = 'lebar ' + fmt(l) + ' m';
        }

        function hitung() {
            const qty = Math.max(parseInt(el('qty').value) || 0, 0);
            const p = meteran ? Math.max(parseFloat(el('panjang').value) || 0, 0) : 1;
            const l = meteran ? Math.max(parseFloat(el('lebar').value) || 0, 0) : 1;
            const desain = el('opsi_desain').value === 'custom_desain' ? 50000 : 0;
            const luas = p * l;
            const total = hargaDasar * luas * qty + desain;
            el('sumQty').textContent = qty;
            el('sumTotal').textContent = 'Rp ' + rupiah.format(Math.round(total));
            el('sumDesain').style.display = desain ? 'flex' : 'none';
            if (meteran) {
                gambar(p, l);
                el('sumLuas').textContent = fmt(luas) + ' m²';
            }
        }

        let dskBuka = false;
        function cekDeskripsi() {
            const dsk = el('deskripsi');
            const btn = el('deskripsiToggle');
            if (dskBuka) { return; }
            dsk.classList.add('collapsed');
            const terpotong = dsk.scrollHeight > dsk.clientHeight + 1;
            btn.hidden = !terpotong;
            if (!terpotong) { dsk.classList.remove('collapsed'); }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const dsk = el('deskripsi');
            const btn = el('deskripsiToggle');
            btn.addEventListener('click', function () {
                dskBuka = !dskBuka;
                dsk.classList.toggle('collapsed', !dskBuka);
                btn.textContent = dskBuka ? 'Tampilkan lebih sedikit' : 'Selengkapnya';
                btn.setAttribute('aria-expanded', dskBuka);
            });
            window.addEventListener('resize', cekDeskripsi);
            cekDeskripsi();
            toggleAlamat();
            hitung();
            const form = document.querySelector('form');
            form.addEventListener('input', hitung);
            form.addEventListener('change', hitung);
        });
    </script>
    <script>
        (function () {
            const wa = document.querySelector('.floating-wa');
            const kunci = 'posisiWa';
            let mulaiX = 0, mulaiY = 0, awalKiri = 0, awalAtas = 0, geser = false, aktif = false;

            function pasang(kiri, atas) {
                const maksKiri = Math.max(window.innerWidth - wa.offsetWidth - 8, 8);
                const maksAtas = Math.max(window.innerHeight - wa.offsetHeight - 8, 8);
                wa.style.left = Math.min(Math.max(kiri, 8), maksKiri) + 'px';
                wa.style.top = Math.min(Math.max(atas, 8), maksAtas) + 'px';
                wa.style.right = 'auto';
                wa.style.bottom = 'auto';
            }

            try {
                const simpan = JSON.parse(localStorage.getItem(kunci));
                if (simpan) { pasang(simpan.kiri, simpan.atas); }
            } catch (e) {}

            wa.addEventListener('pointerdown', function (e) {
                aktif = true;
                geser = false;
                const r = wa.getBoundingClientRect();
                mulaiX = e.clientX;
                mulaiY = e.clientY;
                awalKiri = r.left;
                awalAtas = r.top;
                wa.setPointerCapture(e.pointerId);
            });

            wa.addEventListener('pointermove', function (e) {
                if (!aktif) { return; }
                const dx = e.clientX - mulaiX;
                const dy = e.clientY - mulaiY;
                if (!geser && Math.hypot(dx, dy) < 6) { return; }
                geser = true;
                wa.classList.add('dragging');
                pasang(awalKiri + dx, awalAtas + dy);
            });

            function selesai() {
                if (!aktif) { return; }
                aktif = false;
                wa.classList.remove('dragging');
                if (geser) {
                    const r = wa.getBoundingClientRect();
                    try { localStorage.setItem(kunci, JSON.stringify({ kiri: r.left, atas: r.top })); } catch (e) {}
                }
            }

            wa.addEventListener('pointerup', selesai);
            wa.addEventListener('pointercancel', selesai);

            wa.addEventListener('click', function (e) {
                if (geser) {
                    e.preventDefault();
                    geser = false;
                }
            });

            window.addEventListener('resize', function () {
                if (wa.style.left) {
                    const r = wa.getBoundingClientRect();
                    pasang(r.left, r.top);
                }
            });
        })();
    </script>
</body>
</html>