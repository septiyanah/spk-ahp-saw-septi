<?php
session_start();
include('../config/config.php');

// Cek jika request method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form input
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $kode = trim($_POST['kode']);
    $alternatif = trim($_POST['alternatif']);
    $posisi = trim($_POST['posisi']);

    // Validasi posisi hanya boleh "spiker"
    if ($posisi !== 'spiker') {
        echo "Posisi hanya boleh 'spiker'!";
        exit();
    }

    // Validasi input kode dan alternatif (pastikan tidak kosong)
    if (empty($kode) || empty($alternatif)) {
        echo "Kode dan Alternatif tidak boleh kosong!";
        exit();
    }

    // Jika ID ada, lakukan UPDATE, jika tidak INSERT
    if ($id > 0) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE alternatif SET kode=?, alternatif=?, posisi=? WHERE id=?");
        $stmt->bind_param("sssi", $kode, $alternatif, $posisi, $id);
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO alternatif (kode, alternatif, posisi) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $kode, $alternatif, $posisi);
    }

    // Cek apakah query berhasil dieksekusi
    if ($stmt->execute()) {
        echo "Data alternatif berhasil disimpan!";
    } else {
        // Jika terjadi error saat eksekusi query
        echo "Terjadi kesalahan saat menyimpan data! Error: " . $stmt->error;
    }

    // Tutup prepared statement
    $stmt->close();
} else {
    echo "Request tidak valid!";
}

// Tutup koneksi ke database
$conn->close();