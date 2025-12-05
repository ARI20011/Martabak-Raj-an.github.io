<?php
session_start();

require_once __DIR__ . '/includes/avatar_helper.php';

$isLoggedIn = isset($_SESSION['user']);
$currentUserName = $isLoggedIn ? ($_SESSION['user']['full_name'] ?? 'User') : 'Guest';
$userAvatar = $isLoggedIn ? getUserAvatar() : 'img/converted_image.png';

// Add cache busting based on session timestamp or current time
$avatarTimestamp = isset($_SESSION['user']['selected_avatar_index']) ? $_SESSION['user']['selected_avatar_index'] : time();

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$menus = [
    'martabak-3-rasa' => [
        'name' => 'Martabak 3 Rasa',
        'location' => 'NTT | Alor , Teluk Mutiara • Indoor & Outdoor Seating',
        'image' => 'img/5f40bfe4e8c16.jpg',
        'description' => 'Kombinasi keju, cokelat, dan kacang premium dalam satu loyang. Adonan lembut dengan tekstur renyah di luar menghadirkan sensasi rasa seimbang untuk dinikmati bersama keluarga.',
        'slots' => ['10:00 AM','10:45 AM','11:30 AM','12:00 PM','12:30 PM','02:00 PM','03:00 PM','04:00 PM','05:00 PM'],
        'featured_slot' => '12:00 PM',
        'hours' => '10.00 - 23.00 WITA',
        'map' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.9211476738154!2d123.607123!3d-10.177199!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDEwJzM4LjAiUyAxMjPCsDM2JzI2LjYiRQ!5e0!3m2!1sen!2sid!4v1700000000000'
    ],
    'martabak-mesir' => [
        'name' => 'Martabak Mesir',
        'location' => 'NTT | Alor ,  Teluk Mutiara • Indoor Seating',
        'image' => 'img/mesir.jpg',
        'description' => 'Martabak gurih berisi daging cincang dan rempah Timur Tengah. Cocok untuk penggemar rasa kaya dan hangat, disajikan bersama acar segar dan sambal khas.',
        'slots' => ['10:30 AM','11:15 AM','12:00 PM','12:45 PM','01:30 PM','03:00 PM','04:30 PM','06:00 PM'],
        'featured_slot' => '12:45 PM',
        'hours' => '11.00 - 23.00 WITA',
        'map' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.9064750230165!2d123.6125!3d-10.1782!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDEwJzQxLjUiUyAxMjPCsDM2JzQ1LjAiRQ!5e0!3m2!1sid!2sid!4v1700000000001'
    ],
    'martabak-jasuke' => [
        'name' => 'Martabak Jasuke',
        'location' => 'NTT | Ende Barat • Indoor & Delivery',
        'image' => 'img/jasuke.webp',
        'description' => 'Perpaduan jagung manis, susu, dan keju dalam versi martabak tebal yang lembut. Favorit anak muda karena rasa creamy dan legit.',
        'slots' => ['11:00 AM','11:30 AM','12:30 PM','01:00 PM','02:30 PM','04:00 PM','05:30 PM','07:00 PM'],
        'featured_slot' => '01:00 PM',
        'hours' => '10.30 - 22.00 WITA',
        'map' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.9155112300694!2d123.6129!3d-10.1775!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDEwJzM5LjAiUyAxMjPCsDM2JzQ2LjQiRQ!5e0!3m2!1sid!2sid!4v1700000000002'
    ],
    'martabak-oreo' => [
        'name' => 'Martabak Oreo',
        'location' => 'NTT | Kupang Leoleba • Indoor Seating',
        'image' => 'img/oreo.jpg',
        'description' => 'Martabak kekinian bertopping oreo crumble dan cream cheese, menghadirkan sensasi crunchy dan creamy dalam satu gigitan.',
        'slots' => ['10:15 AM','11:00 AM','12:00 PM','01:45 PM','03:15 PM','05:00 PM','06:30 PM','08:00 PM'],
        'featured_slot' => '03:15 PM',
        'hours' => '10.00 - 23.00 WITA',
        'map' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.9300000000001!2d123.615!3d-10.179!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDEwJzQ0LjQiUyAxMjPCsDM2JzU0LjAiRQ!5e0!3m2!1sid!2sid!4v1700000000003'
    ],
    'martabak-marsmelow' => [
        'name' => 'Martabak Marsmelow',
        'location' => 'NTT | Kupang Alak • Outdoor Friendly',
        'image' => 'img/mars.jpeg',
        'description' => 'Topping marshmallow panggang dengan saus cokelat lumer membuat martabak ini pas untuk dessert malam hari.',
        'slots' => ['11:30 AM','12:15 PM','01:00 PM','02:00 PM','03:30 PM','05:00 PM','07:00 PM','09:00 PM'],
        'featured_slot' => '07:00 PM',
        'hours' => '11.00 - 24.00 WITA',
        'map' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.9350000000001!2d123.618!3d-10.1765!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDEwJzM3LjQiUyAxMjPCsDM3JzA0LjgiRQ!5e0!3m2!1sid!2sid!4v1700000000004'
    ],
];

$defaultMenuKey = 'martabak-3-rasa';
$requestedKey = strtolower($_GET['menu'] ?? $defaultMenuKey);
$menuKey = array_key_exists($requestedKey, $menus) ? $requestedKey : $defaultMenuKey;
$menu = $menus[$menuKey];
// If caller provided override image or name (from bookings), prefer that for display
if (!empty($_GET['menu_image'])) {
    $menu['image'] = htmlspecialchars($_GET['menu_image'], ENT_QUOTES);
}
if (!empty($_GET['menu_name'])) {
    $menu['name'] = htmlspecialchars($_GET['menu_name'], ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Martabak - Martabak Rajan</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f7f8fc;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        .order-wrapper {
            max-width: 1200px;
            margin: 30px auto 60px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }
        .order-card {
            background: #fff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .order-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #222;
        }
        .order-location {
            color: #777;
            font-size: 0.95rem;
        }
        .order-hero {
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 25px;
        }
        .order-hero img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
        }
        .order-info p {
            margin: 10px 0 0;
            color: #555;
            line-height: 1.6;
        }
        .booking-form {
            margin-top: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        .booking-form label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 6px;
        }
        .booking-form input,
        .booking-form select {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px;
            font-size: 0.95rem;
        }
        .order-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .order-actions button {
            border: none;
            border-radius: 12px;
            padding: 12px 26px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.95rem;
        }
        .order-actions .primary {
            background: var(--rajan-orange, #ff8c00);
            color: #fff;
            box-shadow: 0 10px 25px rgba(255,140,0,0.35);
        }
        .order-actions .ghost {
            background: transparent;
            border: 1px solid rgba(0,0,0,0.1);
            color: #666;
        }
        .time-slots {
            margin-top: 30px;
        }
        .time-slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .time-slot {
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #eee;
            font-weight: 600;
            color: #555;
            background: #fff;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .time-slot.active {
            background: rgba(255,140,0,0.12);
            border-color: rgba(255,140,0,0.4);
            color: #e06100;
        }
        .order-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .order-sidebar .card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        }
        .promo-card {
            background: linear-gradient(135deg, #ffb457, #ff8c00);
            color: #fff;
        }
        .promo-card h3 {
            margin: 0 0 8px;
        }
        .map-card iframe {
            width: 100%;
            border: 0;
            border-radius: 12px;
            height: 220px;
        }
        .web-card .website-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
        }
        .web-card .website-item + .website-item {
            border-top: 1px solid #f0f0f0;
        }
        .web-card img {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 50%;
        }
        @media (max-width: 900px) {
            .order-wrapper {
                grid-template-columns: 1fr;
            }
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
    // expose current menu data from PHP
    var __currentMenuSlug = '<?php echo isset($menuKey) ? addslashes($menuKey) : ''; ?>';
    var __currentMenuImage = '<?php echo isset($menu['image']) ? addslashes($menu['image']) : ''; ?>';

    function gotoslots(){
        // Read selected booking date and people, send as query params to slots.php
        try {
            var dateEl = document.getElementById('booking_date');
            var peopleEl = document.getElementById('booking_people');
            var timeEl = document.getElementById('booking_time');
            var params = [];
            if (dateEl && dateEl.value) params.push('date=' + encodeURIComponent(dateEl.value));
            if (timeEl && timeEl.value) params.push('time=' + encodeURIComponent(timeEl.value));
            if (peopleEl && peopleEl.value) params.push('people=' + encodeURIComponent(peopleEl.value));
            if (__currentMenuSlug) params.push('menu=' + encodeURIComponent(__currentMenuSlug));
            if (__currentMenuImage) params.push('menu_image=' + encodeURIComponent(__currentMenuImage));
            var target = 'slots.php' + (params.length ? ('?' + params.join('&')) : '');
            window.location.href = target;
        } catch (e) {
            window.location.href = "slots.php";
        }
    }
    function handleProfileClick(){
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
            <a href="contact.php" class="nav-link">Contact Us</a>
            <?php if ($isLoggedIn): ?>
                <a href="logout.php" class="nav-link">Sign Out</a>
            <?php else: ?>
                <a href="javascript:void(0)" class="nav-link" onclick="gotologin()">Login</a>
                <a href="javascript:void(0)" class="nav-link cta" onclick="gotoregister()">Register</a>
            <?php endif; ?>
            <span class="vertical-divider"></span>
            <div class="user-profile" onclick="handleProfileClick()">
                <span class="user-name"><?php echo h($currentUserName); ?></span>
                <img src="<?php echo h($userAvatar); ?>?v=<?php echo $avatarTimestamp; ?>" alt="User Avatar" class="avatar" id="user-avatar-navbar">
            </div>
        </div>
    </nav>

    <div class="order-wrapper">
        <section class="order-card">
            <div class="order-header">
                <div>
                    <h1><?php echo h($menu['name']); ?></h1>
                    <p class="order-location"><?php echo h($menu['location']); ?></p>
                </div>
                <div>
                    <span class="order-location"><i class="fas fa-clock"></i> <?php echo h($menu['hours']); ?></span>
                </div>
            </div>
            <div class="order-hero">
                <img src="<?php echo h($menu['image']); ?>" alt="<?php echo h($menu['name']); ?>">
            </div>
            <div class="order-info">
                <p><?php echo h($menu['description']); ?></p>
            </div>
            <form class="booking-form">
                    <div>
                        <label>Tanggal</label>
                        <input type="date" id="booking_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                <div>
                    <label>Waktu</label>
                        <select id="booking_time">
                            <option value="10:00 AM">10:00 AM</option>
                            <option value="12:00 PM">12:00 PM</option>
                            <option value="03:00 PM">03:00 PM</option>
                            <option value="06:00 PM">06:00 PM</option>
                            <option value="09:00 PM">09:00 PM</option>
                        </select>
                </div>
                <div>
                    <label>Jumlah Orang</label>
                        <select id="booking_people">
                            <option value="1">1 Orang</option>
                            <option value="2">2 Orang</option>
                            <option value="3">3 Orang</option>
                            <option value="4">4 Orang</option>
                            <option value="5">5+ Orang</option>
                        </select>
                </div>
                <div>
                    <label>Tipe Tempat</label>
                    <select>
                        <option>Indoor</option>
                        <option>Outdoor</option>
                        <option>Delivery</option>
                    </select>
                </div>
            </form>
            <div class="order-actions">
                <button type="button" class="ghost">Cancel</button>
                <button type="button" class="primary" onclick="gotoslots()">Find Slots</button>
            </div>

            <div class="time-slots">
                <p>Tersedia Slot Waktu</p>
                <div class="time-slot-grid">
                    <?php foreach ($menu['slots'] as $slot): ?>
                        <span class="time-slot <?php echo $slot === $menu['featured_slot'] ? 'active' : ''; ?>"><?php echo h($slot); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <aside class="order-sidebar">
            <div class="card promo-card">
                <h3>Diskon 20% QRIS</h3>
                <p>Bayar menggunakan QRIS untuk mendapatkan diskon tambahan 20% (berlaku transaksi pertama)</p>
            </div>
            <div class="card map-card">
                <h3>Lokasi Dapur</h3>
                <iframe src="<?php echo h($menu['map']); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="card web-card">
                <h3>Official Websites</h3>
                <div class="website-item">
                    <img src="img/mangga.png" alt="Mangga">
                    <div>
                        <p class="website-name">Mangga reseller</p>
                        <a class="website-status" href="https://www.google.com/maps?q=Mangga+Online" target="_blank" rel="noopener">Open</a>
                    </div>
                </div>
                <div class="website-item">
                    <img src="img/Makn.jpeg" alt="Makn Ende">
                    <div>
                        <p class="website-name">Makn Ende</p>
                        <a class="website-status" href="https://makn-ende.sch.id/" target="_blank" rel="noopener">Open</a>
                    </div>
                </div>
            </div>
        </aside>
    </div>

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
</body>
</html>