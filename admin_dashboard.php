<?php
session_start();
// Require role 'admin'
if (!isset($_SESSION['admin']) || strtolower(($_SESSION['admin']['role'] ?? '')) !== 'admin') {
    header('Location: login-admin.php');
    exit;
}
$admin = $_SESSION['admin'];
// Messages for UI
$admin_message = '';
$admin_message_type = ''; // 'success' or 'error'

// Order messages
$order_message = '';
$order_message_type = '';

// Path to admins.json
$adminsFile = __DIR__ . '/data/admins.json';

// Ensure data dir exists
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

// Initialize admins.json with defaults if missing
if (!file_exists($adminsFile)) {
    $defaultAdmins = [
        [ 'username' => 'alip', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'role' => 'admin', 'created_at' => date('c') ],
        [ 'username' => 'Yusuf', 'password' => password_hash('chef123', PASSWORD_DEFAULT), 'role' => 'chef', 'created_at' => date('c') ],
        [ 'username' => 'Acho', 'password' => password_hash('gojek123', PASSWORD_DEFAULT), 'role' => 'gojek', 'created_at' => date('c') ],
        [ 'username' => 'Kholid', 'password' => password_hash('gojek234', PASSWORD_DEFAULT), 'role' => 'gojek', 'created_at' => date('c') ]
    ];
    file_put_contents($adminsFile, json_encode($defaultAdmins, JSON_PRETTY_PRINT));
}

// Load existing admins
$all_admins = json_decode(file_get_contents($adminsFile), true) ?: [];

// Ensure menus and bookings files exist and load data for stats
$menusFile = __DIR__ . '/data/menus.json';
$bookingsFile = __DIR__ . '/data/bookings.json';
if (!file_exists($menusFile)) {
    // try to seed from known items or create empty
    $seedMenus = [
        [ 'name' => 'Martabak 3 rasa', 'category' => 'Manis', 'price' => 35000, 'created_at' => date('c') ],
        [ 'name' => 'Martabak Mesir', 'category' => 'Manis', 'price' => 30000, 'created_at' => date('c') ],
        [ 'name' => 'Martabak Jasuke', 'category' => 'Manis', 'price' => 25000, 'created_at' => date('c') ]
    ];
    file_put_contents($menusFile, json_encode($seedMenus, JSON_PRETTY_PRINT));
}
if (!file_exists($bookingsFile)) {
    file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));
}
$menus = json_decode(file_get_contents($menusFile), true) ?: [];
$bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];

// Compute stats
$totalMenus = count($menus);
$today = date('Y-m-d');
$ordersToday = 0;
$ordersInProcess = 0;
$ordersDone = 0;
foreach ($bookings as $b) {
    $created = isset($b['created_at']) ? substr($b['created_at'],0,10) : '';
    $status = strtolower($b['status'] ?? 'pending');
    if ($created === $today) $ordersToday++;
    if ($status === 'selesai') {
        $ordersDone++;
    } else {
        // consider pending, diproses chef, siap antar as in-process
        $ordersInProcess++;
    }
}

// Handle POST actions: add_admin, delete_admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    // ensure bookings file
    $bookingsFile = __DIR__ . '/data/bookings.json';
    if (!file_exists($bookingsFile)) {
        file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));
    }
    if ($action === 'add_admin') {
        $new_username = trim($_POST['new_username'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $new_role = strtolower(trim($_POST['new_role'] ?? 'admin'));
        $allowed = ['admin','chef','gojek'];
        if ($new_username === '' || $new_password === '') {
            $admin_message = 'Username dan password wajib diisi.';
            $admin_message_type = 'error';
        } elseif (!in_array($new_role, $allowed)) {
            $admin_message = 'Role tidak valid.';
            $admin_message_type = 'error';
        } else {
            // reload to get current contents
            $all_admins = json_decode(file_get_contents($adminsFile), true) ?: [];
            // check duplicate (case-insensitive)
            $exists = false;
            foreach ($all_admins as $a) {
                if (isset($a['username']) && strtolower($a['username']) === strtolower($new_username)) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $admin_message = 'Username sudah ada.';
                $admin_message_type = 'error';
            } else {
                $record = [
                    'username' => $new_username,
                    'password' => password_hash($new_password, PASSWORD_DEFAULT),
                    'role' => $new_role,
                    'created_at' => date('c')
                ];
                $all_admins[] = $record;
                file_put_contents($adminsFile, json_encode($all_admins, JSON_PRETTY_PRINT));
                $admin_message = 'Akun berhasil dibuat.';
                $admin_message_type = 'success';
            }
        }
    } elseif ($action === 'delete_admin') {
        $del_username = trim($_POST['del_username'] ?? '');
        if ($del_username === '') {
            $admin_message = 'Nama pengguna tidak valid.';
            $admin_message_type = 'error';
        } else {
            // reload and filter
            $all_admins = json_decode(file_get_contents($adminsFile), true) ?: [];
            $filtered = [];
            foreach ($all_admins as $a) {
                if (isset($a['username']) && strtolower($a['username']) === strtolower($del_username)) {
                    // skip (delete)
                    continue;
                }
                $filtered[] = $a;
            }
            file_put_contents($adminsFile, json_encode($filtered, JSON_PRETTY_PRINT));
            $all_admins = $filtered;
            $admin_message = 'Akun dihapus.';
            $admin_message_type = 'success';
        }
    }
    // Add order action
    if ($action === 'add_order') {
        $cust = trim($_POST['order_customer'] ?? '');
        $addr = trim($_POST['order_address'] ?? '');
        $menu = trim($_POST['order_menu'] ?? '');
        $status = trim($_POST['order_status'] ?? 'Pending');
        $total = trim($_POST['order_total'] ?? '0');
        // If admin checked send_to_gojek, force status to 'Siap Antar'
        if (!empty($_POST['send_to_gojek'])) {
            $status = 'Siap Antar';
        }

        if ($cust === '' || $addr === '' || $menu === '' || $total === '') {
            $order_message = 'Semua field (Customer, Alamat, Menu, Total) harus diisi.';
            $order_message_type = 'error';
        } else {
            $bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];
            $newOrder = [
                'order_id' => 'ORD' . time(),
                'customer' => $cust,
                'address' => $addr,
                'menu' => $menu,
                'status' => $status,
                'total' => $total,
                'created_at' => date('c')
            ];
            $bookings[] = $newOrder;
            file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT));
            $order_message = 'Pesanan baru berhasil ditambahkan.';
            $order_message_type = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Martabak Raj'an</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        :root{--green:#20a86b;--muted:#f6f8f9;--card:#ffffff;--text:#213042;--muted-text:#647681}
        body{font-family:Inter, system-ui, Arial, sans-serif;background:var(--muted);color:var(--text);margin:0}
        .app{display:flex;min-height:100vh}
        /* Sidebar */
        .sidebar{width:240px;background:linear-gradient(180deg,var(--green),#17a05f);color:#fff;padding:28px 18px;box-shadow:2px 0 8px rgba(0,0,0,0.04)}
        .brand{font-weight:700;font-size:22px;margin-bottom:18px}
        .nav{margin-top:18px}
        .nav a{display:flex;align-items:center;padding:12px;border-radius:8px;color:rgba(255,255,255,0.95);text-decoration:none;margin-bottom:8px}
        .nav a.active{background:rgba(255,255,255,0.08)}
        .nav a i{width:24px;text-align:center;margin-right:10px}
        .sidebar .logout{position:absolute;left:20px;bottom:28px;color:#fff;text-decoration:none}

        /* Main */
        .main{flex:1;padding:28px 28px;max-width:1200px;margin:0 auto}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
        .topbar h2{margin:0;font-size:22px}
        .user-pill{display:flex;align-items:center;gap:10px;color:#36454f}
        .user-pill .avatar{width:40px;height:40px;border-radius:50%;background:#eef3f5;display:inline-flex;align-items:center;justify-content:center;color:var(--green);font-weight:700}

        .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:18px}
        .card{background:var(--card);padding:18px;border-radius:12px;box-shadow:0 4px 12px rgba(24,39,51,0.06)}
        .card .title{font-size:13px;color:var(--muted-text);font-weight:600}
        .card .value{font-size:26px;font-weight:700;margin-top:6px}

        .panels{display:grid;grid-template-columns:2fr 1.4fr;gap:18px}
        .table{width:100%;border-collapse:collapse;background:transparent}
        .table th,.table td{padding:10px 12px;text-align:left;border-bottom:1px solid #f4f6f8}
        .table th{font-weight:700;color:#506075;background:transparent}
        .small{font-size:13px;color:var(--muted-text)}
        .btn{background:var(--green);color:#fff;padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
        .btn.secondary{background:#eef3f5;color:#223;border:none}
        .actions .edit{background:#25a39a;color:#fff;padding:6px 8px;border-radius:6px;border:none;text-decoration:none}
        .actions .del{background:#e74c3c;color:#fff;padding:6px 8px;border-radius:6px;border:none}

        .cards-row{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:18px}
        tr:hover td{background:#fbfdfe}
        .table thead th{border-bottom:2px solid #eef3f5}
        .card h3{margin:0 0 10px 0}
        .card .controls{display:flex;gap:8px;align-items:center}

        @media (max-width:980px){.grid{grid-template-columns:repeat(2,1fr)}.panels{grid-template-columns:1fr}.sidebar{display:none}}
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">Martabak Rajan</div>
            <nav class="nav">
                <a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="#"><i class="fas fa-utensils"></i> Menu</a>
                <a href="#"><i class="fas fa-receipt"></i> Pesanan</a>
                <a href="#"><i class="fas fa-users"></i> Pelanggan</a>
                <a href="#"><i class="fas fa-user-shield"></i> Admin</a>
            </nav>
            <a class="logout" href="logout-admin.php"><i class="fas fa-power-off"></i> Logout</a>
        </aside>

        <main class="main">
            <div class="topbar">
                <h2>Dashboard</h2>
                <div class="user-pill"><div class="avatar"><?php echo strtoupper(substr(htmlspecialchars($admin['username']),0,1)); ?></div><div><?php echo htmlspecialchars($admin['username']); ?></div></div>
            </div>

            <section class="grid">
                <div class="card">
                    <div class="title">Total Menu</div>
                    <div class="value"><?php echo (int)$totalMenus; ?></div>
                </div>
                <div class="card">
                    <div class="title">Pesanan Hari Ini</div>
                    <div class="value"><?php echo (int)$ordersToday; ?></div>
                </div>
                <div class="card">
                    <div class="title">Pesanan Dalam Proses</div>
                    <div class="value"><?php echo (int)$ordersInProcess; ?></div>
                </div>
                <div class="card">
                    <div class="title">Pesanan Selesai</div>
                    <div class="value"><?php echo (int)$ordersDone; ?></div>
                </div>
            </section>

            <section class="panels">
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <h3 style="margin:0">Manajemen Menu</h3>
                        <a href="admin_menu.php" class="btn" style="text-decoration:none;display:inline-block">Tambah Menu</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Martabak coklat</td><td>Manis</td><td>Rp 20.000</td><td class="actions"><button class="edit">Edit</button></td></tr>
                            <tr><td>Martabak Telur</td><td>Telur</td><td>Rp 25.000</td><td class="actions"><button class="edit">Edit</button></td></tr>
                            <tr><td>Martabak keju</td><td>Manis</td><td>Rp 35.000</td><td class="actions"><button class="edit">Edit</button></td></tr>
                            <tr><td>Martabak jasuke</td><td>manis</td><td>Rp 5.000</td><td class="actions"><button class="edit">Edit</button></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <h3 style="margin:0">Manajemen Pesanan</h3>
                        <button id="toggle-add-order" class="btn">Tambah</button>
                    </div>
                    <?php if ($order_message): ?>
                        <div style="margin-bottom:10px;padding:10px;border-radius:8px;background:<?php echo $order_message_type==='success'? '#e6ffef':'#fff1f0'; ?>;color:<?php echo $order_message_type==='success'? '#0b5f3b':'#8a1f11'; ?>">
                            <?php echo htmlspecialchars($order_message); ?>
                        </div>
                    <?php endif; ?>

                    <div id="add-order-form" style="display:none;margin-bottom:12px;padding:12px;border:1px dashed #e6eef2;border-radius:8px;background:#fff">
                        <form method="post">
                            <input type="hidden" name="action" value="add_order">
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <input name="order_customer" placeholder="Nama Pelanggan" required style="padding:8px;border-radius:6px;border:1px solid #e6eef2;flex:1">
                                <input name="order_address" placeholder="Alamat" required style="padding:8px;border-radius:6px;border:1px solid #e6eef2;flex:1">
                                <input name="order_menu" placeholder="Menu (contoh: Martabak Telur x2)" required style="padding:8px;border-radius:6px;border:1px solid #e6eef2;flex:1">
                                <input name="order_total" placeholder="Total (contoh: 45000)" required style="padding:8px;border-radius:6px;border:1px solid #e6eef2;width:160px">
                                <select name="order_status" style="padding:8px;border-radius:6px;border:1px solid #e6eef2;width:180px">
                                    <option>Pending</option>
                                    <option>Diproses Chef</option>
                                    <option>Siap Antar</option>
                                    <option>Selesai</option>
                                </select>
                                <label style="display:flex;align-items:center;gap:8px;margin-left:8px"><input type="checkbox" name="send_to_gojek" value="1" checked> Kirim ke Gojek (Siap Antar)</label>
                                <div style="flex-basis:100%"></div>
                                <button class="btn" type="submit">Simpan</button>
                                <button type="button" id="cancel-add-order" class="btn secondary" style="margin-left:8px">Batal</button>
                            </div>
                        </form>
                    </div>
                    <table class="table">
                        <thead>
                            <tr><th>No</th><th>Pelanggan</th><th>Status</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr><td colspan="4" class="small">Belum ada pesanan.</td></tr>
                        <?php else: foreach ($bookings as $i => $b): ?>
                            <tr>
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo htmlspecialchars($b['customer'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($b['status'] ?? ''); ?></td>
                                <td>Rp <?php echo number_format((float)($b['total'] ?? 0),0,',','.'); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="cards-row">
                <div class="card">
                    <h3>Manajemen Pelanggan</h3>
                    <table class="table">
                        <thead><tr><th>Nama</th><th>Email</th><th>Status</th></tr></thead>
                        <tbody>
                            <tr><td>Alya</td><td>alya@example.com</td><td>Aktif</td></tr>
                            <tr><td>Budi</td><td>budi@example.com</td><td>Aktif</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="card">
                        <h3>Manajemen Admin</h3>
                        <?php if ($admin_message): ?>
                            <div style="margin-bottom:10px;padding:10px;border-radius:8px;background:<?php echo $admin_message_type==='success'? '#e6ffef':'#fff1f0'; ?>;color:<?php echo $admin_message_type==='success'? '#0b5f3b':'#8a1f11'; ?>">
                                <?php echo htmlspecialchars($admin_message); ?>
                            </div>
                        <?php endif; ?>

                        <div style="margin-bottom:12px;display:flex;gap:10px;align-items:center">
                            <form method="post" style="display:flex;gap:8px;align-items:center">
                                <input type="hidden" name="action" value="add_admin">
                                <input type="text" name="new_username" placeholder="username" required style="padding:8px;border-radius:6px;border:1px solid #e6eef2">
                                <input type="password" name="new_password" placeholder="password" required style="padding:8px;border-radius:6px;border:1px solid #e6eef2">
                                <select name="new_role" style="padding:8px;border-radius:6px;border:1px solid #e6eef2">
                                    <option value="admin">Admin</option>
                                    <option value="chef">Chef</option>
                                    <option value="gojek">Gojek</option>
                                </select>
                                <button class="btn" type="submit">Tambah</button>
                            </form>
                        </div>

                        <table class="table">
                            <thead><tr><th>Nama</th><th>Role</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($all_admins as $a): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['username'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($a['role'] ?? 'admin'); ?></td>
                                    <td>
                                        <?php if (strtolower($a['username'] ?? '') !== strtolower($admin['username'])): ?>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="action" value="delete_admin">
                                            <input type="hidden" name="del_username" value="<?php echo htmlspecialchars($a['username'] ?? ''); ?>">
                                            <button class="del" type="submit">Hapus</button>
                                        </form>
                                        <?php else: ?>
                                            &nbsp;
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                </div>
            </section>
        </main>
    </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
            <script>
                // Toggle add-order form
                (function(){
                    var btn = document.getElementById('toggle-add-order');
                    var form = document.getElementById('add-order-form');
                    var cancel = document.getElementById('cancel-add-order');
                    if (btn && form) {
                        btn.addEventListener('click', function(){
                            form.style.display = form.style.display === 'none' ? 'block' : 'none';
                            if (form.style.display === 'block') { btn.textContent = 'Tutup'; } else { btn.textContent = 'Tambah'; }
                        });
                    }
                    if (cancel && form) {
                        cancel.addEventListener('click', function(){ form.style.display = 'none'; if(btn) btn.textContent = 'Tambah'; });
                    }
                })();
            </script>
</body>
</html>
