<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['admin_username'] ?? '');
    $password = $_POST['admin_password'] ?? '';
    $roleInput = strtolower(trim($_POST['admin_role'] ?? 'admin'));
    $allowedRoles = ['admin','chef','gojek'];
    $role = in_array($roleInput, $allowedRoles) ? $roleInput : 'admin';

    $adminsFile = __DIR__ . '/data/admins.json';
    if (!file_exists($adminsFile)) {
        if (!is_dir(__DIR__ . '/data')) {
            mkdir(__DIR__ . '/data', 0755, true);
        }
        $defaultAdmins = [
            [
                'username' => 'alip',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => date('c')
            ],
            [
                'username' => 'Yusuf',
                'password' => password_hash('chef123', PASSWORD_DEFAULT),
                'role' => 'chef',
                'created_at' => date('c')
            ],
            [
                'username' => 'Acho',
                'password' => password_hash('gojek123', PASSWORD_DEFAULT),
                'role' => 'gojek',
                'created_at' => date('c')
            ],
            [
                'username' => 'Kholid',
                'password' => password_hash('gojek234', PASSWORD_DEFAULT),
                'role' => 'gojek',
                'created_at' => date('c')
            ]
        ];
        file_put_contents($adminsFile, json_encode($defaultAdmins, JSON_PRETTY_PRINT));
    }

    $admins = json_decode(file_get_contents($adminsFile), true) ?: [];
    $found = false;
    foreach ($admins as $a) {
        if (isset($a['username']) && strtolower($a['username']) === strtolower($username)) {
            $found = true;
            if (isset($a['password']) && password_verify($password, $a['password'])) {
                // Successful login
                // Apply username->role mapping for specific users
                $unameLower = strtolower($a['username']);
                if ($unameLower === 'yusuf') {
                    $assignedRole = 'chef';
                } elseif (in_array($unameLower, ['acho','kholid'])) {
                    $assignedRole = 'gojek';
                } else {
                    $assignedRole = isset($a['role']) ? $a['role'] : 'admin';
                }

                $_SESSION['admin'] = [
                    'username' => $a['username'],
                    'role' => $assignedRole
                ];
                $roleRedirect = strtolower($_SESSION['admin']['role']);
                switch ($roleRedirect) {
                    case 'chef': header('Location: chef_dashboard.php'); break;
                    case 'gojek': header('Location: gojek_dashboard.php'); break;
                    case 'admin':
                    default: header('Location: admin_dashboard.php'); break;
                }
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
            break;
        }
    }

    // If not found, create the admin record in admins.json using provided role
    if (!$found) {
        // Apply mapping for well-known usernames when creating accounts
        $unameLower = strtolower($username);
        if ($unameLower === 'yusuf') {
            $createdRole = 'chef';
        } elseif (in_array($unameLower, ['acho','kholid'])) {
            $createdRole = 'gojek';
        } else {
            $createdRole = $role;
        }

        $newAdmin = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $createdRole,
            'created_at' => date('c')
        ];
        $admins[] = $newAdmin;
        file_put_contents($adminsFile, json_encode($admins, JSON_PRETTY_PRINT));

        // Log in the newly created account
        $_SESSION['admin'] = [
            'username' => $newAdmin['username'],
            'role' => $newAdmin['role']
        ];
        switch ($newAdmin['role']) {
            case 'chef': header('Location: chef_dashboard.php'); break;
            case 'gojek': header('Location: gojek_dashboard.php'); break;
            case 'admin':
            default: header('Location: admin_dashboard.php'); break;
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Martabak Rajan</title>
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
    </style>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <a href="index.php" class="auth-back-link"><i class="fas fa-chevron-left"></i> Kembali ke Beranda</a>
        <div class="auth-card login-card">
            <div class="auth-card__intro">
                <span class="auth-tagline">Panel Admin</span>
                <h1>Login Admin Martabak Rajan</h1>
                <p>Masuk untuk mengelola menu, pesanan, dan promosi Martabak Rajan. Pastikan Anda menggunakan akun yang memiliki hak akses resmi.</p>
                <div class="auth-highlight">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>Keamanan Penting</strong>
                        <p>Jangan bagikan kredensial admin Anda. Semua aktivitas diaudit secara berkala.</p>
                    </div>
                </div>
            </div>
            <form class="auth-form" action="#" method="post">
                <div class="form-heading">
                    <h2>Masuk Admin</h2>
                    <p>Bukan admin? <a href="login.php">Login pelanggan</a></p>
                </div>
                <label>
                    Username Admin
                    <input type="text" name="admin_username" placeholder="admin" required>
                </label>
                <label>
                    Kata Sandi
                    <input type="password" name="admin_password" placeholder="Masukkan kata sandi" required>
                </label>
                <div class="form-actions-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_admin">
                        Ingat perangkat ini
                    </label>
                    <a href="#" class="link-muted">Butuh bantuan?</a>
                </div>
                <button type="submit" class="auth-submit-btn">Masuk</button>
                <p class="auth-footer-note">Akses panel admin hanya untuk staff yang terdaftar. Hubungi super admin bila mengalami kendala.</p>
            </form>
        </div>
    </div>

</body>
</html>


