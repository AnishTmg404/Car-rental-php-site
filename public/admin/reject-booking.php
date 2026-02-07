<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Only admin can reject
require_admin();

$booking_id = intval($_GET['id'] ?? 0);
if (!$booking_id) {
    set_flash_message('error', 'Invalid booking ID.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

// Fetch booking
$booking = db_fetch("SELECT * FROM bookings WHERE id = ?", [$booking_id]);
if (!$booking) {
    set_flash_message('error', 'Booking not found.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

if ($booking['status'] !== 'pending') {
    set_flash_message('error', 'Only pending bookings can be rejected.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

// Update booking status
db_update('bookings', ['status' => 'rejected'], ['id' => $booking_id]);

set_flash_message('success', "Booking #$booking_id has been rejected.");
header('Location: ' . base_url('admin/dashboard.php'));
exit;
