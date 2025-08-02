<?php
session_start();
require '../config/config.php'; // Koneksi database

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Pastikan PHPMailer sudah terinstall

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Cek apakah email ada di database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) { // Jika email ditemukan
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Buat token unik & waktu kadaluarsa
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+3 minute"));

        // Simpan token di database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt->bind_param("ssi", $token, $expiry, $user_id);
        $stmt->execute();

        // Buat link reset password
        $reset_link = "http://localhost/skripsi/function/reset_password.php?token=" . $token;

        // Kirim email dengan PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Konfigurasi SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Ganti dengan SMTP email Anda
            $mail->SMTPAuth   = true;
            $mail->Username   = 'septi.yanah10@perbanas.id'; // Ganti dengan email Anda
            $mail->Password   = 'vrxa ykma fvil xvsr'; // Ganti dengan password email
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Pengirim & Penerima
            $mail->setFrom('no-reply@seya.com', 'SPK AHP-SAW');
            $mail->addAddress($email);

            // Konten Email
            $mail->isHTML(true);
            $mail->Subject = "Reset Password";
            $mail->Body    = "Klik link berikut untuk mereset password Anda: <a href='$reset_link'>$reset_link</a>";

            // Kirim Email
            $mail->send();
            $_SESSION['success_message'] = "Link reset password telah dikirim ke email Anda.";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Gagal mengirim email: {$mail->ErrorInfo}";
        }
    } else { // Jika email tidak ditemukan
        $_SESSION['error_message'] = "Email tidak terdaftar!";
    }

    header("Location: ../auth/forgot_password.php");
    exit();
}