<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai1 = $_POST['nilai1'];
    $nilai2 = $_POST['nilai2'];

    foreach ($nilai1 as $alternatif_id => $kriteria_nilai1) {
        foreach ($kriteria_nilai1 as $kriteria_id => $n1) {
            $n2 = $nilai2[$alternatif_id][$kriteria_id];

            // Hitung rata-rata
            $rata2 = round(($n1 + $n2) / 2, 2);

            // Simpan nilai ke tabel penilaian
            $sql = "INSERT INTO penilaian (alternatif_id, kriteria_id, nilai) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'iid', $alternatif_id, $kriteria_id, $rata2);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: ../pages/penilaian_alternatif.php?status=sukses");
    exit();
}