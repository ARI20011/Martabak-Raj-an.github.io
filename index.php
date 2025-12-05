<?php
session_start();

require_once __DIR__ . '/includes/avatar_helper.php';

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$isLoggedIn = isset($_SESSION['user']);
$currentUserName = $isLoggedIn ? ($_SESSION['user']['full_name'] ?? 'User') : 'Guest';
$userAvatar = $isLoggedIn ? getUserAvatar() : 'img/converted_image.png';

// Add cache busting based on session timestamp or current time
$avatarTimestamp = isset($_SESSION['user']['selected_avatar_index']) ? $_SESSION['user']['selected_avatar_index'] : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Martabak Raj'an</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <script>
function goToPayment() {
    window.location.href = "payment.php"; 
}
function goToOrder(menuSlug) {
    var target = "order.php";
    if (menuSlug) {
        target += "?menu=" + encodeURIComponent(menuSlug);
    }
    window.location.href = target;
}
function gotomenu() {
    window.location.href = "menu.php"; 
}
function gotoregister() {
    window.location.href = "registasi.php"; 
}
function gotologin() {
    window.location.href = "login.php";
}
function handleProfileClick() {
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
         <b>  Welcome to </b> <span class="brand-name">Martabak Rajan</span>
        </span>
    </div>

    <div class="navbar-right">
        <a href="index.php" class="nav-link active">Home</a>
        <a href="contact.php" class="nav-link">Contact Us</a>
        <a href="javascript:void(0)" class="nav-link" onclick="gotologin()">Login</a>
        <a href="javascript:void(0)" class="nav-link cta" onclick="gotoregister()">Register</a>

        <span class="vertical-divider"></span>

        <div class="user-profile" onclick="handleProfileClick()">
            <span class="user-name"><?php echo h($currentUserName); ?></span>
            <img src="<?php echo h($userAvatar); ?>?v=<?php echo $avatarTimestamp; ?>" alt="User Avatar" class="avatar" id="user-avatar-navbar">
        </div>
    </div>
</nav>

 <header class="header-section">
    <div class="hero-banner">
        <div class="discount-overlay">
            <p class="discount-text">DISCOUNT 20%</p>
            <p class="promo-detail">Buy With QRIS Save More 20% Discount Only Applies To First Purchase</p>
            <button  class="buy-now-btn" onclick="gotomenu()">Lebih Banyak</button>
        </div>
    </div>
</header>

<div class="main-wrapper">
    <main class="menu-content">
        <h2 class="section-title">Best Menu🔥</h2>

        <div class="menu-item-card">
            <div class="item-left">
                <img src="img/5f40bfe4e8c16.jpg" alt="Martabak 3 Rasa" class="item-img">
                <div class="item-details">
                    <h3 class="item-name">Martabak 3 rasa</h3>
                    <p class="item-location">NTT/NUSA TENGGARA TIMUR, Alor. Teluk Mutiara</p>
                    <p class="item-price">Harga: Rp 35.000</p>
                    <a href="#" class="show-card-link">Show Menu Card</a>
                    <div class="item-actions">
                    <button class="action-btn pesan-btn" onclick="goToOrder('martabak-3-rasa')">Pesan</button>
                    <button class="action-btn beli-btn" onclick="goToPayment()">Beli</button>

                    </div>
                </div>
            </div>
            
            <span class="separator-icon">«</span>

            <div class="item-right">
                <img src="img/keju.jpg" alt="Thumbnail Keju" class="thumb-img">
                <img src="img/coklat.jpg" alt="Thumbnail Cokelat" class="thumb-img">
                <img src="img/kacang.jpg" alt="Thumbnail kacang" class="thumb-img">
            </div>
        </div>
        <div class="menu-item-card">
            <div class="item-left">
                <img src="img/mesir.jpg" alt="Martabak Mesir" class="item-img">
                <div class="item-details">
                    <h3 class="item-name">Martabak Mesir</h3>
                    <p class="item-location">NTT/NUSA TENGGARA TIMUR, Alor. Teluk mutiara</p>
                    <p class="item-price">Harga: Rp 30.000</p>
                    <a href="#" class="show-card-link">Show Menu Card</a>
                    <div class="item-actions">
                    <button class="action-btn pesan-btn" onclick="goToOrder('martabak-mesir')">Pesan</button>
                    <button class="action-btn beli-btn" onclick="goToPayment()">Beli</button>
                    </div>
                </div>
            </div>
            
            <span class="separator-icon">«</span>

            <div class="item-right">
                <img src="img/telor.jpg" alt="Thumbnail Telur" class="thumb-img">
                <img src="img/pangsint.webp" alt="Thumbnail Roll" class="thumb-img">
            </div>
        </div>
         <div class="menu-item-card">
            <div class="item-left">
                <img src="img/jasuke.webp" alt="Martabak jasuke" class="item-img">
                <div class="item-details">
                    <h3 class="item-name">Martabak Jasuke</h3>
                    <p class="item-location">NTT/NUSA TENGGARA TIMUR, Ende, Ende Barat</p>
                    <p class="item-price">Harga: Rp 25.000</p>
                    <a href="#" class="show-card-link">Show Menu Card</a>
                    <div class="item-actions">
                        <button class="action-btn pesan-btn" onclick="goToOrder('martabak-jasuke')">Pesan</button>
                    <button class="action-btn beli-btn" onclick="goToPayment()">Beli</button>
                    </div>
                </div>
            </div>
            
            <span class="separator-icon">«</span>

            <div class="item-right">
                <img src="img/jagung.webp" alt="Thumbnail Jagung" class="thumb-img">
                <img src="img/keju.jpg" alt="Thumbnail Keju" class="thumb-img">
            </div>
        </div>
         <div class="menu-item-card">
            <div class="item-left">
                <img src="img/oreo.jpg" alt="Martabak oreo" class="item-img">
                <div class="item-details">
                    <h3 class="item-name">Martabak Oreo</h3>
                    <p class="item-location">NTT/NUSA TENGGARA TIMUR, Kupang, leoleba</p>
                    <p class="item-price">Harga: Rp 40.000</p>
                    <a href="#" class="show-card-link">Show Menu Card</a>
                    <div class="item-actions">
                        <button class="action-btn pesan-btn" onclick="goToOrder('martabak-oreo')">Pesan</button>
                    <button class="action-btn beli-btn" onclick="goToPayment()">Beli</button>
                    </div>
                </div>
            </div>
            
            <span class="separator-icon">«</span>

            <div class="item-right">
                <img src="img/cream.jpeg" alt="Thumbnail cream" class="thumb-img">
                <img src="img/coklat.jpg" alt="Thumbnail coklat" class="thumb-img">
            </div>
        </div>
         <div class="menu-item-card">
            <div class="item-left">
                <img src="img/mars.jpeg" alt="Martabak mars" class="item-img">
                <div class="item-details">
                    <h3 class="item-name">Martabak Marsmelow</h3>
                    <p class="item-location">NTT/NUSA TENGGARA TIMUR, kupang, alak</p>
                    <p class="item-price">Harga: Rp 38.000</p>
                    <a href="#" class="show-card-link">Show Menu Card</a>
                    <div class="item-actions">
                        <button class="action-btn pesan-btn" onclick="goToOrder('martabak-marsmelow')">Pesan</button>
                    <button class="action-btn beli-btn" onclick="goToPayment()">Beli</button>
                    </div>
                </div>
            </div>
            
            <span class="separator-icon">«</span>

            <div class="item-right">
                <img src="img/marb.jpeg" alt="Thumbnail rsndom" class="thumb-img">
                <img src="img/pisang.jpg" alt="Thumbnail pisang" class="thumb-img">
            </div>
        </div>
        </main>

    <aside class="sidebar">
        
        <div class="savings-box">
            <h3 class="savings-title">Your Savings</h3>
            <span class="discount-label">20%</span>
        </div>

        <div class="locations-box">
            <h3 class="sidebar-subtitle">All Locations</h3>
            <p>NTT(Nusa tenggara timur), Alor, Teluk mutiara</p>
            <p>NTT(Nusa tenggara timur), Kupang, leoleba</p>
            <p>NTT(Nusa tenggara timur), Kupang, alak</p>
            <p>Kelupauan Bangka Belitung , Bangka , Tanjung Pandan</p>
            <p>NTT(Nusa tenggara timur) , Ende , Ende Barat</p>
            </div>

        <div class="websites-box">
            <h3 class="sidebar-subtitle">Official Websites</h3>
            <div class="website-item">
                <img src="img/mangga.png" alt="Mangga Online" class="website-icon">
                <div class="website-details">
                    <p class="website-name">Mangga online</p>
                    <a class="website-status" href="WEB MANGA/index.HTML" target="_blank" rel="noopener">Open</a>
                </div>
            </div>
            <div class="website-item">
                <img src="img/Makn.jpeg" alt="Makn Ende" class="website-icon">
                <div class="website-details">
                    <p class="website-name">Makn Ende</p>
                    <a class="website-status" href="https://makn-ende.sch.id/" target="_blank" rel="noopener">Open</a>
                </div>
            </div>
            </div>
            
    </aside>
</div>
<section class="flavor-selection">
    <div class="flavor-header">
        <h2 class="section-title">choose a flavor</h2>
    </div>
    
    <div class="flavor-grid-wrapper">
        <div class="flavor-grid">
            <div class="flavor-option">
                <img src="img/ju.jpg" alt="Keju">
                <p>Chess</p>
            </div>
            <div class="flavor-option">
                <img src="img/cha.jpg" alt="Matcha">
                <p>Matcha</p>
            </div>
            <div class="flavor-option">
                <img src="img/reo.jpg" alt="Oreo">
                <p>Oreo</p>
            </div>
            <div class="flavor-option">
                <img src="img/nuthela.webp" alt="Nuthela">
                <p>Nuthela</p>
            </div>
            <div class="flavor-option">
                <img src="img/mozarela.webp" alt="Mozarella">
                <p>Mozarella</p>
            </div>
            <div class="flavor-option">
                <img src="img/lat.webp" alt="Coklat">
                <p>Coklat</p>
            </div>
            <div class="flavor-option">
                <img src="img/low.jpeg" alt="Marsmelow">
                <p>Marsmelow</p>
            </div>
            <div class="flavor-option">
                <img src="img/cang.jpg" alt="Peanut">
                <p>Peanut</p>
            </div>
            <div class="flavor-option">
                <img src="img/gung.webp" alt="Corn">
                <p>corn</p>
            </div>
            <div class="flavor-option">
                <img src="img/sang.jpg" alt="Banana">
                <p>Banana</p>
            </div>
            <div class="flavor-option">
                <img src="img/telor.jpeg" alt="Egg">
                <p>Egg</p>
            </div>
            <div class="flavor-option">
                <img src="img/lapa.webp" alt=" Coconut">
                <p>Coconut</p>
            </div>
            </div>
            </div>
    </div>
</section>
<section class="download-app-section">
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

    <footer class="footer-rajan">
        <div class="register-promo">
            <h3 class="register-title">REGISTER FOR FREE</h3>
            <p class="register-subtitle">Register with us and win amazing discount points on <span>Martabak Raj'an</span></p>
            <button class="register-btn" onclick="gotoregister()">Register</button>
        </div>


<footer class="footer-rajan">
    <div class="footer-details-wrapper">
        <span class="footer-brand">Martabak Rajan</span>
        <nav class="footer-links">
            <a href="#">Service</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
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
</footer>
<p class="footer-note">© Alif Firmansya Prendje. All rights reserved.</p>
</body>
</html>
