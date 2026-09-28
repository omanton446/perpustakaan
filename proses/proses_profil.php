<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['admin'])) { exit(); }
$username = $_SESSION['admin'];

// Update Email
if (isset($_POST['update_info'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $stmt = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "ss", $email, $username);
    mysqli_stmt_execute($stmt);
    header("Location: ../pages/profil.php?pesan=update_sukses");
    exit();
}

// Update Password
if (isset($_POST['update_password'])) {
    $n_pass = $_POST['n_pass'];
    $c_pass = $_POST['c_pass'];

    if ($n_pass === $c_pass) {
        $hashed = password_hash($n_pass, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "ss", $hashed, $username);
        mysqli_stmt_execute($stmt);
        header("Location: ../pages/profil.php?pesan=update_sukses");
    } else {
        header("Location: ../pages/profil.php?pesan=pass_tidak_match");
    }
    exit();
}
?>