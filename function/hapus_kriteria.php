<?php
include('../config/config.php');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM kriteria WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Data berhasil dihapus!";
    } else {
        echo "Gagal menghapus data!";
    }
}
