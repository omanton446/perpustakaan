<!-- file: register.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - MyLibrary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #4601b4, #121242); height: 100vh; display: flex; align-items: center; }
        .reg-card { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        .form-control { border-radius: 10px; padding: 10px; }
        .password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #6c757d; cursor: pointer; z-index: 5; }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="reg-card">
            <h4 class="fw-bold text-center mb-4">Buat Akun Baru</h4>
            <form action="../proses/proses_register.php" method="POST">
                
                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="username" required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Aktif</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password</label>
                    <div class="position-relative">
                        <input type="password" name="password" id="regPassword" class="form-control" 
                               placeholder="••••••••" required minlength="6" onkeyup="cekPassword()" style="padding-right: 40px;">
                        <button type="button" class="password-toggle" onclick="togglePassword('regPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <!-- Progress Bar -->
                    <div class="progress mt-2" style="height: 6px;">
                        <div id="passwordStrength" class="progress-bar" style="width: 0%; background-color: #dc3545;"></div>
                    </div>
                    <small id="passwordHelp" class="form-text" style="font-size: 0.75rem; color: #6c757d;">
                        ⚠️ Minimal 6 karakter
                    </small>
                </div>

                <!-- Konfirmasi Password -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Konfirmasi Password</label>
                    <div class="position-relative">
                        <input type="password" name="confirm_password" id="confirmPasswordInput" class="form-control" 
                            placeholder="••••••••" required onkeyup="cekKonfirmasiPassword()" style="padding-right: 40px;">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPasswordInput', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small id="confirmHelp" class="form-text" style="font-size: 0.75rem; display: none;"></small>
                </div>

                <button type="submit" name="register" class="btn btn-primary w-100 mb-3" style="border-radius: 10px;">Daftar</button>
                <a href="login.php" class="btn btn-outline-secondary w-100" style="border-radius: 10px;">Kembali ke Login</a>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <?php
    if (isset($_GET['pesan'])) {
        $pesan = $_GET['pesan'];
        if ($pesan === 'email_invalid') {
            echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Format email tidak valid!', confirmButtonColor: '#d33' }); });</script>";
        } elseif ($pesan === 'email_ada') {
            echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'warning', title: 'Gagal!', text: 'Email sudah terdaftar!', confirmButtonColor: '#f39c12' }); });</script>";
        }
    }
    ?>

    <script>
    function cekKonfirmasiPassword() {
        const password = document.getElementById('regPassword').value;
        const confirm = document.getElementById('confirmPasswordInput').value;
        const help = document.getElementById('confirmHelp');
        
        if (confirm.length === 0) {
            help.style.display = 'none';
        } else if (password === confirm) {
            help.style.display = 'block';
            help.innerHTML = '✅ Password cocok';
            help.style.color = '#28a745';
        } else {
            help.style.display = 'block';
            help.innerHTML = '❌ Password tidak cocok!';
            help.style.color = '#dc3545';
        }
    }

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
    </script>
</body>
</html>