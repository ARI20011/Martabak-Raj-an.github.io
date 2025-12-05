<?php
session_start();
// Require role 'chef'
if (!isset($_SESSION['admin']) || strtolower(($_SESSION['admin']['role'] ?? '')) !== 'chef') {
    header('Location: login-admin.php');
    exit;
}
$user = $_SESSION['admin'];

// Load bookings for initial rendering
$bookingsFile = __DIR__ . '/data/bookings.json';
if (!file_exists($bookingsFile)) {
    file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));
}
$bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];

// Load menus so chef can see available menu items
$menusFile = __DIR__ . '/data/menus.json';
if (!file_exists($menusFile)) {
    file_put_contents($menusFile, json_encode([], JSON_PRETTY_PRINT));
}
$menus = json_decode(file_get_contents($menusFile), true) ?: [];

// Determine orders needing cooking (pending or diproses chef)
$ordersToCook = [];
foreach ($bookings as $b) {
    $status = strtolower($b['status'] ?? 'pending');
    if (in_array($status, ['pending', 'diproses chef'])) {
        $ordersToCook[] = $b;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chef Dashboard - Martabak Raj'an</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        :root{--green:#20a86b;--muted:#f6f8f9;--card:#ffffff;--text:#213042}
        body{font-family:Inter, system-ui, Arial, sans-serif;background:var(--muted);color:var(--text);margin:0}
        .app{display:flex;min-height:100vh}
        .sidebar{width:240px;background:linear-gradient(180deg,var(--green),#17a05f);color:#fff;padding:28px 18px}
        .brand{font-weight:700;font-size:22px;margin-bottom:18px}
        .nav a{display:flex;align-items:center;padding:12px;border-radius:8px;color:rgba(255,255,255,0.95);text-decoration:none;margin-bottom:8px}
        .main{flex:1;padding:28px 36px}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
        .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:18px}
        .card{background:var(--card);padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(24,39,51,0.04)}
        .title{font-size:13px;color:#6b7785}
        .value{font-size:28px;font-weight:700;margin-top:6px}
        .panels{display:grid;grid-template-columns:2fr 1.4fr;gap:18px}
        .table{width:100%;border-collapse:collapse}
        .table th,.table td{padding:10px 12px;text-align:left;border-bottom:1px solid #f0f2f4}
        .btn{background:var(--green);color:#fff;padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
        .small{font-size:13px;color:#647681}
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">Martabak Rajan</div>
            <nav class="nav">
                <a href="chef_dashboard.php" class="active">Dashboard</a>
                <a href="#">Pesanan</a>
                <a href="#">Bahan</a>
                <a href="#">Pengaturan</a>
            </nav>
            <a class="logout" href="logout-admin.php" style="color:#fff;text-decoration:none;position:absolute;left:20px;bottom:28px">Logout</a>
        </aside>

        <main class="main">
            <div class="topbar">
                <h2>Chef Dashboard</h2>
                <div style="display:flex;align-items:center;gap:10px"><div style="width:36px;height:36px;border-radius:50%;background:#eef3f5;display:flex;align-items:center;justify-content:center;color:var(--green);font-weight:700"><?php echo strtoupper(substr(htmlspecialchars($user['username']),0,1)); ?></div><div><?php echo htmlspecialchars($user['username']); ?></div></div>
            </div>

            <section class="grid">
                <div class="card"><div class="title">Total Pesanan</div><div class="value"><?php echo count($bookings); ?></div></div>
                <div class="card"><div class="title">Pesanan Hari Ini</div><div class="value"><?php
                    $today = date('Y-m-d'); $countToday = 0; foreach ($bookings as $b) { if (isset($b['created_at']) && substr($b['created_at'],0,10) === $today) $countToday++; } echo $countToday;
                ?></div></div>
                <div class="card"><div class="title">Dalam Dapur</div><div class="value"><?php echo count($ordersToCook); ?></div></div>
                <div class="card"><div class="title">Selesai</div><div class="value"><?php
                    $done = 0; foreach ($bookings as $b) { if (strtolower($b['status'] ?? '') === 'selesai') $done++; } echo $done;
                ?></div></div>
            </section>

            <section class="panels">
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <h3 style="margin:0">Pesanan Untuk Dimasak</h3>
                        <button id="refresh-orders" class="btn">Segarkan</button>
                    </div>
                    <div id="orders-list">
                        <table class="table">
                            <thead><tr><th>No</th><th>Order</th><th>Menu</th><th>Aksi</th></tr></thead>
                            <tbody>
                            <?php if (empty($ordersToCook)): ?>
                                <tr><td colspan="4" class="small">Tidak ada pesanan untuk dimasak.</td></tr>
                            <?php else: ?>
                                <?php foreach ($ordersToCook as $i => $o): ?>
                                    <tr>
                                        <td><?php echo $i+1; ?></td>
                                        <td><?php echo htmlspecialchars($o['order_id'] ?? ('#'.($i+1))); ?></td>
                                        <td><?php echo htmlspecialchars($o['menu'] ?? ''); ?></td>
                                        <td><button class="btn mark-cooked" data-order-id="<?php echo htmlspecialchars($o['order_id'] ?? ''); ?>">Mark Cooked</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h3>Stok Bahan</h3>
                    <p class="small">Contoh daftar bahan dan stok saat ini.</p>
                    <table class="table">
                        <thead><tr><th>Bahan</th><th>Stok</th></tr></thead>
                        <tbody>
                            <tr><td>Telur</td><td>120</td></tr>
                            <tr><td>Tepung</td><td>10 kg</td></tr>
                            <tr><td>Keju</td><td>1 kg</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section style="margin-top:18px">
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <h3 style="margin:0">Daftar Menu</h3>
                        <button id="refresh-menus" class="btn">Segarkan Menu</button>
                    </div>
                    <div id="menus-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
                        <?php if (empty($menus)): ?>
                            <div class="small">Belum ada menu.</div>
                        <?php else: foreach ($menus as $m): ?>
                            <div style="background:#fff;border-radius:8px;padding:10px;box-shadow:0 4px 12px rgba(24,39,51,0.04);display:flex;gap:8px;align-items:center">
                                <?php if (!empty($m['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($m['image']); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" style="width:64px;height:64px;object-fit:cover;border-radius:6px">
                                <?php else: ?>
                                    <div style="width:64px;height:64px;border-radius:6px;background:#f1f5f7;display:flex;align-items:center;justify-content:center;color:#6b7785;font-weight:700">M</div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:700"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="small"><?php echo htmlspecialchars($m['category'] ?? ''); ?></div>
                                    <div style="margin-top:6px;font-weight:600">Rp <?php echo number_format($m['price'] ?? 0,0,',','.'); ?></div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
        <script>
            // Fetch bookings.json and re-render orders list
            async function loadOrders() {
                try {
                    const res = await fetch('data/bookings.json', {cache: 'no-store'});
                    if (!res.ok) return;
                    const bookings = await res.json();
                    // filter pending/diproses chef
                    const orders = bookings.filter(b => {
                        const s = (b.status || 'pending').toLowerCase();
                        return s === 'pending' || s === 'diproses chef';
                    });
                    const tbody = orders.map((o,i) => `
                        <tr>
                            <td>${i+1}</td>
                            <td>${(o.order_id||('#'+(i+1)))}</td>
                            <td>${(o.menu||'')}</td>
                            <td><button class="btn mark-cooked" data-order-id="${(o.order_id||'')}">Mark Cooked</button></td>
                        </tr>
                    `).join('');
                    const container = document.querySelector('#orders-list tbody');
                    if (container) container.innerHTML = orders.length ? tbody : '<tr><td colspan="4" class="small">Tidak ada pesanan untuk dimasak.</td></tr>';
                } catch (e) {
                    console.error(e);
                }
            }

            document.getElementById('refresh-orders').addEventListener('click', async function(){
                const btn = this;
                btn.textContent = 'Memuat...';
                try {
                    // call server to mark pending orders as finished
                    const res = await fetch('orders_mark_done.php', { method: 'POST', headers: { 'Accept':'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.changed) {
                            console.info('Orders marked done:', data.changed);
                        }
                    } else {
                        console.warn('orders_mark_done.php returned', res.status);
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    await loadOrders();
                    btn.textContent = 'Segarkan';
                }
            });

            // Fetch menus.json and re-render menus list
            async function loadMenus() {
                try {
                    const res = await fetch('data/menus.json', {cache: 'no-store'});
                    if (!res.ok) return;
                    const menus = await res.json();
                    const container = document.getElementById('menus-list');
                    if (!container) return;
                    if (!menus || menus.length === 0) {
                        container.innerHTML = '<div class="small">Belum ada menu.</div>';
                        return;
                    }
                    const html = menus.map(m => {
                        const img = m.image ? `<img src="${m.image}" alt="${m.name}" style="width:64px;height:64px;object-fit:cover;border-radius:6px">` : `<div style="width:64px;height:64px;border-radius:6px;background:#f1f5f7;display:flex;align-items:center;justify-content:center;color:#6b7785;font-weight:700">M</div>`;
                        return `
                            <div style="background:#fff;border-radius:8px;padding:10px;box-shadow:0 4px 12px rgba(24,39,51,0.04);display:flex;gap:8px;align-items:center">
                                ${img}
                                <div>
                                    <div style="font-weight:700">${m.name || ''}</div>
                                    <div class="small">${m.category || ''}</div>
                                    <div style="margin-top:6px;font-weight:600">Rp ${Number(m.price||0).toLocaleString('id-ID')}</div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    container.innerHTML = html;
                } catch (e) {
                    console.error(e);
                }
            }

            document.getElementById('refresh-menus').addEventListener('click', async function(){
                const btn = this;
                btn.textContent = 'Memuat...';
                try {
                    // call server endpoint to add menus into bookings (if not exist)
                    const res = await fetch('menu_to_orders.php', { method: 'POST', headers: { 'Accept':'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        // optionally show how many added
                        if (data && data.added) {
                            console.info('Menus added to orders:', data.added);
                        }
                    } else {
                        console.warn('menu_to_orders.php returned', res.status);
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    // reload lists
                    await loadMenus();
                    await loadOrders();
                    btn.textContent = 'Segarkan Menu';
                }
            });

            // Initial load of menus and orders when page opens
            window.addEventListener('load', function(){ 
                loadMenus(); 
                loadOrders();
                // Poll every 5 seconds for updates (menus + orders)
                setInterval(function(){ loadOrders(); loadMenus(); }, 5000);
            });

            // Optional: handle mark cooked by updating bookings.json via simple POST (not implemented server-side)
            // For now, just reload when clicking mark cooked
            document.addEventListener('click', function(e){
                if (e.target && e.target.classList.contains('mark-cooked')){
                    alert('Tandai selesai di server (fungsi backend belum diimplementasikan).');
                }
            });
        </script>
</body>
</html>
