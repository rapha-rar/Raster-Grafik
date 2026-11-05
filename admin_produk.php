<?php
session_start();
require_once 'config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ---------------------------------------------------------
// 1. PROSES HAPUS SATU GAMBAR SPESIFIK (VIA ICON DELETE)
// ---------------------------------------------------------
if(isset($_GET['hapus_gambar']) && isset($_GET['id_produk'])){
    $img_to_delete = mysqli_real_escape_string($koneksi, $_GET['hapus_gambar']);
    $id_prod = intval($_GET['id_produk']);

    $q_img = mysqli_query($koneksi, "SELECT gambar_katalog FROM produk WHERE id_produk = $id_prod");
    if($d_img = mysqli_fetch_assoc($q_img)) {
        $gbr_raw = $d_img['gambar_katalog'];
        $gbr_list = [];
        $dec = json_decode($gbr_raw, true);
        
        if (is_array($dec)) { $gbr_list = $dec; }
        else if (strpos($gbr_raw, ',') !== false) { $gbr_list = explode(',', $gbr_raw); }
        else if (!empty($gbr_raw)) { $gbr_list = [$gbr_raw]; }

        if (($key = array_search($img_to_delete, $gbr_list)) !== false) {
            unset($gbr_list[$key]);
            
            $file_path = 'public/uploads/produk/' . $img_to_delete;
            if (file_exists($file_path) && is_file($file_path)) {
                unlink($file_path);
            }

            $gbr_list = array_values($gbr_list);
            $json_baru = empty($gbr_list) ? '' : json_encode($gbr_list, JSON_UNESCAPED_UNICODE);
            $json_baru_escaped = mysqli_real_escape_string($koneksi, $json_baru);

            mysqli_query($koneksi, "UPDATE produk SET gambar_katalog = '$json_baru_escaped' WHERE id_produk = $id_prod");
            
            echo "<script>alert('Satu gambar berhasil dihapus!'); window.location='admin_produk.php';</script>";
            exit();
        }
    }
}

// ---------------------------------------------------------
// 2. PROSES TAMBAH PRODUK BARU
// ---------------------------------------------------------
if (isset($_POST['simpan_produk'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga = $_POST['harga_dasar'];
    $tipe = $_POST['tipe_harga'];

    $spesifikasi_array = [];
    if (isset($_POST['kategori_nama']) && is_array($_POST['kategori_nama'])) {
        $namas = $_POST['kategori_nama'];
        $opsis = $_POST['kategori_opsi'];

        for ($i = 0; $i < count($namas); $i++) {
            $kat_nama = trim($namas[$i]);
            $list_opsi = array_map('trim', explode(',', $opsis[$i]));
            $list_opsi = array_filter($list_opsi); 
            if (!empty($kat_nama) && !empty($list_opsi)) {
                $spesifikasi_array[$kat_nama] = array_values($list_opsi);
            }
        }
    }
    $json_spesifikasi = mysqli_real_escape_string($koneksi, json_encode($spesifikasi_array, JSON_UNESCAPED_UNICODE));

    $gambar_array = [];
    if(isset($_FILES['gambar_katalog'])){
        $countfiles = count($_FILES['gambar_katalog']['name']);
        for($i = 0; $i < $countfiles; $i++){
            $filename = $_FILES['gambar_katalog']['name'][$i];
            if(!empty($filename) && $_FILES['gambar_katalog']['error'][$i] == 0){
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $newname = time() . '_' . uniqid() . '_' . $i . '.' . $ext;
                move_uploaded_file($_FILES['gambar_katalog']['tmp_name'][$i], 'public/uploads/produk/' . $newname);
                $gambar_array[] = $newname;
            }
        }
    }
    $json_gambar = empty($gambar_array) ? '' : json_encode($gambar_array, JSON_UNESCAPED_UNICODE);

    $query = "INSERT INTO produk (nama_produk, deskripsi, harga_dasar, tipe_harga, gambar_katalog, spesifikasi_json) 
              VALUES ('$nama', '$deskripsi', '$harga', '$tipe', '$json_gambar', '$json_spesifikasi')";
              
    if(mysqli_query($koneksi, $query)){
        echo "<script>alert('Produk berhasil ditambahkan!'); window.location='admin_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah produk!'); window.history.back();</script>";
    }
}

// ---------------------------------------------------------
// 3. PROSES UPDATE (EDIT) PRODUK
// ---------------------------------------------------------
if (isset($_POST['update_produk'])) {
    $id_produk = intval($_POST['id_produk']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga = $_POST['harga_dasar'];
    $tipe = $_POST['tipe_harga'];

    $spesifikasi_array = [];
    if (isset($_POST['kategori_nama']) && is_array($_POST['kategori_nama'])) {
        $namas = $_POST['kategori_nama'];
        $opsis = $_POST['kategori_opsi'];
        for ($i = 0; $i < count($namas); $i++) {
            $kat_nama = trim($namas[$i]);
            $list_opsi = array_map('trim', explode(',', $opsis[$i]));
            $list_opsi = array_filter($list_opsi); 
            if (!empty($kat_nama) && !empty($list_opsi)) {
                $spesifikasi_array[$kat_nama] = array_values($list_opsi);
            }
        }
    }
    $json_spesifikasi = mysqli_real_escape_string($koneksi, json_encode($spesifikasi_array, JSON_UNESCAPED_UNICODE));

    $q_old = mysqli_query($koneksi, "SELECT gambar_katalog FROM produk WHERE id_produk = $id_produk");
    $d_old = mysqli_fetch_assoc($q_old);
    $gambar_lama_raw = $d_old['gambar_katalog'];
    
    $gambar_final_array = [];
    $decoded = json_decode($gambar_lama_raw, true);
    if (is_array($decoded)) { $gambar_final_array = $decoded; }
    else if (strpos($gambar_lama_raw, ',') !== false) { $gambar_final_array = explode(',', $gambar_lama_raw); }
    else if (!empty($gambar_lama_raw)) { $gambar_final_array = [$gambar_lama_raw]; }

    if(isset($_FILES['gambar_katalog_baru']) && !empty($_FILES['gambar_katalog_baru']['name'][0])){
        $countfiles = count($_FILES['gambar_katalog_baru']['name']);
        for($i = 0; $i < $countfiles; $i++){
            $filename = $_FILES['gambar_katalog_baru']['name'][$i];
            if(!empty($filename) && $_FILES['gambar_katalog_baru']['error'][$i] == 0){
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $newname = time() . '_edit_' . uniqid() . '_' . $i . '.' . $ext;
                move_uploaded_file($_FILES['gambar_katalog_baru']['tmp_name'][$i], 'public/uploads/produk/' . $newname);
                $gambar_final_array[] = $newname;
            }
        }
    }

    $json_gambar_final = empty($gambar_final_array) ? '' : json_encode($gambar_final_array, JSON_UNESCAPED_UNICODE);
    $json_gambar_final_escaped = mysqli_real_escape_string($koneksi, $json_gambar_final);

    $query = "UPDATE produk SET 
              nama_produk = '$nama', 
              deskripsi = '$deskripsi', 
              harga_dasar = '$harga', 
              tipe_harga = '$tipe', 
              spesifikasi_json = '$json_spesifikasi',
              gambar_katalog = '$json_gambar_final_escaped'
              WHERE id_produk = $id_produk";
              
    if(mysqli_query($koneksi, $query)){
        echo "<script>alert('Produk berhasil diupdate!'); window.location='admin_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate produk!'); window.history.back();</script>";
    }
}

// ---------------------------------------------------------
// 4. PROSES HAPUS PRODUK KESELURUHAN
// ---------------------------------------------------------
if(isset($_GET['hapus'])){
    $id_hapus = intval($_GET['hapus']);
    
    $q_del = mysqli_query($koneksi, "SELECT gambar_katalog FROM produk WHERE id_produk = $id_hapus");
    $d_del = mysqli_fetch_assoc($q_del);
    if($d_del) {
        $del_dec = json_decode($d_del['gambar_katalog'], true);
        $del_arr = is_array($del_dec) ? $del_dec : [$d_del['gambar_katalog']];
        foreach($del_arr as $img_name) {
            $path = 'public/uploads/produk/' . $img_name;
            if (file_exists($path) && is_file($path)) unlink($path);
        }
    }

    mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk = $id_hapus");
    header("Location: admin_produk.php");
    exit();
}

$query_produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
$data_produk = [];
while($row = mysqli_fetch_assoc($query_produk)){
    $data_produk[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; }
        .navbar-admin { background: #0f172a; padding: 15px 0; }
        .logo-raster { color: #2373F4; font-weight: 900; letter-spacing: -0.5px; }
        .logo-grafik { color: #ffffff; font-weight: 900; letter-spacing: -0.5px; }
        .nav-link-admin { color: #94a3b8; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: 0.3s; text-decoration: none;}
        .nav-link-admin:hover, .nav-link-admin.active { color: #ffffff; background: rgba(255,255,255,0.1); }
        .card-admin { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .table-admin th { background: #f1f5f9; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem; border-bottom: 2px solid #e2e8f0;}
        .table-admin td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .btn-tambah { background: #2373F4; color: white; border-radius: 8px; font-weight: 600; transition: 0.3s; }
        .btn-tambah:hover { background: #1d5ec9; color: white; }
    </style>
</head>
<body>

    <nav class="navbar-admin mb-4">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-4">
                <a href="admin_pesanan.php" class="text-decoration-none fs-4 me-3">
                    <span class="logo-raster">RASTER</span><span class="logo-grafik">GRAFIK</span>
                    <span class="text-secondary fs-6 fw-normal ms-2 border-start border-secondary ps-2">Admin Panel</span>
                </a>
                <a href="admin_pesanan.php" class="nav-link-admin"><i class="bi bi-cart-check me-2"></i>Kelola Pesanan</a>
                <a href="admin_produk.php" class="nav-link-admin active"><i class="bi bi-box-seam me-2"></i>Kelola Produk</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light fw-medium"><i class="bi bi-person-circle me-2 text-primary"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold px-3 border-danger border-opacity-50">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0"><i class="bi bi-box-seam text-primary me-2"></i>Katalog Produk</h3>
            <button class="btn btn-tambah px-4" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
                <i class="bi bi-plus-lg me-2"></i>Tambah Produk Baru
            </button>
        </div>

        <div class="card-admin p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr>
                            <th width="80">Thumbnail</th>
                            <th>Nama & Deskripsi</th>
                            <th>Tipe</th>
                            <th>Harga Dasar</th>
                            <th width="220" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_produk as $row) : 
                            $gambar_raw = $row['gambar_katalog'];
                            $decoded = json_decode($gambar_raw, true);
                            if(is_array($decoded)) { $thumbnail = $decoded[0] ?? 'default'; }
                            else if(strpos($gambar_raw, ',') !== false) { $exp = explode(',', $gambar_raw); $thumbnail = $exp[0]; }
                            else { $thumbnail = $gambar_raw; }
                            
                            $img_src = (strpos(trim($thumbnail), 'http') === 0) ? trim($thumbnail) : 'public/uploads/produk/' . trim($thumbnail);
                        ?>
                        <tr>
                            <td>
                                <img src="<?= $img_src; ?>" class="rounded bg-light" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #e2e8f0;" onerror="this.src='https://via.placeholder.com/60?text=No+Img'">
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($row['nama_produk']); ?></strong>
                                <small class="text-muted d-inline-block text-truncate" style="max-width: 300px;"><?= htmlspecialchars($row['deskripsi']); ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border px-2 py-1"><?= strtoupper($row['tipe_harga']); ?></span></td>
                            <td class="fw-bold text-primary">Rp <?= number_format($row['harga_dasar'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <!-- TOMBOL PREVIEW DETAIL PRODUK -->
                                <button class="btn btn-sm btn-outline-info px-2 py-1 rounded-pill fw-bold me-1" data-bs-toggle="modal" data-bs-target="#modalPreview<?= $row['id_produk']; ?>" title="Preview Detail"><i class="bi bi-eye"></i></button>
                                <!-- TOMBOL EDIT -->
                                <button class="btn btn-sm btn-outline-secondary px-3 py-1 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#modalEditProduk<?= $row['id_produk']; ?>">Edit</button>
                                <!-- TOMBOL HAPUS -->
                                <a href="?hapus=<?= $row['id_produk']; ?>" onclick="return confirm('Yakin hapus produk ini?');" class="btn btn-sm btn-outline-danger rounded-circle ms-1"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_produk)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data produk.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =============================================================
         MODAL TAMBAH PRODUK BARU
    ============================================================== -->
    <div class="modal fade" id="modalTambahProduk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form action="" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-plus-circle-fill me-2"></i>Tambah Produk Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-12"><label class="fw-bold mb-2 small">Nama Produk</label><input type="text" name="nama_produk" class="form-control" placeholder="Cth: Cetak Banner Outdoor" required></div>
                        <div class="col-md-6">
                            <label class="fw-bold mb-2 small">Tipe Harga</label>
                            <select name="tipe_harga" class="form-select" required><option value="meteran">Meteran (m²)</option><option value="pcs">Satuan (Pcs)</option></select>
                        </div>
                        <div class="col-md-6"><label class="fw-bold mb-2 small">Harga Dasar (Rp)</label><input type="number" name="harga_dasar" class="form-control" placeholder="Cth: 15000" required></div>
                        <div class="col-md-12"><label class="fw-bold mb-2 small">Deskripsi Produk</label><textarea name="deskripsi" class="form-control" rows="2" placeholder="Tuliskan penjelasan produk di sini..." required></textarea></div>
                    </div>

                    <hr class="my-4 border-secondary border-opacity-25">

                    <div class="mb-3">
                        <label class="fw-bold mb-1 text-primary"><i class="bi bi-sliders me-2"></i>Opsi Spesifikasi</label>
                        <p class="text-muted small mb-3">Tambahkan kategori pilihan yang bisa dipilih pelanggan. (Dipisahkan koma ",")</p>
                        <div id="wrapper-spesifikasi">
                            <div class="card p-3 mb-2 border bg-light item-kategori rounded-3 shadow-sm">
                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-md-5"><input type="text" name="kategori_nama[]" class="form-control form-control-sm fw-bold border-primary" value="Pilihan Bahan" required></div>
                                    <div class="col-md-7 text-end"><button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="hapusKategori(this)"><i class="bi bi-x-circle me-1"></i> Hapus</button></div>
                                </div>
                                <div><textarea name="kategori_opsi[]" class="form-control form-control-sm" rows="2" required>Flexi 280g, Flexi 340g</textarea></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 mt-2" onclick="tambahKategori()"><i class="bi bi-plus-lg me-1"></i> Tambah Kategori Lain</button>
                    </div>

                    <hr class="my-4 border-secondary border-opacity-25">

                    <div class="mb-2">
                        <label class="fw-bold mb-2 small text-dark">Upload Gambar Katalog (Multi Upload)</label>
                        <input type="file" name="gambar_katalog[]" class="form-control" accept="image/*" multiple required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
                    <button type="button" class="btn btn-light border px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_produk" class="btn btn-primary px-4 rounded-pill fw-bold">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- =============================================================
         LOOPING MODAL PREVIEW DETAIL PRODUK (ADMIN VIEW)
    ============================================================== -->
    <?php foreach ($data_produk as $row) : ?>
    <div class="modal fade" id="modalPreview<?= $row['id_produk']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-light border-bottom-0 pb-2">
                    <h6 class="modal-title fw-bold text-primary"><i class="bi bi-eye me-2"></i>Preview Tampilan Pelanggan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 bg-white">
                    <!-- TAMPILAN KARTU PRODUK MIRIP DI BERANDA -->
                    <div class="card border rounded-4 shadow-sm overflow-hidden bg-white">
                        
                        <!-- Slider Preview Gambar -->
                        <div id="carouselPreview<?= $row['id_produk'] ?>" class="carousel slide bg-light" data-bs-ride="false" style="aspect-ratio: 1/1; position: relative;">
                            <span class="badge bg-primary position-absolute top-0 end-0 m-3 rounded-pill px-3 py-2 shadow-sm" style="z-index: 10; font-size:0.7rem;">Custom</span>
                            <div class="carousel-inner h-100">
                                <?php 
                                    $prev_raw = $row['gambar_katalog'];
                                    $prev_list = [];
                                    $dec_prev = json_decode($prev_raw, true);
                                    if(is_array($dec_prev)) { $prev_list = $dec_prev; }
                                    else if(strpos($prev_raw, ',') !== false) { $prev_list = explode(',', $prev_raw); }
                                    else if(!empty($prev_raw)) { $prev_list = [$prev_raw]; }
                                    $prev_list = array_filter(array_map('trim', $prev_list));
                                    if(empty($prev_list)) { $prev_list = ['default']; }

                                    $p_count = 0;
                                    foreach($prev_list as $pgbr):
                                        $p_active = ($p_count === 0) ? 'active' : '';
                                        $p_url = (strpos($pgbr, 'http') === 0) ? $pgbr : 'public/uploads/produk/' . $pgbr;
                                        if($pgbr === 'default') $p_url = 'https://via.placeholder.com/400x400?text=No+Image';
                                ?>
                                    <div class="carousel-item h-100 <?= $p_active; ?>">
                                        <img src="<?= $p_url; ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='https://via.placeholder.com/400?text=Error+Load'">
                                    </div>
                                <?php $p_count++; endforeach; ?>
                            </div>
                            <?php if($p_count > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselPreview<?= $row['id_produk'] ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span></button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselPreview<?= $row['id_produk'] ?>" data-bs-slide="next"><span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span></button>
                            <?php endif; ?>
                        </div>

                        <!-- Info Detail -->
                        <div class="p-3">
                            <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($row['nama_produk']); ?></h5>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($row['deskripsi']); ?></p>
                            
                            <div class="mb-3">
                                <span class="text-muted small d-block">Mulai dari</span>
                                <h4 class="fw-bold text-primary mb-0">Rp <?= number_format($row['harga_dasar'], 0, ',', '.'); ?> <small class="text-muted fs-6">/ <?= ($row['tipe_harga']=='meteran')?'m²':'pcs'; ?></small></h4>
                            </div>

                            <!-- Simulasi Pilihan Spesifikasi JSON -->
                            <div class="bg-light p-3 rounded-3 border mb-3">
                                <span class="small fw-bold text-secondary d-block mb-2"><i class="bi bi-sliders me-1"></i> Simulasi Pilihan Pelanggan:</span>
                                <?php 
                                    $sim_spek = json_decode($row['spesifikasi_json'], true);
                                    if(!empty($sim_spek) && is_array($sim_spek)):
                                        foreach($sim_spek as $skat => $sops):
                                ?>
                                    <div class="mb-2">
                                        <label class="small fw-bold text-dark mb-1"><?= htmlspecialchars($skat); ?></label>
                                        <select class="form-select form-select-sm bg-white" disabled>
                                            <?php foreach($sops as $so): ?>
                                                <option><?= htmlspecialchars($so); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php 
                                        endforeach;
                                    else:
                                        echo '<small class="text-muted">Tidak ada spesifikasi khusus.</small>';
                                    endif;
                                ?>
                            </div>

                            <button type="button" class="btn btn-primary w-100 py-2 rounded-pill fw-bold opacity-75" disabled><i class="bi bi-bag-plus me-1"></i> Tombol Pesan (Pelanggan)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- =============================================================
         LOOPING MODAL EDIT PRODUK
    ============================================================== -->
    <?php foreach ($data_produk as $row) : ?>
    <div class="modal fade" id="modalEditProduk<?= $row['id_produk']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form action="" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
                <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Edit Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-12"><label class="fw-bold mb-2 small">Nama Produk</label><input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($row['nama_produk']); ?>" required></div>
                        <div class="col-md-6">
                            <label class="fw-bold mb-2 small">Tipe Harga</label>
                            <select name="tipe_harga" class="form-select" required>
                                <option value="meteran" <?= ($row['tipe_harga'] == 'meteran') ? 'selected' : ''; ?>>Meteran (m²)</option>
                                <option value="pcs" <?= ($row['tipe_harga'] == 'pcs') ? 'selected' : ''; ?>>Satuan (Pcs)</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="fw-bold mb-2 small">Harga Dasar (Rp)</label><input type="number" name="harga_dasar" class="form-control" value="<?= $row['harga_dasar']; ?>" required></div>
                        <div class="col-md-12"><label class="fw-bold mb-2 small">Deskripsi Produk</label><textarea name="deskripsi" class="form-control" rows="2" required><?= htmlspecialchars($row['deskripsi']); ?></textarea></div>
                    </div>

                    <hr class="my-4 border-secondary border-opacity-25">

                    <div class="mb-3">
                        <label class="fw-bold mb-1 text-primary"><i class="bi bi-sliders me-2"></i>Opsi Spesifikasi</label>
                        <div id="wrapper-spesifikasi-edit-<?= $row['id_produk']; ?>">
                            <?php 
                            $spek_edit = json_decode($row['spesifikasi_json'], true);
                            if (!empty($spek_edit) && is_array($spek_edit)): 
                                foreach($spek_edit as $kategori => $opsis): 
                                    $string_opsi = implode(', ', $opsis);
                            ?>
                                <div class="card p-3 mb-2 border bg-light item-kategori rounded-3 shadow-sm">
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-md-5"><input type="text" name="kategori_nama[]" class="form-control form-control-sm fw-bold border-primary" value="<?= htmlspecialchars($kategori); ?>" required></div>
                                        <div class="col-md-7 text-end"><button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="hapusKategori(this)"><i class="bi bi-x-circle"></i></button></div>
                                    </div>
                                    <div><textarea name="kategori_opsi[]" class="form-control form-control-sm" rows="2" required><?= htmlspecialchars($string_opsi); ?></textarea></div>
                                </div>
                            <?php endforeach; else: ?>
                                <div class="card p-3 mb-2 border bg-light item-kategori rounded-3 shadow-sm">
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-md-5"><input type="text" name="kategori_nama[]" class="form-control form-control-sm fw-bold border-primary" placeholder="Cth: Bahan" required></div>
                                        <div class="col-md-7 text-end"><button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="hapusKategori(this)"><i class="bi bi-x-circle"></i></button></div>
                                    </div>
                                    <div><textarea name="kategori_opsi[]" class="form-control form-control-sm" rows="2" required></textarea></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 mt-2" onclick="tambahKategoriEdit(<?= $row['id_produk']; ?>)"><i class="bi bi-plus-lg me-1"></i> Kategori Lain</button>
                    </div>

                    <hr class="my-4 border-secondary border-opacity-25">

                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-dark"><i class="bi bi-images me-2"></i>Kelola Gambar Produk Saat Ini (Klik Sampah untuk Hapus)</label>
                        
                        <div class="d-flex flex-wrap gap-2 mb-3 bg-light p-3 border rounded-3">
                            <?php 
                                $gbr_raw = $row['gambar_katalog'];
                                $gbr_list = [];
                                $dec = json_decode($gbr_raw, true);
                                if (is_array($dec)) { $gbr_list = $dec; }
                                else if (strpos($gbr_raw, ',') !== false) { $gbr_list = explode(',', $gbr_raw); }
                                else if (!empty($gbr_raw)) { $gbr_list = [$gbr_raw]; }

                                if(empty($gbr_list) || (count($gbr_list)==1 && $gbr_list[0]=='')):
                                    echo '<span class="text-muted small">Tidak ada gambar tersimpan.</span>';
                                else:
                                    foreach($gbr_list as $gbr):
                                        $gbr = trim($gbr);
                                        if(empty($gbr)) continue;
                                        $img_src = (strpos($gbr, 'http') === 0) ? $gbr : 'public/uploads/produk/' . $gbr;
                            ?>
                                <div class="position-relative border rounded p-1 bg-white shadow-sm text-center" style="width: 85px;">
                                    <img src="<?= $img_src; ?>" class="rounded" style="width: 100%; height: 75px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/75?text=No+Img'">
                                    
                                    <a href="?hapus_gambar=<?= urlencode($gbr); ?>&id_produk=<?= $row['id_produk']; ?>" 
                                       onclick="return confirm('Yakin ingin menghapus gambar ini secara permanen?');" 
                                       class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center rounded-circle shadow" 
                                       style="width: 22px; height: 22px; z-index: 10;" title="Hapus Gambar">
                                        <i class="bi bi-trash text-white" style="font-size: 0.75rem;"></i>
                                    </a>
                                </div>
                            <?php 
                                    endforeach;
                                endif; 
                            ?>
                        </div>

                        <label class="fw-bold mb-2 small text-dark">Tambah Gambar Baru (Opsional)</label>
                        <input type="file" name="gambar_katalog_baru[]" class="form-control" accept="image/*" multiple>
                    </div>

                </div>
                
                <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
                    <button type="button" class="btn btn-light border px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_produk" class="btn btn-primary px-4 rounded-pill fw-bold">Update Produk</button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- SCRIPT JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function tambahKategori() {
        let html = `
        <div class="card p-3 mb-2 border bg-light item-kategori rounded-3 shadow-sm">
            <div class="row g-2 mb-2 align-items-center">
                <div class="col-md-5"><input type="text" name="kategori_nama[]" class="form-control form-control-sm fw-bold border-primary" placeholder="Nama Kategori" required></div>
                <div class="col-md-7 text-end"><button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="hapusKategori(this)"><i class="bi bi-x-circle me-1"></i> Hapus</button></div>
            </div>
            <div><textarea name="kategori_opsi[]" class="form-control form-control-sm" rows="2" placeholder="Pilihan..." required></textarea></div>
        </div>`;
        document.getElementById('wrapper-spesifikasi').insertAdjacentHTML('beforeend', html);
    }

    function tambahKategoriEdit(id) {
        let html = `
        <div class="card p-3 mb-2 border bg-light item-kategori rounded-3 shadow-sm">
            <div class="row g-2 mb-2 align-items-center">
                <div class="col-md-5"><input type="text" name="kategori_nama[]" class="form-control form-control-sm fw-bold border-primary" placeholder="Nama Kategori" required></div>
                <div class="col-md-7 text-end"><button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="hapusKategori(this)"><i class="bi bi-x-circle me-1"></i> Hapus</button></div>
            </div>
            <div><textarea name="kategori_opsi[]" class="form-control form-control-sm" rows="2" placeholder="Pilihan..." required></textarea></div>
        </div>`;
        document.getElementById('wrapper-spesifikasi-edit-' + id).insertAdjacentHTML('beforeend', html);
    }

    function hapusKategori(btn) {
        let wrapper = btn.closest('.mb-3'); 
        let items = wrapper.querySelectorAll('.item-kategori');
        if(items.length > 1) {
            btn.closest('.item-kategori').remove();
        } else {
            alert('Minimal harus ada 1 kategori spesifikasi!');
        }
    }
    </script>
</body>
</html>