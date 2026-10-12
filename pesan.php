<?php
// Untuk sementara kita panggil koneksi DB (nantinya digunakan untuk insert pesanan)
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --biru-rg: #0088cc;
            --merah-rg: #cc0000;
        }
        .text-merah { color: var(--merah-rg); }
        .bg-merah { background-color: var(--merah-rg); color: white; }
        .sticky-ringkasan {
            position: -webkit-sticky;
            position: sticky;
            top: 20px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar Sederhana -->
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span style="color:var(--biru-rg);">RASTER</span><span class="text-merah">GRAFIK</span>
            </a>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Batal & Kembali</a>
        </div>
    </nav>

    <div class="container pb-5">
        <h3 class="fw-bold mb-4">Buat Pesanan: <span class="text-merah">Spanduk Flexi 280gr</span></h3>
        
        <!-- FORM DIMULAI DI SINI -->
        <form action="proses_pesanan.php" method="POST" enctype="multipart/form-data" class="row">
            
            <!-- KOLOM KIRI: FORM INPUT -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">1. Spesifikasi Cetak</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Panjang (Meter)</label>
                            <input type="number" id="input-panjang" name="panjang" class="form-control calc-trigger" step="0.1" min="0.1" value="1" placeholder="Contoh: 3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lebar (Meter)</label>
                            <input type="number" id="input-lebar" name="lebar" class="form-control calc-trigger" step="0.1" min="0.1" value="1" placeholder="Contoh: 2" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Cetak (Pcs)</label>
                            <input type="number" id="input-qty" name="qty" class="form-control calc-trigger" min="1" value="1" required>
                        </div>
                    </div>

                    <h5 class="fw-bold mt-4 mb-3 border-bottom pb-2">2. Opsi Desain</h5>
                    <div class="mb-3">
                        <select id="opsi-desain" name="opsi_desain" class="form-select calc-trigger" required>
                            <option value="upload_sendiri" data-harga="0">Upload File Sendiri (Gratis)</option>
                            <option value="pilih_template" data-harga="0">Pilih Template dari Raster Grafik (Gratis)</option>
                            <option value="custom_desain" data-harga="50000">Request Custom Desain (+ Rp 50.000)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan / Brief Desain</label>
                        <textarea class="form-control" name="catatan" rows="3" placeholder="Contoh: Tolong warna dominan merah, kasih logo halal..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload File / Referensi (Opsional)</label>
                        <!-- Pastikan name atribut diset agar file bisa ditangkap PHP nantinya -->
                        <input class="form-control" type="file" name="file_desain">
                        <small class="text-muted">Maksimal ukuran file: 20MB. Format: JPG, PNG, PDF, CDR.</small>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: RINGKASAN & PEMBAYARAN -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 sticky-ringkasan">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Ringkasan Biaya</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Harga Dasar:</span>
                        <span>Rp 20.000/m²</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Biaya Cetak:</span>
                        <span id="text-cetak" class="fw-bold">Rp 20.000</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Jasa Desain:</span>
                        <span id="text-desain" class="fw-bold">Rp 0</span>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Metode Pengiriman</label>
                        <select id="metode-pengiriman" name="pengiriman" class="form-select" required>
                            <option value="ambil_di_toko">Ambil di Toko (Gratis)</option>
                            <option value="ojek_online">Kirim via Ojol (Gosend/Grab)</option>
                        </select>
                    </div>

                    <!-- Kotak Peringatan Ongkir COD (Sembunyi secara default) -->
                    <div id="info-ongkir" class="alert alert-warning py-2 mb-3" style="display: none; font-size: 0.85rem;">
                        ⚠️ <b>Ongkos Kirim Belum Termasuk!</b><br>Ongkir dibayarkan langsung kepada driver saat pesanan Anda tiba (COD).
                    </div>

                    <div class="d-flex justify-content-between mb-3 bg-light p-2 rounded">
                        <h5 class="fw-bold mb-0">TOTAL TAGIHAN:</h5>
                        <h5 class="fw-bold text-merah mb-0" id="text-grandtotal">Rp 20.000</h5>
                    </div>

                    <!-- Syarat & Ketentuan Resolusi File -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="syarat-file">
                        <label class="form-check-label text-muted" style="font-size: 0.8rem;" for="syarat-file">
                            Saya setuju bahwa saya bertanggung jawab penuh jika hasil cetak pecah akibat resolusi file saya yang rendah.
                        </label>
                    </div>

                    <!-- Tombol berubah menjadi tipe Submit -->
                    <button type="submit" id="btn-checkout" class="btn bg-merah w-100 fw-bold py-2" disabled>Buat Pesanan & Bayar</button>
                </div>
            </div>
            
        </form>
        <!-- FORM SELESAI DI SINI -->
    </div>

    <!-- LOGIKA JAVASCRIPT -->
    <script>
        // Harga Master Spanduk Flexi per meter persegi
        const hargaPerMeter = 20000;

        // Ambil elemen HTML untuk Kalkulator
        const inputP = document.getElementById('input-panjang');
        const inputL = document.getElementById('input-lebar');
        const inputQty = document.getElementById('input-qty');
        const opsiDesain = document.getElementById('opsi-desain');
        
        // Ambil elemen Text yang akan diubah angkanya
        const textCetak = document.getElementById('text-cetak');
        const textDesain = document.getElementById('text-desain');
        const textGrandtotal = document.getElementById('text-grandtotal');

        // Fungsi Format Rupiah
        function formatRupiah(angka) {
            return "Rp " + angka.toLocaleString('id-ID');
        }

        // FUNGSI UTAMA: Menghitung Total
        function hitungTotal() {
            // 1. Ambil nilai dari input (jika kosong, anggap 0)
            let p = parseFloat(inputP.value) || 0;
            let l = parseFloat(inputL.value) || 0;
            let qty = parseInt(inputQty.value) || 0;
            
            // 2. Hitung Biaya Cetak (P x L x Qty x Harga)
            let totalCetak = p * l * qty * hargaPerMeter;
            
            // 3. Ambil Biaya Desain (Membaca atribut data-harga karena value-nya sekarang berupa teks)
            let opsiTerpilih = opsiDesain.options[opsiDesain.selectedIndex];
            let biayaDesain = parseInt(opsiTerpilih.getAttribute('data-harga')) || 0;
            
            // 4. Hitung Grand Total
            let grandTotal = totalCetak + biayaDesain;

            // 5. Tampilkan ke layar
            textCetak.innerText = formatRupiah(totalCetak);
            textDesain.innerText = formatRupiah(biayaDesain);
            textGrandtotal.innerText = formatRupiah(grandTotal);
        }

        // PASANG LISTENER: Jalankan hitungTotal() setiap kali user mengetik/memilih
        const triggers = document.querySelectorAll('.calc-trigger');
        triggers.forEach(trigger => {
            trigger.addEventListener('input', hitungTotal);
            trigger.addEventListener('change', hitungTotal);
        });

        // -------------------------------------------------------------
        // LOGIKA PENGIRIMAN & TOMBOL CHECKOUT
        
        const pengiriman = document.getElementById('metode-pengiriman');
        const infoOngkir = document.getElementById('info-ongkir');
        const checkboxSyarat = document.getElementById('syarat-file');
        const btnCheckout = document.getElementById('btn-checkout');

        // Tampilkan peringatan Ojol jika dipilih
        pengiriman.addEventListener('change', function() {
            if (this.value === 'ojek_online') {
                infoOngkir.style.display = 'block';
            } else {
                infoOngkir.style.display = 'none';
            }
        });

        // Kunci tombol checkout jika syarat belum dicentang
        checkboxSyarat.addEventListener('change', function() {
            if(this.checked) {
                btnCheckout.removeAttribute('disabled');
            } else {
                btnCheckout.setAttribute('disabled', 'true');
            }
        });
    </script>
</body>
</html>