<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/bahasa.php';
cekLogin();
cekRole('admin');

// Data statistik
$totalBuku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
$totalPeminjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE peran='peminjam'"))['total'];
$sedangDipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='Dipinjam'"))['total'];
$totalDenda = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(denda),0) as total FROM peminjaman"))['total'];
$bukuTerlaris = mysqli_query($conn, "SELECT b.judul, COUNT(*) as total FROM peminjaman p JOIN buku b ON p.buku_id=b.id GROUP BY p.buku_id ORDER BY total DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - MyLibrary</title>
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
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <h3 class="fw-bold mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h3>
    
    <!-- Statistik Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card bg-primary bg-opacity-10 p-3 text-center">
                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                <h4 class="fw-bold"><?= $totalBuku ?></h4>
                <small>Total Buku</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-success bg-opacity-10 p-3 text-center">
                <i class="fas fa-users fa-2x text-success mb-2"></i>
                <h4 class="fw-bold"><?= $totalPeminjam ?></h4>
                <small>Peminjam</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-warning bg-opacity-10 p-3 text-center">
                <i class="fas fa-hand-holding fa-2x text-warning mb-2"></i>
                <h4 class="fw-bold"><?= $sedangDipinjam ?></h4>
                <small>Dipinjam</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-danger bg-opacity-10 p-3 text-center">
                <i class="fas fa-money-bill-wave fa-2x text-danger mb-2"></i>
                <h4 class="fw-bold">Rp <?= number_format($totalDenda,0,',','.') ?></h4>
                <small>Total Denda</small>
            </div>
        </div>
    </div>
    
    <!-- Buku Terlaris -->
    <div class="card p-4 mb-4">
        <h5 class="fw-bold mb-3">📊 Buku Terlaris</h5>
        <table class="table table-sm">
            <thead><tr><th>#</th><th>Judul</th><th>Dipinjam</th></tr></thead>
            <tbody>
                <?php $no=1; while($b = mysqli_fetch_assoc($bukuTerlaris)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($b['judul']) ?></td>
                    <td><span class="badge bg-primary"><?= $b['total'] ?>x</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <a href="index.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<?php include __DIR__ . '/../includes/footer_scripts.php'; ?>
</body>
</html>