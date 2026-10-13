<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id_user = $_SESSION['id_user']; 
    $id_produk = isset($_POST['id_produk']) ? intval($_POST['id_produk']) : 1;
    $panjang = floatval($_POST['panjang']);
    $lebar = floatval($_POST['lebar']);
    $qty = intval($_POST['qty']);
    $opsi_desain_raw = $_POST['opsi_desain'];
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $pengiriman = $_POST['pengiriman'];
    $metode_pembayaran = isset($_POST['metode_pembayaran']) ? mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']) : 'qris';

    if ($opsi_desain_raw == 'custom_desain' || $opsi_desain_raw == 'custom') {
        $opsi_desain = 'custom_desain';
    } else {
        $opsi_desain = 'upload_mandiri';
    }

    $dir_upload = "public/uploads/file_desain/";
    if (!is_dir($dir_upload)) mkdir($dir_upload, 0777, true);

    $nama_file_desain = "";
    if (isset($_FILES['file_desain']) && $_FILES['file_desain']['error'] == 0) {
        $ext = pathinfo($_FILES['file_desain']['name'], PATHINFO_EXTENSION);
        $nama_file_desain = "DESAIN_" . time() . "_" . rand(100, 999) . "." . $ext;
        move_uploaded_file($_FILES['file_desain']['tmp_name'], $dir_upload . $nama_file_desain);
    }

    $query_harga = mysqli_query($koneksi, "SELECT harga_dasar, tipe_harga FROM produk WHERE id_produk = $id_produk");
    $data_produk = mysqli_fetch_assoc($query_harga);
    
    $harga_dasar = $data_produk ? floatval($data_produk['harga_dasar']) : 20000;
    $tipe_harga = $data_produk ? $data_produk['tipe_harga'] : 'meteran';

    if ($tipe_harga == 'meteran') {
        $subtotal = $panjang * $lebar * $qty * $harga_dasar;
    } else {
        $subtotal = $qty * $harga_dasar;
    }

    $biaya_desain = ($opsi_desain == 'custom_desain') ? 50000 : 0;
    $total_tagihan = $subtotal + $biaya_desain;
    $nomor_invoice = "INV-" . date("Ymd") . "-" . rand(100, 999);

    mysqli_begin_transaction($koneksi);

    try {
        // Menyimpan metode_pembayaran ke dalam database
        $sql_pesanan = "INSERT INTO pesanan (nomor_invoice, id_user, metode_pengiriman, metode_pembayaran, total_tagihan) 
                        VALUES ('$nomor_invoice', $id_user, '$pengiriman', '$metode_pembayaran', $total_tagihan)";
        mysqli_query($koneksi, $sql_pesanan);
        
        $id_pesanan_baru = mysqli_insert_id($koneksi);

        $sql_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, panjang, lebar, jumlah, opsi_desain, biaya_desain_custom, catatan_desain, file_desain, subtotal) 
                       VALUES ($id_pesanan_baru, $id_produk, $panjang, $lebar, $qty, '$opsi_desain', $biaya_desain, '$catatan', '$nama_file_desain', $subtotal)";
        mysqli_query($koneksi, $sql_detail);

        $sql_riwayat = "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) 
                        VALUES ($id_pesanan_baru, 'Menunggu Pembayaran', 'Pesanan baru dibuat dengan pembayaran via " . strtoupper($metode_pembayaran) . ".', $id_user)";
        mysqli_query($koneksi, $sql_riwayat);

        mysqli_commit($koneksi);
        
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet'>
            <style>body { font-family: 'Poppins', sans-serif; }</style>
        </head>
        <body style='background-color:#f4f7f6;'>
            <script>
                Swal.fire({
                    title: 'Pesanan Berhasil Dibuat!',
                    text: 'Nomor Invoice Anda: $nomor_invoice',
                    icon: 'success',
                    confirmButtonColor: '#0088cc',
                    confirmButtonText: 'Selesaikan Pembayaran'
                }).then(() => {
                    window.location.href = 'detail_pesanan.php?id=$id_pesanan_baru';
                });
            </script>
        </body>
        </html>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "Gagal menyimpan pesanan: " . $e->getMessage();
    }
}
?>