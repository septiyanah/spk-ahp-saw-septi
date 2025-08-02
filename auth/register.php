<?php
session_start();
require '../config/config.php'; // Pastikan koneksi database ada di sini

if (isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Proses registrasi jika form dikirim
$error = ""; // Inisialisasi error agar tidak muncul undefined variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua kolom wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        // Hash password untuk keamanan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Cek apakah username atau email sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username atau Email sudah digunakan!";
        } else {
            // Simpan data pengguna ke database
            $stmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $username, $email, $hashed_password);

            if ($stmt->execute()) {
                // Setelah registrasi berhasil, arahkan langsung ke login.php
                header("Location: ../auth/login.php");
                exit();
            } else {
                $error = "Terjadi kesalahan, coba lagi.";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/style.css?v2">
</head>

<body>
    <div class="register">
        <div class="register-container">
            <h2>Register</h2>
            <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
            <form method="POST" action="">
                <input type="text" name="name" placeholder="Name" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Register</button>
            </form>
            <p class="login-link">Sudah punya akun? <a href="login.php"><br>SIGN IN</a></p>
        </div>
    </div>
</body>

</html>