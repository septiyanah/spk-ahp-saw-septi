<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v2">
</head>

<body>
    <div class="sidebar">
        <h1>SPK <br>AHP-SAW</h1>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="kriteria.php">Kriteria</a></li>
            <li><a href="alternatif.php">Alternatif</a></li>
            <li><a href="penilaian_alternatif.php">Penilaian Alternatif</a></li>
            <li><a href="ahp.php">Perhitungan AHP</a></li>
            <li><a href="saw.php">Perhitungan SAW</a></li>
            <li><a href="hasil.php">Hasil</a></li>
        </ul>
        <a href="#" class="logout" onclick="confirmLogout()">LOGOUT</a>

    </div>
    <script>
    function confirmLogout() {
        if (confirm("Apakah Anda yakin ingin logout?")) {
            window.location.href = "../auth/logout.php";
        }
    }
    </script>

</body>

</html>