<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cek semua input tersedia
    if (isset($_POST['berat_badan'], $_POST['tinggi_badan'], $_POST['kecepatan'], $_POST['imt'])) {
        foreach ($_POST['berat_badan'] as $alternatif_id => $berat_badan) {
            // Ambil data lain sesuai alternatif_id yang sama
            $tinggi_badan = $_POST['tinggi_badan'][$alternatif_id];
            $kecepatan = $_POST['kecepatan'][$alternatif_id];
            $imt = $_POST['imt'][$alternatif_id];

            // Validasi input numerik
            if (!is_numeric($berat_badan) || !is_numeric($tinggi_badan) || !is_numeric($kecepatan) || !is_numeric($imt)) {
                die("Error: Semua input harus berupa angka.");
            }

            // Query dengan ON DUPLICATE KEY UPDATE
            $query = "INSERT INTO alternatif_data (alternatif_id, berat_badan, tinggi_badan, kecepatan, imt) 
                      VALUES (?, ?, ?, ?, ?) 
                      ON DUPLICATE KEY UPDATE berat_badan = ?, tinggi_badan = ?, kecepatan = ?, imt = ?";

            if ($stmt = mysqli_prepare($conn, $query)) {
                // Bind parameter: 1 int, 4 decimal, 4 decimal (total 9 param)
                mysqli_stmt_bind_param(
                    $stmt,
                    "idddddddd",
                    $alternatif_id,
                    $berat_badan,
                    $tinggi_badan,
                    $kecepatan,
                    $imt,
                    $berat_badan,
                    $tinggi_badan,
                    $kecepatan,
                    $imt
                );

                if (!mysqli_stmt_execute($stmt)) {
                    die("Error: Gagal menyimpan data. " . mysqli_stmt_error($stmt));
                }

                mysqli_stmt_close($stmt);
            } else {
                die("Error: Gagal mempersiapkan query.");
            }
        }

        // Redirect jika sukses
        header("Location: ../pages/penilaian_alternatif.php?status=sukses");
        exit();
    } else {
        die("Error: Data tidak lengkap.");
    }
}