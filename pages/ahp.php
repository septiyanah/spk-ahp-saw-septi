<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$queryKriteria = "SELECT * FROM kriteria ORDER BY id";
$resultKriteria = mysqli_query($conn, $queryKriteria);
$kriteria = [];
while ($row = mysqli_fetch_assoc($resultKriteria)) {
    $kriteria[] = $row;
}

// Proses penyimpanan input perbandingan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan data nilai_ahli1 dan nilai_ahli2 ada
    if (isset($_POST['nilai_ahli1']) && isset($_POST['nilai_ahli2'])) {
        $nilai_ahli1 = $_POST['nilai_ahli1'];
        $nilai_ahli2 = $_POST['nilai_ahli2'];

        foreach ($nilai_ahli1 as $k1_id => $kriteria_perbandingan) {
            foreach ($kriteria_perbandingan as $k2_id => $nilai1) {
                // Ambil nilai dari ahli 2
                $nilai2 = isset($nilai_ahli2[$k1_id][$k2_id]) ? $nilai_ahli2[$k1_id][$k2_id] : null;

                // Hitung rata-rata antara nilai ahli 1 dan ahli 2
                $rata2 = 0;
                if ($nilai1 !== null && $nilai2 !== null) {
                    $rata2 = (floatval($nilai1) + floatval($nilai2)) / 2;
                } else {
                    // Jika salah satu nilai kosong, ambil nilai yang ada
                    $rata2 = floatval($nilai1) ?: floatval($nilai2);
                }

                // Simpan rata-rata ke database (misalnya di tabel perbandingan_kriteria)
                $query = "INSERT INTO perbandingan_kriteria (kriteria1_id, kriteria2_id, nilai) 
                          VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE nilai = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "iidd", $k1_id, $k2_id, $rata2, $rata2);
                mysqli_stmt_execute($stmt);
            }
        }
    }
}




// Ambil data perbandingan dari database
$queryPerbandingan = "SELECT * FROM perbandingan_kriteria";
$resultPerbandingan = mysqli_query($conn, $queryPerbandingan);
$perbandingan = [];
$adaDataPerbandingan = mysqli_num_rows($resultPerbandingan) > 0;

if ($adaDataPerbandingan) {
    // Inisialisasi matriks perbandingan
    foreach ($kriteria as $k1) {
        foreach ($kriteria as $k2) {
            $perbandingan[$k1['id']][$k2['id']] = ($k1['id'] == $k2['id']) ? 1 : 0;
        }
    }

    // Isi matriks dari database
    while ($row = mysqli_fetch_assoc($resultPerbandingan)) {
        $perbandingan[$row['kriteria1_id']][$row['kriteria2_id']] = $row['nilai'];
    }

    // Hitung total kolom
    $totalKolom = [];
    foreach ($kriteria as $k2) {
        $sum = 0;
        foreach ($kriteria as $k1) {
            $sum += $perbandingan[$k1['id']][$k2['id']];
        }
        $totalKolom[$k2['id']] = ($sum == 0) ? 1 : $sum;
    }

    // Hitung normalisasi & bobot
    $normalisasi = [];
    $bobotPrioritas = [];
    foreach ($kriteria as $k1) {
        $bobotPrioritas[$k1['id']] = 0;
        foreach ($kriteria as $k2) {
            $normalisasi[$k1['id']][$k2['id']] = $perbandingan[$k1['id']][$k2['id']] / $totalKolom[$k2['id']];
            $bobotPrioritas[$k1['id']] += $normalisasi[$k1['id']][$k2['id']];
        }
        $bobotPrioritas[$k1['id']] = (count($kriteria) > 0) ? ($bobotPrioritas[$k1['id']] / count($kriteria)) : 0;
    }

    // Simpan bobot ke database
    foreach ($bobotPrioritas as $kriteria_id => $nilai_bobot) {
        mysqli_query($conn, "INSERT INTO bobot_kriteria (kriteria_id, bobot) 
                             VALUES ($kriteria_id, $nilai_bobot) 
                             ON DUPLICATE KEY UPDATE bobot = $nilai_bobot");
    }

    // Hitung Konsistensi
    $lambdaMax = 0;
    foreach ($kriteria as $k1) {
        $sum = 0;
        foreach ($kriteria as $k2) {
            $sum += $perbandingan[$k1['id']][$k2['id']] * $bobotPrioritas[$k2['id']];
        }
        $lambdaMax += $sum / $bobotPrioritas[$k1['id']];
    }
    $lambdaMax /= count($kriteria);
    $CI = ($lambdaMax - count($kriteria)) / (count($kriteria) - 1);
    $RI_values = [0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41, 1.45];
    $RI = ($RI_values[count($kriteria)] ?? 1.49);
    $CR = $CI / $RI;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perhitungan AHP</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <?php include('../includes/navbar.php'); ?>

    <div class="perhitungan-ahp">
        <h2>PERHITUNGAN AHP</h2>
        <hr>
        <h3>INPUT PERBANDINGAN KRITERIA</h3>

        <div class="petunjuk-perbandingan">
            <p><strong>Petunjuk Pengisian:</strong></p>
            <p>Untuk setiap pasangan kriteria, pilih nilai yang sesuai dengan tingkat kepentingannya berdasarkan skala
                <strong>Saaty (1-9)</strong> di bawah ini. Anda akan diminta untuk mengisi nilai dari dua ahli berbeda:
            </p>
            <ul>
                <li><strong>Ahli 1:</strong> Inputkan nilai berdasarkan penilaian Ahli 1.</li>
                <li><strong>Ahli 2:</strong> Inputkan nilai berdasarkan penilaian Ahli 2.</li>
            </ul>
            <p>Untuk setiap pasangan kriteria, pilih nilai yang sesuai berdasarkan skala berikut:</p>
            <ul>
                <li><strong>1</strong> = Sama penting</li>
                <li><strong>2</strong> = Sedikit lebih penting</li>
                <li><strong>3</strong> = Cukup penting</li>
                <li><strong>4</strong> = Agak penting</li>
                <li><strong>5</strong> = Lebih penting</li>
                <li><strong>6</strong> = Antara lebih dan sangat penting</li>
                <li><strong>7</strong> = Sangat penting</li>
                <li><strong>8</strong> = Antara sangat dan mutlak penting</li>
                <li><strong>9</strong> = Mutlak penting</li>
                <li>Jika Anda ingin membandingkan kriteria yang satu lebih penting daripada yang lain, pilih nilai
                    antara <strong>1</strong> sampai <strong>9</strong>. Sebaliknya, pilih nilai antara
                    <strong>1/2</strong> hingga <strong>1/9</strong> untuk kriteria yang lebih kurang penting.
                </li>
            </ul>
            <p><strong>Catatan:</strong> Pastikan setiap nilai yang diisi sesuai dengan pasangan kriteria yang
                dibandingkan dan sesuai dengan penilaian dari Ahli 1 dan Ahli 2.</p>
        </div>

        <form class="form-perbandingan-kriteria" method="POST">
            <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th>Kriteria</th>
                        <?php foreach ($kriteria as $k) : ?>
                        <th><?php echo htmlspecialchars($k['kriteria']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kriteria as $k1) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($k1['kriteria']); ?></td>
                        <?php foreach ($kriteria as $k2) : ?>
                        <td>
                            <?php if ($k1['id'] == $k2['id']) { ?>
                            1
                            <?php } else { ?>
                            <select name="nilai_ahli1[<?php echo $k1['id']; ?>][<?php echo $k2['id']; ?>]">
                                <option value="">-</option>
                                <?php
                                            $skala = [
                                                '0.111' => '1/9, Mutlak kurang penting',
                                                '0.125' => '1/8, Antara sangat kurang dan mutlak kurang',
                                                '0.142' => '1/7, Sangat kurang penting',
                                                '0.16'  => '1/6, Antara lebih kurang dan sangat kurang',
                                                '0.2'   => '1/5, Lebih kurang penting',
                                                '0.25'  => '1/4, Cukup kurang penting',
                                                '0.333' => '1/3, Kurang penting',
                                                '0.5'   => '1/2, Sedikit kurang penting',
                                                '1'     => '1, Sama penting',
                                                '2'     => '2, Sedikit lebih penting',
                                                '3'     => '3, Cukup penting',
                                                '4'     => '4, Agak penting',
                                                '5'     => '5, Lebih penting',
                                                '6'     => '6, Antara lebih dan sangat penting',
                                                '7'     => '7, Sangat penting',
                                                '8'     => '8, Antara sangat dan mutlak penting',
                                                '9'     => '9, Mutlak penting'
                                            ];


                                            foreach ($skala as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="nilai_ahli2[<?php echo $k1['id']; ?>][<?php echo $k2['id']; ?>]">
                                <option value="">-</option>
                                <?php foreach ($skala as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php } ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit">Simpan</button>
        </form>

        <h3>HASIL PERBANDINGAN KRITERIA</h3>
        <?php if ($adaDataPerbandingan): ?>
        <table border="1" class="tabel-ahp">
            <thead>
                <tr>
                    <th>Kriteria</th>
                    <?php foreach ($kriteria as $k) : ?>
                    <th><?php echo htmlspecialchars($k['kode']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kriteria as $k1) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($k1['kode']); ?></td>
                    <?php foreach ($kriteria as $k2) : ?>
                    <td><?php echo round($perbandingan[$k1['id']][$k2['id']], 4); ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td><strong>Total</strong></td>
                    <?php foreach ($kriteria as $k) : ?>
                    <td><strong><?php echo round($totalKolom[$k['id']], 4); ?></strong></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
        <p><em>Belum ada data perbandingan kriteria. Silakan isi terlebih dahulu.</em></p>
        <?php endif; ?>

        <h3>MATRIKS NORMALISASI & BOBOT PRIORITAS</h3>
        <p class="p-ahp">Matriks normalisasi diperoleh dengan membagi setiap elemen dalam matriks perbandingan dengan
            total nilai
            kolom masing-masing. Selanjutnya, bobot prioritas untuk setiap kriteria dihitung dengan merata-ratakan nilai
            pada setiap baris hasil normalisasi tersebut.</p>

        <?php if ($adaDataPerbandingan): ?>
        <table border="1" class="tabel-ahp">
            <thead>
                <tr>
                    <th>Kriteria</th>
                    <?php foreach ($kriteria as $k) : ?>
                    <th><?php echo htmlspecialchars($k['kode']); ?></th>
                    <?php endforeach; ?>
                    <th>Bobot</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kriteria as $k1) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($k1['kode']); ?></td>
                    <?php foreach ($kriteria as $k2) : ?>
                    <td><?php echo round($normalisasi[$k1['id']][$k2['id']], 4); ?></td>
                    <?php endforeach; ?>
                    <td><strong><?php echo round($bobotPrioritas[$k1['id']], 4); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p><em>Belum ada hasil perhitungan.</em></p>
        <?php endif; ?>

        <h3>HASIL KONSISTENSI</h3>
        <p class="p-ahp">Uji konsistensi dilakukan untuk memastikan bahwa perbandingan antar kriteria tidak bersifat
            acak dan memiliki konsistensi logis. Nilai rasio konsistensi (CR) harus kurang dari 0,1 agar hasil
            perhitungan dianggap konsisten.</p>

        <?php if ($adaDataPerbandingan): ?>
        <div class="hasil-konsistensi">
            <p>Lambda Max: <strong><?php echo round($lambdaMax, 4); ?></strong></p>
            <p>CI: <strong><?php echo round($CI, 4); ?></strong></p>
            <p>CR: <strong><?php echo round($CR, 4); ?></strong>
                (<?php echo ($CR < 0.1) ? "✅ Konsisten" : "❌ Tidak Konsisten"; ?>)</p>
        </div>
        <?php else: ?>
        <p><em>Belum ada hasil konsistensi.</em></p>
        <?php endif; ?>
    </div>
</body>

</html>