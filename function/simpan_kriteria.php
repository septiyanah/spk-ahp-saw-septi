<?php
session_start();
include('../config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $kode = trim($_POST['kode']);
    $kriteria = trim($_POST['kriteria']);
    $jenis = trim($_POST['jenis']);

    if (!in_array($jenis, ['benefit', 'cost'])) {
        echo "Jenis tidak valid!";
        exit();
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE kriteria SET kode=?, kriteria=?, jenis=? WHERE id=?");
        $stmt->bind_param("sssi", $kode, $kriteria, $jenis, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO kriteria (kode, kriteria, jenis) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $kode, $kriteria, $jenis);
    }

    if ($stmt->execute()) {
        echo "Data berhasil disimpan!";
    } else {
        echo "Terjadi kesalahan!";
    }
}
