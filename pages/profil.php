<?php
include '../config/koneksi.php';
session_start();

// Proteksi Login
if (!isset($_SESSION['admin'])) { 
    header("Location: login.php"); 
    exit(); 
}

// Variabel untuk navbar
$username_login = $_SESSION['admin'];
$role = $_SESSION['role'];
$lang = $_SESSION['lang'] ?? 'id';

$username = $_SESSION['admin'];

// Ambil data user dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Logika Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Update Email
    if (isset($_POST['update_profil'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $stmt = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email, $username);
        mysqli_stmt_execute($stmt);
        header("Location: profil.php?pesan=email_sukses");
        exit();
    } 
    
    // 2. Update Password
    elseif (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_pass'];
        $n_pass = $_POST['n_pass'];
        $c_pass = $_POST['c_pass'];

        if (!password_verify($old_pass, $user['password'])) {
            header("Location: profil.php?pesan=pass_lama_salah");
            exit();
        }

        if (strlen($n_pass) < 4) {
            header("Location: profil.php?pesan=pass_terlalu_pendek");
            exit();
        }

        if ($n_pass === $c_pass) {
            $hashed = password_hash($n_pass, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "ss", $hashed, $username);
            mysqli_stmt_execute($stmt);
            header("Location: profil.php?pesan=pass_sukses");
        } else {
            header("Location: profil.php?pesan=pass_tidak_match");
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - MyLibrary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .glass-card { background: white; border-radius: 16px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .nav-pills .nav-link.active { background-color: #4601b4; color: white !important; }
        .profile-icon { width: 80px; height: 80px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #4601b4; }
        .input-group .btn-outline-secondary { border-color: #e0e0e0; background-color: #f8f9fa; }
        .input-group .btn-outline-secondary:hover { background-color: #e9ecef; color: #4601b4; }
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

<div class="container mt-4">
    <div class="row">
        
    <!-- SIDEBAR KIRI -->
<div class="col-12 col-md-4 col-lg-3 mb-3">
    <div class="glass-card p-3 text-center">
        
        <!-- FOTO PROFIL (Ikon Statis) -->
        <div class="profile-photo mx-auto mb-2" style="width: 80px; height: 80px; margin: 0 auto;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; border: 3px solid #4601b4;">
                <i class="fas fa-user" style="font-size: 2.5rem; color: #4601b4;"></i>
            </div>
        </div>
        
        <h5 class="fw-bold mb-0"><?= htmlspecialchars($user['username']) ?></h5>
        <p class="text-muted small"><?= ucfirst($user['peran']) ?></p>

        <!-- Nav pills -->
        <div class="nav flex-row flex-md-column nav-pills justify-content-center mt-3" role="tablist">
            <button class="nav-link active m-1" data-bs-toggle="pill" data-bs-target="#info">
                <i class="fas fa-id-card me-1"></i> <span class="d-none d-md-inline">Info Profil</span>
            </button>
            <button class="nav-link m-1" data-bs-toggle="pill" data-bs-target="#security">
                <i class="fas fa-lock me-1"></i> <span class="d-none d-md-inline">Ubah Password</span>
            </button>
            <a href="index.php" class="nav-link text-danger m-1">
                <i class="fas fa-arrow-left me-1"></i> <span class="d-none d-md-inline">Kembali</span>
            </a>
        </div>
    </div>
</div>
        
        <!-- KONTEN KANAN -->
        <div class="col-12 col-md-8 col-lg-9">
            <div class="tab-content">
                
                <!-- TAB INFO PROFIL -->
                <div class="tab-pane fade show active" id="info">
                    <div class="glass-card p-4">
                        <h5 class="fw-bold mb-4">Informasi Profil</h5>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Username</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Email Aktif</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <button type="submit" name="update_profil" class="btn btn-primary w-100 w-md-auto px-4">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
                
                <!-- TAB UBAH PASSWORD -->
                <div class="tab-pane fade" id="security">
                    <div class="glass-card p-4">
                        <h5 class="fw-bold mb-4">Keamanan Akun</h5>
                        <form action="" method="POST" onsubmit="return validasiPassword()">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Password Lama</label>
                                <div class="input-group">
                                    <input type="password" name="old_pass" id="oldPassInput" class="form-control" required placeholder="Masukkan password lama">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('oldPassInput', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Password Baru</label>
                                <div class="input-group">
                                    <input type="password" name="n_pass" id="newPassInput" class="form-control" required minlength="4" placeholder="Minimal 4 karakter" onkeyup="cekPasswordBaru()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassInput', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small id="newPassHelp" class="form-text" style="font-size: 0.75rem;"></small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <input type="password" name="c_pass" id="confirmNewPassInput" class="form-control" required minlength="4" placeholder="Ulangi password" onkeyup="cekKonfirmasiPasswordBaru()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmNewPassInput', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small id="confirmNewHelp" class="form-text" style="font-size: 0.75rem;"></small>
                            </div>
                            <button type="submit" name="update_password" class="btn btn-dark w-100 w-md-auto px-4">Perbarui Password</button>
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- FOOTER SCRIPTS -->
<?php include __DIR__ . '/../includes/footer_scripts.php'; ?>

<script>
// Toggle password
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Cek password baru
function cekPasswordBaru() {
    const pass = document.getElementById('newPassInput').value;
    const help = document.getElementById('newPassHelp');
    if (pass.length === 0) { help.innerHTML = ''; }
    else if (pass.length < 4) { help.innerHTML = '❌ Minimal 4 karakter (' + pass.length + '/4)'; help.style.color = '#dc3545'; }
    else { help.innerHTML = '✅ Password valid (' + pass.length + ' karakter)'; help.style.color = '#28a745'; }
}

// Cek konfirmasi
function cekKonfirmasiPasswordBaru() {
    const pass = document.getElementById('newPassInput').value;
    const confirm = document.getElementById('confirmNewPassInput').value;
    const help = document.getElementById('confirmNewHelp');
    if (confirm.length === 0) { help.innerHTML = ''; }
    else if (pass === confirm) { help.innerHTML = '✅ Password cocok'; help.style.color = '#28a745'; }
    else { help.innerHTML = '❌ Password tidak cocok!'; help.style.color = '#dc3545'; }
}

// Validasi submit
function validasiPassword() {
    const pass = document.getElementById('newPassInput').value;
    const confirm = document.getElementById('confirmNewPassInput').value;
    if (pass !== confirm) {
        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Konfirmasi password tidak cocok!', confirmButtonColor: '#dc3545' });
        return false;
    }
    if (pass.length < 4) {
        Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Password minimal 4 karakter!', confirmButtonColor: '#f39c12' });
        return false;
    }
    return true;
}

// SweetAlert notifikasi
const urlParams = new URLSearchParams(window.location.search);
const pesan = urlParams.get('pesan');
if (pesan === 'email_sukses') {
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Email berhasil diperbarui!', confirmButtonColor: '#4601b4' });
} else if (pesan === 'pass_sukses') {
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Password berhasil diperbarui!', confirmButtonColor: '#4601b4' });
} else if (pesan === 'pass_lama_salah') {
    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Password lama yang Anda masukkan salah!', confirmButtonColor: '#d33' });
} else if (pesan === 'pass_tidak_match') {
    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Konfirmasi password baru tidak cocok!', confirmButtonColor: '#d33' });
} else if (pesan === 'pass_terlalu_pendek') {
    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Password minimal 4 karakter!', confirmButtonColor: '#f39c12' });
}

if (pesan === 'foto_sukses') {
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Foto profil berhasil diperbarui!', confirmButtonColor: '#4601b4' });
} else if (pesan === 'foto_gagal') {
    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Gagal upload foto. Coba lagi.', confirmButtonColor: '#d33' });
} else if (pesan === 'foto_besar') {
    Swal.fire({ icon: 'warning', title: 'Terlalu Besar!', text: 'Ukuran foto maksimal 1MB.', confirmButtonColor: '#f39c12' });
}
</script>

<script>
// Deteksi mobile
function isMobile() {
    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

// Override klik foto profil
document.querySelector('.profile-photo').addEventListener('click', function(e) {
    e.preventDefault();
    
    if (isMobile()) {
        // HP: Tampilkan pilihan
        Swal.fire({
            title: 'Ubah Foto Profil',
            text: 'Pilih sumber foto',
            showDenyButton: true,
            confirmButtonText: '📷 Kamera',
            denyButtonText: '🖼️ Galeri',
            showCancelButton: true,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Kamera
                var input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.capture = 'environment';
                input.onchange = function() {
                    document.getElementById('fotoInput').files = this.files;
                    document.getElementById('fotoForm').submit();
                };
                input.click();
            } else if (result.isDenied) {
                // Galeri
                document.getElementById('fotoInput').click();
            }
        });
    } else {
        // Desktop: langsung pilih file
        document.getElementById('fotoInput').click();
    }
});
</script>
</body>
</html>