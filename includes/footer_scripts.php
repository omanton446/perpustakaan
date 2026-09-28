<footer class="mt-5 py-3 text-center text-muted" style="font-size: 0.8rem; border-top: 1px solid #e0e0e0;">
    <div class="container">
        <i class="fas fa-book-reader me-1"></i> MyLibrary &copy; <?= date('Y') ?> | 
        Jam Operasional: 06:00 - 23:00 WIB
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ========== DARK MODE ==========
(function() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
})();

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    const sw = document.getElementById('darkModeSwitch');
    const icon = document.getElementById('modeIcon');
    
    if (sw) sw.checked = isDark;
    
    // ✅ UBAH IKON
    if (icon) {
        if (isDark) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    }
    
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

// ✅ Update ikon saat halaman dimuat
(function() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        const icon = document.getElementById('modeIcon');
        if (icon) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    }
})();

function isDarkMode() {
    return document.body.classList.contains('dark-mode');
}

// ========== OVERRIDE GLOBAL SWEETALERT ==========
// Semua Swal.fire() akan otomatis pakai tema ini
const originalSwalFire = Swal.fire.bind(Swal);

Swal.fire = function(options) {
    const isDark = isDarkMode();
    
    // Default options jika tidak diset
    const defaultOptions = {
        background: isDark ? '#1a1a2e' : '#ffffff',
        color: isDark ? '#ffffff' : '#545454',
        confirmButtonColor: '#4601b4',
        cancelButtonColor: '#6c757d'
    };
    
    // Gabungkan dengan options yang dikirim
    const mergedOptions = { ...defaultOptions, ...options };
    
    return originalSwalFire(mergedOptions);
};

// ========== LOGOUT ==========
function konfirmasiKeluar(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Ingin Keluar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal'
    }).then(r => { if (r.isConfirmed) window.location.href = '../proses/logout.php'; });
}

// Perbaiki semua gambar yang error
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('img').forEach(function(img) {
        img.onerror = function() {
            this.src = 'data:image/svg+xml,' + encodeURIComponent(
                '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="140">' +
                '<rect fill="#e9ecef" width="100" height="140" rx="8"/>' +
                '<text x="50%" y="50%" fill="#6c757d" font-size="12" font-family="Arial" text-anchor="middle" dy=".3em">No Cover</text>' +
                '</svg>'
            );
        };
    });
});

// Scroll to Top Button
window.addEventListener('scroll', function() {
    var btn = document.getElementById('scrollTopBtn');
    if (btn) {
        btn.style.display = window.scrollY > 300 ? 'block' : 'none';
    }
});

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<script>
// Sembunyikan loading screen setelah halaman selesai dimuat
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('loadingScreen').classList.add('hidden');
    }, 100); // 0.1 detik
});
</script>

