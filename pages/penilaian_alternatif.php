<?php
session_start();
include('../config/config.php'); // Koneksi ke database

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil data kriteria
$queryKriteria = "SELECT * FROM kriteria";
$resultKriteria = mysqli_query($conn, $queryKriteria);
$kriteria = [];
while ($row = mysqli_fetch_assoc($resultKriteria)) {
    $kriteria[] = $row;
}

// Ambil data alternatif
$queryAlternatif = "SELECT * FROM alternatif";
$resultAlternatif = mysqli_query($conn, $queryAlternatif);
$alternatif = [];
while ($row = mysqli_fetch_assoc($resultAlternatif)) {
    $alternatif[] = $row;
}

// Ambil data inputan berat badan, tinggi badan, kecepatan, dan IMT yang sudah ada
$queryInput = "SELECT * FROM alternatif_data WHERE alternatif_id IN (SELECT id FROM alternatif)";
$resultInput = mysqli_query($conn, $queryInput);
$inputData = [];
while ($row = mysqli_fetch_assoc($resultInput)) {
    $inputData[$row['alternatif_id']] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Alternatif</title>
    <link rel="stylesheet" href="styles.css">
    <script>
    // Fungsi untuk menghitung IMT secara otomatis
    function hitungIMT(idAlternatif) {
        var beratBadan = parseFloat(document.getElementById("berat_badan_" + idAlternatif).value);
        var tinggiBadan = parseFloat(document.getElementById("tinggi_badan_" + idAlternatif).value) /
            100; // mengonversi cm ke meter

        if (isNaN(beratBadan) || isNaN(tinggiBadan) || tinggiBadan <= 0) {
            document.getElementById("imt_" + idAlternatif).value = '';
            return;
        }

        var imt = beratBadan / (tinggiBadan * tinggiBadan); // Rumus IMT
        document.getElementById("imt_" + idAlternatif).value = imt.toFixed(2); // Menampilkan IMT dengan 2 desimal
    }
    </script>
</head>

<body>
    <?php include('../includes/navbar.php'); ?>

    <div class="penilaian-alternatif">
        <h2>PENILAIAN ALTERNATIF</h2>
        <hr class="penilaian-alternatif">
        <div class="petunjuk-pengisian">
            <!-- Petunjuk Pengisian Form Input -->
            <section class="petunjuk-input">
                <h3>Input Data Fisik Pemain</h3>
                <ul>
                    <li><strong>Berat Badan (kg):</strong> Masukkan berat badan pemain dalam kilogram. Pastikan data
                        yang dimasukkan sesuai dengan kondisi aktual pemain.</li>
                    <li><strong>Tinggi Badan (cm):</strong> Masukkan tinggi badan pemain dalam sentimeter. Pastikan
                        diukur dengan benar.</li>
                    <li><strong>Kecepatan (km/h):</strong> Masukkan kecepatan maksimal pemain dalam kilometer per jam.
                        Ini bisa diambil dari hasil sprint atau lari cepat pemain.</li>
                    <li><strong>IMT (Indeks Massa Tubuh):</strong> Kolom ini akan terisi otomatis setelah Anda mengisi
                        berat dan tinggi badan. IMT dihitung dengan rumus:
                        <pre><code>IMT = Berat Badan (kg) / (Tinggi Badan (m))²</code></pre>
                        Pastikan berat dan tinggi badan sudah diisi agar nilai IMT muncul secara otomatis.
                    </li>
                </ul>
            </section>


            <!-- Form Input Berat Badan, Tinggi Badan, Kecepatan dalam bentuk tabel -->
            <form class="form-input" action="../function/simpan_input.php" method="post">
                <table class="tabel-input" border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Alternatif</th>
                            <th>Berat Badan (kg)</th>
                            <th>Tinggi Badan (cm)</th>
                            <th>Kecepatan (km/h)</th>
                            <th>IMT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alternatif as $alt): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alt['alternatif']); ?></td>
                            <td>
                                <input type="number" id="berat_badan_<?php echo $alt['id']; ?>"
                                    name="berat_badan[<?php echo $alt['id']; ?>]" min="1" required
                                    value="<?php echo isset($inputData[$alt['id']]) ? $inputData[$alt['id']]['berat_badan'] : ''; ?>"
                                    onchange="hitungIMT(<?php echo $alt['id']; ?>)"
                                    oninput="hitungIMT(<?php echo $alt['id']; ?>)">
                            </td>
                            <td>
                                <input type="number" id="tinggi_badan_<?php echo $alt['id']; ?>"
                                    name="tinggi_badan[<?php echo $alt['id']; ?>]" min="1" required
                                    value="<?php echo isset($inputData[$alt['id']]) ? $inputData[$alt['id']]['tinggi_badan'] : ''; ?>"
                                    onchange="hitungIMT(<?php echo $alt['id']; ?>)"
                                    oninput="hitungIMT(<?php echo $alt['id']; ?>)">
                            </td>
                            <td>
                                <input type="number" name="kecepatan[<?php echo $alt['id']; ?>]" min="0" step="0.01"
                                    required
                                    value="<?php echo isset($inputData[$alt['id']]) ? $inputData[$alt['id']]['kecepatan'] : ''; ?>">
                            </td>
                            <td>
                                <input type="text" id="imt_<?php echo $alt['id']; ?>"
                                    name="imt[<?php echo $alt['id']; ?>]" readonly
                                    value="<?php echo isset($inputData[$alt['id']]) ? $inputData[$alt['id']]['imt'] : ''; ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br>
                <div class="button-container">
                    <button class="btn-input" type="submit">Simpan Data</button>
                </div>
            </form>
        </div>
        <br>
        <br>


        <!-- Petunjuk Pengisian untuk Form Penilaian -->
        <div class="petunjuk-pengisian">
            <section class="petunjuk-penilaian">
                <h3>Penilaia Pemain</h3>
                <ul>
                    <li><strong>Penilaian Alternatif:</strong> Setiap alternatif (pemain) akan dinilai berdasarkan
                        beberapa kriteria yang telah ditentukan sebelumnya.</li>
                    <li><strong>Skala Penilaian:</strong> Penilaian dilakukan pada rentang angka <strong>1 hingga
                            100</strong>. Anda dapat memberikan nilai dalam bentuk angka desimal jika diperlukan
                        (misalnya 75.5).</li>
                    <li><strong>Penilaian Kecepatan dan IMT:</strong></li>
                    <ul>
                        <li><strong>Kecepatan:</strong> Masukkan nilai kecepatan pemain sesuai dengan yang telah Anda
                            input sebelumnya di form pertama.</li>
                        <li><strong>IMT:</strong> Masukkan nilai IMT yang dihitung otomatis pada form pertama. Pastikan
                            nilai IMT yang Anda masukkan sesuai dengan yang tampil di kolom <strong>IMT</strong>.</li>
                    </ul>
                </ul>
            </section>

            <!-- Form Penilaian Alternatif -->
            <form class="form-penilaian" action="../function/simpan_penilaian.php" method="post">
                <table class="tabel-penilaian" border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Alternatif</th>
                            <?php foreach ($kriteria as $k): ?>
                            <th><?php echo htmlspecialchars($k['kriteria']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alternatif as $alt): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alt['alternatif']); ?></td>
                            <?php foreach ($kriteria as $k):
                                    $nilai1 = isset($penilaian[$alt['id']][$k['id']]) ? $penilaian[$alt['id']][$k['id']] : '';
                                    $nilai2 = isset($penilaian[$alt['id']][$k['id'] . '_ahli2']) ? $penilaian[$alt['id']][$k['id'] . '_ahli2'] : '';
                                ?>
                            <td>
                                <div style="margin-bottom: 5px;">
                                    <label><strong>Ahli 1:</strong></label><br>
                                    <input type="number"
                                        name="nilai1[<?php echo $alt['id']; ?>][<?php echo $k['id']; ?>]"
                                        value="<?php echo number_format((float)$nilai1, 2, '.', ''); ?>" min="1"
                                        max="100" step="0.01">
                                </div>
                                <div>
                                    <label><strong>Ahli 2:</strong></label><br>
                                    <input type="number"
                                        name="nilai2[<?php echo $alt['id']; ?>][<?php echo $k['id']; ?>]"
                                        value="<?php echo number_format((float)$nilai2, 2, '.', ''); ?>" min="1"
                                        max="100" step="0.01">
                                </div>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br>
                <div class="button-container">
                    <button class="btn-submit" type="submit">Simpan Penilaian</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>