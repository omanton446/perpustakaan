<?php
// Pastikan variabel $username_login, $role, $lang, $text sudah ada
if (!isset($username_login)) $username_login = $_SESSION['admin'];
if (!isset($role)) $role = $_SESSION['role'];
if (!isset($lang)) $lang = $_SESSION['lang'] ?? 'id';

// Jika $text belum di-set, load dari bahasa.php
if (!isset($text)) {
    require_once __DIR__ . '/bahasa.php';
    $text = $translations[$lang] ?? $translations['id'];
}
?>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-2 mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-book-reader me-2"></i>MyLibrary
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link px-3 fw-medium" href="index.php">
                        <i class="fas fa-home me-1"></i><?= $text['menu_beranda'] ?? 'Beranda' ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 fw-medium" href="riwayat_pinjam.php">
                        <i class="fas fa-history me-1"></i><?= $text['menu_riwayat'] ?? 'Riwayat' ?>
                    </a>
                </li>
                <?php if ($role === 'admin') : ?>
<li class="nav-item">
    <a class="nav-link px-3 fw-medium" href="dashboard.php">
        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
    </a>
</li>
<li class="nav-item">
    <a class="nav-link px-3 fw-medium" href="tambah_buku.php">
        <i class="fas fa-plus-circle me-1"></i><?= $text['menu_tambah'] ?? 'Tambah' ?>
    </a>
</li>
<?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a class="text-white text-decoration-none px-2" href="#" onclick="toggleDarkMode()" title="Mode Gelap" id="darkModeIcon">
    <i class="fas fa-moon" id="modeIcon"></i>
</a>
                <div class="dropdown">
                    <a class="d-flex align-items-center text-white text-decoration-none bg-white bg-opacity-10 rounded-pill px-3 py-1" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <span class="small fw-bold"><?= htmlspecialchars($username_login) ?></span>
                        <i class="fas fa-chevron-down ms-2 small"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="min-width: 200px;">
                        <li class="px-3 py-2">
                            <small class="text-muted"><?= $text['login_sebagai'] ?? 'Login sebagai' ?></small>
                            <div class="fw-bold text-uppercase small"><?= htmlspecialchars($role) ?></div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded-3 py-2" href="profil.php"><?= $text['menu_profil'] ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3 py-1"><small class="text-muted text-uppercase"><?= $text['menu_bahasa'] ?></small></li>
                        <li><a class="dropdown-item" href="?lang=id">🇮🇩 Indonesia</a></li>
                        <li><a class="dropdown-item" href="?lang=en">🇬🇧 English</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div class="dropdown-item d-flex justify-content-between" onclick="toggleDarkMode()" style="cursor:pointer;">
                                <?= $text['menu_gelap'] ?>
                                <input class="form-check-input" type="checkbox" id="darkModeSwitch">
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../proses/logout.php" onclick="konfirmasiKeluar(event)"><?= $text['menu_keluar'] ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>