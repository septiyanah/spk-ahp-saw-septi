<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['nilai'] as $kriteria1_id => $subArray) {
        foreach ($subArray as $kriteria2_id => $nilai) {
            // Hitung nilai kebalikan (1/nilai)
            $nilaiInverse = round(1 / $nilai, 4);

            // Cek apakah data sudah ada
            $queryCek = "SELECT * FROM perbandingan_kriteria WHERE kriteria1_id = $kriteria1_id AND kriteria2_id = $kriteria2_id";
            $resultCek = mysqli_query($conn, $queryCek);

            if (mysqli_num_rows($resultCek) > 0) {
                // Update jika sudah ada
                $queryUpdate = "UPDATE perbandingan_kriteria SET nilai = $nilai WHERE kriteria1_id = $kriteria1_id AND kriteria2_id = $kriteria2_id";
                mysqli_query($conn, $queryUpdate);
            } else {
                // Insert jika belum ada
                $queryInsert = "INSERT INTO perbandingan_kriteria (kriteria1_id, kriteria2_id, nilai) VALUES ($kriteria1_id, $kriteria2_id, $nilai)";
                mysqli_query($conn, $queryInsert);
            }

            // Simpan nilai kebalikan
            $queryCekInverse = "SELECT * FROM perbandingan_kriteria WHERE kriteria1_id = $kriteria2_id AND kriteria2_id = $kriteria1_id";
            $resultCekInverse = mysqli_query($conn, $queryCekInverse);

            if (mysqli_num_rows($resultCekInverse) > 0) {
                $queryUpdateInverse = "UPDATE perbandingan_kriteria SET nilai = $nilaiInverse WHERE kriteria1_id = $kriteria2_id AND kriteria2_id = $kriteria1_id";
                mysqli_query($conn, $queryUpdateInverse);
            } else {
                $queryInsertInverse = "INSERT INTO perbandingan_kriteria (kriteria1_id, kriteria2_id, nilai) VALUES ($kriteria2_id, $kriteria1_id, $nilaiInverse)";
                mysqli_query($conn, $queryInsertInverse);
            }
        }
    }
    header("Location: ../pages/ahp.php?status=sukses");
}
