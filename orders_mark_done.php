<?php
session_start();
header('Content-Type: application/json');
// Only chef or admin can mark orders done
$role = strtolower($_SESSION['admin']['role'] ?? '');
if (!in_array($role, ['chef','admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$bookingsFile = __DIR__ . '/data/bookings.json';
if (!file_exists($bookingsFile)) file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));
$bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];

$changed = 0;
foreach ($bookings as &$b) {
    $status = strtolower($b['status'] ?? 'pending');
    if ($status === 'pending' || $status === 'diproses chef') {
        $b['status'] = 'Selesai';
        $changed++;
    }
}
// write back
file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'changed' => $changed]);
