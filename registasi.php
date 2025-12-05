<?php
session_start();

$dataDir = __DIR__ . '/data';
$usersFile = $dataDir . '/users.json';
$successMessage = '';
$errorMessage = '';
$formData = [
    'full_name' => '',
    'phone' => '',
    'email' => ''
];

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([], JSON_PRETTY_PRINT));
}

$users = json_decode(file_get_contents($usersFile), true) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';

    if (
        $formData['full_name'] === '' ||
        $formData['phone'] === '' ||
        $formData['email'] === '' ||
        !filter_var($formData['email'], FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $errorMessage = 'Mohon lengkapi seluruh data dengan benar (password minimal 8 karakter).';
    } elseif ($password !== $passwordConfirmation) {
        $errorMessage = 'Konfirmasi kata sandi tidak sesuai.';
    } else {
        $emailExists = false;
        foreach ($users as $user) {
            if (strtolower($user['email']) === strtolower($formData['email'])) {
                $emailExists = true;
                break;
            }
        }

        if ($emailExists) {
            $errorMessage = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
        } else {
            $users[] = [
                'full_name' => $formData['full_name'],
                'phone' => $formData['phone'],
                'email' => $formData['email'],
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'created_at' => date('c')
            ];

            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            $successMessage = 'Registrasi berhasil! Silakan login menggunakan email Anda.';
            $formData = [
                'full_name' => '',
                'phone' => '',
                'email' => ''
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Martabak Rajan</title>
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
        }

        .auth-alert.success {
            background: #e6fff2;
            color: #0f8c3a;
            border: 1px solid #a4e0bd;
        }

        .auth-alert.error {
            background: #ffecec;
            color: #b10f1b;
            border: 1px solid #f5a2a8;
        }
    </style>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <a href="index.php" class="auth-back-link"><i class="fas fa-chevron-left"></i> Kembali ke Beranda</a>
        <div class="auth-card registration-card">
            <div class="auth-card__intro">
                <span class="auth-tagline">Gabung Komunitas #TimMartabak</span>
                <h1>Buat Akun Martabak Rajan</h1>
                <p>Dapatkan akses promo eksklusif, pantau pesanan, dan kumpulkan poin loyalti. Tinggal isi formulir singkat di samping.</p>
                <ul class="auth-benefits">
                    <li><i class="fas fa-check-circle"></i> Voucher diskon member senilai 20%</li>
                    <li><i class="fas fa-check-circle"></i> Riwayat pesanan yang selalu tersimpan</li>
                    <li><i class="fas fa-check-circle"></i> Support prioritas via WhatsApp</li>
                </ul>
            </div>
            <form class="auth-form" action="<?php echo h($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-heading">
                    <h2>Registrasi</h2>
                    <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                </div>
                <?php if ($successMessage): ?>
                    <div class="auth-alert success"><?php echo h($successMessage); ?></div>
                <?php elseif ($errorMessage): ?>
                    <div class="auth-alert error"><?php echo h($errorMessage); ?></div>
                <?php endif; ?>
                <div class="form-grid">
                    <label>
                        Nama Lengkap
                        <input type="text" name="full_name" placeholder="Fullname" value="<?php echo h($formData['full_name']); ?>" required>
                    </label>
                    <label>
                        Nomor Telepon
                        <input type="tel" name="phone" placeholder="+62 8*** ***" value="<?php echo h($formData['phone']); ?>" required>
                    </label>
                </div>
                <label>
                    Email Aktif
                    <input type="email" name="email" placeholder="nama@email.com" value="<?php echo h($formData['email']); ?>" required>
                </label>
                <div class="form-grid">
                    <label>
                        Kata Sandi
                        <input type="password" name="password" placeholder="Minimal 8 karakter" required>
                    </label>
                    <label>
                        Konfirmasi Kata Sandi
                        <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi" required>
                    </label>
                </div>
                <label class="checkbox-label">
                    <input type="checkbox" required>
                    Saya menyetujui Syarat & Ketentuan Martabak Rajan.
                </label>
                <button type="submit" class="auth-submit-btn">Daftar Sekarang</button>
                <p class="auth-footer-note">Dengan mendaftar Anda akan menerima notifikasi promo Martabak Rajan. Anda bisa berhenti kapan saja.</p>
                <p class="auth-footer-note">
                    Admin store? <a href="login-admin.php">Masuk ke panel admin</a>
                </p>
            </form>
        </div>
    </div>

</body>
</html>