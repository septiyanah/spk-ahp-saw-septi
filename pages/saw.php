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

// Ambil data kriteria
$queryKriteria = "SELECT * FROM kriteria ORDER BY id";
$resultKriteria = mysqli_query($conn, $queryKriteria);
$kriteria = [];
while ($row = mysqli_fetch_assoc($resultKriteria)) {
    $kriteria[$row['id']] = $row;
}

// Ambil data bobot kriteria
$queryBobot = "SELECT * FROM bobot_kriteria";
$resultBobot = mysqli_query($conn, $queryBobot);
$bobotKriteria = [];
while ($row = mysqli_fetch_assoc($resultBobot)) {
    $bobotKriteria[$row['kriteria_id']] = $row['bobot'];
}

// Ambil data penilaian alternatif
$queryPenilaian = "SELECT * FROM penilaian";
$resultPenilaian = mysqli_query($conn, $queryPenilaian);
$penilaian = [];

// Inisialisasi matriks keputusan
foreach ($alternatif as $altId => $alt) {
    foreach ($kriteria as $kritId => $krit) {
        $penilaian[$altId][$kritId] = 0;
    }
}

// Isi matriks keputusan dari database
while ($row = mysqli_fetch_assoc($resultPenilaian)) {
    $penilaian[$row['alternatif_id']][$row['kriteria_id']] = $row['nilai'];
}

// Normalisasi Matriks Keputusan
$normalisasi = [];
foreach ($kriteria as $kritId => $krit) {
    $jenis = $krit['jenis'];
    $values = array_column($penilaian, $kritId);

    if ($jenis == 'benefit') {
        $max = max($values);
        foreach ($alternatif as $altId => $alt) {
            $normalisasi[$altId][$kritId] = $max != 0 ? $penilaian[$altId][$kritId] / $max : 0;
        }
    } else { // cost
        $min = min($values);
        foreach ($alternatif as $altId => $alt) {
            $normalisasi[$altId][$kritId] = $min != 0 ? $min / $penilaian[$altId][$kritId] : 0;
        }
    }
}

// Hitung Nilai Preferensi (Skor Akhir)
$skorAkhir = [];
foreach ($alternatif as $altId => $alt) {
    $skor = 0;
    foreach ($kriteria as $kritId => $krit) {
        $skor += $normalisasi[$altId][$kritId] * $bobotKriteria[$kritId];
    }
    $skorAkhir[$altId] = $skor;
}

// Simpan skor akhir ke database (UPDATE jika sudah ada, INSERT jika belum)
foreach ($skorAkhir as $idAlt => $skor) {
    $checkQuery = "SELECT * FROM saw_skor WHERE alternatif_id = $idAlt";
    $resultCheck = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($resultCheck) > 0) {
        // Update skor jika sudah ada
        $updateQuery = "UPDATE saw_skor SET skor = $skor WHERE alternatif_id = $idAlt";
        mysqli_query($conn, $updateQuery);
    } else {
        // Insert skor jika belum ada
        $insertQuery = "INSERT INTO saw_skor (alternatif_id, skor) VALUES ($idAlt, $skor)";
        mysqli_query($conn, $insertQuery);
    }
}

// Urutkan skor akhir dari yang tertinggi ke terendah
arsort($skorAkhir);

// Fungsi untuk mengonversi nilai ke nilai CRISP sesuai dengan kriteria
function konversiNilaiCrisp($nilai, $kriteria)
{
    $crisp = 0;

    // Tentukan nilai CRISP berdasarkan jenis kriteria
    switch ($kriteria) {
        case 'C1':
        case 'C2':
        case 'C3':
        case 'C4':
        case 'C5': // Passing, Servis, Smash, Blok, Kerjasama Tim
            if ($nilai <= 50) {
                $crisp = 1;
            } elseif ($nilai >= 51 && $nilai <= 69) {
                $crisp = 2;
            } elseif ($nilai >= 70 && $nilai <= 84) {
                $crisp = 3;
            } else {
                $crisp = 4;
            }
            break;

        case 'C6': // IMT
            if ($nilai < 18.5 || $nilai >= 30) {
                $crisp = 1;
            } elseif ($nilai >= 25.0 && $nilai <= 29.9) {
                $crisp = 2;
            } elseif ($nilai >= 18.5 && $nilai <= 20.9) {
                $crisp = 3;
            } else {
                $crisp = 4;
            }
            break;

        case 'C7': // Kecepatan
            if ($nilai <= 5.0) {
                $crisp = 1;
            } elseif ($nilai >= 5.1 && $nilai <= 6.5) {
                $crisp = 2;
            } elseif ($nilai >= 6.6 && $nilai <= 7.5) {
                $crisp = 3;
            } else {
                $crisp = 4;
            }
            break;

        default:
            $crisp = $nilai; // Default, jika tidak ada kriteria yang sesuai
            break;
    }

    return $crisp;
}

// Konversi nilai penilaian alternatif ke nilai CRISP
$penilaianCrisp = [];
foreach ($alternatif as $altId => $alt) {
    foreach ($kriteria as $kritId => $krit) {
        $nilai = $penilaian[$altId][$kritId];
        $penilaianCrisp[$altId][$kritId] = konversiNilaiCrisp($nilai, $krit['kode']);
    }
}


?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perhitungan SAW</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include('../includes/navbar.php'); ?>
    <div class="perhitungan-saw">
        <h2>PERHITUNGAN SAW</h2>
        <hr>
        <h3>TABEL PENILAIAN ALTERNATIF</h3>
        <p class="p-ahp">
            Nilai dalam tabel berikut diperoleh dari inputan penilaian alternatif terhadap masing-masing kriteria yang
            telah dilakukan sebelumnya. Nilai-nilai ini digunakan sebagai dasar dalam proses perhitungan menggunakan
            metode AHP dan SAW.
        </p>

        <table class="tabel-saw" border="1">
            <thead>
                <tr>
                    <th>Nama Alternatif</th>
                    <?php foreach ($kriteria as $krit) : ?>
                    <th><?php echo htmlspecialchars($krit['kriteria']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alternatif as $altId => $alt) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($alt['alternatif']); ?></td>
                    <?php foreach ($kriteria as $kritId => $krit) : ?>
                    <td><?php echo $penilaian[$altId][$kritId]; ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>NILAI CRISP</h3>
        <p class="crips">
            Nilai CRISP adalah skala nilai yang digunakan untuk mengkategorikan performa atau kualitas dari setiap
            kriteria dalam sistem. Setiap kriteria memiliki rentang nilai tertentu yang menggambarkan level pencapaian
            yang dapat dicapai oleh alternatif yang diuji. Nilai CRISP ini digunakan untuk menentukan bobot atau
            kontribusi masing-masing kriteria dalam proses perhitungan sistem.
        </p>
        <p class="crips">
            Skala nilai CRISP dapat berbeda-beda tergantung pada jenis kriteria yang dinilai. Misalnya, untuk kriteria
            yang bersifat <strong>benefit</strong>, nilai yang lebih tinggi menunjukkan performa yang lebih baik.
            Sebaliknya, untuk
            kriteria <strong>cost</strong>, nilai yang lebih rendah menunjukkan performa yang lebih baik. Sistem ini
            digunakan untuk
            memberikan penilaian yang lebih objektif dan sistematis terhadap setiap alternatif berdasarkan kriteria yang
            telah ditentukan sebelumnya.
        </p>
        <!-- Tabel Gabungan C1 hingga C5 -->
        <h4>C1 - C5 (Passing, Servis, Smash, Blok, Kerjasama Tim)</h4>
        <table class="tabel-saw" border="1">
            <thead>
                <tr>
                    <th>Nilai</th>
                    <th>Bobot</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>≤ 50</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>51–69</td>
                    <td>2</td>
                </tr>
                <tr>
                    <td>70–84</td>
                    <td>3</td>
                </tr>
                <tr>
                    <td>≥ 85</td>
                    <td>4</td>
                </tr>
            </tbody>
        </table>

        <!-- C6 (IMT) -->
        <h4>C6 (IMT)</h4>
        <table class="tabel-saw" border="1">
            <thead>
                <tr>
                    <th>Nilai</th>
                    <th>Bobot</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        < 18.5 atau ≥ 30</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>25.0 – 29.9</td>
                    <td>2</td>
                </tr>
                <tr>
                    <td>18.5 – 20.9</td>
                    <td>3</td>
                </tr>
                <tr>
                    <td>21.0 – 24.9</td>
                    <td>4</td>
                </tr>
            </tbody>
        </table>

        <!-- C7 (Kecepatan) -->
        <h4>C7 (Kecepatan)</h4>
        <table class="tabel-saw" border="1">
            <thead>
                <tr>
                    <th>Nilai</th>
                    <th>Bobot</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>≤ 5.0</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>5.1 – 6.5</td>
                    <td>2</td>
                </tr>
                <tr>
                    <td>6.6 – 7.5</td>
                    <td>3</td>
                </tr>
                <tr>
                    <td>> 7.5</td>
                    <td>4</td>
                </tr>
            </tbody>
        </table>
        <h3>Konversi Tabel Penilaian Alternatif ke Nilai CRISP</h3>
        <table class="tabel-saw" border="1">
            <thead>
                <tr>
                    <th>Kode Alternatif</th>
                    <?php foreach ($kriteria as $krit) : ?>
                    <th><?php echo htmlspecialchars($krit['kode']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alternatif as $altId => $alt) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($alt['kode']); ?></td>
                    <?php foreach ($kriteria as $kritId => $krit) : ?>
                    <td><?php echo $penilaianCrisp[$altId][$kritId]; ?></td> <!-- CRISP -->
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>

                <!-- Row for Jenis (Benefit/Cost) -->
                <tr>
                    <td>Jenis</td>
                    <?php foreach ($kriteria as $krit) : ?>
                    <td><?php echo $krit['jenis']; ?></td> <!-- Jenis -->
                    <?php endforeach; ?>
                </tr>

                <!-- Row for Bobot (Weight) with 4 decimals -->
                <tr>
                    <td>Bobot</td>
                    <?php foreach ($kriteria as $kritId => $krit) : ?>
                    <td><?php echo number_format($bobotKriteria[$kritId], 4); ?></td> <!-- Bobot (4 decimals) -->
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
        <h3>Normalisasi Matriks Berdasarkan Nilai CRISP</h3>
        <p class="p-ahp">
            Setelah nilai alternatif dikonversikan menjadi nilai crisp, langkah berikutnya adalah normalisasi untuk
            menyamakan skala antar kriteria. Dengan metode SAW, untuk kriteria benefit, nilai alternatif dibagi dengan
            nilai maksimum (Max), sementara untuk kriteria cost, dibagi dengan nilai minimum (Min). Normalisasi ini
            memastikan perbandingan yang adil antar kriteria yang memiliki satuan atau rentang nilai berbeda.
        </p>



        <table class="tabel-saw" border="1">
            <thead>
                <tr>
                    <th>Kode Alternatif</th>
                    <?php foreach ($kriteria as $krit) : ?>
                    <th><?php echo htmlspecialchars($krit['kode']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Normalisasi Matriks Berdasarkan Nilai CRISP
                $normalisasiCrisp = [];

                foreach ($kriteria as $kritId => $krit) {
                    $jenis = $krit['jenis']; // Benefit or Cost
                    $valuesCrisp = array_column($penilaianCrisp, $kritId);

                    if ($jenis == 'benefit') {
                        // Untuk Benefit: normalisasi = nilai / nilai maksimal
                        $maxCrisp = max($valuesCrisp);
                        foreach ($alternatif as $altId => $alt) {
                            $normalisasiCrisp[$altId][$kritId] = $maxCrisp != 0 ? $penilaianCrisp[$altId][$kritId] / $maxCrisp : 0;
                        }
                    } else { // Cost
                        // Untuk Cost: normalisasi = nilai minimal / nilai
                        $minCrisp = min($valuesCrisp);
                        foreach ($alternatif as $altId => $alt) {
                            $normalisasiCrisp[$altId][$kritId] = $minCrisp != 0 ? $minCrisp / $penilaianCrisp[$altId][$kritId] : 0;
                        }
                    }
                }
                ?>

                <!-- Tampilkan hasil normalisasi matriks -->
                <?php foreach ($alternatif as $altId => $alt) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($alt['kode']); ?></td>
                    <?php foreach ($kriteria as $kritId => $krit) : ?>
                    <td><?php echo number_format($normalisasiCrisp[$altId][$kritId], 4); ?></td> <!-- Normalisasi -->
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>

                <tr>
                    <td><strong>Bobot</strong></td>
                    <?php foreach ($kriteria as $kritId => $krit) : ?>
                    <td><?php echo number_format($bobotKriteria[$kritId], 4); ?></td> <!-- Bobot -->
                    <?php endforeach; ?>
                </tr>


            </tbody>
        </table>
        <!-- Tabel Nilai Preferensi berdasarkan Normalisasi Matriks CRISP -->
        <h3>Nilai Preferensi</h3>
        <p class="p-ahp">
            Setelah normalisasi, nilai yang telah disesuaikan dikalikan dengan bobot masing-masing kriteria. Hasil
            perkalian ini kemudian dijumlahkan untuk mendapatkan skor akhir setiap alternatif. Alternatif dengan skor
            tertinggi menunjukkan pilihan terbaik berdasarkan kriteria yang telah ditetapkan.
        </p>


        <table class="tabel-saw" border="1">
            <thead>
                <tr>
                    <th>Kode Alternatif</th>
                    <?php foreach ($kriteria as $krit) : ?>
                    <th><?php echo htmlspecialchars($krit['kode']); ?></th>
                    <?php endforeach; ?>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($alternatif as $altId => $alt) :
                    $jumlah = 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($alt['kode']); ?></td>
                    <?php foreach ($kriteria as $kritId => $krit) :
                            $nilaiNormalisasiCrisp = $normalisasiCrisp[$altId][$kritId];
                            $bobot = $bobotKriteria[$kritId];
                            $nilaiPreferensi = $nilaiNormalisasiCrisp * $bobot;
                            $jumlah += $nilaiPreferensi;
                        ?>
                    <td><?php echo round($nilaiPreferensi, 4); ?></td>
                    <?php endforeach; ?>
                    <td><?php echo round($jumlah, 4); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Hasil Perhitungan Skor Akhir</h3>
        <table class="tabel-saw" border="1">
            <thead>
                <tr>
                    <th>Nama Alternatif</th>
                    <th>Skor Akhir</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Memastikan variabel alternatif dan kriteria terdefinisi dengan benar
                if (isset($alternatif) && isset($kriteria) && isset($normalisasiCrisp) && isset($bobotKriteria)) {
                    foreach ($alternatif as $altId => $alt) :
                        $jumlahSkorAkhir = 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($alt['alternatif']); ?></td>
                    <td>
                        <?php
                                // Menghitung skor akhir berdasarkan nilai preferensi
                                foreach ($kriteria as $kritId => $krit) :
                                    // Pastikan normalisasiCrisp dan bobotKriteria memiliki data yang valid
                                    if (isset($normalisasiCrisp[$altId][$kritId]) && isset($bobotKriteria[$kritId])) {
                                        $nilaiNormalisasiCrisp = $normalisasiCrisp[$altId][$kritId];
                                        $bobot = $bobotKriteria[$kritId];
                                        $jumlahSkorAkhir += $nilaiNormalisasiCrisp * $bobot;
                                    }
                                endforeach;

                                // Menampilkan skor akhir yang telah dihitung
                                $skorAkhir = round($jumlahSkorAkhir, 4);
                                echo $skorAkhir;

                                // Menyimpan hasil skor akhir ke dalam tabel saw_skor
                                // Pastikan koneksi database sudah ada
                                $checkQuery = "SELECT * FROM saw_skor WHERE alternatif_id = '$altId'";
                                $resultCheck = mysqli_query($conn, $checkQuery);

                                if (mysqli_num_rows($resultCheck) > 0) {
                                    // Update skor akhir jika sudah ada
                                    $updateQuery = "UPDATE saw_skor SET skor = '$skorAkhir' WHERE alternatif_id = '$altId'";
                                    mysqli_query($conn, $updateQuery);
                                } else {
                                    // Insert skor akhir jika belum ada
                                    $insertQuery = "INSERT INTO saw_skor (alternatif_id, skor) VALUES ('$altId', '$skorAkhir')";
                                    mysqli_query($conn, $insertQuery);
                                }
                                ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php } else { ?>
                <tr>
                    <td colspan="2">Data alternatif atau kriteria tidak ditemukan.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>



    </div>
</body>

</html>