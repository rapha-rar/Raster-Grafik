<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$dir = 'public/uploads/kategori/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $gambar = '';
    if(isset($_FILES['gambar']['name']) && $_FILES['gambar']['error'] == 0){
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $dir . $gambar);
    }
    mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori, gambar_kategori) VALUES ('$nama', '$gambar')");
    header("Location: admin_kategori.php");
    exit();
}

if (isset($_POST['edit'])) {
    $id = intval($_POST['id_kategori']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    
    $q_old = mysqli_query($koneksi, "SELECT gambar_kategori FROM kategori WHERE id_kategori = $id");
    $d_old = mysqli_fetch_assoc($q_old);
    $gambar = $d_old['gambar_kategori'];
    
    if(isset($_FILES['gambar']['name']) && $_FILES['gambar']['error'] == 0){
        if ($gambar && file_exists($dir . $gambar)) unlink($dir . $gambar);
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = time() . '_edit_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $dir . $gambar);
    }
    mysqli_query($koneksi, "UPDATE kategori SET nama_kategori='$nama', gambar_kategori='$gambar' WHERE id_kategori=$id");
    header("Location: admin_kategori.php");
    exit();
}

if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);
    $q_old = mysqli_query($koneksi, "SELECT gambar_kategori FROM kategori WHERE id_kategori = $id");
    $d_old = mysqli_fetch_assoc($q_old);
    if ($d_old['gambar_kategori'] && file_exists($dir . $d_old['gambar_kategori'])) {
        unlink($dir . $d_old['gambar_kategori']);
    }
    mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = $id");
    mysqli_query($koneksi, "UPDATE produk SET id_kategori = NULL WHERE id_kategori = $id");
    header("Location: admin_kategori.php");
    exit();
}

$kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori DESC");
$data_kat = [];
while($r = mysqli_fetch_assoc($kategori)) $data_kat[] = $r;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin</title>
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
                <a href="admin_kategori.php" class="nav-link-admin active"><i class="bi bi-tags me-2"></i>Kelola Kategori</a>
                <a href="admin_produk.php" class="nav-link-admin"><i class="bi bi-box-seam me-2"></i>Kelola Produk</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light fw-medium"><i class="bi bi-person-circle me-2 text-primary"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold px-3 border-danger border-opacity-50">Keluar</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid px-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0"><i class="bi bi-tags text-primary me-2"></i>Kelola Kategori</h3>
            <button class="btn btn-tambah px-4" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="bi bi-plus-lg me-2"></i>Tambah Kategori</button>
        </div>
        <div class="card-admin p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr>
                            <th width="80">Thumbnail</th>
                            <th>Nama Kategori</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_kat as $row) : ?>
                        <tr>
                            <td><img src="public/uploads/kategori/<?= $row['gambar_kategori'] ?>" class="rounded bg-light" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #e2e8f0;" onerror="this.src='https://via.placeholder.com/60?text=No+Img'"></td>
                            <td><strong class="d-block text-dark"><?= htmlspecialchars($row['nama_kategori']); ?></strong></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary px-3 py-1 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_kategori']; ?>">Edit</button>
                                <a href="?hapus=<?= $row['id_kategori']; ?>" onclick="return confirm('Yakin hapus kategori ini?');" class="btn btn-sm btn-outline-danger rounded-circle ms-1"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($data_kat)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data kategori.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-plus-circle-fill me-2"></i>Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold mb-2 small">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="fw-bold mb-2 small">Gambar Kategori</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
                    <button type="button" class="btn btn-light border px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary px-4 rounded-pill fw-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php foreach ($data_kat as $row) : ?>
    <div class="modal fade" id="modalEdit<?= $row['id_kategori']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
                <input type="hidden" name="id_kategori" value="<?= $row['id_kategori']; ?>">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold mb-2 small">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($row['nama_kategori']); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="fw-bold mb-2 small">Gambar Kategori Baru (Opsional)</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
                    <button type="button" class="btn btn-light border px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary px-4 rounded-pill fw-bold">Update</button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>