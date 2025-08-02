<?php
session_start();
include '../config/config.php'; // Koneksi database

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token tidak valid!");
}

// Cek token di database
$stmt = $conn->prepare("SELECT id, reset_token_expiry FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    // Cek apakah token masih berlaku
    if (strtotime($user['reset_token_expiry']) < time()) {
        die("Token telah kadaluarsa!");
    }
} else {
    die("Token tidak valid!");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="reset-password">
        <div class="reset-password-container">
            <h2>RESET PASSWORD</h2>
            <form action="update_password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="new_password" placeholder="Password Baru" required>
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
                <button class="btn-reset" type="submit">UBAH PASSWORD</button>
            </form>
        </div>
    </div>
</body>

</html>