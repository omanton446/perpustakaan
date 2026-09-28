<?php
session_start(); // Memulai session agar bisa dihapus
session_unset(); // Menghapus semua isi variabel session
session_destroy(); // Menghancurkan session secara total

// Arahkan kembali ke halaman login
header("Location: ../pages/login.php");
exit();
?>