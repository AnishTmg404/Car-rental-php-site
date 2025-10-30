<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';   // Adjust path relative to logout.php


// Logout user
logout_user();
set_flash_message('success', 'You have been logged out successfully.');

// Redirect to login page
// header('Location: ' . base_url('auth/login.php'));
// exit;

url_redirect('auth/login.php');
?>
