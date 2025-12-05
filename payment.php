<?php
session_start();
require_once __DIR__ . '/includes/avatar_helper.php';

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$menusFile = __DIR__ . '/data/menus.json';
if (!file_exists($menusFile)) file_put_contents($menusFile, json_encode([], JSON_PRETTY_PRINT));
$menus = json_decode(file_get_contents($menusFile), true) ?: [];

$selectedMenuSlug = $_GET['menu'] ?? '';
$selectedMenu = null;
// try to find menu by slug (simple slug: lowercase, non-alnum -> '-')
foreach ($menus as $m) {
    $slug = isset($m['slug']) ? $m['slug'] : preg_replace('/[^a-z0-9]+/','-',strtolower($m['name'] ?? ''));
    if ($selectedMenuSlug && $slug === $selectedMenuSlug) { $selectedMenu = $m; break; }
}

$message = '';
$success = false;
$orderId = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cust = trim($_POST['customer'] ?? '');
    $addr = trim($_POST['address'] ?? '');
    $menu = trim($_POST['menu_name'] ?? ($_POST['menu'] ?? ''));
    $total = floatval($_POST['total'] ?? 0);
    $method = trim($_POST['payment_method'] ?? 'QRIS');

    // collect method-specific details
    $payment_details = [];
    if ($method === 'Card') {
        $payment_details['card_number'] = trim($_POST['card_number'] ?? '');
        $payment_details['card_exp'] = trim($_POST['card_exp'] ?? '');
        $payment_details['card_cvc'] = trim($_POST['card_cvc'] ?? '');
    } elseif (in_array($method, ['GoPay','OVO','DANA'])) {
        $payment_details['ewallet_number'] = trim($_POST['ewallet_number'] ?? '');
    } elseif ($method === 'Bank Transfer') {
        $payment_details['bank_name'] = trim($_POST['bank_name'] ?? '');
        $payment_details['bank_va'] = trim($_POST['bank_va'] ?? '');
    } elseif ($method === 'COD') {
        $payment_details['cod_note'] = trim($_POST['cod_note'] ?? '');
    }

    if ($cust === '' || $addr === '' || $menu === '' || $total <= 0) {
        $message = 'Lengkapi data pembayaran.';
    } else {
        $bookingsFile = __DIR__ . '/data/bookings.json';
        if (!file_exists($bookingsFile)) file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));
        $bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];
        $orderId = 'ORD' . time() . rand(100,999);
        $new = [
            'order_id' => $orderId,
            'customer' => $cust,
            'address' => $addr,
            'user_email' => isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : '',
            'menu' => $menu,
            'status' => 'Siap Antar',
            'total' => $total,
            'payment_method' => $method,
            'payment_details' => $payment_details,
            'created_at' => date('c')
        ];
        $bookings[] = $new;
        file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT));
        $message = 'Pembayaran berhasil. Pesanan dibuat.';
        $success = true;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Payment - Martabak Raj'an</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body{font-family:Inter,Arial,sans-serif;background:#f6f8f9;color:#213042;margin:0}
        .navbar{display:flex;justify-content:space-between;align-items:center;padding:14px 22px;background:#fff;border-bottom:1px solid #eef3f5}
        .brand{display:flex;align-items:center;gap:12px}
        .brand img{height:44px}
        .container{max-width:980px;margin:28px auto;padding:0 18px}
        .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 8px 26px rgba(16,32,56,0.04);margin-bottom:16px}
        .grid{display:grid;grid-template-columns:1fr 360px;gap:18px}
        .field{display:flex;flex-direction:column;margin-bottom:10px}
        label{font-size:13px;color:#6b7785;margin-bottom:6px}
        input[type=text], input[type=number], textarea, select{padding:10px;border-radius:8px;border:1px solid #e6eef2}
        .btn{background:#20a86b;color:#fff;padding:10px 14px;border-radius:10px;border:none;cursor:pointer}
        .muted{font-size:13px;color:#647681}
        .price{font-weight:700;font-size:20px}
        @media (max-width:900px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <?php
    $isLoggedIn = isset($_SESSION['user']);
    $currentUserName = $isLoggedIn ? ($_SESSION['user']['full_name'] ?? 'User') : 'Guest';
    $userAvatar = $isLoggedIn ? getUserAvatar() : 'img/converted_image.png';
    $avatarTimestamp = isset($_SESSION['user']['selected_avatar_index']) ? $_SESSION['user']['selected_avatar_index'] : time();
    ?>
    <nav class="navbar-rajan">
        <div class="navbar-left">
            <img src="img/Cokelat Krem Ilustrasi Imut Logo Martabak Manis (3).png" alt="Martabak Rajan Logo" class="logo-img">
            <span class="welcome-text">
             <b>  Welcome to </b> <span class="brand-name">Martabak Rajan</span>
            </span>
        </div>

        <div class="navbar-right">
            <a href="index.php" class="nav-link">Home</a>
            <a href="contact.php" class="nav-link">Contact Us</a>
            <?php if (!$isLoggedIn): ?>
                <a href="javascript:void(0)" class="nav-link" onclick="gotologin()">Login</a>
                <a href="javascript:void(0)" class="nav-link cta" onclick="gotoregister()">Register</a>
            <?php else: ?>
                <a href="profile.php" class="nav-link">Profile</a>
            <?php endif; ?>

            <span class="vertical-divider"></span>

            <div class="user-profile" onclick="handleProfileClick()">
                <span class="user-name"><?php echo h($currentUserName); ?></span>
                <img src="<?php echo h($userAvatar); ?>?v=<?php echo $avatarTimestamp; ?>" alt="User Avatar" class="avatar" id="user-avatar-navbar">
            </div>
        </div>
    </nav>

    <script>
    function goToPayment() { window.location.href = "payment.php"; }
    function goToOrder(menuSlug) { var target = "order.php"; if (menuSlug) { target += "?menu=" + encodeURIComponent(menuSlug); } window.location.href = target; }
    function gotomenu() { window.location.href = "menu.php"; }
    function gotoregister() { window.location.href = "registasi.php"; }
    function gotologin() { window.location.href = "login.php"; }
    function handleProfileClick() {
        <?php if ($isLoggedIn): ?>
        window.location.href = "profile.php";
        <?php else: ?>
        alert("Kamu harus login terlebih dahulu.");
        <?php endif; ?>
    }
    </script>

    <div class="container">
        <div class="card">
            <h2 style="margin:0 0 8px 0">Payment</h2>
            <p class="muted">Selesaikan pembayaran untuk melanjutkan pesanan.</p>
        </div>

        <?php if ($message && $success): ?>
            <div class="card">
                <h3>Terima kasih — Pesanan Diterima</h3>
                <p class="muted"><?php echo h($message); ?></p>
                <p>Order ID: <strong><?php echo h($orderId); ?></strong></p>
                <p><a href="profile.php" class="btn">Lihat Pesanan Saya</a></p>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="post">
            <div class="grid">
                <div>
                    <div class="card">
                        <h3 style="margin-top:0">Informasi Pembeli</h3>
                        <div class="field"><label>Nama</label><input type="text" name="customer" value="<?php echo h($_SESSION['user']['full_name'] ?? ''); ?>" required></div>
                        <div class="field"><label>Alamat</label><textarea name="address" required><?php echo h($_SESSION['user']['address'] ?? ''); ?></textarea></div>
                    </div>

                    <div class="card">
                        <h3 style="margin-top:0">Metode Pembayaran</h3>
                        <div class="field"><label>Pilih Metode</label>
                            <select name="payment_method" id="payment_method">
                                <option value="QRIS">QRIS (Scan)</option>
                                <option value="Card">Credit/Debit Card</option>
                                <option value="Bank Transfer">Bank Transfer (VA)</option>
                                <option value="GoPay">GoPay</option>
                                <option value="OVO">OVO</option>
                                <option value="DANA">DANA</option>
                                <option value="COD">Cash on Delivery (COD)</option>
                            </select>
                        </div>

                        <div id="card-fields" style="display:none">
                            <div class="field"><label>Nomor Kartu</label><input type="text" name="card_number" placeholder="1234 5678 9012 3456"></div>
                            <div style="display:flex;gap:8px"><div class="field" style="flex:1"><label>Exp</label><input type="text" name="card_exp" placeholder="MM/YY"></div><div class="field" style="width:120px"><label>CVC</label><input type="text" name="card_cvc" placeholder="123"></div></div>
                        </div>

                        <div id="ewallet-fields" style="display:none">
                            <div class="field"><label>Nomor / ID E-Wallet</label><input type="text" name="ewallet_number" placeholder="0812xxxx (GoPay/OVO/DANA)"></div>
                        </div>

                        <div id="bank-fields" style="display:none">
                            <div class="field"><label>Pilih Bank</label>
                                <select name="bank_name">
                                    <option value="BCA">BCA</option>
                                    <option value="BNI">BNI</option>
                                    <option value="BRI">BRI</option>
                                    <option value="Mandiri">Mandiri</option>
                                </select>
                            </div>
                            <div class="field"><label>Nomor Rekening / VA (opsional)</label><input type="text" name="bank_va" placeholder="VA / No Rekening"></div>
                        </div>

                        <div id="cod-note" style="display:none">
                            <div class="muted">Pilih COD jika pelanggan akan membayar saat barang diterima.</div>
                            <div class="field"><label>Catatan untuk kurir (opsional)</label><input type="text" name="cod_note" placeholder="Contoh: Taruh di meja depan"></div>
                        </div>
                    </div>
                </div>

                <aside>
                    <div class="card">
                        <h3 style="margin-top:0">Ringkasan Pesanan</h3>
                        <?php if ($selectedMenu): ?>
                            <div class="muted">Menu</div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px"><div><?php echo h($selectedMenu['name']); ?></div><div class="price">Rp <?php echo number_format($selectedMenu['price'],0,',','.'); ?></div></div>
                            <input type="hidden" name="menu_name" value="<?php echo h($selectedMenu['name']); ?>">
                            <input type="hidden" name="total" value="<?php echo (float)$selectedMenu['price']; ?>">
                        <?php else: ?>
                            <div class="field"><label>Menu</label><input type="text" name="menu" placeholder="Nama menu" required></div>
                            <div class="field"><label>Total (Rp)</label><input type="number" name="total" step="1" required></div>
                        <?php endif; ?>
                        <div style="margin-top:12px">
                            <button class="btn" type="submit">Bayar Sekarang</button>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
        <?php endif; ?>

    </div>

    <footer style="padding:18px 0;text-align:center;color:#647681">&copy; Martabak Rajan</footer>

    <script>
        const pmSelect = document.getElementById('payment_method');
        const cardFields = document.getElementById('card-fields');
        const ewalletFields = document.getElementById('ewallet-fields');
        const bankFields = document.getElementById('bank-fields');
        const codNote = document.getElementById('cod-note');
        function updatePaymentFields() {
            const v = pmSelect.value;
            cardFields.style.display = (v === 'Card') ? 'block' : 'none';
            ewalletFields.style.display = (v === 'GoPay' || v === 'OVO' || v === 'DANA') ? 'block' : 'none';
            bankFields.style.display = (v === 'Bank Transfer') ? 'block' : 'none';
            codNote.style.display = (v === 'COD') ? 'block' : 'none';
        }
        pmSelect.addEventListener('change', updatePaymentFields);
        // initial
        updatePaymentFields();
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>