<?php
session_start();
include '../config/config.php'; // Koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Konfirmasi password tidak cocok!";
        header("Location: ../function/reset_password.php?token=" . urlencode($token));
        exit();
    }

    // Cek token di database
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password & hapus token reset
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Password berhasil diperbarui!";
            header("Location: ../auth/login.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui password!";
        }
    } else {
        $_SESSION['error_message'] = "Token tidak valid!";
    }

    header("Location: ../function/reset_password.php?token=" . urlencode($token));
    exit();
}