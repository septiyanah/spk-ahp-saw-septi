<?php
session_start();
include '../config/config.php'; // Koneksi database

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Ambil password lama dari database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Cek apakah password lama cocok
    if (!$user || !password_verify($old_password, $user['password'])) {
        $_SESSION['error_message'] = "Password lama salah!";
        header("Location: ../pages/profile.php");
        exit();
    }

    // Cek apakah password baru dan konfirmasi cocok
    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Konfirmasi password baru tidak cocok!";
        header("Location: ../pages/profile.php");
        exit();
    }

    // Hash password baru
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password di database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Password berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui password!";
    }

    $stmt->close();

    // Redirect ke halaman profile setelah update password
    header("Location: ../pages/profile.php");
    exit();
}