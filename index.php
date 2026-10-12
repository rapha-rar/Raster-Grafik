<?php
// Memanggil file koneksi
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tes Sistem Raster Grafik</title>
</head>
<body>
    <h2>Halo Raster Grafik!</h2>
    <?php
        if ($koneksi) {
            echo "<h3 style='color: green;'>Yeay! Database db_rastergrafik BERHASIL tersambung ke web.</h3>";
        }
    ?>
</body>
</html>