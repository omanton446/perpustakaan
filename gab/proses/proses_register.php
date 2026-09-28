<?php
include '../config/koneksi.php';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password']; 
    $peran = 'peminjam'; 

    // Validasi Format Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../pages/register.php?pesan=email_invalid");
        exit();
    }

    // Cek email sudah ada - CARA BENAR
    $stmt = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt); // Ini yang benar
    
    if (mysqli_num_rows($result) > 0) {
        header("Location: ../pages/register.php?pesan=email_ada");
        exit();
    }

    // Hash password
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert dengan prepared statement
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, peran) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password_hashed, $peran);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../pages/login.php?pesan=reg_sukses");
        exit();
    } else {
        die("Error Database: " . mysqli_error($conn)); 
    }
}
?>