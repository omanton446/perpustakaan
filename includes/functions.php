<?php
function uploadGambar($file) {
    $targetDir = __DIR__ . "/../assets/img/";
    $fileName = time() . '_' . basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validasi
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) return false;
if ($file["size"] > 2000000) return false; // 2MB max
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) return false;

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
       return 'assets/img/' . $fileName;
    }
    return false;
}

function formatTanggal($date) {
    return date('d M Y', strtotime($date));
}

function hitungDenda($jumlah, $hargaPerBuku = 5000) {
    return $jumlah * $hargaPerBuku;
}
?>