<?php
require_once 'config/database.php';

// Cek apakah data dikirim dari form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Tangkap data dari form (pesan.php)
    $panjang = floatval($_POST['panjang']);
    $lebar = floatval($_POST['lebar']);
    $qty = intval($_POST['qty']);
    $opsi_desain = $_POST['opsi_desain'];
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $pengiriman = $_POST['pengiriman'];

    // Data Dummy (Nanti diganti dengan data dari fitur Login)
    $id_user = 1; 
    $id_produk = 1; 
    $harga_dasar = 20000;

    // 2. Hitung Ulang di Server (Keamanan: Jangan percaya angka dari browser/JS)
    $subtotal = $panjang * $lebar * $qty * $harga_dasar;
    $biaya_desain = ($opsi_desain == 'custom_desain') ? 50000 : 0;
    $total_tagihan = $subtotal + $biaya_desain;

    // 3. Generate Nomor Invoice (Contoh: INV-20261012-456)
    $nomor_invoice = "INV-" . date("Ymd") . "-" . rand(100, 999);

    // 4. Mulai Proses Simpan ke Database (Transaction)
    mysqli_begin_transaction($koneksi);

    try {
        // A. Simpan ke tabel pesanan
        $sql_pesanan = "INSERT INTO pesanan (nomor_invoice, id_user, metode_pengiriman, total_tagihan) 
                        VALUES ('$nomor_invoice', $id_user, '$pengiriman', $total_tagihan)";
        mysqli_query($koneksi, $sql_pesanan);
        
        $id_pesanan_baru = mysqli_insert_id($koneksi); // Ambil ID pesanan yang baru saja dibuat

        // B. Simpan ke tabel detail_pesanan
        $sql_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, panjang, lebar, jumlah, opsi_desain, biaya_desain_custom, catatan_desain, subtotal) 
                       VALUES ($id_pesanan_baru, $id_produk, $panjang, $lebar, $qty, '$opsi_desain', $biaya_desain, '$catatan', $subtotal)";
        mysqli_query($koneksi, $sql_detail);

        // C. Simpan ke tabel riwayat_status (Timeline)
        $sql_riwayat = "INSERT INTO riwayat_status (id_pesanan, status, keterangan, diupdate_oleh) 
                        VALUES ($id_pesanan_baru, 'Menunggu Pembayaran', 'Pesanan baru dibuat, menunggu transfer dari pelanggan.', $id_user)";
        mysqli_query($koneksi, $sql_riwayat);

        // Jika semua sukses, kunci perubahan ke database
        mysqli_commit($koneksi);
        
        // Redirect ke halaman sukses / invoice
        echo "<script>alert('Pesanan Berhasil Dibuat! Nomor Invoice Anda: $nomor_invoice'); window.location.href='index.php';</script>";

    } catch (Exception $e) {
        // Jika ada error, batalkan semua simpanan
        mysqli_rollback($koneksi);
        echo "Gagal menyimpan pesanan: " . $e->getMessage();
    }
}
?>