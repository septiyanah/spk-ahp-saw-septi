<?php
include('../config/config.php');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM alternatif WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Data alternatif berhasil dihapus!";
    } else {
        echo "Gagal menghapus data!";
    }
}
