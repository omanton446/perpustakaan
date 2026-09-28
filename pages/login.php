<?php
session_start();
include '../config/koneksi.php';

// Jika sudah login, langsung lempar ke index
if (isset($_SESSION['admin'])) {
    if ($_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
} else {
    header("Location: index.php");
}
    exit();
}

$error = null;

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin'] = $user['username'];
            $_SESSION['role']  = $user['peran']; 
            header("Location: index.php");
            exit();
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak terdaftar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MyLibrary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #4601b4, #d2d1ff, #121242);
            height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .form-control {
            padding: 12px;
            border-radius: 10px;
        }
        .btn-primary {
            padding: 12px;
            border-radius: 10px;
            background: #4601b4;
            border: none;
        }
        .btn-primary:hover {
            background: #35018a;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0;
        }
        .password-toggle:hover {
            color: #4601b4;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card">
        <h3 class="text-center fw-bold mb-2">MyLibrary</h3>
        <p class="text-center text-muted mb-4">Silakan masuk ke akun Anda</p>
        
        <form method="POST" action="login.php" autocomplete="off">
            <!-- Username -->
            <div class="mb-3">
                <label class="form-label small fw-bold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            
            <!-- Password -->
            <div class="mb-3">
    <label class="form-label small fw-bold">Password</label>
    <div class="position-relative"> <!-- ✅ Gunakan position-relative -->
        <input type="password" name="password" id="loginPassword" class="form-control" 
               placeholder="••••••••" required style="padding-right: 40px;">
        <button type="button" onclick="togglePassword('loginPassword', this)" 
                class="password-toggle" style="z-index: 5;"> <!-- ✅ Tambah z-index -->
            <i class="fas fa-eye"></i>
        </button>
    </div>
</div>
            
            <button type="submit" name="login" class="btn btn-primary w-100 fw-bold">Masuk Sekarang</button>
        </form>

        <div class="text-center mt-4">
            <p class="small mb-0 text-muted">Belum punya akun?</p>
            <a href="register.php" class="text-decoration-none fw-bold" style="color: #4601b4;">Daftar Akun Baru</a>
        </div> 
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($error): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Masuk',
            text: '<?= addslashes($error) ?>',
            confirmButtonColor: '#4601b4',
            allowOutsideClick: true,
            allowEscapeKey: true
        });
    });
</script>
<?php endif; ?>

<script>
    // Cek pesan sukses registrasi
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('pesan') && urlParams.get('pesan') === 'reg_sukses') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Akun terdaftar. Silakan login.',
                confirmButtonColor: '#4601b4',
                allowOutsideClick: true,
                allowEscapeKey: true
            });
        }
    });

    // Toggle password visibility
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