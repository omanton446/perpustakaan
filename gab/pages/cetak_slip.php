<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

cekLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data peminjaman
$stmt = mysqli_prepare($conn, 
    "SELECT p.*, b.judul, b.pengarang, b.gambar 
     FROM peminjaman p 
     JOIN buku b ON p.buku_id = b.id 
     WHERE p.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    die("Data tidak ditemukan.");
}

// Hitung denda jika ada
$denda = $data['denda'] ?? 0;
$kondisi = $data['kondisi'] ?? 'baik';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Peminjaman #<?= $id ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Courier New', monospace;
            background: #000000;
            padding: 20px;
        }
        
        .slip {
            max-width: 400px;
            margin: 0 auto;
            border: 2px dashed #333;
            padding: 20px;
            border-radius: 10px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .header h2 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .header .logo {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 0.85rem;
        }
        
        .info-label {
            color: #666;
        }
        
        .info-value {
            font-weight: bold;
            text-align: right;
        }
        
        .divider {
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }
        
        .barcode {
            text-align: center;
            font-size: 0.7rem;
            color: #999;
            margin-top: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .status-dipinjam { background: #fff3cd; color: #856404; }
        .status-kembali { background: #d4edda; color: #155724; }
        .status-rusak { background: #f8d7da; color: #721c24; }
        
        .footer {
            text-align: center;
            font-size: 0.7rem;
            color: #999;
            margin-top: 15px;
        }
        
        .btn-print {
            display: block;
            margin: 20px auto;
            padding: 10px 30px;
            background: #4601b4;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        @media print {
    .no-print { display: none !important; }
    body { padding: 0; }
    .slip { border: 2px dashed #000; }
}
        
        .btn-print {
    padding: 10px 30px;
    background: #4601b4;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
}

.btn-print:hover {
    opacity: 0.9;
}

@media print {
    .btn-print { display: none; }
}
@media screen {
    .slip {
        background: white;
        color: #333;
        max-width: 400px;
        margin: 0 auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.3);
    }
}

@media print {
    .no-print { display: none !important; }
    body { 
        padding: 0; 
        background: white !important;
        color: black !important;
    }
    .slip { 
        border: 2px dashed #000;
        background: white !important;
        box-shadow: none !important;
    }
    .info-label { color: #555 !important; }
    .info-value { color: #000 !important; }
}
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button class="btn-print" onclick="window.print()" style="margin-right: 10px;">🖨️ Cetak Slip</button>
    <a href="riwayat_pinjam.php" class="btn-print" style="background: #6c757d; text-decoration: none; display: inline-block;">← Kembali</a>
</div>

<div class="slip">
    <!-- Header -->
    <div class="header">
        <div class="logo">📚</div>
        <h2>MyLibrary</h2>
        <small>Slip Peminjaman Buku</small>
    </div>
    
    <!-- Info Peminjaman -->
    <div class="info-row">
        <span class="info-label">No. Transaksi</span>
        <span class="info-value">#PK<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Tanggal</span>
        <span class="info-value"><?= date('d/m/Y H:i') ?></span>
    </div>
    
    <div class="divider"></div>
    
    <!-- Info Buku -->
    <div class="info-row">
        <span class="info-label">Judul Buku</span>
        <span class="info-value"><?= htmlspecialchars($data['judul']) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Pengarang</span>
        <span class="info-value"><?= htmlspecialchars($data['pengarang']) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Jumlah</span>
        <span class="info-value"><?= $data['jumlah_pinjam'] ?> buku</span>
    </div>
    
    <div class="divider"></div>
    
    <!-- Info Peminjam & Tanggal -->
    <div class="info-row">
        <span class="info-label">Peminjam</span>
        <span class="info-value"><?= htmlspecialchars($data['nama_peminjam']) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Tgl Pinjam</span>
        <span class="info-value"><?= date('d/m/Y', strtotime($data['tgl_pinjam'])) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Tgl Kembali</span>
        <span class="info-value"><?= date('d/m/Y', strtotime($data['tgl_kembali'])) ?></span>
    </div>
    
    <div class="divider"></div>
    
    <!-- Status -->
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value">
            <?php 
            $statusClass = 'status-dipinjam';
            if (strpos($data['status'], 'Kembali') !== false) {
                $statusClass = strpos($data['status'], 'Rusak') !== false ? 'status-rusak' : 'status-kembali';
            }
            ?>
            <span class="status-badge <?= $statusClass ?>"><?= $data['status'] ?></span>
        </span>
    </div>
    
    <?php if ($denda > 0): ?>
    <div class="info-row" style="color: #dc3545;">
        <span class="info-label">Denda</span>
        <span class="info-value">Rp <?= number_format($denda, 0, ',', '.') ?></span>
    </div>
    <?php endif; ?>
    
    <!-- Barcode -->
    <div class="barcode">
        ||||||||||||||||||||||||||||||<br>
        PK<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?><br>
        <?= date('Ymd') ?>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        MyLibrary &copy; <?= date('Y') ?> | Jam Operasional 06:00 - 23:00<br>
        Terima kasih telah menggunakan layanan kami
    </div>
</div>

<script>
// Auto print saat halaman dibuka
window.onload = function() {
    // Tunda sebentar agar konten termuat
    setTimeout(function() {
        window.print();
    }, 500);
};
</script>

</body>
</html>