<?php
session_start();
include('../config/config.php'); // Koneksi ke database

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil data alternatif
$queryAlternatif = "SELECT * FROM alternatif ORDER BY id";
$resultAlternatif = mysqli_query($conn, $queryAlternatif);
$alternatif = [];
while ($row = mysqli_fetch_assoc($resultAlternatif)) {
    $alternatif[$row['id']] = $row;
}

// Ambil skor akhir dari SAW
$querySkor = "SELECT * FROM saw_skor ORDER BY skor DESC";
$resultSkor = mysqli_query($conn, $querySkor);
$skorAkhir = [];

while ($row = mysqli_fetch_assoc($resultSkor)) {
    if (isset($alternatif[$row['alternatif_id']])) { // Cek apakah ID ada di tabel alternatif
        $skorAkhir[$row['alternatif_id']] = [
            'skor' => $row['skor'],
            'posisi' => strtolower($alternatif[$row['alternatif_id']]['posisi']) // Lowercase untuk perbandingan
        ];
    }
}

// Pisahkan berdasarkan posisi
$nonTosserLibero = []; // Semua selain Tosser dan Libero
$tosserList = [];
$liberoList = [];

// Proses skor untuk pemisahan posisi
foreach ($skorAkhir as $idAlt => $data) {
    if ($data['posisi'] == 'tosser') {
        $tosserList[$idAlt] = $data['skor'];
    } elseif ($data['posisi'] == 'libero') {
        $liberoList[$idAlt] = $data['skor'];
    } else {
        $nonTosserLibero[$idAlt] = $data['skor'];
    }
}

// Ambil 5 skor tertinggi dari Non-Tosser/Libero untuk Spiker
arsort($nonTosserLibero); // Urutkan skor tertinggi ke terendah
$spikers = array_slice(array_keys($nonTosserLibero), 0, 5);

// Ambil Tosser dengan skor tertinggi
$tosser = !empty($tosserList) ? array_keys($tosserList, max($tosserList)) : [];

// Ambil Libero dengan skor tertinggi
$libero = !empty($liberoList) ? array_keys($liberoList, max($liberoList)) : [];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Seleksi Tim Inti Bola Voli</title>
    <link rel="stylesheet" href="styles.css">
    <script>
    function printPage() {
        window.print();
    }
    </script>
</head>

<body>
    <?php include('../includes/navbar.php'); ?>
    <div class="skor-akhir">
        <h2>HASIL SELEKSI TIM INTI BOLA VOLI</h2>
        <hr>
        <h3>🏆 DAFTAR PEMAIN LOLOS SELEKSI</h3>
        <table class="tabel-skor-akhir" border="1">
            <thead>
                <tr>
                    <th>Peringkat</th>
                    <th>Nama Pemain</th>
                    <th>Posisi</th>
                    <th>Skor Akhir</th>
                    <th>Tim Inti</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 1;
                foreach ($skorAkhir as $idAlt => $data) :
                    if (!isset($alternatif[$idAlt])) continue; // Cek apakah alternatif valid

                    $statusLolos = "❌ Cadangan"; // Default status
                    // Tentukan status berdasarkan posisi dan peringkat
                    if (in_array($idAlt, $spikers)) {
                        $statusLolos = "✅ Lolos"; // Spiker lolos sebagai pemain inti
                    }
                ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo htmlspecialchars($alternatif[$idAlt]['alternatif']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($data['posisi'])); ?></td>
                    <td><?php echo round($data['skor'], 4); ?></td>
                    <td><?php echo $statusLolos; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button class="btn-skor-akhir" onclick="printPage()">🖨 Cetak Hasil</button>
    </div>
</body>

</html>