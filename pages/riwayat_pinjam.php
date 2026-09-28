<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/bahasa.php';
require_once __DIR__ . '/../includes/functions.php';

cekLogin();

//Logika Ganti Bahasa
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'id';
$text = $translations[$lang];
$username_login = $_SESSION['admin'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    $sql = "SELECT p.*, b.judul, b.gambar, b.pengarang FROM peminjaman p JOIN buku b ON p.buku_id = b.id ORDER BY p.tgl_pinjam DESC";
} else {
    $sql = "SELECT p.*, b.judul, b.gambar, b.pengarang FROM peminjaman p JOIN buku b ON p.buku_id = b.id WHERE p.nama_peminjam = ? ORDER BY p.tgl_pinjam DESC";
}

$stmt = mysqli_prepare($conn, $sql);
if ($role !== 'admin') mysqli_stmt_bind_param($stmt, "s", $username_login);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$totalPinjam = 0; $sedangDipinjam = 0; $dikembalikan = 0; $totalDenda = 0; $dataRows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $dataRows[] = $row;
    $totalPinjam++;
    if ($row['status'] === 'Dipinjam') $sedangDipinjam++; else $dikembalikan++;
    $totalDenda += $row['denda'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text['judul_riwayat'] ?? 'Riwayat Peminjaman' ?> - MyLibrary</title>
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

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0"><i class="fas fa-history me-2"></i><?= $text['judul_riwayat'] ?? 'Riwayat Peminjaman' ?></h3>
            <p class="text-muted">
                <?= $role === 'admin' ? ($text['semua_riwayat'] ?? 'Semua riwayat.') : ($text['riwayat_anda'] ?? 'Riwayat Anda.') ?>
                <br>
                <span style="font-size: 0.85rem;">
                    <i class="fas fa-clock me-1"></i>
                    <?= $text['status'] ?? 'Status' ?>: <span id="statusOperasional">🔄</span>
                    | <span id="jamRealtime">--:--:--</span>
                </span>
            </p>
        </div>
        <a href="index.php" class="btn btn-outline-primary rounded-pill"><i class="fas fa-arrow-left me-2"></i><?= $text['kembali'] ?? 'Kembali' ?></a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3"><div class="card stat-card bg-primary bg-opacity-10 p-3 text-center"><i class="fas fa-book fa-2x text-primary mb-2"></i><h4 class="fw-bold"><?= $totalPinjam ?></h4><small><?= $text['total'] ?? 'Total' ?></small></div></div>
        <div class="col-md-3 mb-3"><div class="card stat-card bg-warning bg-opacity-10 p-3 text-center"><i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i><h4 class="fw-bold"><?= $sedangDipinjam ?></h4><small><?= $text['dipinjam'] ?? 'Dipinjam' ?></small></div></div>
        <div class="col-md-3 mb-3"><div class="card stat-card bg-success bg-opacity-10 p-3 text-center"><i class="fas fa-check-circle fa-2x text-success mb-2"></i><h4 class="fw-bold"><?= $dikembalikan ?></h4><small><?= $text['kembali'] ?? 'Kembali' ?></small></div></div>
        <div class="col-md-3 mb-3"><div class="card stat-card bg-danger bg-opacity-10 p-3 text-center"><i class="fas fa-money-bill-wave fa-2x text-danger mb-2"></i><h4 class="fw-bold">Rp <?= number_format($totalDenda,0,',','.') ?></h4><small><?= $text['denda'] ?? 'Denda' ?></small></div></div>
    </div>

    <div class="glass-card p-4">
        <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling:touch; max-width:100vw; display:block;">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th><?= $text['cover'] ?? 'Cover' ?></th><th><?= $text['judul'] ?? 'Judul' ?></th><?php if($role==='admin'): ?><th><?= $text['peminjam'] ?? 'Peminjam' ?></th><?php endif; ?><th><?= $text['tgl_pinjam'] ?? 'Pinjam' ?></th><th><?= $text['tgl_kembali'] ?? 'Kembali' ?></th><th><?= $text['jumlah'] ?? 'Jml' ?></th><th><?= $text['status'] ?? 'Status' ?></th><?php if($role==='admin'): ?><th><?= $text['aksi'] ?? 'Aksi' ?></th><?php endif; ?></tr></thead>
                <tbody>
                    <?php if(count($dataRows)>0): foreach($dataRows as $row): 
                        $status=$row['status']; $isLate = ($status == 'Dipinjam' && strtotime($row['tgl_kembali']) < strtotime(date('Y-m-d')));;
                        if(strpos($status,'Rusak')!==false){$badge='bg-danger';$icon='fa-exclamation-triangle';}
                        elseif($status==='Kembali'){$badge='bg-success';$icon='fa-check-circle';}
                        elseif($isLate){$badge='bg-danger late-badge';$icon='fa-clock';}
                        else{$badge='bg-warning text-dark';$icon='fa-hourglass-half';}
                       $gambar_db = $row['gambar'] ?? '';
if (!empty($gambar_db)) {
    if (!filter_var($gambar_db, FILTER_VALIDATE_URL)) {
        $gambar = '../' . $gambar_db;
    } else {
        $gambar = $gambar_db;
    }
} else {
    $gambar = 'https://via.placeholder.com/50x70';
}
                    ?>
                    <tr class="<?= $isLate?'table-danger':'' ?>">
                        <td><img src="<?= htmlspecialchars($gambar) ?>" class="rounded" style="width:50px;height:70px;object-fit:cover;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='75' height='110'%3E%3Crect fill='%23e9ecef' width='75' height='110'/%3E%3Ctext x='50%25' y='50%25' fill='%236c757d' font-size='10' font-family='Arial' text-anchor='middle' dy='.3em'%3ENo Cover%3C/text%3E%3C/svg%3E'"></td>
                        <td><div class="fw-bold"><?= htmlspecialchars($row['judul']) ?></div><small class="text-muted"><?= htmlspecialchars($row['pengarang']) ?></small><?php if($isLate): ?><br><span class="badge bg-danger mt-1"><?= $text['terlambat'] ?? 'Terlambat!' ?></span><?php endif; ?></td>
                        <?php if($role==='admin'): ?><td><?= htmlspecialchars($row['nama_peminjam']) ?></td><?php endif; ?>
                        <td><?= formatTanggal($row['tgl_pinjam']) ?></td>
                        <td><?= formatTanggal($row['tgl_kembali']) ?></td>
                        <td><?= $row['jumlah_pinjam'] ?> <?= $text['buku'] ?? 'buku' ?></td>
                        <td><span class="badge <?= $badge ?> px-3 py-2"><i class="fas <?= $icon ?> me-1"></i><?= $status ?></span><?php if($row['denda']>0): ?><br><small class="text-danger"><?= $text['denda'] ?? 'Denda' ?>: Rp <?= number_format($row['denda'],0,',','.') ?></small><?php endif; ?></td>
                        <?php if($role==='admin'&&$status==='Dipinjam'): ?>
<td>
    <button class="btn btn-sm btn-success rounded-pill" onclick="konfirmasiPengembalian(<?= $row['id'] ?>,<?= $row['buku_id'] ?>,'<?= addslashes($row['judul']) ?>','<?= addslashes($row['nama_peminjam']) ?>','<?= addslashes($gambar) ?>',<?= $row['jumlah_pinjam'] ?>)">
        <i class="fas fa-undo me-1"></i><?= $text['kembalikan_btn'] ?? 'Kembalikan' ?>
    </button>
    <!-- ✅ TOMBOL CETAK -->
    <a href="cetak_slip.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill mt-1">
        <i class="fas fa-print me-1"></i>Cetak
    </a>
</td>
<?php elseif($role==='admin'): ?>
<td>
    <a href="cetak_slip.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
        <i class="fas fa-print me-1"></i>Slip
    </a>
</td>
<?php endif; ?>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="<?= $role==='admin'?'8':'6' ?>" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block"></i><?= $text['belum_ada_riwayat'] ?? 'Belum ada riwayat.' ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- FOOTER SCRIPTS -->
<?php include __DIR__ . '/../includes/footer_scripts.php'; ?>

<!-- ✅ SWEETALERT UNTUK PENGEMBALIAN -->
<?php if (isset($_SESSION['alert_kembali'])) : 
    $data = $_SESSION['alert_kembali'];
    unset($_SESSION['alert_kembali']);
    $isRusak = ($data['kondisi'] === 'rusak');
    $dendaAmount = $data['denda'] ?? 0;
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRusak = <?= $isRusak ? 'true' : 'false' ?>;
    const iconColor = isRusak ? '#f39c12' : '#28a745';
    const judulPopup = isRusak ? '<?= $text['pengembalian_rusak'] ?? 'Pengembalian Dicatat (Rusak)' ?>' : '<?= $text['pengembalian_berhasil'] ?? 'Pengembalian Berhasil!' ?>';
    const iconPopup = isRusak ? '<i class="fas fa-exclamation-triangle text-warning me-2"></i>' : '<i class="fas fa-check-circle text-success me-2"></i>';
    const kondisiText = isRusak ? '<?= $text['kondisi_rusak'] ?? '⚠️ Kondisi Rusak' ?>' : '<?= $text['kondisi_baik'] ?? '✓ Kondisi Baik' ?>';
    const badgeClass = isRusak ? 'bg-warning text-dark' : 'bg-success';
    const confirmText = isRusak ? '<i class="fas fa-clipboard-list me-2"></i><?= $text['catat_selesai'] ?? 'Catat & Selesai' ?>' : '<i class="fas fa-check-circle me-2"></i><?= $text['ok_selesai'] ?? 'OK, Selesai' ?>';
    
    let dendaHTML = '';
    <?php if($dendaAmount>0): ?>
    dendaHTML = '<div class="alert alert-warning mt-2"><?= $text['denda'] ?? 'Denda' ?>: Rp <?= number_format($dendaAmount,0,',','.') ?></div>';
    <?php endif; ?>
    
    const isDark = document.body.classList.contains('dark-mode');

Swal.fire({
    title: iconPopup + ' ' + judulPopup,
    html: '...',
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

<script>
// Fungsi Jam Real-Time & Status Operasional
function updateJamOperasional() {
    const sekarang = new Date();
    const jam = sekarang.getHours().toString().padStart(2, '0');
    const menit = sekarang.getMinutes().toString().padStart(2, '0');
    const detik = sekarang.getSeconds().toString().padStart(2, '0');
    
    document.getElementById('jamRealtime').textContent = jam + ':' + menit + ':' + detik;
    
    const jamInt = sekarang.getHours();
    const statusEl = document.getElementById('statusOperasional');
    
    if (jamInt >= 6 && jamInt < 23) {
        statusEl.innerHTML = '<span style="color: #28a745;">🟢 <?= $text['buka'] ?? 'Buka' ?></span>';
    } else {
        statusEl.innerHTML = '<span style="color: #dc3545;">🔴 <?= $text['tutup'] ?? 'Tutup' ?></span>';
    }
}

setInterval(updateJamOperasional, 1000);
updateJamOperasional();
</script>

<script>
function konfirmasiPengembalian(idPinjam, idBuku, judul, peminjam, gambar, jumlah) {
    const dendaPerBuku = 5000;
    const totalDenda = dendaPerBuku * jumlah;
    const isDark = document.body.classList.contains('dark-mode');
    
    Swal.fire({
        title: '<?= $text['konfirmasi_pengembalian'] ?? 'Konfirmasi Pengembalian' ?>',
        html: '<div class="text-center mb-3"><img src="'+gambar+'" style="width:80px;height:110px;object-fit:cover;border-radius:8px;"><h5 class="fw-bold mt-2">'+judul+'</h5><span class="badge bg-info">'+peminjam+' - '+jumlah+' <?= $text['buku'] ?? 'buku' ?></span></div><div class="alert alert-warning"><?= $text['kondisi_pertanyaan'] ?? 'Bagaimana kondisi buku?' ?></div><div class="d-flex gap-3 justify-content-center"><div onclick="pilihKondisi(\'baik\','+jumlah+',0)" id="optBaik" style="cursor:pointer;" class="bg-success bg-opacity-10 rounded-3 p-3 text-center"><i class="fas fa-check-circle fa-2x text-success"></i><h6 class="text-success"><?= $text['baik'] ?? 'Baik' ?></h6><small><?= $text['tanpa_denda'] ?? 'Tanpa Denda' ?></small></div><div onclick="pilihKondisi(\'rusak\','+jumlah+','+totalDenda+')" id="optRusak" style="cursor:pointer;" class="bg-danger bg-opacity-10 rounded-3 p-3 text-center"><i class="fas fa-exclamation-triangle fa-2x text-danger"></i><h6 class="text-danger"><?= $text['rusak'] ?? 'Rusak' ?></h6><small><?= $text['denda'] ?? 'Denda' ?> Rp '+totalDenda.toLocaleString('id-ID')+'</small></div></div><input type="hidden" id="kondisiBuku" value=""><input type="hidden" id="dendaAkhir" value="0">',
        showCancelButton: true,
        confirmButtonText: '<?= $text['proses'] ?? 'Proses' ?>',
        confirmButtonColor: '#28a745',
        cancelButtonText: '<?= $text['batal'] ?? 'Batal' ?>',
        background: isDark ? '#1a1a2e' : '#ffffff',
        color: isDark ? '#ffffff' : '#545454',
        preConfirm: () => {
            const kondisi = document.getElementById('kondisiBuku').value;
            if (!kondisi) { Swal.showValidationMessage('<?= $text['pilih_kondisi'] ?? 'Pilih kondisi!' ?>'); return false; }
            return { kondisi: kondisi, denda: document.getElementById('dendaAkhir').value };
        }
    }).then(r => {
        if (r.isConfirmed) window.location.href = '../proses/proses.php?kembali='+idPinjam+'&buku_id='+idBuku+'&kondisi='+r.value.kondisi+'&denda='+r.value.denda;
    });
}
function pilihKondisi(kondisi, jumlah, denda) {
    document.getElementById('kondisiBuku').value = kondisi;
    document.getElementById('dendaAkhir').value = denda;
    ['optBaik','optRusak'].forEach(id => { const el = document.getElementById(id); el.style.border = '2px solid transparent'; el.style.transform = 'scale(1)'; });
    const sel = document.getElementById(kondisi==='baik'?'optBaik':'optRusak');
    sel.style.border = '2px solid '+(kondisi==='baik'?'#28a745':'#dc3545');
    sel.style.transform = 'scale(1.1)';
}
</script>
</body>
</html>