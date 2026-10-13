<?php
session_start();
require_once 'config/database.php';

// Proteksi: Wajib Login untuk Memproses Pesanan
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Tangkap data dari form (pesan.php)
    $id_user = $_SESSION['id_user']; 
    $id_produk = isset($_POST['id_produk']) ? intval($_POST['id_produk']) : 1;
    $panjang = floatval($_POST['panjang']);
    $lebar = floatval($_POST['lebar']);
    $qty = intval($_POST['qty']);
    $opsi_desain = $_POST['opsi_desain'];
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $pengiriman = $_POST['pengiriman'];

    // Folder Upload File Desain Pelanggan
    $dir_upload = "public/uploads/file_desain/";
    if (!is_dir($dir_upload)) {
        mkdir($dir_upload, 0777, true);
    }

    $nama_file_desain = NULL;
    if (isset($_FILES['file_desain']) && $_FILES['file_desain']['error'] == 0) {
        $ext = pathinfo($_FILES['file_desain']['name'], PATHINFO_EXTENSION);
        $nama_file_desain = "DESAIN_" . time() . "_" . rand(100, 999) . "." . $ext;
        move_uploaded_file($_FILES['file_desain']['tmp_name'], $dir_upload . $nama_file_desain);
    }

    // 2. Ambil harga dasar & tipe harga produk dari database
    $query_harga = mysqli_query($koneksi, "SELECT harga_dasar, tipe_harga FROM produk WHERE id_produk = $id_produk");
    $data_produk = mysqli_fetch_assoc($query_harga);
    
    $harga_dasar = $data_produk ? floatval($data_produk['harga_dasar']) : 20000;
    $tipe_harga = $data_produk ? $data_produk['tipe_harga'] : 'meteran';

    // 3. Hitung Ulang Total Tagihan di Server
    if ($tipe_harga == 'meteran') {
        $subtotal = $panjang * $lebar * $qty * $harga_dasar;
    } else {
        $subtotal = $qty * $harga_dasar;
    }

    $biaya_desain = ($opsi_desain == 'custom_desain') ? 50000 : 0;
    $total_tagihan = $subtotal + $biaya_desain;

    // 4. Generate Nomor Invoice Unik
    $nomor_invoice = "INV-" . date("Ymd") . "-" . rand(100, 999);

    // 5. Mulai Transaksi Simpan ke Database
    mysqli_begin_transaction($koneksi);

    try {
        // A. Simpan ke tabel pesanan
        $sql_pesanan = "INSERT INTO pesanan (nomor_invoice, id_user, metode_pengiriman, total_tagihan) 
                        VALUES ('$nomor_invoice', $id_user, '$pengiriman', $total_tagihan)";
        mysqli_query($koneksi, $sql_pesanan);
        
        $id_pesanan_baru = mysqli_insert_id($koneksi);

        // B. Simpan ke tabel detail_pesanan
        $sql_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, panjang, lebar, jumlah, opsi_desain, biaya_desain_custom, catatan_desain, subtotal) 
                       VALUES ($id_pesanan_baru, $id_produk, $panjang, $lebar, $qty, '$opsi_desain', $biaya_desain, '$catatan', $subtotal)";
        mysqli_query($koneksi, $sql_detail);

        // C. Simpan ke tabel riwayat_status
        $sql_riwayat = "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) 
                        VALUES ($id_pesanan_baru, 'Menunggu Pembayaran', 'Pesanan baru dibuat, menunggu transfer dari pelanggan.', $id_user)";
        mysqli_query($koneksi, $sql_riwayat);

        // Commit perubahan
        mysqli_commit($koneksi);
        
        // Redirect ke dashboard pelanggan
        echo "<script>alert('Pesanan Berhasil Dibuat! Nomor Invoice Anda: $nomor_invoice'); window.location.href='dashboard_pelanggan.php';</script>";

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "Gagal menyimpan pesanan: " . $e->getMessage();
    }
}
?>