// script.js

// 1. Fungsi Tampil Detail dengan Tombol Pinjam
function tampilDetail(judul, pengarang, kategori, stok, imgPath, deskripsi, id) {
    let footerContent = '';
    
    if (typeof userRole !== 'undefined' && userRole === 'peminjam') {
        footerContent = `
            <a href="form_pinjam.php?id=${id}" class="btn btn-primary">
                <i class="fas fa-shopping-cart me-2"></i> Pinjam Buku
            </a>
            <button class="btn btn-secondary" onclick="Swal.close()">Tutup</button>
        `;
    } else {
        footerContent = `<button class="btn btn-secondary" onclick="Swal.close()">Tutup</button>`;
    }

    Swal.fire({
        title: 'Detail Buku',
        html: `
            <div class="text-center">
                <img src="${imgPath}" class="rounded shadow-sm mb-3" style="width: 160px; height: 230px; object-fit: cover; border: 2px solid #eee;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='75' height='110'%3E%3Crect fill='%23e9ecef' width='75' height='110'/%3E%3Ctext x='50%25' y='50%25' fill='%236c757d' font-size='10' font-family='Arial' text-anchor='middle' dy='.3em'%3ENo Cover%3C/text%3E%3C/svg%3E'">
                <h3 class="fw-bold mb-1">${judul}</h3>
                <p class="text-muted small mb-3">Karya: ${pengarang}</p>
                <div class="mb-3">
                    <span class="badge bg-primary px-3">${kategori}</span>
                    <span class="badge bg-success px-3">Stok: ${stok}</span>
                </div>
                <hr>
                <div class="p-2" style="text-align: justify; font-size: 0.85rem; color: #555; max-height: 120px; overflow-y: auto;">
                    ${deskripsi ? deskripsi : '<i>Tidak ada deskripsi tersedia.</i>'}
                </div>
            </div>
        `,
        showConfirmButton: false,
        footer: footerContent,
        width: '400px',
        allowOutsideClick: true,
        allowEscapeKey: true
    });
}

// 2. Fungsi Hapus Buku
// Fungsi hapus buku dengan konfirmasi SweetAlert
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

// 3. Alert Otomatis Berdasarkan URL
(function() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAlerts);
    } else {
        initAlerts();
    }
    
    function initAlerts() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('pesan')) {
            const pesan = urlParams.get('pesan');
            let config = {};

            switch (pesan) {
                case 'tambah_sukses':
                case 'update_sukses':
                case 'sukses_pinjam':
                case 'kembali_sukses':
                    config = { icon: 'success', title: 'Berhasil!', text: 'Operasi berhasil dilakukan.' };
                    break;
                case 'hapus_sukses':
                    config = { icon: 'warning', title: 'Dihapus!', text: 'Buku telah dihapus.' };
                    break;
                case 'akses_ditolak':
                    config = { icon: 'error', title: 'Ditolak!', text: 'Anda tidak memiliki akses.' };
                    break;
                case 'stok_kurang':
                    config = { icon: 'warning', title: 'Gagal!', text: 'Stok buku tidak mencukupi.' };
                    break;
                case 'masih_pinjam':  
                    config = { icon: 'warning', title: 'Tidak Bisa!', text: 'Anda masih memiliki pinjaman aktif untuk buku ini. Kembalikan dulu sebelum meminjam lagi.' };
                    break;
                    case 'batas_pinjam':
    config = { icon: 'warning', title: 'Batas Maksimal!', text: 'Anda sudah meminjam 3 buku. Kembalikan dulu sebelum meminjam lagi.' };
    break;
                case 'tanggal_invalid':  
                    config = { icon: 'error', title: 'Error!', text: 'Tanggal kembali tidak boleh sebelum tanggal pinjam.' };
                    break;
                    case 'diluar_jam':
    config = { icon: 'warning', title: 'Di Luar Jam Operasional!', text: 'Peminjaman hanya bisa dilakukan pukul 09:00 - 23:00.' };
    break;
                case 'gagal':
                case 'gagal_pinjam':
                    config = { icon: 'error', title: 'Error!', text: 'Terjadi kesalahan sistem.' };
                    break;
            }

            if (config.icon) {
                Swal.fire({ 
                    ...config, 
                    timer: 2000, 
                    showConfirmButton: false,
                    allowOutsideClick: true,
                    allowEscapeKey: true
                });
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        }
    }
})();