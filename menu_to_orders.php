<?php
session_start();
header('Content-Type: application/json');
// allow only admin or chef to run this
$role = strtolower($_SESSION['admin']['role'] ?? '');
if (!in_array($role, ['admin','chef'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$menusFile = __DIR__ . '/data/menus.json';
$bookingsFile = __DIR__ . '/data/bookings.json';
if (!file_exists($menusFile)) file_put_contents($menusFile, json_encode([], JSON_PRETTY_PRINT));
if (!file_exists($bookingsFile)) file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));

$menus = json_decode(file_get_contents($menusFile), true) ?: [];
$bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];

$added = 0;
foreach ($menus as $m) {
    $menuName = trim($m['name'] ?? '');
    if ($menuName === '') continue;
    // skip if there's already a non-finished booking with this menu
    $exists = false;
    foreach ($bookings as $b) {
        if (isset($b['menu']) && strtolower($b['menu']) === strtolower($menuName) && strtolower($b['status'] ?? '') !== 'selesai') {
            $exists = true; break;
        }
    }
    if ($exists) continue;
    $bookings[] = [
        'order_id' => 'ORD' . time() . rand(100,999),
        'customer' => 'AutoChef',
        'address' => 'Tidak ada',
        'menu' => $menuName,
        'status' => 'Pending',
        'total' => $m['price'] ?? 0,
        'created_at' => date('c')
    ];
    $added++;
}

file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT));
echo json_encode(['success' => true, 'added' => $added]);
