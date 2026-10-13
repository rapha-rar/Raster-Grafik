<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$dir_upload = "public/uploads/produk/";
if (!is_dir($dir_upload)) mkdir($dir_upload, 0777, true);

// --- LOGIKA AKSI ADMIN (CRUD) ---
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah_produk') {
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $tipe_harga = $_POST['tipe_harga'];
    $harga_dasar = floatval($_POST['harga_dasar']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $nama_file_gambar = "default.jpg";

    if (isset($_FILES['gambar_katalog']) && $_FILES['gambar_katalog']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar_katalog']['name'], PATHINFO_EXTENSION);
        $nama_file_gambar = "PRODUK_" . time() . "_" . rand(100, 999) . "." . $ext;
        move_uploaded_file($_FILES['gambar_katalog']['tmp_name'], $dir_upload . $nama_file_gambar);
    }
    $query_tambah = "INSERT INTO produk (nama_produk, tipe_harga, harga_dasar, deskripsi, gambar_katalog) VALUES ('$nama_produk', '$tipe_harga', $harga_dasar, '$deskripsi', '$nama_file_gambar')";
    if (mysqli_query($koneksi, $query_tambah)) {
        echo "<script>alert('Produk baru berhasil ditambahkan!'); window.location.href='admin_produk.php';</script>";
    }
}

if (isset($_POST['aksi']) && $_POST['aksi'] == 'edit_produk') {
    $id_produk = intval($_POST['id_produk']);
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $tipe_harga = $_POST['tipe_harga'];
    $harga_dasar = floatval($_POST['harga_dasar']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $gambar_lama = $_POST['gambar_lama'];
    $nama_file_gambar = $gambar_lama;

    if (isset($_FILES['gambar_katalog']) && $_FILES['gambar_katalog']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar_katalog']['name'], PATHINFO_EXTENSION);
        $nama_file_gambar = "PRODUK_" . time() . "_" . rand(100, 999) . "." . $ext;
        move_uploaded_file($_FILES['gambar_katalog']['tmp_name'], $dir_upload . $nama_file_gambar);
    }
    $query_edit = "UPDATE produk SET nama_produk = '$nama_produk', tipe_harga = '$tipe_harga', harga_dasar = $harga_dasar, deskripsi = '$deskripsi', gambar_katalog = '$nama_file_gambar' WHERE id_produk = $id_produk";
    if (mysqli_query($koneksi, $query_edit)) {
        echo "<script>alert('Data produk berhasil diperbarui!'); window.location.href='admin_produk.php';</script>";
    }
}

if (isset($_POST['aksi']) && $_POST['aksi'] == 'hapus_produk') {
    $id_produk = intval($_POST['id_produk']);
    try {
        if (mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk = $id_produk")) {
            echo "<script>alert('Produk berhasil dihapus!'); window.location.href='admin_produk.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Produk tidak dapat dihapus karena sudah ada riwayat pesanan pelanggan yang menggunakan produk ini.'); window.location.href='admin_produk.php';</script>";
    }
}

$query_produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Raster Grafik</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; --bg-light: #f4f7f6; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); color: #333; }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg) !important; }
        
        .admin-navbar { background-color: #1a1d20 !important; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        
        .table-custom th { background-color: #f8f9fa; font-weight: 600; color: #555; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border-bottom: 2px solid #eee; }
        .table-custom td { vertical-align: middle; font-size: 0.9rem; }
        .badge-custom { padding: 0.5em 0.8em; font-weight: 500; border-radius: 30px; }
    </style>
</head>
<body>

    <!-- Top Navigation Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar shadow-sm mb-4 py-3">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold fs-4" href="#">
                <span class="text-biru">RASTER</span><span class="text-danger">GRAFIK</span> 
                <span class="fs-6 text-secondary fw-normal ms-2 border-start border-secondary ps-2">Panel Admin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4 fw-medium">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_pesanan.php"><i class="bi bi-cart-check me-1"></i> Kelola Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <!-- UBAH WARNA DARI KUNING KE BIRU SAMA SEPERTI LOGO -->
                        <a class="nav-link active fw-bold" style="color: var(--biru-rg) !important;" href="admin_produk.php"><i class="bi bi-box-seam me-1"></i> Kelola Produk</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-light me-4 small"><i class="bi bi-person-badge me-1"></i> Halo, <?= htmlspecialchars($_SESSION['nama']); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-boxes me-2 text-biru"></i> Kelola Katalog Produk</h3>
            <button class="btn btn-danger fw-medium rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
                <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
            </button>
        </div>

        <div class="card card-custom p-4">
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Gambar</th>
                            <th width="20%">Nama Produk</th>
                            <th width="15%">Tipe Harga</th>
                            <th width="15%">Harga Dasar</th>
                            <th width="25%">Deskripsi</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_produk) > 0): ?>
                            <?php $no = 1; while ($p = mysqli_fetch_assoc($query_produk)): 
                                $img_src = (strpos($p['gambar_katalog'], 'http') === 0) ? $p['gambar_katalog'] : 'public/uploads/produk/' . $p['gambar_katalog'];
                            ?>
                                <tr>
                                    <td class="fw-medium"><?= $no++; ?></td>
                                    <td>
                                        <img src="<?= $img_src; ?>" width="60" height="60" class="rounded-3 shadow-sm object-fit-cover border" alt="Produk">
                                    </td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama_produk']); ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($p['tipe_harga'] == 'meteran') ? 'info' : 'secondary'; ?> bg-opacity-10 text-<?= ($p['tipe_harga'] == 'meteran') ? 'info' : 'secondary'; ?> badge-custom text-capitalize">
                                            <i class="bi <?= ($p['tipe_harga'] == 'meteran') ? 'bi-rulers' : 'bi-box'; ?> me-1"></i> <?= $p['tipe_harga']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-merah">
                                        Rp <?= number_format($p['harga_dasar'], 0, ',', '.'); ?>
                                        <small class="text-muted fw-normal d-block"><?= ($p['tipe_harga'] == 'meteran') ? '/ m²' : '/ pcs'; ?></small>
                                    </td>
                                    <td class="small text-muted" style="line-height: 1.4;"><?= htmlspecialchars($p['deskripsi']); ?></td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm btn-light border rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditProduk<?= $p['id_produk']; ?>" title="Edit Produk">
                                                <i class="bi bi-pencil-square text-primary"></i>
                                            </button>
                                            <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                                <input type="hidden" name="aksi" value="hapus_produk">
                                                <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
                                                <button type="submit" class="btn btn-sm btn-light border rounded-circle shadow-sm" title="Hapus Produk">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modalEditProduk<?= $p['id_produk']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                                                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-biru"></i>Edit Produk</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body px-4 pt-4">
                                                    <input type="hidden" name="aksi" value="edit_produk">
                                                    <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
                                                    <input type="hidden" name="gambar_lama" value="<?= $p['gambar_katalog']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">Nama Produk</label>
                                                        <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($p['nama_produk']); ?>" required>
                                                    </div>
                                                    <div class="row mb-3 g-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-medium">Tipe Harga</label>
                                                            <select name="tipe_harga" class="form-select">
                                                                <option value="meteran" <?= ($p['tipe_harga'] == 'meteran') ? 'selected' : ''; ?>>Meteran (per m²)</option>
                                                                <option value="satuan" <?= ($p['tipe_harga'] == 'satuan') ? 'selected' : ''; ?>>Satuan (per pcs)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-medium">Harga Dasar (Rp)</label>
                                                            <input type="number" name="harga_dasar" class="form-control" value="<?= $p['harga_dasar']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">Deskripsi Produk</label>
                                                        <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($p['deskripsi']); ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">Upload Gambar Baru (Opsional)</label>
                                                        <input type="file" name="gambar_katalog" class="form-control" accept="image/*">
                                                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Biarkan kosong jika foto tidak diubah.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-light"></i>
                                    Belum ada produk di database.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahProduk" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2 text-biru"></i>Tambah Produk Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 pt-4">
                        <input type="hidden" name="aksi" value="tambah_produk">
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Produk</label>
                            <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Umbul-Umbul Kain" required>
                        </div>
                        <div class="row mb-3 g-3">
                            <div class="col-6">
                                <label class="form-label fw-medium">Tipe Harga</label>
                                <select name="tipe_harga" class="form-select">
                                    <option value="meteran">Meteran (per m²)</option>
                                    <option value="satuan">Satuan (per pcs)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-medium">Harga Dasar (Rp)</label>
                                <input type="number" name="harga_dasar" class="form-control" placeholder="25000" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Deskripsi Produk</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan spesifikasi bahan dan keunggulannya..." required></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Upload Foto Gambar Produk</label>
                            <input type="file" name="gambar_katalog" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger fw-medium rounded-pill px-4 shadow-sm">Tambah Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>