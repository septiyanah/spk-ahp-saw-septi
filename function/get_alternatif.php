<?php
include('../config/config.php');

$query = "SELECT * FROM alternatif";
$result = $conn->query($query);
$data = [];
$no = 1;

while ($row = $result->fetch_assoc()) {
    $row['no'] = $no++;
    $row['aksi'] = "<a href='javascript:void(0);' class='tombol-aksi' onclick=\"editAlternatif('{$row['id']}', '{$row['kode']}', '{$row['alternatif']}', '{$row['posisi']}')\">✏️</a>
                    <a href='javascript:void(0);' class='tombol-aksi' onclick=\"hapusAlternatif('{$row['id']}')\">❌</a>";
    $data[] = $row;
}

echo json_encode(["data" => $data]);
