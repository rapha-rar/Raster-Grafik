<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
header("Location: login.php");
exit();
}
if (isset($_POST['simpan_produk'])) {
    $id_kategori = intval($_POST['id_kategori']);
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
    $gambar = '';
    if(isset($_FILES['gambar_katalog']) && $_FILES['gambar_katalog']['error'] == 0){
        $ext = pathinfo($_FILES['gambar_katalog']['name'], PATHINFO_EXTENSION);
        $gambar = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar_katalog']['tmp_name'], 'public/uploads/produk/' . $gambar);
    }
    $query = "INSERT INTO produk (id_kategori, nama_produk, deskripsi, harga_dasar, tipe_harga, gambar_katalog, spesifikasi_json) VALUES ($id_kategori, '$nama', '$deskripsi', '$harga', '$tipe', '$gambar', '$json_spesifikasi')";
    if(mysqli_query($koneksi, $query)){
        echo "<script>alert('Produk berhasil ditambahkan!'); window.location='admin_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah produk!'); window.history.back();</script>";
    }
}
?>