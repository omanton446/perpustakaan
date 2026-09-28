<?php
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "perpus";

// Mengaktifkan reporting mode strict untuk mysqli agar error lebih mudah dilacak (opsional tapi disarankan)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $pass, $db);
} catch (Exception $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>