<?php
session_start(); // Memulai sesi

// Cek apakah user sudah login atau belum
if (!isset($_SESSION['user_id'])) { // Ganti 'user_id' sesuai dengan variabel sesi yang digunakan saat login
    header("Location: ../auth/login.php"); // Redirect ke halaman login
    exit(); // Hentikan eksekusi kode agar tidak melanjutkan loading halaman
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Space+Grotesk:wght@300..700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v2">

</head>

<body>
    <!-- Memanggil navbar.php -->
    <?php include('../includes/navbar.php'); ?>
    <div class="home">
        <h1>
            SELAMAT DATANG DI SISTEM <br> PENDUKUNG KEPUTUSAN AHP-SAW <br> UNTUK PEMILIHAN PEMAIN INTI BOLA VOLI
        </h1>
        <hr>
        <p>
            Sistem ini dirancang untuk membantu pelatih dan tim manajemen dalam memilih pemain
            inti bola voli secara objektif dan akurat. Sistem ini menggabungkan metode Analytical Hierarchy Process
            (AHP) untuk menentukan bobot kriteria dan Simple Additive Weighting (SAW) untuk melakukan
            perhitungan dan pemeringkatan alternatif terbaik. Dengan pendekatan berbasis data,
            pemilihan pemain menjadi lebih transparan dan terstruktur, sehingga dapat membentuk tim yang
            lebih kompetitif dan seimbang.
        </p>
    </div>
    <?php include('../includes/footer.php'); ?>
</body>

</html>