<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

//Logika Ganti Bahasa
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

cekLogin();
cekRole('admin');

$id = (int)$_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM buku WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$b) {
    header("Location: index.php?pesan=tidak_ditemukan");
    exit();
}

$csrf_token = generateCsrfToken();

// Siapkan gambar preview
$gambar_preview = !empty($b['gambar']) ? $b['gambar'] : 'https://via.placeholder.com/150x210';
if (!empty($b['gambar']) && !filter_var($b['gambar'], FILTER_VALIDATE_URL)) {
    $gambar_preview = '../' . $gambar_preview;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Buku - MyLibrary</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .preview-img { width: 150px; height: 210px; object-fit: cover; border-radius: 12px; border: 3px dashed #ddd; display: block; margin: 0 auto; }
        .upload-area { border: 2px dashed #4601b4; border-radius: 12px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .upload-area:hover { background: #f0e6ff; border-color: #36018d; }
    </style>
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


<!-- NAVBAR -->
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card glass-card p-4">
                <h4 class="fw-bold mb-4">✏ Edit Data Buku</h4>

                <form action="../proses/proses.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">

                    <!-- Judul -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul Buku</label>
                        <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($b['judul']) ?>" required>
                    </div>

                    <!-- Pengarang -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pengarang</label>
                        <input type="text" name="pengarang" class="form-control" value="<?= htmlspecialchars($b['pengarang']) ?>" required>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <input type="text" name="kategori" class="form-control" value="<?= htmlspecialchars($b['kategori'] ?? '') ?>">
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($b['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <!-- ========== BAGIAN GAMBAR (BERSIH, TIDAK DUPLIKAT) ========== -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cover Buku</label>
                        
                        <!-- Preview gambar saat ini -->
                        <div class="text-center mb-3">
                            <img src="<?= htmlspecialchars($gambar_preview) ?>" class="preview-img" id="previewImage" alt="Cover">
                        </div>

                        <!-- Upload gambar baru -->
                        <div class="upload-area mb-3" onclick="document.getElementById('gambar').click()">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                            <p class="mb-1 fw-bold">Klik untuk upload gambar baru</p>
                            <small class="text-muted">JPG/PNG/GIF, max 2MB</small>
                        </div>
                        <input type="file" name="gambar" id="gambar" class="d-none" accept="image/*" onchange="previewGambar(this)">

                        <!-- Atau URL -->
                        <div class="text-center my-2">
                            <small class="text-muted">— atau gunakan URL —</small>
                        </div>
                        <input type="text" name="gambar_url" class="form-control" value="<?= htmlspecialchars($b['gambar'] ?? '') ?>" placeholder="Kosongkan jika tidak ingin mengubah gambar">
                        <small class="text-muted">Biarkan kosong jika upload file.</small>
                    </div>
                    <!-- ========== AKHIR BAGIAN GAMBAR ========== -->

                    <!-- Stok -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Stok</label>
                        <input type="number" name="stok" class="form-control" value="<?= $b['stok'] ?>" min="0" required>
                    </div>

                    <!-- Tombol -->
                    <div class="d-flex gap-2">
                        <button type="submit" name="update" class="btn btn-primary w-100">Simpan Perubahan</button>
                        <a href="index.php" class="btn btn-light w-100">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- FOOTER SCRIPTS -->
<?php include __DIR__ . '/../includes/footer_scripts.php'; ?>

<script>
function previewGambar(input) {
    const file = input.files[0];
    if (file) {
        if (file.size > 2000000) { alert('Ukuran terlalu besar! Maksimal 2MB.'); return; }
        const reader = new FileReader();
        reader.onload = e => document.getElementById('previewImage').src = e.target.result;
        reader.readAsDataURL(file);
    }
}
</script>
</body>
</html>