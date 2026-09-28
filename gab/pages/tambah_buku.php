<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

//Logika Ganti Bahasa
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

cekLogin();
cekRole('admin');

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku Baru - MyLibrary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .preview-img {
            width: 150px; height: 210px; object-fit: cover;
            border-radius: 12px; border: 3px dashed #ddd;
            display: block; margin: 0 auto;
        }
        .upload-area {
            border: 2px dashed #4601b4; border-radius: 12px;
            padding: 20px; text-align: center; cursor: pointer;
            transition: all 0.3s; background: #f8f9fa;
        }
        .upload-area:hover { background: #f0e6ff; border-color: #36018d; }
        .glass-card {
            background: white; border-radius: 20px; border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; display: flex; align-items: center;
        }
        .btn-primary {
            background: #4601b4; border: none; border-radius: 12px;
            padding: 12px; font-weight: bold; transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #36018d; transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(70,1,180,0.3);
        }
        .btn-light { border-radius: 12px; padding: 12px; font-weight: bold; }
        .form-control, .form-select {
            border-radius: 12px; padding: 12px 15px;
            border: 2px solid #e0e0e0; transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #4601b4; box-shadow: 0 0 0 0.25rem rgba(70,1,180,0.1);
        }
        .form-label { font-weight: 600; color: #333; }
    </style>
</head>
<body>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="glass-card p-4 p-md-5">
                
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                        <i class="fas fa-book-medical fa-2x text-primary"></i>
                    </div>
                    <h4 class="fw-bold">📘 Tambah Buku Baru</h4>
                    <p class="text-muted">Lengkapi informasi buku di bawah ini</p>
                </div>

                <!-- ✅ FORM TANPA JAVASCRIPT VALIDASI -->
                <form action="../proses/proses.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-book me-2"></i>Judul Buku <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" placeholder="Masukkan judul buku" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-pen-nib me-2"></i>Pengarang <span class="text-danger">*</span></label>
                        <input type="text" name="pengarang" class="form-control" placeholder="Nama pengarang" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-tags me-2"></i>Kategori</label>
                        <select name="kategori" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            <option>Fiksi</option><option>Non-Fiksi</option><option>Sejarah</option>
                            <option>Teknologi</option><option>Pendidikan</option><option>Agama</option>
                            <option>Romance</option><option>Horror</option><option>Komik</option>
                            <option>Biografi</option><option>Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-align-left me-2"></i>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Deskripsi singkat..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-image me-2"></i>Cover Buku</label>
                        
                        <div class="text-center mb-3" id="previewContainer" style="display:none;">
                            <img id="previewImage" class="preview-img" src="#" alt="Preview">
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="resetGambar()">
                                <i class="fas fa-times me-1"></i> Hapus Gambar
                            </button>
                        </div>

                        <div class="upload-area" id="uploadArea" onclick="document.getElementById('gambarFile').click()">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                            <p class="mb-1 fw-bold text-dark">Klik untuk upload gambar</p>
                            <small class="text-muted">JPG/PNG max 2MB</small>
                        </div>
                        <input type="file" name="gambar" id="gambarFile" class="d-none" accept="image/*" onchange="previewGambar(this)">
                        
                        <div class="text-center mt-2"><small class="text-muted">atau</small></div>
                        <input type="url" name="gambar_url" class="form-control mt-2" placeholder="URL gambar (opsional)">
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-boxes me-2"></i>Stok Buku <span class="text-danger">*</span></label>
                        <input type="number" name="stok" class="form-control" value="1" min="1" max="999" required>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <a href="index.php" class="btn btn-light flex-fill"><i class="fas fa-arrow-left me-2"></i>Batal</a>
                        <button type="submit" name="tambah" class="btn btn-primary flex-fill">
                            <i class="fas fa-save me-2"></i>Simpan Buku
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- ✅ HANYA FUNGSI PREVIEW GAMBAR -->
 <script>
// Cek dark mode
(function() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
})();
</script>
<script>
function previewGambar(input) {
    var file = input.files[0];
    if (file) {
        if (file.size > 2000000) { alert('Ukuran terlalu besar! Maksimal 2MB.'); input.value = ''; return; }
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('previewContainer').style.display = 'block';
            document.getElementById('uploadArea').style.display = 'none';
        };
        reader.readAsDataURL(file);
        document.querySelector('input[name="gambar_url"]').value = '';
    }
}

function resetGambar() {
    document.getElementById('gambarFile').value = '';
    document.getElementById('previewContainer').style.display = 'none';
    document.getElementById('uploadArea').style.display = 'block';
}
</script>

</body>
</html>