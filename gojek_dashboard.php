<?php
session_start();
// Require role 'gojek'
if (!isset($_SESSION['admin']) || strtolower(($_SESSION['admin']['role'] ?? '')) !== 'gojek') {
    header('Location: login-admin.php');
    exit;
}
$user = $_SESSION['admin'];
// Load bookings so gojek can see deliveries
$bookingsFile = __DIR__ . '/data/bookings.json';
if (!file_exists($bookingsFile)) {
    file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));
}
$bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gojek Dashboard - Martabak Raj'an</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        :root{--green:#20a86b;--muted:#f6f8f9;--card:#ffffff;--text:#213042}
        body{font-family:Inter, system-ui, Arial, sans-serif;background:var(--muted);color:var(--text);margin:0}
        .app{display:flex;min-height:100vh}
        .sidebar{width:240px;background:linear-gradient(180deg,var(--green),#17a05f);color:#fff;padding:28px 18px}
        .brand{font-weight:700;font-size:22px;margin-bottom:18px}
        .nav a{display:block;padding:12px;color:rgba(255,255,255,0.95);text-decoration:none;margin-bottom:8px}
        .main{flex:1;padding:28px 36px}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
        .card{background:var(--card);padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(24,39,51,0.04)}
        .deliveries{display:grid;gap:12px;margin-top:12px}
        .delivery{display:flex;justify-content:space-between;gap:12px;padding:12px;border-radius:8px;border:1px solid #eee;align-items:center}
        .btn{background:var(--green);color:white;border:none;padding:8px 12px;border-radius:8px;cursor:pointer}
        .btn.secondary{background:#eef3f5;color:#223}
        .small{font-size:13px;color:#647681}
        /* Modal */
        .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,0.4);display:none;align-items:center;justify-content:center;z-index:1200}
        .modal{background:#fff;border-radius:10px;padding:18px;max-width:520px;width:92%;box-shadow:0 12px 40px rgba(16,32,56,0.2)}
        .modal h4{margin:0 0 8px 0}
        .modal .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f3f5}
        .modal .row:last-child{border-bottom:0}
        .modal .label{color:#6b7785}
        .modal .value{font-weight:700}
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">Martabak Rajan</div>
            <nav class="nav">
                <a href="gojek_dashboard.php">Dashboard</a>
                <a href="#">Deliveries</a>
                <a href="#">Riwayat</a>
            </nav>
            <a class="logout" href="logout-admin.php" style="color:#fff;text-decoration:none;position:absolute;left:20px;bottom:28px">Logout</a>
        </aside>

        <main class="main">
            <div class="topbar">
                <h2>Gojek Dashboard</h2>
                <div style="display:flex;align-items:center;gap:10px"><div style="width:36px;height:36px;border-radius:50%;background:#eef3f5;display:flex;align-items:center;justify-content:center;color:var(--green);font-weight:700"><?php echo strtoupper(substr(htmlspecialchars($user['username']),0,1)); ?></div><div><?php echo htmlspecialchars($user['username']); ?></div></div>
            </div>

            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <h3 style="margin:0">Assigned Deliveries</h3>
                        <p class="small">Daftar pesanan yang perlu dijemput/antar.</p>
                    </div>
                    <div>
                        <button id="refresh-deliveries" class="btn">Segarkan</button>
                    </div>
                </div>
                <div id="deliveries" class="deliveries" style="margin-top:12px">
                    <?php
                    // render initial deliveries: show bookings with status 'siap antar' (case-insensitive)
                    $has = false;
                    foreach ($bookings as $b) {
                        $status = strtolower(trim($b['status'] ?? ''));
                        if ($status === 'siap antar' || $status === 'siapantar' || $status === 'siap_antar') {
                            $has = true;
                            ?>
                            <div class="delivery" data-order-id="<?php echo htmlspecialchars($b['order_id'] ?? ''); ?>">
                                <div>
                                    <strong><?php echo htmlspecialchars($b['order_id'] ?? ''); ?></strong>
                                    <div class="small">Customer: <?php echo htmlspecialchars($b['customer'] ?? '-'); ?> â€” <?php echo htmlspecialchars($b['address'] ?? '-'); ?></div>
                                </div>
                                <div style="text-align:right">
                                    <div class="small">Pickup: Martabak Rajan</div>
                                    <div style="margin-top:8px">
                                        <button class="btn mark-picked">Mark Picked</button>
                                        <button class="btn secondary detail-btn" style="margin-left:8px">Detail</button>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    if (!$has) {
                        echo '<div class="small">Belum ada pesanan untuk diantar.</div>';
                    }
                    ?>
                </div>
            </div>
            <!-- Detail modal -->
            <div id="modal-backdrop" class="modal-backdrop" role="dialog" aria-hidden="true">
                <div class="modal" role="document" aria-labelledby="modal-title">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <h4 id="modal-title">Detail Pesanan</h4>
                        <button id="modal-close" class="btn secondary">Tutup</button>
                    </div>
                    <div id="modal-content">
                        <div class="row"><div class="label">Nama Pelanggan</div><div class="value" id="d-customer">-</div></div>
                        <div class="row"><div class="label">Alamat</div><div class="value" id="d-address">-</div></div>
                        <div class="row"><div class="label">Menu</div><div class="value" id="d-menu">-</div></div>
                        <div class="row"><div class="label">Total</div><div class="value" id="d-total">-</div></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        async function loadDeliveries() {
            try {
                const res = await fetch('data/bookings.json', { cache: 'no-store' });
                if (!res.ok) return;
                const bookings = await res.json();
                const container = document.getElementById('deliveries');
                if (!container) return;
                const deliveries = bookings.filter(b => {
                    const s = (b.status||'').toLowerCase().replace(/\s+/g,'');
                    return s === 'siapantar' || s === 'siap_antar' || s === 'siapantar' || s === 'siapantar';
                });
                if (!deliveries.length) {
                    container.innerHTML = '<div class="small">Belum ada pesanan untuk diantar.</div>';
                    return;
                }
                container.innerHTML = deliveries.map(b => `
                    <div class="delivery" data-order-id="${b.order_id||''}">
                        <div>
                            <strong>${b.order_id||''}</strong>
                            <div class="small">Customer: ${b.customer||'-'} â€” ${b.address||'-'}</div>
                        </div>
                        <div style="text-align:right">
                            <div class="small">Pickup: Martabak Rajan</div>
                            <div style="margin-top:8px">
                                <button class="btn mark-picked">Mark Picked</button>
                                <button class="btn secondary detail-btn" style="margin-left:8px">Detail</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (e) { console.error(e); }
        }

        document.getElementById('refresh-deliveries').addEventListener('click', async function(){
            const btn = this; btn.textContent = 'Memuat...';
            await loadDeliveries();
            btn.textContent = 'Segarkan';
        });

        // initial load + polling every 5s
        window.addEventListener('load', function(){ loadDeliveries(); setInterval(loadDeliveries, 5000); });

        // optional: mark picked/delivered per-order (client side only for now)
        document.addEventListener('click', async function(e){
            if (e.target && e.target.classList.contains('mark-picked')) {
                const parent = e.target.closest('.delivery');
                if (!parent) return;
                const orderId = parent.getAttribute('data-order-id');
                // For now, change UI and optionally call server endpoint later
                parent.style.opacity = '0.6';
                e.target.textContent = 'Picked';
            }
            if (e.target && e.target.classList.contains('detail-btn')) {
                const parent = e.target.closest('.delivery');
                if (!parent) return;
                const orderId = parent.getAttribute('data-order-id');
                try {
                    const res = await fetch('data/bookings.json', { cache: 'no-store' });
                    if (!res.ok) return;
                    const bookings = await res.json();
                    const order = bookings.find(b => (b.order_id||'') === orderId);
                    if (!order) return;
                    document.getElementById('d-customer').textContent = order.customer || '-';
                    document.getElementById('d-address').textContent = order.address || '-';
                    document.getElementById('d-menu').textContent = order.menu || '-';
                    document.getElementById('d-total').textContent = 'Rp ' + (Number(order.total||0)).toLocaleString('id-ID');
                    const backdrop = document.getElementById('modal-backdrop');
                    backdrop.style.display = 'flex';
                    backdrop.setAttribute('aria-hidden','false');
                } catch (err) {
                    console.error(err);
                }
            }
        });

        // modal close
        document.getElementById('modal-close').addEventListener('click', function(){
            const backdrop = document.getElementById('modal-backdrop');
            backdrop.style.display = 'none';
            backdrop.setAttribute('aria-hidden','true');
        });
    </script>
</body>
</html>
