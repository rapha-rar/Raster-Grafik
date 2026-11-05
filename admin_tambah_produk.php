<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Admin Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold m-0"><i class="bi bi-box-seam text-primary me-2"></i>Tambah Produk Baru</h3>
                    <a href="admin_produk.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Batal / Kembali</a>
                </div>

                <div class="card p-4">
                    <form action="proses_tambah_produk.php" method="POST" enctype="multipart/form-data">
                        <!-- Data Utama Produk -->
                        <div class="mb-3">
                            <label class="fw-bold mb-2">Nama Produk</label>
                            <input type="text" name="nama_produk" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold mb-2">Deskripsi Singkat</label>
                            <textarea name="deskripsi" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="fw-bold mb-2">Harga Dasar (Rp)</label>
                                <input type="number" name="harga_dasar" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold mb-2">Tipe Harga</label>
                                <select name="tipe_harga" class="form-select" required>
                                    <option value="meteran">Per Meter Persegi (m²)</option>
                                    <option value="pcs">Per Pcs / Satuan</option>
                                </select>
                            </div>
                        </div>

                        <hr class="text-muted mb-4">

                        <!-- KELOLA SPESIFIKASI DINAMIS (JSON) -->
                        <div class="mb-4">
                            <label class="fw-bold mb-2 text-primary fs-5"><i class="bi bi-sliders me-2"></i>Spesifikasi & Variasi Produk</label>
                            <p class="text-muted small">Tambahkan kategori pilihan yang bisa dipilih pelanggan (Bahan, Model, Finishing, dll).</p>
                            
                            <div id="wrapper-spesifikasi">
                                <!-- Default Item -->
                                <div class="card p-3 mb-3 border bg-light item-kategori">
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-md-5">
                                            <input type="text" name="kategori_nama[]" class="form-control form-control-sm fw-bold border-primary" placeholder="Nama Kategori (Cth: Bahan)" value="Bahan Cetak" required>
                                        </div>
                                        <div class="col-md-7 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusKategori(this)"><i class="bi bi-trash"></i> Hapus</button>
                                        </div>
                                    </div>
                                    <div>
                                        <input type="text" name="kategori_opsi[]" class="form-control form-control-sm" placeholder="Pilihan (pisahkan koma. Cth: Flexi 280gr, Flexi 340gr)" required>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-primary rounded-pill fw-bold px-3" onclick="tambahKategori()">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Kategori Lain
                            </button>
                        </div>

                        <hr class="text-muted mb-4">

                        <div class="mb-4">
                            <label class="fw-bold mb-2">Upload Gambar Katalog</label>
                            <input type="file" name="gambar_katalog" class="form-control" accept="image/*" required>
                        </div>

                        <button type="submit" name="simpan_produk" class="btn btn-success w-100 py-2 fw-bold fs-5 rounded-pill"><i class="bi bi-save me-2"></i>Simpan Produk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function tambahKategori() {
        let html = `
        <div class="card p-3 mb-3 border bg-light item-kategori">
            <div class="row g-2 mb-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="kategori_nama[]" class="form-control form-control-sm fw-bold border-primary" placeholder="Nama Kategori (Cth: Finishing)" required>
                </div>
                <div class="col-md-7 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusKategori(this)"><i class="bi bi-trash"></i> Hapus</button>
                </div>
            </div>
            <div>
                <input type="text" name="kategori_opsi[]" class="form-control form-control-sm" placeholder="Pilihan (pisahkan koma. Cth: Mata Ayam, Lipat Pas, Selongsong)" required>
            </div>
        </div>`;
        document.getElementById('wrapper-spesifikasi').insertAdjacentHTML('beforeend', html);
    }

    function hapusKategori(btn) {
        let items = document.querySelectorAll('.item-kategori');
        if(items.length > 1) {
            btn.closest('.item-kategori').remove();
        } else {
            alert('Minimal harus ada 1 kategori spesifikasi!');
        }
    }
    </script>
</body>
</html>