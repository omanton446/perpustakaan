<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔧 ATUR TIMEZONE INDONESIA
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Cek login
cekLogin();

$role = $_SESSION['role'];

// ===== 1. TAMBAH BUKU (Hanya Admin) =====
if (isset($_POST['tambah'])) {
    if ($role !== 'admin') {
        header("Location: ../pages/index.php?pesan=akses_ditolak");
        exit();
    }
    
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        die("CSRF token tidak valid! Silakan refresh halaman.");
    }
    
    $judul      = trim($_POST['judul']);
    $pengarang  = trim($_POST['pengarang']);
    $kategori   = trim($_POST['kategori']);
    $deskripsi  = trim($_POST['deskripsi']);
    $gambar_url = trim($_POST['gambar_url'] ?? '');
    $stok       = (int)$_POST['stok'];
    
    // Validasi input
    if (empty($judul) || empty($pengarang)) {
        header("Location: ../pages/tambah_buku.php?pesan=field_kosong");
        exit();
    }
    
    // Proses gambar
    $gambar = '';
    
    // Prioritas: Upload file
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = uploadGambar($_FILES['gambar']);
        if ($gambar === false) {
            header("Location: ../pages/tambah_buku.php?pesan=upload_gagal");
            exit();
        }
    } 
    // Fallback: URL gambar
    elseif (!empty($gambar_url)) {
        if (filter_var($gambar_url, FILTER_VALIDATE_URL)) {
            $gambar = $gambar_url;
        }
    }
    
    // Insert ke database
    $stmt = mysqli_prepare($conn, "INSERT INTO buku (judul, pengarang, kategori, deskripsi, gambar, stok) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssi", $judul, $pengarang, $kategori, $deskripsi, $gambar, $stok);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../pages/index.php?pesan=tambah_sukses");
    } else {
        header("Location: ../pages/tambah_buku.php?pesan=gagal");
    }
    exit();
}

// ===== 2. PINJAM BUKU =====
if (isset($_POST['proses_pinjam'])) {
    $buku_id       = (int)$_POST['buku_id'];
    $nama_peminjam = trim($_POST['nama_peminjam']);
    $tgl_pinjam    = $_POST['tgl_pinjam'];
    $tgl_kembali   = $_POST['tgl_kembali'];
    $jumlah        = (int)$_POST['jumlah'];
    $status        = 'Dipinjam';
    
        // Validasi tanggal
    if (strtotime($tgl_kembali) < strtotime($tgl_pinjam)) {
        header("Location: ../pages/index.php?pesan=tanggal_invalid");
        exit();
    }
    
    // ✅ CEK JAM OPERASIONAL (09:00 - 23:00)
    $jam_sekarang = (int)date('H'); // Ambil jam sekarang (0-23)
    if ($jam_sekarang < 6 || $jam_sekarang >= 23) {
        header("Location: ../pages/index.php?pesan=diluar_jam");
        exit();
    }

// ✅ CEK 0: Batas maksimal peminjaman (max 3 buku)
$cekMax = mysqli_prepare($conn, 
    "SELECT COUNT(*) as total FROM peminjaman 
     WHERE nama_peminjam = ? AND status = 'Dipinjam'"
);
mysqli_stmt_bind_param($cekMax, "s", $nama_peminjam);
mysqli_stmt_execute($cekMax);
$resultMax = mysqli_stmt_get_result($cekMax);
$rowMax = mysqli_fetch_assoc($resultMax);

if ($rowMax['total'] >= 3) {
    header("Location: ../pages/index.php?pesan=batas_pinjam");
    exit();
}

    // ✅ CEK 1: Apakah user sudah meminjam buku ini dan belum dikembalikan?
    $cekPinjam = mysqli_prepare($conn, 
        "SELECT id FROM peminjaman 
         WHERE buku_id = ? AND nama_peminjam = ? AND status = 'Dipinjam'"
    );
    mysqli_stmt_bind_param($cekPinjam, "is", $buku_id, $nama_peminjam);
    mysqli_stmt_execute($cekPinjam);
    $resultCek = mysqli_stmt_get_result($cekPinjam);
    
    if (mysqli_num_rows($resultCek) > 0) {
        // User masih punya pinjaman aktif untuk buku ini
        header("Location: ../pages/index.php?pesan=masih_pinjam");
        exit();
    }
    
    // ✅ CEK 2: Cek stok tersedia
    $cekStok = mysqli_prepare($conn, "SELECT stok, judul FROM buku WHERE id = ?");
    mysqli_stmt_bind_param($cekStok, "i", $buku_id);
    mysqli_stmt_execute($cekStok);
    $resultStok = mysqli_stmt_get_result($cekStok);
    $s = mysqli_fetch_assoc($resultStok);
    
    if (!$s) {
        header("Location: ../pages/index.php?pesan=buku_tidak_ada");
        exit();
    }
    
    if ($s['stok'] >= $jumlah) {
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO peminjaman (buku_id, nama_peminjam, tgl_pinjam, tgl_kembali, jumlah_pinjam, status) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "isssis", $buku_id, $nama_peminjam, $tgl_pinjam, $tgl_kembali, $jumlah, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            // Kurangi stok
            mysqli_query($conn, "UPDATE buku SET stok = stok - $jumlah WHERE id = $buku_id");
            header("Location: ../pages/riwayat_pinjam.php?pesan=sukses_pinjam");
        } else {
            header("Location: ../pages/index.php?pesan=gagal_pinjam");
        }
    } else {
        header("Location: ../pages/index.php?pesan=stok_kurang");
    }
    exit();
}

// ===== 3. PENGEMBALIAN BUKU (Hanya Admin) =====
if (isset($_GET['kembali'])) {
    if ($role !== 'admin') {
        header("Location: ../pages/index.php?pesan=akses_ditolak");
        exit();
    }
    
    $id_pinjam = (int)$_GET['kembali'];
    $id_buku   = (int)$_GET['buku_id'];
    $kondisi   = $_GET['kondisi'] ?? 'baik';
    $denda     = (int)($_GET['denda'] ?? 0); // Denda dari popup (rusak)
    
    mysqli_begin_transaction($conn);
    try {
        // Ambil data peminjaman
        $stmt = mysqli_prepare($conn, "SELECT p.*, b.judul, b.gambar FROM peminjaman p JOIN buku b ON p.buku_id = b.id WHERE p.id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $id_pinjam);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data_p = mysqli_fetch_assoc($result);
        
        if (!$data_p) {
            throw new Exception("Data tidak ditemukan");
        }
        
        $jml = $data_p['jumlah_pinjam'];
        $tgl_kembali_seharusnya = $data_p['tgl_kembali'];
        $hari_ini = date('Y-m-d');

        // Hitung keterlambatan
        $tgl1 = new DateTime($tgl_kembali_seharusnya);
        $tgl2 = new DateTime($hari_ini);
        $selisih = $tgl1->diff($tgl2);
        $hari_terlambat = ($tgl2 > $tgl1) ? $selisih->days : 0;

        // Hitung TOTAL DENDA
        $denda_rusak = ($kondisi === 'rusak') ? 5000 * $jml : 0;
        $denda_telat = $hari_terlambat * 1000 * $jml;
        $total_denda = $denda_rusak + $denda_telat;

        // Buat status
        $status_kembali = 'Kembali';
        if ($kondisi === 'rusak') $status_kembali = 'Kembali (Rusak)';
        if ($hari_terlambat > 0) $status_kembali .= ' + Telat ' . $hari_terlambat . ' hari';
        
        // Update status peminjaman
        $stmtUpdate = mysqli_prepare($conn, "UPDATE peminjaman SET status = ?, kondisi = ?, denda = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmtUpdate, "ssii", $status_kembali, $kondisi, $total_denda, $id_pinjam);
        mysqli_stmt_execute($stmtUpdate);
        
        // Update stok buku
        mysqli_query($conn, "UPDATE buku SET stok = stok + $jml WHERE id = $id_buku");
        
        mysqli_commit($conn);
        
        // Simpan data untuk SweetAlert
        $_SESSION['alert_kembali'] = [
            'judul'    => $data_p['judul'],
            'gambar'   => $data_p['gambar'],
            'peminjam' => $data_p['nama_peminjam'],
            'jumlah'   => $jml,
            'kondisi'  => $kondisi,
            'denda'    => $total_denda 
        ];
        header("Location: ../pages/riwayat_pinjam.php?pesan=kembali_sukses");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: ../pages/index.php?pesan=error");
    }
    exit();
}

// ===== 4. HAPUS & UPDATE (Hanya Admin) =====
if (isset($_GET['hapus']) || isset($_POST['update'])) {
    if ($role !== 'admin') {
        header("Location: ../pages/index.php?pesan=akses_ditolak");
        exit();
    }
    
    // --- HAPUS BUKU ---
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        $stmt = mysqli_prepare($conn, "DELETE FROM buku WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        header("Location: ../pages/index.php?pesan=hapus_sukses");
        exit();
    }
    
    // --- UPDATE BUKU ---
    if (isset($_POST['update'])) {
        // CSRF Validation
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            die("CSRF token tidak valid!");
        }
        
        $id        = (int)$_POST['id'];
        $judul     = trim($_POST['judul']);
        $pengarang = trim($_POST['pengarang']);
        $kategori  = trim($_POST['kategori']);
        $deskripsi = trim($_POST['deskripsi']);
        $gambar_url = trim($_POST['gambar_url'] ?? '');
        $stok      = (int)$_POST['stok'];
        
        // Cek data lama
        $stmtOld = mysqli_prepare($conn, "SELECT gambar FROM buku WHERE id = ?");
        mysqli_stmt_bind_param($stmtOld, "i", $id);
        mysqli_stmt_execute($stmtOld);
        $resultOld = mysqli_stmt_get_result($stmtOld);
        $oldData = mysqli_fetch_assoc($resultOld);
        $gambar = $oldData['gambar'] ?? '';
        
        // Upload gambar baru jika ada
        if (!empty($_FILES['gambar']['name'])) {
            $uploadResult = uploadGambar($_FILES['gambar']);
            if ($uploadResult !== false) {
                $gambar = $uploadResult;
            }
        } elseif (!empty($gambar_url)) {
            $gambar = $gambar_url;
        }
        
        // Update database
        $stmt = mysqli_prepare($conn, "UPDATE buku SET judul=?, pengarang=?, kategori=?, deskripsi=?, gambar=?, stok=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssssii", $judul, $pengarang, $kategori, $deskripsi, $gambar, $stok, $id);
        mysqli_stmt_execute($stmt);
        
        header("Location: ../pages/index.php?pesan=update_sukses");
        exit();
    }
}

// Jika tidak ada yang diproses
header("Location: ../pages/index.php");
exit();
?>