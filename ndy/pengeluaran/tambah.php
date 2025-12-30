<?php
require_once __DIR__ . "/../sistem/koneksi.php";
$hub = open_connection();

// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['simpan'])) {
    $jumlah   = $_POST['jumlah'];
    $tanggal  = $_POST['tanggal'];
    $kategori = $_POST['kategori'];
    $metode   = $_POST['metode'];
    $ket      = $_POST['keterangan'];
    $query = "INSERT INTO pengeluaran 
              (jumlah, tanggal, kategori, metode_bayar, keterangan)
              VALUES 
              ('$jumlah', '$tanggal', '$kategori', '$metode', '$ket')";
    if (mysqli_query($hub, $query)) {
        header("Location: png_1.php");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($hub);
    }
}

// Format tanggal dan waktu
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$nama_hari = $hari[date('w')];
$tanggal_sekarang = date('d') . ' ' . $nama_bulan[date('m')] . ' ' . date('Y');
$waktu_sekarang = date('H:i') . ' WIB';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Pengeluaran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 480px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            color: white;
        }
        
        .header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .datetime-badge {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            color: white;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .datetime-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .datetime-divider {
            width: 1px;
            height: 16px;
            background: rgba(255, 255, 255, 0.3);
        }
        
        form {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        input[type="number"],
        input[type="date"],
        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-family: 'Inter', Arial, sans-serif;
            background: #f9fafb;
        }
        
        input[type="number"]:focus,
        input[type="date"]:focus,
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        input[type="number"] {
            padding-left: 45px;
        }
        
        .currency-symbol {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 600;
            color: #6b7280;
            font-size: 15px;
        }
        
        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
            padding-right: 45px;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
            font-size: 14px;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
        
        button,
        .btn-cancel {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        button[type="submit"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .btn-cancel {
            background: #f3f4f6;
            color: #6b7280;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .btn-cancel:hover {
            background: #e5e7eb;
            color: #374151;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        button:active,
        .btn-cancel:active {
            transform: translateY(0);
        }
        
        .kategori-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        
        .kategori-pill {
            padding: 6px 14px;
            background: #f3f4f6;
            border: 2px solid transparent;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #6b7280;
            font-weight: 500;
        }
        
        .kategori-pill:hover {
            background: #e5e7eb;
            color: #374151;
        }
        
        .metode-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 8px;
        }
        
        .metode-option {
            padding: 12px;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            color: #374151;
        }
        
        .metode-option:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .metode-option.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            color: #667eea;
            font-weight: 600;
        }
        
        @media (max-width: 480px) {
            form {
                padding: 25px;
            }
            
            .header h2 {
                font-size: 24px;
            }
            
            .datetime-badge {
                flex-direction: column;
                gap: 8px;
                padding: 12px 16px;
            }
            
            .datetime-divider {
                display: none;
            }
            
            .metode-grid {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column-reverse;
            }
            
            button,
            .btn-cancel {
                width: 100%;
            }
        }
        
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>💸 Tambah Pengeluaran</h2>
            <p>Catat pengeluaran Anda dengan mudah</p>
            <div class="datetime-badge">
                <div class="datetime-item">
                    📅 <?= $nama_hari ?>, <?= $tanggal_sekarang ?>
                </div>
                <div class="datetime-divider"></div>
                <div class="datetime-item">
                    🕐 <?= $waktu_sekarang ?>
                </div>
            </div>
        </div>
        
        <form method="post" id="expenseForm">
            <div class="form-group">
                <label>💰 Jumlah</label>
                <div class="input-wrapper">
                    <span class="currency-symbol">Rp</span>
                    <input type="number" name="jumlah" required placeholder="0">
                </div>
            </div>
            
            <div class="form-group">
                <label>📅 Tanggal</label>
                <input type="date" name="tanggal" required id="tanggalInput">
            </div>
            
            <div class="form-group">
                <label>🏷️ Kategori</label>
                <div class="kategori-pills">
                    <span class="kategori-pill" onclick="setKategori('Makanan & Minuman')">🍔 Makanan</span>
                    <span class="kategori-pill" onclick="setKategori('Transport')">🚗 Transport</span>
                    <span class="kategori-pill" onclick="setKategori('Belanja')">🛒 Belanja</span>
                    <span class="kategori-pill" onclick="setKategori('Tagihan')">📱 Tagihan</span>
                    <span class="kategori-pill" onclick="setKategori('Kesehatan')">🏥 Kesehatan</span>
                    <span class="kategori-pill" onclick="setKategori('Hiburan')">🎬 Hiburan</span>
                </div>
                <input type="text" name="kategori" value="Lainnya" id="kategoriInput">
            </div>
            
            <div class="form-group">
                <label>💳 Metode Pembayaran</label>
                <select name="metode" style="display:none;" id="metodeSelect">
                    <option>Tunai</option>
                    <option>Kartu Debit</option>
                    <option>Kartu Kredit</option>
                    <option>E-Wallet</option>
                    <option>Transfer</option>
                </select>
                <div class="metode-grid">
                    <div class="metode-option active" onclick="setMetode('Tunai', this)">💵 Tunai</div>
                    <div class="metode-option" onclick="setMetode('Kartu Debit', this)">💳 Kartu Debit</div>
                    <div class="metode-option" onclick="setMetode('Kartu Kredit', this)">💳 Kartu Kredit</div>
                    <div class="metode-option" onclick="setMetode('E-Wallet', this)">📱 E-Wallet</div>
                    <div class="metode-option" onclick="setMetode('Transfer', this)">🏦 Transfer</div>
                </div>
            </div>
            
            <div class="form-group">
                <label>📝 Keterangan</label>
                <textarea name="keterangan" placeholder="Tambahkan catatan (opsional)"></textarea>
            </div>
            
            <div class="button-group">
                <a href="png_1.php" class="btn-cancel">
                    ❌ Batal
                </a>
                <button type="submit" name="simpan">
                    ✅ Simpan
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // Set tanggal hari ini sebagai default
        document.getElementById('tanggalInput').valueAsDate = new Date();
        
        function setKategori(kategori) {
            document.getElementById('kategoriInput').value = kategori;
        }
        
        function setMetode(metode, element) {
            // Update select value
            document.getElementById('metodeSelect').value = metode;
            
            // Update active state
            document.querySelectorAll('.metode-option').forEach(opt => {
                opt.classList.remove('active');
            });
            element.classList.add('active');
        }
        
        // Format input jumlah dengan separator ribuan
        const jumlahInput = document.querySelector('input[name="jumlah"]');
        jumlahInput.addEventListener('input', function(e) {
            // Remove non-digit characters for processing
            let value = this.value.replace(/\D/g, '');
            // Format with thousand separator for display
            this.value = value;
        });
    </script>
</body>
</html>