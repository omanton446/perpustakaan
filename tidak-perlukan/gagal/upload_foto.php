<?php
session_start();
include '../config/koneksi.php';
include '../includes/functions.php'; // Pakai fungsi uploadGambar yang sama!

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['admin'];

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    // Gunakan fungsi uploadGambar yang SAMA dengan upload buku
    $gambar = uploadGambar($_FILES['foto']);
    
    if ($gambar !== false) {
        // Path yang disimpan: assets/img/profile_xxx.jpg (sama seperti buku)
        mysqli_query($conn, "UPDATE users SET foto = '$gambar' WHERE username = '$username'");
        header("Location: profil.php?pesan=foto_sukses");
        exit();
    }
}

header("Location: profil.php?pesan=foto_gagal");
exit();