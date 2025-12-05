<?php
session_start();
// Require role 'admin'
if (!isset($_SESSION['admin']) || strtolower(($_SESSION['admin']['role'] ?? '')) !== 'admin') {
    header('Location: login-admin.php');
    exit;
}
$message = '';
$message_type = '';

$menusFile = __DIR__ . '/data/menus.json';
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}
if (!file_exists($menusFile)) {
    file_put_contents($menusFile, json_encode([], JSON_PRETTY_PRINT));
}
$menus = json_decode(file_get_contents($menusFile), true) ?: [];

// Handle add / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_menu') {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $image = trim($_POST['image'] ?? '');
        if ($name === '' || $price <= 0) {
            $message = 'Nama menu dan harga wajib diisi.';
            $message_type = 'error';
        } else {
            $menus = json_decode(file_get_contents($menusFile), true) ?: [];
            $menus[] = [
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'image' => $image,
                'created_at' => date('c')
            ];
            file_put_contents($menusFile, json_encode($menus, JSON_PRETTY_PRINT));
            $message = 'Menu berhasil ditambahkan.';
            $message_type = 'success';
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete_menu') {
        $idx = intval($_POST['idx'] ?? -1);
        $menus = json_decode(file_get_contents($menusFile), true) ?: [];
        if (isset($menus[$idx])) {
            array_splice($menus, $idx, 1);
            file_put_contents($menusFile, json_encode($menus, JSON_PRETTY_PRINT));
            $message = 'Menu dihapus.';
            $message_type = 'success';
        } else {
            $message = 'Index menu tidak ditemukan.';
            $message_type = 'error';
        }
    }
    // reload menus after change
    $menus = json_decode(file_get_contents($menusFile), true) ?: [];
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manajemen Menu - Martabak Raj'an</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body{font-family:Inter,Arial,sans-serif;background:#f6f8f9;color:#213042;margin:0;padding:20px}
        .container{max-width:1000px;margin:0 auto}
        .card{background:#fff;padding:16px;border-radius:10px;box-shadow:0 6px 18px rgba(24,39,51,0.04);margin-bottom:12px}
        .table{width:100%;border-collapse:collapse}
        .table th,.table td{padding:10px 12px;border-bottom:1px solid #f0f2f4}
        .btn{background:#20a86b;color:#fff;padding:8px 10px;border-radius:8px;border:none;cursor:pointer}
        .small{font-size:13px;color:#647681}
        .flex{display:flex;gap:8px;align-items:center}
    </style>
</head>
<body>
    <div class="container">
        <h2>Manajemen Menu</h2>
        <?php if ($message): ?>
            <div style="margin-bottom:10px;padding:10px;border-radius:8px;background:<?php echo $message_type==='success' ? '#e6ffef':'#fff1f0'; ?>;color:<?php echo $message_type==='success' ? '#0b5f3b':'#8a1f11'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="post">
                <input type="hidden" name="action" value="add_menu">
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <input name="name" placeholder="Nama menu" required style="padding:8px;border-radius:6px;border:1px solid #e6eef2;flex:2">
                    <input name="category" placeholder="Kategori (contoh: Manis)" style="padding:8px;border-radius:6px;border:1px solid #e6eef2;flex:1">
                    <input name="price" placeholder="Harga (angka)" required type="number" style="padding:8px;border-radius:6px;border:1px solid #e6eef2;width:140px">
                    <input name="image" placeholder="URL gambar (opsional)" style="padding:8px;border-radius:6px;border:1px solid #e6eef2;flex:1">
                    <div style="flex-basis:100%"></div>
                    <button class="btn" type="submit">Tambah Menu</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Daftar Menu</h3>
            <table class="table">
                <thead><tr><th>No</th><th>Nama</th><th>Kategori</th><th>Harga</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($menus)): ?>
                    <tr><td colspan="5" class="small">Belum ada menu.</td></tr>
                <?php else: foreach ($menus as $i => $m): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo htmlspecialchars($m['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($m['category'] ?? ''); ?></td>
                        <td>Rp <?php echo number_format($m['price'] ?? 0,0,',','.'); ?></td>
                        <td>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="action" value="delete_menu">
                                <input type="hidden" name="idx" value="<?php echo $i; ?>">
                                <button class="btn" type="submit" style="background:#e74c3c">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <a href="admin_dashboard.php" style="text-decoration:none;display:inline-block;margin-top:8px">&larr; Kembali ke Dashboard</a>
    </div>
</body>
</html>
