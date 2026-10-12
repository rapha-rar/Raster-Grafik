<?php
require_once 'config/database.php';

// Simulasi Pelanggan yang sedang Login (Menggunakan ID Dummy yang kita buat sebelumnya)
$id_user = 1; 

// Menarik data profil pelanggan
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_user");
$user = mysqli_fetch_assoc($query_user);

// Menarik riwayat pesanan pelanggan ini, diurutkan dari yang paling baru
$query_pesanan = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_user = $id_user ORDER BY waktu_pesan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - Raster Grafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --biru-rg: #0088cc;
            --merah-rg: #cc0000;
        }
        .text-merah { color: var(--merah-rg); }
        .text-biru { color: var(--biru-rg); }
        .btn-primer { background-color: var(--merah-rg); color: white; }
        .btn-primer:hover { background-color: #aa0000; color: white; }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span class="text-biru">RASTER</span><span class="text-merah">GRAFIK</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="me-3 fw-semibold">Halo, <?= $user['nama']; ?>!</span>
                <a href="index.php" class="btn btn-outline-danger btn-sm">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Konten Dashboard -->
    <div class="container pb-5">
        <div class="row">
            
            <!-- Sidebar / Profil Singkat -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama']); ?>&background=0088cc&color=fff" class="rounded-circle mb-3" width="80" alt="Avatar">
                        <h5 class="fw-bold mb-1"><?= $user['nama']; ?></h5>
                        <p class="text-muted small mb-3"><?= $user['email']; ?></p>
                        <hr>
                        <ul class="list-unstyled text-start small">
                            <li class="mb-2">📞 <?= $user['no_whatsapp']; ?></li>
                            <li>📍 <?= $user['alamat_lengkap']; ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tabel Riwayat Pesanan -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Riwayat Pesanan Saya</h4>
                        <a href="pesan.php" class="btn btn-primer btn-sm">+ Buat Pesanan Baru</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Total Tagihan</th>
                                    <th>Status Pembayaran</th>
                                    <th>Status Cetak</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($query_pesanan) > 0) : ?>
                                    <?php while($row = mysqli_fetch_assoc($query_pesanan)) : ?>
                                        <tr>
                                            <td class="fw-bold"><?= $row['nomor_invoice']; ?></td>
                                            <td><?= date('d M Y, H:i', strtotime($row['waktu_pesan'])); ?></td>
                                            <td class="fw-semibold text-merah">Rp <?= number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                                            
                                            <!-- Badge Status Pembayaran -->
                                            <td>
                                                <?php if($row['status_pembayaran'] == 'belum_bayar'): ?>
                                                    <span class="badge bg-danger">Belum Bayar</span>
                                                <?php elseif($row['status_pembayaran'] == 'menunggu_verifikasi'): ?>
                                                    <span class="badge bg-warning text-dark">Menunggu Cek Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Lunas</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Badge Status Pesanan -->
                                            <td>
                                                <span class="badge bg-secondary text-capitalize">
                                                    <?= str_replace('_', ' ', $row['status_pesanan']); ?>
                                                </span>
                                            </td>

                                            <!-- Tombol Aksi -->
                                            <td>
                                                <?php if($row['status_pembayaran'] == 'belum_bayar'): ?>
                                                    <a href="invoice.php?id=<?= $row['id_pesanan']; ?>" class="btn btn-sm btn-primary">Bayar Sekarang</a>
                                                <?php else: ?>
                                                    <a href="#" class="btn btn-sm btn-outline-secondary">Lacak Progres</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada pesanan yang dibuat.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>