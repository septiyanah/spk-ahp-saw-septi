<?php
session_start();
include '../config/config.php'; // Koneksi database

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil data pengguna dari database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Space+Grotesk:wght@300..700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v2">
</head>

<body>
    <?php include('../includes/navbar.php'); ?>

    <div class="profile">
        <h2>PROFIL PENGGUNA</h2>
        <hr>

        <!-- Notifikasi jika password berhasil/gagal diubah -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<script>alert('" . $_SESSION['success_message'] . "');</script>";
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo "<script>alert('" . $_SESSION['error_message'] . "');</script>";
            unset($_SESSION['error_message']);
        }
        ?>

        <p><strong>Nama</strong>&emsp;&emsp;&emsp;: <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Username</strong>&emsp;: <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email</strong>&emsp;&emsp;&emsp;: <?php echo htmlspecialchars($user['email']); ?></p>

        <p><button class="btn-profile" onclick="openPopup()">UBAH PASSWORD</button></p>
    </div>

    <!-- Popup Ubah Password -->
    <div id="popup" class="popup-profile">
        <div class="popup-profile-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <h3>UBAH PASSWORD</h3>
            <form action="../function/proses_ubah_password.php" method="POST">
                <label>Password Lama:</label>
                <input type="password" name="old_password" required>

                <label>Password Baru:</label>
                <input type="password" name="new_password" required>

                <label>Konfirmasi Password Baru:</label>
                <input type="password" name="confirm_password" required>

                <button type="submit" class="btn-popup-profile">SIMPAN</button>
            </form>
        </div>
    </div>

    <script>
    function openPopup() {
        document.getElementById("popup").style.display = "flex";
    }

    function closePopup() {
        document.getElementById("popup").style.display = "none";
    }
    </script>
    <?php include('../includes/footer.php'); ?>

</body>

</html>