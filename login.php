<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: profile.php');
    exit;
}

$dataDir = __DIR__ . '/data';
$usersFile = $dataDir . '/users.json';
$loginError = '';

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([], JSON_PRETTY_PRINT));
}

$users = json_decode(file_get_contents($usersFile), true) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $loginError = 'Mohon masukkan email dan kata sandi.';
    } else {
        foreach ($users as $user) {
            if (strtolower($user['email']) === strtolower($email) && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? '',
                    'avatar_path' => $user['avatar_path'] ?? null,
                    'selected_avatar_index' => $user['selected_avatar_index'] ?? 1
                ];
                echo "<script>alert('Login berhasil!'); window.location.href='profile.php';</script>";
                exit;
            }
        }
        $loginError = 'Email atau kata sandi salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Martabak Rajan</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Animasi untuk card */
        .auth-card {
            animation: slideInScale 0.8s ease-out 0.3s both;
        }

        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .auth-alert {
            margin-bottom: 15px;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 600;
            background: #ffecec;
            color: #b10f1b;
            border: 1px solid #f5a2a8;
        }
    </style>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <a href="index.php" class="auth-back-link"><i class="fas fa-chevron-left"></i> Kembali ke Beranda</a>
        <div class="auth-card login-card">
            <div class="auth-card__intro">
                <span class="auth-tagline">Selamat Datang Kembali!</span>
                <h1>Masuk ke Martabak Rajan</h1>
                <p>Nikmati kemudahan mengatur pesanan favorit, cek status pengiriman, dan kumpulkan poin ekstra setiap kali transaksi.</p>
                <div class="auth-highlight">
                    <i class="fas fa-gift"></i>
                    <div>
                        <strong>Bonus member</strong>
                        <p>Masuk hari ini dan klaim voucher topping gratis untuk pesanan berikutnya.</p>
                    </div>
                </div>
            </div>
            <form class="auth-form" action="<?php echo h($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-heading">
                    <h2>Masuk</h2>
                    <p>Belum punya akun? <a href="registasi.php">Daftar dulu</a></p>
                </div>
                <?php if ($loginError): ?>
                    <div class="auth-alert"><?php echo h($loginError); ?></div>
                <?php endif; ?>
                <label>
                    Email
                    <input type="email" name="identifier" placeholder="emailmu@gmail.com" required>
                </label>
                <label>
                    Kata Sandi
                    <input type="password" name="password" placeholder="Masukkan kata sandi" required>
                </label>
                <div class="form-actions-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        Ingat saya
                    </label>
                    <a href="https://myaccount.google.com/?utm_source=chrome-profile-chooser&pli=1" class="link-muted">Lupa kata sandi?</a>
                </div>
                <button type="submit" class="auth-submit-btn">Masuk</button>
                <div class="auth-divider">
                    <span></span>
                    <p>atau masuk dengan</p>
                    <span></span>
                </div>
                <div class="social-login-buttons">
                    <button type="button"><i class="fab fa-google"></i> Google</button>
                    <button type="button"><i class="fab fa-facebook-f"></i> Facebook</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>



