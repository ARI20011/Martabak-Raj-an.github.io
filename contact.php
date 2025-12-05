<?php
session_start();

require_once __DIR__ . '/includes/avatar_helper.php';

$isLoggedIn = isset($_SESSION['user']);
$currentUserName = $isLoggedIn ? ($_SESSION['user']['full_name'] ?? 'User') : 'Guest';
$userAvatar = $isLoggedIn ? getUserAvatar() : 'img/converted_image.png';

// Add cache busting based on session timestamp or current time
$avatarTimestamp = isset($_SESSION['user']['selected_avatar_index']) ? $_SESSION['user']['selected_avatar_index'] : time();

$successMessage = '';
$errorMessage = '';
$formData = [
    'full_name' => '',
    'email' => '',
    'phone_code' => '+62',
    'phone' => '',
    'subject' => '',
    'message' => ''
];

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['phone_code'] = $_POST['phone_code'] ?? '+62';
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['subject'] = trim($_POST['subject'] ?? '');
    $formData['message'] = trim($_POST['message'] ?? '');

    if (
        $formData['full_name'] === '' ||
        $formData['email'] === '' ||
        !filter_var($formData['email'], FILTER_VALIDATE_EMAIL) ||
        $formData['phone'] === '' ||
        $formData['subject'] === '' ||
        $formData['message'] === ''
    ) {
        $errorMessage = 'Mohon lengkapi formulir dengan email yang valid.';
    } else {
        $to = 'alifprendje@gmail.com';
        $mailSubject = 'Pesan Baru dari Website Martabak Rajan';
        $body = "Nama: {$formData['full_name']}\r\n"
            . "Email: {$formData['email']}\r\n"
            . "Telepon: {$formData['phone_code']} {$formData['phone']}\r\n"
            . "Subjek: {$formData['subject']}\r\n"
            . "Pesan:\r\n{$formData['message']}";

        $headers = "From: Martabak Rajan <no-reply@martabakrajan.local>\r\n";
        $headers .= "Reply-To: {$formData['email']}\r\n";

        if (mail($to, $mailSubject, $body, $headers)) {
            $successMessage = 'Pesan berhasil dikirim! Kami akan menghubungi Anda secepatnya.';
            $formData = [
                'full_name' => '',
                'email' => '',
                'phone_code' => '+62',
                'phone' => '',
                'subject' => '',
                'message' => ''
            ];
        } else {
            $errorMessage = 'Terjadi kendala saat mengirim email. Coba lagi beberapa saat.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - Martabak Rajan</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contact-alert {
            margin-bottom: 1rem;
            padding: 0.9rem 1.2rem;
            border-radius: 0.6rem;
            font-weight: 600;
        }
        .contact-alert.success {
            background: #e6fff2;
            color: #0f8c3a;
            border: 1px solid #a4e0bd;
        }
        .contact-alert.error {
            background: #ffecec;
            color: #b10f1b;
            border: 1px solid #f5a2a8;
        }
    </style>
</head>
<body>
<script>
function goToPayment(){window.location.href="payment.php";}
function goToOrder(menuSlug){
    let target = "order.php";
    if (menuSlug){
        target += "?menu=" + encodeURIComponent(menuSlug);
    }
    window.location.href = target;
}
function gotomenu(){window.location.href="menu.php";}
function gotoregister(){window.location.href="registasi.php";}
function gotologin(){window.location.href="login.php";}
function notifyLoginRequired(){
<?php if ($isLoggedIn): ?>
    window.location.href = "profile.php";
<?php else: ?>
    alert("Kamu harus login terlebih dahulu.");
<?php endif; ?>
}
</script>

<nav class="navbar-rajan">
    <div class="navbar-left">
        <img src="img/Cokelat Krem Ilustrasi Imut Logo Martabak Manis (3).png" alt="Martabak Rajan Logo" class="logo-img">
        <span class="welcome-text">
            <b>Welcome to</b> <span class="brand-name">Martabak Rajan</span>
        </span>
    </div>
    <div class="navbar-right">
        <a href="index.php" class="nav-link">Home</a>
        <a href="contact.php" class="nav-link active">Contact Us</a>
        <a href="javascript:void(0)" class="nav-link" onclick="gotologin()">Login</a>
        <a href="javascript:void(0)" class="nav-link cta" onclick="gotoregister()">Register</a>
        <span class="vertical-divider"></span>
        <div class="user-profile" onclick="notifyLoginRequired()">
            <span class="user-name"><?php echo h($currentUserName); ?></span>
            <img src="<?php echo h($userAvatar); ?>?v=<?php echo $avatarTimestamp; ?>" alt="User Avatar" class="avatar" id="user-avatar-navbar">
        </div>
    </div>
</nav>

<header class="contact-hero">
    <div class="contact-hero__content">
        <p class="contact-label">Get in touch</p>
        <h1>Let's Answer Your Queries</h1>
        <p>Kami siap membantu setiap pertanyaan mengenai menu, pesanan, maupun peluang kerjasama. Tinggalkan pesan Anda, tim Martabak Rajan akan merespons secepatnya.</p>
    </div>
</header>

<section class="contact-form-section">
    <div class="contact-form__wrapper">
        <form class="contact-form" action="<?php echo h($_SERVER['PHP_SELF']); ?>" method="post">
            <?php if ($successMessage): ?>
                <div class="contact-alert success"><?php echo h($successMessage); ?></div>
            <?php elseif ($errorMessage): ?>
                <div class="contact-alert error"><?php echo h($errorMessage); ?></div>
            <?php endif; ?>
            <div class="contact-form__grid">
                <label>
                    Full Name
                    <input type="text" name="full_name" placeholder="Fullname" value="<?php echo h($formData['full_name']); ?>" required>
                </label>
                <label>
                    Email Address
                    <input type="email" name="email" placeholder="nama@email.com" value="<?php echo h($formData['email']); ?>" required>
                </label>
            </div>
            <div class="contact-form__grid">
                <label>
                    Phone Number
                    <div class="phone-input">
                        <select name="phone_code">
                            <option value="+62" <?php echo $formData['phone_code'] === '+62' ? 'selected' : ''; ?>>+62</option>
                            <option value="+60" <?php echo $formData['phone_code'] === '+60' ? 'selected' : ''; ?>>+60</option>
                            <option value="+65" <?php echo $formData['phone_code'] === '+65' ? 'selected' : ''; ?>>+65</option>
                            <option value="+1" <?php echo $formData['phone_code'] === '+1' ? 'selected' : ''; ?>>+1</option>
                        </select>
                        <input type="tel" name="phone" placeholder="812 3456 7890" value="<?php echo h($formData['phone']); ?>" required>
                    </div>
                </label>
                <label>
                    Pesan singkat
                    <input type="text" name="subject" placeholder="cth. Kolaborasi reseller" value="<?php echo h($formData['subject']); ?>" required>
                </label>
            </div>
            <label>
                Message
                <textarea name="message" rows="6" placeholder="Tulis detail kebutuhan Anda di sini..." required><?php echo h($formData['message']); ?></textarea>
            </label>
            <button type="submit" class="contact-submit-btn">Submit</button>
        </form>
    </div>
</section>

<section class="contact-info-strip">
    <div>
        <h3>Jam Operasional</h3>
        <p>Setiap Hari • 10.00 - 23.00 WITA</p>
    </div>
    <div>
        <h3>Alamat Dapur Pusat</h3>
        <p>Jl. Diponegoro No. 18, Kupang, NTT</p>
    </div>
    <div>
        <h3>Customer Support</h3>
        <p>support@martabakrajan.com • (0380) 888-123</p>
    </div>
</section>

<section class="download-app-section contact-download">
    <div class="download-content">
        <h2 class="download-title">DOWNLOAD THE APP</h2>
        <div class="app-buttons">
            <a href="https://play.google.com/store/games?hl=en" class="app-btn android-btn">
                <i class="fab fa-google-play"></i> Get It On Android
            </a>
            <a href="https://www.apple.com/id/app-store/" class="app-btn ios-btn">
                <i class="fab fa-apple"></i> Get It On iOS
            </a>
        </div>
    </div>
</section>

<section class="register-promo contact-register">
    <div class="register-overlay">
        <h3 class="register-title">REGISTER FOR FREE</h3>
        <p class="register-subtitle">Register with us and win amazing discount points on <span>Martabak Raj'an</span></p>
        <button class="register-btn" onclick="gotoregister()">Register</button>
    </div>
</section>

<footer class="footer-rajan">
    <div class="footer-details-wrapper">
        <span class="footer-brand">Martabak Rajan</span>
        <nav class="footer-links">
            <a href="menu.php">Service</a>
            <a href="#">About Us</a>
            <a href="contact.php">Contact Us</a>
            <a href="#">FAQs</a>
            <a href="login.php">Sign In</a>
        </nav>
        <div class="footer-social-icons">
            <span class="social-icon-circle"><i class="fas fa-envelope"></i></span>
            <span class="social-icon-circle"><i class="far fa-calendar-alt"></i></span>
            <span class="social-icon-circle"><i class="fas fa-map-marker-alt"></i></span>
            <span class="social-icon-circle"><i class="fab fa-facebook-f"></i></span>
            <span class="social-icon-circle"><i class="far fa-clock"></i></span>
            
        </div>
    </div>
    <p class="footer-note">© <?php echo date('Y'); ?> Alif Firmansya Prendje. All rights reserved.

</body>
</html>



