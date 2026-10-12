<?php
// config/database.php

$host = "localhost";
$user = "root";     // Username default Laragon/XAMPP
$pass = "";         // Password default kosong
$db   = "db_rastergrafik"; // Nama database yang tadi kita buat di phpMyAdmin

// Membuat koneksi ke MySQL
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Mengecek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>