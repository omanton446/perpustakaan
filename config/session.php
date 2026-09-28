<?php
date_default_timezone_set('Asia/Jakarta');

session_start();
require_once __DIR__ . '/koneksi.php';

// Cek login
function cekLogin() {
    if (!isset($_SESSION['admin'])) {
        header("Location: " . BASE_URL . "pages/login.php");
        exit();
    }
}

// Cek role
function cekRole($role) {
    if ($_SESSION['role'] !== $role) {
        header("Location: " . BASE_URL . "pages/404.php");
        exit();
    }
}

// CSRF Token
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Base URL (sesuaikan dengan folder Anda)
define('BASE_URL', '/perpustakan/');
?>