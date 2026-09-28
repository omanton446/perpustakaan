<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/bahasa.php';
require_once __DIR__ . '/../includes/functions.php';

// Cek login
cekLogin();

// Logika Ganti Bahasa
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'id';
$text = $translations[$lang];
$username_login = $_SESSION['admin'];
$role = $_SESSION['role'];

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Pencarian
$cari = $_GET['cari'] ?? '';
$kategori_filter = $_GET['kategori'] ?? '';
$searchTerm = "%$cari%";

// Query jumlah total data (dengan filter kategori)
if (!empty($kategori_filter)) {
    $sqlCount = "SELECT COUNT(*) as total FROM buku WHERE (judul LIKE ? OR pengarang LIKE ?) AND kategori = ?";
    $stmtCount = mysqli_prepare($conn, $sqlCount);
    mysqli_stmt_bind_param($stmtCount, "sss", $searchTerm, $searchTerm, $kategori_filter);
} else {
    $sqlCount = "SELECT COUNT(*) as total FROM buku WHERE judul LIKE ? OR pengarang LIKE ?";
    $stmtCount = mysqli_prepare($conn, $sqlCount);
    mysqli_stmt_bind_param($stmtCount, "ss", $searchTerm, $searchTerm);
}
// ✅ EKSEKUSI DI LUAR IF-ELSE (untuk kedua kondisi)
mysqli_stmt_execute($stmtCount);
$resultCount = mysqli_stmt_get_result($stmtCount);
$rowCount = mysqli_fetch_assoc($resultCount);
$totalData = $rowCount['total'];
$totalPages = ceil($totalData / $limit);

// Query data buku dengan LIMIT
if (!empty($kategori_filter)) {
    $sql = "SELECT * FROM buku WHERE (judul LIKE ? OR pengarang LIKE ?) AND kategori = ? ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssii", $searchTerm, $searchTerm, $kategori_filter, $limit, $offset);
} else {
    $sql = "SELECT * FROM buku WHERE judul LIKE ? OR pengarang LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $searchTerm, $searchTerm, $limit, $offset);
}
// ✅ EKSEKUSI DI LUAR IF-ELSE (untuk kedua kondisi)
mysqli_stmt_execute($stmt);
$tampil = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLibrary - <?= ucfirst($role) ?></title>
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

<!-- Scroll to Top Button -->
<button id="scrollTopBtn" onclick="scrollToTop()" title="Kembali ke atas">
    <i class="fas fa-chevron-up"></i>
</button>


<?php include __DIR__ . '/../includes/navbar.php'; ?>

<?php
// Cek buku yang terlambat untuk user yang sedang login
if ($role === 'peminjam') {
    $stmtLate = mysqli_prepare($conn, 
        "SELECT p.*, b.judul 
         FROM peminjaman p 
         JOIN buku b ON p.buku_id = b.id 
         WHERE p.nama_peminjam = ? 
         AND p.status = 'Dipinjam' 
         AND p.tgl_kembali < CURDATE()"
    );
    mysqli_stmt_bind_param($stmtLate, "s", $username_login);
    mysqli_stmt_execute($stmtLate);
    $lateResult = mysqli_stmt_get_result($stmtLate);
    $lateBooks = [];
    while ($late = mysqli_fetch_assoc($lateResult)) {
        $lateBooks[] = $late;
    }
}

// Tampilkan notifikasi jika ada buku terlambat
if (!empty($lateBooks)): ?>
<div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Pemberitahuan Keterlambatan!</h5>
    <p class="mb-1">Anda memiliki <strong><?= count($lateBooks) ?> buku</strong> yang terlambat dikembalikan:</p>
    <ul class="mb-2">
        <?php foreach ($lateBooks as $b): 
            $hariTelat = (new DateTime($b['tgl_kembali']))->diff(new DateTime())->days;
        ?>
        <li>
            <strong><?= htmlspecialchars($b['judul']) ?></strong> - 
            Telat <span class="badge bg-danger"><?= $hariTelat ?> hari</span>
            (Denda: Rp <?= number_format($hariTelat * 1000 * $b['jumlah_pinjam'], 0, ',', '.') ?>)
        </li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0"><?= $text['koleksi_buku'] ?></h3>
            <p class="text-muted">
                <?= $text['kelola_buku'] ?>
                <br>
                <span style="font-size: 0.9rem;">
                    <i class="fas fa-clock me-1"></i>
                    <?= $text['jam_operasional'] ?>: <strong>09:00 - 23:00</strong> | 
                    <?= $text['status'] ?>: <span id="statusOperasional">🔄</span>
                    | <span id="jamRealtime">--:--:--</span>
                </span>
            </p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="d-flex flex-wrap justify-content-md-end gap-2">
                <form action="" method="GET" class="d-flex flex-wrap gap-2">
    <!-- Filter Kategori -->
    <select name="kategori" class="form-select rounded-pill px-3" style="max-width: 180px;" onchange="this.form.submit()">
        <option value="">📂 Semua Kategori</option>
        <?php
        // Ambil daftar kategori dari database
        $katQuery = mysqli_query($conn, "SELECT DISTINCT kategori FROM buku WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori");
        while ($kat = mysqli_fetch_assoc($katQuery)) {
            $selected = (isset($_GET['kategori']) && $_GET['kategori'] === $kat['kategori']) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($kat['kategori']) . '" ' . $selected . '>' . htmlspecialchars($kat['kategori']) . '</option>';
        }
        ?>
    </select>
    
    <!-- Pencarian -->
    <div class="input-group" style="max-width: 300px;">
        <input type="text" name="cari" class="form-control rounded-start-pill px-3" 
               placeholder="<?= $text['cari_buku'] ?>" value="<?= htmlspecialchars($cari) ?>">
        <button class="btn btn-primary rounded-end-pill px-3" type="submit">
            <i class="fas fa-search"></i>
        </button>
    </div>
    
    <?php if (!empty($cari) || !empty($_GET['kategori'])) : ?>
    <a href="index.php" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="fas fa-times me-1"></i> <?= $text['reset'] ?>
    </a>
    <?php endif; ?>
</form>
            
                <?php if ($role === 'admin') : ?>
                <a href="tambah_buku.php" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-plus me-2"></i><?= $text['tambah_buku'] ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($cari)) : ?>
    <div class="alert alert-info rounded-3">
        <i class="fas fa-search me-2"></i>
        <?= $text['hasil_pencarian'] ?>: <strong>"<?= htmlspecialchars($cari) ?>"</strong>
        <span class="badge bg-primary ms-2"><?= $totalData ?> <?= $text['ditemukan'] ?></span>
        <a href="index.php" class="btn btn-sm btn-outline-primary ms-3 rounded-pill">
            <i class="fas fa-times-circle me-1"></i> <?= $text['hapus_filter'] ?>
        </a>
    </div>
    <?php endif; ?>

    <div class="glass-card p-4" style="overflow:visible !important;">
        <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling:touch; max-width:100vw; display:block;">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;"><?= $text['cover'] ?></th>
                        <th><?= $text['judul_deskripsi'] ?></th>
                        <th><?= $text['pengarang'] ?></th>
                        <th style="width: 150px;"><?= $text['aksi'] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($tampil) > 0) : 
                        while ($data = mysqli_fetch_assoc($tampil)) : 
                            $id = $data['id'];
                            $judul = htmlspecialchars($data['judul']);
                            $deskripsi = htmlspecialchars($data['deskripsi']);
                            $pengarang = htmlspecialchars($data['pengarang']);
                            // Ambil gambar dari database
$gambar_db = $data['gambar'] ?? '';
// Tentukan src untuk ditampilkan
if (!empty($gambar_db)) {
    // Jika bukan URL lengkap (lokal), tambahkan '../' karena kita di folder pages/
    if (!filter_var($gambar_db, FILTER_VALIDATE_URL)) {
        $gambar = '../' . $gambar_db;
    } else {
        $gambar = $gambar_db;
    }
} else {
    $gambar = 'https://via.placeholder.com/75x110';
}
                    ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($gambar) ?>" class="buku-thumb" alt="Cover" onerror="this.src='data:image/svg+xml,...'">
                        </td>
                        <td>
                            <div class="fw-bold text-dark mb-1"><?= $judul ?></div>
                            <div class="buku-desc"><?= $deskripsi ?: '<span class="text-muted fst-italic">' . $text['tidak_ada_deskripsi'] . '</span>' ?></div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark fw-normal p-2">
                                <i class="fas fa-pen-nib me-1"></i> <?= $pengarang ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-info rounded-pill" 
                                    onclick="lihatDetail('<?= addslashes($judul) ?>', '<?= addslashes($pengarang) ?>', '<?= addslashes($data['kategori'] ?? $text['tidak_ada_kategori'] ?? 'Tidak ada') ?>', '<?= $data['stok'] ?>', '<?= addslashes($gambar) ?>', '<?= addslashes($deskripsi) ?>')" 
                                    title="<?= $text['detail'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($role === 'admin') : ?>
                                    <a href="edit_buku.php?id=<?= $id ?>" class="btn btn-sm btn-outline-warning rounded-pill" title="<?= $text['edit'] ?>"><i class="fas fa-edit"></i></a>
                                    <a href="javascript:void(0)" onclick="hapusBuku('<?= $id ?>', '<?= addslashes($judul) ?>')" class="btn btn-sm btn-outline-danger rounded-pill" title="<?= $text['hapus'] ?>"><i class="fas fa-trash"></i></a>
                                <?php else : ?>
    <?php if ($data['stok'] > 0) : ?>
        <a href="form_pinjam.php?id=<?= $id ?>" class="btn btn-success btn-sm rounded-pill px-3">
            <i class="fas fa-hand-holding me-1"></i> <?= $text['pinjam'] ?>
        </a>
    <?php else : ?>
        <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled title="<?= $text['stok_habis'] ?>">
            <i class="fas fa-hand-holding me-1"></i> <?= $text['stok_habis'] ?>
        </button>
    <?php endif; ?>
<?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else : ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-search mb-3 d-block fa-2x"></i>
                            <?= !empty($cari) ? $text['data_tidak_ditemukan'] : $text['belum_ada_data'] ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1) : ?>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">
                <?= $text['menampilkan'] ?> <?= $offset + 1 ?> - <?= min($offset + $limit, $totalData) ?> <?= $text['dari'] ?> <?= $totalData ?> <?= $text['data'] ?>
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page - 1 ?>&cari=<?= urlencode($cari) ?>"><i class="fas fa-chevron-left"></i></a></li>
                    <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&cari=<?= urlencode($cari) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page + 1 ?>&cari=<?= urlencode($cari) ?>"><i class="fas fa-chevron-right"></i></a></li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer_scripts.php'; ?>
<script src="../assets/script.js"></script>
<script>var userRole = "<?= $role ?>";</script>
<script>
// Fungsi Jam Real-Time & Status Operasional
function updateJamOperasional() {
    const sekarang = new Date();
    const jam = sekarang.getHours().toString().padStart(2, '0');
    const menit = sekarang.getMinutes().toString().padStart(2, '0');
    const detik = sekarang.getSeconds().toString().padStart(2, '0');
    
    // Tampilkan jam
    document.getElementById('jamRealtime').textContent = jam + ':' + menit + ':' + detik;
    
    // Cek status operasional
    const jamInt = sekarang.getHours();
    const statusEl = document.getElementById('statusOperasional');
    
    if (jamInt >= 6 && jamInt < 23) {  // Ubah dari 9 menjadi 6
    statusEl.innerHTML = '<span style="color: #28a745;">🟢 Buka</span>';
} else {
    statusEl.innerHTML = '<span style="color: #dc3545;">🔴 Tutup</span>';
}
}

// Update setiap detik
setInterval(updateJamOperasional, 1000);

// Jalankan saat halaman dimuat
updateJamOperasional();
</script>
<script>
// Notifikasi pesan dari URL
const urlParams = new URLSearchParams(window.location.search);
const pesan = urlParams.get('pesan');

const isDark = document.body.classList.contains('dark-mode');

if (pesan === 'masih_pinjam') {
    Swal.fire({
        icon: 'warning',
        title: '<?= $text['tidak_bisa_pinjam'] ?>',
        text: '<?= $text['masih_pinjam_text'] ?>',
        confirmButtonColor: '#f39c12',
        background: isDark ? '#1a1a2e' : '#ffffff',
        color: isDark ? '#ffffff' : '#545454'
    });
    window.history.replaceState({}, document.title, window.location.pathname);
}
if (pesan === 'stok_kurang') {
    Swal.fire({
        icon: 'error',
        title: 'Stok Tidak Cukup!',
        text: 'Maaf, stok buku tidak mencukupi.',
        confirmButtonColor: '#d33',
        background: isDark ? '#1a1a2e' : '#ffffff',
        color: isDark ? '#ffffff' : '#545454'
    });
    window.history.replaceState({}, document.title, window.location.pathname);
}

if (pesan === 'buku_tidak_ada') {
    Swal.fire({
        icon: 'error',
        title: 'Buku Tidak Ditemukan!',
        text: 'Maaf, buku tidak tersedia.',
        confirmButtonColor: '#d33',
        background: isDark ? '#1a1a2e' : '#ffffff',
        color: isDark ? '#ffffff' : '#545454'
    });
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>
<!-- SweetAlert Pengembalian -->
<?php if (isset($_SESSION['alert_kembali'])) : 
    $data = $_SESSION['alert_kembali'];
    unset($_SESSION['alert_kembali']);
    $isRusak = ($data['kondisi'] === 'rusak');
    $dendaAmount = $data['denda'] ?? 0;
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRusak = <?= $isRusak ? 'true' : 'false' ?>;
    const isDark = document.body.classList.contains('dark-mode');  // ✅ TAMBAHKAN INI
    const iconColor = isRusak ? '#f39c12' : '#28a745';
    const judulPopup = isRusak ? '<?= $text['pengembalian_rusak'] ?>' : '<?= $text['pengembalian_berhasil'] ?>';
    const iconPopup = isRusak ? '<i class="fas fa-exclamation-triangle text-warning me-2"></i>' : '<i class="fas fa-check-circle text-success me-2"></i>';
    const kondisiText = isRusak ? '<?= $text['kondisi_rusak'] ?>' : '<?= $text['kondisi_baik'] ?>';
    const badgeClass = isRusak ? 'bg-warning text-dark' : 'bg-success';
    const confirmText = isRusak ? '<i class="fas fa-clipboard-list me-2"></i><?= $text['catat_selesai'] ?>' : '<i class="fas fa-check-circle me-2"></i><?= $text['ok_selesai'] ?>';
    
    let dendaHTML = '';
    <?php if($dendaAmount>0): ?>
    dendaHTML = '<div class="alert alert-warning mt-2"><?= $text['denda'] ?>: Rp <?= number_format($dendaAmount,0,',','.') ?></div>';
    <?php endif; ?>
    
    Swal.fire({
        title: iconPopup + ' ' + judulPopup,
        html: '<div class="text-center mb-3"><img src="<?= htmlspecialchars($data['gambar']) ?>" class="rounded shadow-sm mb-2" style="width:80px;height:110px;object-fit:cover;border:3px solid '+iconColor+';" onerror="this.src=\'data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='75' height='110'%3E%3Crect fill='%23e9ecef' width='75' height='110'/%3E%3Ctext x='50%25' y='50%25' fill='%236c757d' font-size='10' font-family='Arial' text-anchor='middle' dy='.3em'%3ENo Cover%3C/text%3E%3C/svg%3E\'"><h5 class="fw-bold mb-1"><?= addslashes($data['judul']) ?></h5><span class="badge '+badgeClass+' px-3 py-1">'+kondisiText+'</span></div><div class="bg-light rounded-3 p-3"><div class="d-flex justify-content-between mb-2"><span class="text-muted small"><?= $text['peminjam'] ?></span><span class="fw-bold"><?= addslashes($data['peminjam']) ?></span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted small"><?= $text['jumlah'] ?></span><span class="fw-bold"><?= $data['jumlah'] ?> <?= $text['buku'] ?></span></div><div class="d-flex justify-content-between"><span class="text-muted small"><?= $text['stok_bertambah'] ?></span><span class="fw-bold text-success">+<?= $data['jumlah'] ?></span></div></div>' + dendaHTML,
        icon: null,
        confirmButtonText: confirmText,
        confirmButtonColor: isRusak ? '#f39c12' : '#28a745',
        background: isDark ? '#1a1a2e' : '#ffffff',     
        color: isDark ? '#ffffff' : '#545454',          
        width: '500px',
        customClass: { popup: 'rounded-4 shadow-lg' }
    });
});
</script>
<?php endif; ?>

<!-- Fungsi lihatDetail (Popup Detail Buku) -->
<script>
function lihatDetail(judul, pengarang, kategori, stok, gambar, deskripsi) {
    var stokBadge = stok > 10 ? 'bg-success' : (stok > 0 ? 'bg-warning text-dark' : 'bg-danger');
    var stokText = 'Stok: ' + stok;
    var deskripsiText = deskripsi || '<i>Tidak ada deskripsi</i>';
    var stokHabisHTML = stok == 0 ? '<div class="alert alert-danger mt-3 text-center">Stok habis!</div>' : '';
    var isDark = document.body.classList.contains('dark-mode');
    
    var bgLight = isDark ? 'background-color: #1a1a2e;' : 'background-color: #f8f9fa;';
    var bgWhite = isDark ? 'background-color: #16213e; border-color: #2a2a4a;' : 'background-color: #ffffff;';
    var textMuted = isDark ? 'color: #bbbbbb;' : 'color: #6c757d;';
    var textColor = isDark ? 'color: #ffffff;' : '';
    var borderColor = isDark ? 'border: 1px solid #2a2a4a;' : 'border: 1px solid #dee2e6;';
    
    Swal.fire({
        title: 'Detail Buku',
        html: '<div class="text-center mb-3">' +
              '<img src="' + gambar + '" style="width:160px;height:230px;object-fit:cover;border:3px solid #4601b4;border-radius:12px;" onerror="this.style.display=\'none\'; this.insertAdjacentHTML(\'afterend\', \'<div style=width:160px;height:230px;background:#e9ecef;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#6c757d;font-size:14px>No Cover</div>\')">' +
              '<h4 class="fw-bold mt-2" style="' + textColor + '">' + judul + '</h4>' +
              '<span class="badge bg-primary">' + kategori + '</span> ' +
              '<span class="badge ' + stokBadge + '">' + stokText + '</span>' +
              '</div>' +
              '<div class="rounded-3 p-3 mb-3" style="' + bgLight + '">' +
              '<p style="' + textColor + '"><strong>Pengarang:</strong> ' + pengarang + '</p>' +
              '<p style="' + textColor + '"><strong>Stok:</strong> ' + stok + ' buku</p>' +
              '</div>' +
              '<div class="rounded-3 p-3" style="' + bgWhite + ' ' + borderColor + '">' +
              '<h6 style="' + textColor + '">Deskripsi</h6>' +
              '<p class="mb-0" style="' + textMuted + '">' + deskripsiText + '</p>' +
              '</div>' + stokHabisHTML,
        showCloseButton: true,
        showConfirmButton: false,
        background: isDark ? '#1a1a2e' : '#ffffff',
        color: isDark ? '#ffffff' : '#545454',
        width: '550px'
    });
}
</script>
<script>
// Fungsi hapus buku dengan konfirmasi
function hapusBuku(id, judul) {
    const isDark = document.body.classList.contains('dark-mode');
    
    Swal.fire({
        title: 'Hapus Buku?',
        html: 'Buku <strong>"' + judul + '"</strong> akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: isDark ? '#1a1a2e' : '#ffffff',
        color: isDark ? '#ffffff' : '#545454'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../proses/proses.php?hapus=' + id;
        }
    });
}
</script>
</body>
</html>