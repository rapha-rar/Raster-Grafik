<?php
session_start();
require_once 'config/database.php';

// Proteksi Halaman: Wajib Login & Role Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Folder tujuan upload gambar produk
$dir_upload = "public/uploads/produk/";
if (!is_dir($dir_upload)) {
    mkdir($dir_upload, 0777, true);
}

// --- LOGIKA AKSI ADMIN (CRUD) ---

// 1. TAMBAH PRODUK BARU
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

    $query_tambah = "INSERT INTO produk (nama_produk, tipe_harga, harga_dasar, deskripsi, gambar_katalog) 
                    VALUES ('$nama_produk', '$tipe_harga', $harga_dasar, '$deskripsi', '$nama_file_gambar')";
    
    if (mysqli_query($koneksi, $query_tambah)) {
        echo "<script>alert('Produk baru berhasil ditambahkan!'); window.location.href='admin_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah produk: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 2. EDIT / UPDATE PRODUK
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

    $query_edit = "UPDATE produk SET 
                    nama_produk = '$nama_produk', 
                    tipe_harga = '$tipe_harga', 
                    harga_dasar = $harga_dasar, 
                    deskripsi = '$deskripsi', 
                    gambar_katalog = '$nama_file_gambar' 
                  WHERE id_produk = $id_produk";

    if (mysqli_query($koneksi, $query_edit)) {
        echo "<script>alert('Data produk berhasil diperbarui!'); window.location.href='admin_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui produk: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 3. HAPUS PRODUK
if (isset($_POST['aksi']) && $_POST['aksi'] == 'hapus_produk') {
    $id_produk = intval($_POST['id_produk']);

    try {
        $query_hapus = "DELETE FROM produk WHERE id_produk = $id_produk";
        if (mysqli_query($koneksi, $query_hapus)) {
            echo "<script>alert('Produk berhasil dihapus!'); window.location.href='admin_produk.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Produk tidak dapat dihapus karena sudah ada riwayat pesanan pelanggan yang menggunakan produk ini.'); window.location.href='admin_produk.php';</script>";
    }
}

// Menarik seluruh data produk
$query_produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --biru-rg: #0088cc; --merah-rg: #cc0000; }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg); }
    </style>
</head>
<body class="bg-light">

    <!-- Top Navigation Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span> <small class="fs-6 text-muted">| Panel Admin</small>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_pesanan.php">Kelola Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-warning" href="admin_produk.php">Kelola Produk</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text text-white me-3">Halo, <?= htmlspecialchars($_SESSION['nama']); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Kelola Katalog Produk</h3>
            <button class="btn btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
                + Tambah Produk Baru
            </button>
        </div>

        <!-- TABEL DAFTAR PRODUK -->
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Tipe Harga</th>
                            <th>Harga Dasar</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_produk) > 0): ?>
                            <?php $no = 1; while ($p = mysqli_fetch_assoc($query_produk)): 
                                $img_src = (strpos($p['gambar_katalog'], 'http') === 0) ? $p['gambar_katalog'] : 'public/uploads/produk/' . $p['gambar_katalog'];
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <img src="<?= $img_src; ?>" width="60" height="40" class="rounded border object-fit-cover" alt="Produk">
                                    </td>
                                    <td class="fw-bold"><?= htmlspecialchars($p['nama_produk']); ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($p['tipe_harga'] == 'meteran') ? 'info' : 'secondary'; ?> text-capitalize">
                                            <?= $p['tipe_harga']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-danger">
                                        Rp <?= number_format($p['harga_dasar'], 0, ',', '.'); ?>
                                        <small class="text-muted"><?= ($p['tipe_harga'] == 'meteran') ? '/ m²' : '/ pcs'; ?></small>
                                    </td>
                                    <td class="small text-muted" style="max-width: 250px;"><?= htmlspecialchars($p['deskripsi']); ?></td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <!-- Tombol Edit -->
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditProduk<?= $p['id_produk']; ?>">
                                                ✏️ Edit
                                            </button>
                                            <!-- Tombol Hapus -->
                                            <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                                <input type="hidden" name="aksi" value="hapus_produk">
                                                <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">🗑️ Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- MODAL EDIT PRODUK -->
                                <div class="modal fade" id="modalEditProduk<?= $p['id_produk']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Produk</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="aksi" value="edit_produk">
                                                    <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
                                                    <input type="hidden" name="gambar_lama" value="<?= $p['gambar_katalog']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label font-weight-bold">Nama Produk</label>
                                                        <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($p['nama_produk']); ?>" required>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label">Tipe Harga</label>
                                                            <select name="tipe_harga" class="form-select">
                                                                <option value="meteran" <?= ($p['tipe_harga'] == 'meteran') ? 'selected' : ''; ?>>Meteran (per m²)</option>
                                                                <option value="satuan" <?= ($p['tipe_harga'] == 'satuan') ? 'selected' : ''; ?>>Satuan (per pcs)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">Harga Dasar (Rp)</label>
                                                            <input type="number" name="harga_dasar" class="form-control" value="<?= $p['harga_dasar']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Deskripsi Produk</label>
                                                        <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($p['deskripsi']); ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Upload Gambar Baru (Opsional)</label>
                                                        <input type="file" name="gambar_katalog" class="form-control" accept="image/*">
                                                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto produk.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada produk di database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PRODUK -->
    <div class="modal fade" id="modalTambahProduk" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Produk Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="aksi" value="tambah_produk">
                        
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Nama Produk</label>
                            <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Umbul-Umbul Kain" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Tipe Harga</label>
                                <select name="tipe_harga" class="form-select">
                                    <option value="meteran">Meteran (per m²)</option>
                                    <option value="satuan">Satuan (per pcs)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Harga Dasar (Rp)</label>
                                <input type="number" name="harga_dasar" class="form-control" placeholder="25000" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Produk</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan spesifikasi bahan dan keunggulannya..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Foto Gambar Produk</label>
                            <input type="file" name="gambar_katalog" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger fw-bold">Tambah Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>