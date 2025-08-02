<?php
include('../config/config.php');

$query = "SELECT * FROM kriteria";
$result = $conn->query($query);
$data = [];
$no = 1;

while ($row = $result->fetch_assoc()) {
    $row['no'] = $no++;
    $row['aksi'] = "<a href='javascript:void(0);' class='tombol-aksi' onclick=\"editKriteria('{$row['id']}', '{$row['kode']}', '{$row['kriteria']}', '{$row['jenis']}')\">✏️</a>
                    <a href='javascript:void(0);' class='tombol-aksi' onclick=\"hapusKriteria('{$row['id']}')\">❌</a>";
    $data[] = $row;
}

echo json_encode(["data" => $data]);
