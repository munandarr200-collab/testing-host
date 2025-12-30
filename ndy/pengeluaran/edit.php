<?php
require_once __DIR__ . "/../sistem/koneksi.php";
$hub = open_connection();

$id = $_GET['id'];
$data = mysqli_fetch_assoc(
    mysqli_query($hub, "SELECT * FROM pengeluaran WHERE id='$id'")
);

if (isset($_POST['update'])) {
    $jumlah   = $_POST['jumlah'];
    $tanggal  = $_POST['tanggal'];
    $kategori = $_POST['kategori'];
    $metode   = $_POST['metode'];
    $ket      = $_POST['keterangan'];

    $query = "UPDATE pengeluaran SET
                jumlah='$jumlah',
                tanggal='$tanggal',
                kategori='$kategori',
                metode_bayar='$metode',
                keterangan='$ket'
              WHERE id='$id'";

    mysqli_query($hub, $query);
    header("Location: png_1.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Pengeluaran</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            margin-bottom: 30px;
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
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
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
            border-color: #f5576c;
            background: white;
            box-shadow: 0 0 0 4px rgba(245, 87, 108, 0.1);
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
            gap: 12px;
            margin-top: 8px;
        }
        
        button {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        button[type="submit"] {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
        }
        
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.5);
        }
        
        button[type="submit"]:active {
            transform: translateY(0);
        }
        
        .cancel-btn {
            background: #f3f4f6;
            color: #374151;
        }
        
        .cancel-btn:hover {
            background: #e5e7eb;
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
        
        .kategori-pill.active {
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.2) 0%, rgba(245, 87, 108, 0.2) 100%);
            border-color: #f5576c;
            color: #f5576c;
            font-weight: 600;
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
            border-color: #f5576c;
            background: #fff5f7;
        }
        
        .metode-option.active {
            border-color: #f5576c;
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.1) 100%);
            color: #f5576c;
            font-weight: 600;
        }
        
        .info-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        @media (max-width: 480px) {
            form {
                padding: 25px;
            }
            
            .header h2 {
                font-size: 24px;
            }
            
            .metode-grid {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column-reverse;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="png_1.php" class="back-button">
            ← Kembali ke Daftar
        </a>
        
        <div class="header">
            <h2>✏️ Edit Pengeluaran</h2>
            <p>Perbarui data pengeluaran Anda</p>
        </div>
        
        <form method="post">
            <div class="info-badge">
                📋 ID Transaksi: #<?= $id; ?>
            </div>
            
            <div class="form-group">
                <label>💰 Jumlah</label>
                <div class="input-wrapper">
                    <span class="currency-symbol">Rp</span>
                    <input type="number" name="jumlah" value="<?= $data['jumlah']; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>📅 Tanggal</label>
                <input type="date" name="tanggal" value="<?= $data['tanggal']; ?>" required>
            </div>

            <div class="form-group">
                <label>🏷️ Kategori</label>
                <div class="kategori-pills">
                    <span class="kategori-pill <?= $data['kategori'] == 'Makanan & Minuman' ? 'active' : ''; ?>" onclick="setKategori('Makanan & Minuman')">🍔 Makanan</span>
                    <span class="kategori-pill <?= $data['kategori'] == 'Transport' ? 'active' : ''; ?>" onclick="setKategori('Transport')">🚗 Transport</span>
                    <span class="kategori-pill <?= $data['kategori'] == 'Belanja' ? 'active' : ''; ?>" onclick="setKategori('Belanja')">🛒 Belanja</span>
                    <span class="kategori-pill <?= $data['kategori'] == 'Tagihan' ? 'active' : ''; ?>" onclick="setKategori('Tagihan')">📱 Tagihan</span>
                    <span class="kategori-pill <?= $data['kategori'] == 'Kesehatan' ? 'active' : ''; ?>" onclick="setKategori('Kesehatan')">🏥 Kesehatan</span>
                    <span class="kategori-pill <?= $data['kategori'] == 'Hiburan' ? 'active' : ''; ?>" onclick="setKategori('Hiburan')">🎬 Hiburan</span>
                </div>
                <input type="text" name="kategori" value="<?= $data['kategori']; ?>" id="kategoriInput">
            </div>

            <div class="form-group">
                <label>💳 Metode Pembayaran</label>
                <select name="metode" style="display:none;" id="metodeSelect">
                    <?php
                    $opsi = ['Tunai','Kartu Debit','Kartu Kredit','E-Wallet','Transfer'];
                    foreach ($opsi as $o) {
                        $sel = ($data['metode_bayar'] == $o) ? 'selected' : '';
                        echo "<option $sel>$o</option>";
                    }
                    ?>
                </select>
                <div class="metode-grid">
                    <div class="metode-option <?= $data['metode_bayar'] == 'Tunai' ? 'active' : ''; ?>" onclick="setMetode('Tunai', this)">💵 Tunai</div>
                    <div class="metode-option <?= $data['metode_bayar'] == 'Kartu Debit' ? 'active' : ''; ?>" onclick="setMetode('Kartu Debit', this)">💳 Kartu Debit</div>
                    <div class="metode-option <?= $data['metode_bayar'] == 'Kartu Kredit' ? 'active' : ''; ?>" onclick="setMetode('Kartu Kredit', this)">💳 Kartu Kredit</div>
                    <div class="metode-option <?= $data['metode_bayar'] == 'E-Wallet' ? 'active' : ''; ?>" onclick="setMetode('E-Wallet', this)">📱 E-Wallet</div>
                    <div class="metode-option <?= $data['metode_bayar'] == 'Transfer' ? 'active' : ''; ?>" onclick="setMetode('Transfer', this)">🏦 Transfer</div>
                </div>
            </div>

            <div class="form-group">
                <label>📝 Keterangan</label>
                <textarea name="keterangan" placeholder="Tambahkan catatan (opsional)"><?= $data['keterangan']; ?></textarea>
            </div>

            <div class="button-group">
                <button type="button" class="cancel-btn" onclick="window.location.href='png_1.php'">
                    ❌ Batal
                </button>
                <button type="submit" name="update">
                    ✅ Update Data
                </button>
            </div>
        </form>
    </div>
    
    <script>
        function setKategori(kategori) {
            document.getElementById('kategoriInput').value = kategori;
            
            // Update active state
            document.querySelectorAll('.kategori-pill').forEach(pill => {
                pill.classList.remove('active');
            });
            event.target.classList.add('active');
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
    </script>
</body>
</html>