<?php 
include '../config/koneksi.php'; 
session_start();

//Logika Ganti Bahasa
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] === 'admin') {
    echo "<script>alert('Admin tidak boleh meminjam!'); window.location.href='index.php';</script>";
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0; 

$stmt = mysqli_prepare($conn, "SELECT * FROM buku WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$buku = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if(!$buku) {
    die("Buku tidak ditemukan.");
}

// ✅ TAMBAHKAN INI: Cek stok buku dengan SweetAlert
if ($buku['stok'] <= 0) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Stok Habis!',
            text: 'Maaf, stok buku \"" . addslashes($buku['judul']) . "\" sudah habis. Silakan pilih buku lain.',
            confirmButtonColor: '#d33'
        }).then(function() {
            window.location.href = 'index.php';
        });
    </script>
    </body>
    </html>";
    exit();
}

// Set variabel untuk navbar
$username_login = $_SESSION['admin'];
$role = $_SESSION['role'];
$lang = $_SESSION['lang'] ?? 'id';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pinjam Buku | MyLibrary</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    
<!-- LOADING SCREEN -->
<div id="loadingScreen">
    <div class="loader">
        <div class="icon">📚</div>
        <div class="spinner"></div>
        <p>MyLibrary</p>
    </div>
</div>
<!-- ✅ NAVBAR -->
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 450px; border-radius: 15px;">
        <div class="card-body p-4">
            <h4 class="fw-bold text-center mb-4">Konfirmasi Pinjam</h4>
            <form action="../proses/proses.php" method="POST">
                <input type="hidden" name="buku_id" value="<?= $buku['id'] ?>">
                <div class="text-center mb-3">
                    <?php 
$gambar_buku = $buku['gambar'] ?? '';
if (!empty($gambar_buku) && !filter_var($gambar_buku, FILTER_VALIDATE_URL)) {
    $gambar_buku = '../' . $gambar_buku;
}
?>
<img src="<?= htmlspecialchars($gambar_buku) ?>" style="height: 150px; border-radius: 8px;" class="mb-2" onerror="this.src='data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='75' height='110'%3E%3Crect fill='%23e9ecef' width='75' height='110'/%3E%3Ctext x='50%25' y='50%25' fill='%236c757d' font-size='10' font-family='Arial' text-anchor='middle' dy='.3em'%3ENo Cover%3C/text%3E%3C/svg%3E'">
                    <h5><?= $buku['judul'] ?></h5>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Nama Peminjam</label>
                    <input type="text" name="nama_peminjam" class="form-control bg-light" value="<?= $_SESSION['admin'] ?>" readonly>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="small fw-bold">Tgl Pinjam</label>
                        <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="small fw-bold">Tgl Kembali</label>
                        <input type="date" name="tgl_kembali" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="small fw-bold">Jumlah (Tersedia: <?= $buku['stok'] ?>)</label>
                    <input type="number" name="jumlah" class="form-control" value="1" min="1" max="<?= $buku['stok'] ?>" required>
                </div>
                <button type="submit" name="proses_pinjam" class="btn btn-primary w-100 fw-bold">Pinjam Sekarang</button>
                <a href="index.php" class="btn btn-link w-100 text-muted mt-2">Batal</a>
            </form>
        </div>
    </div>
</div>

<!-- ✅ FOOTER SCRIPTS -->
<?php include __DIR__ . '/../includes/footer_scripts.php'; ?>
</body>
</html>