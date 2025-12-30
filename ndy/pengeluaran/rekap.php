<?php
require_once __DIR__ . "/../sistem/koneksi.php";
$hub = open_connection();

// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$query = "
    SELECT *
    FROM pengeluaran
    WHERE MONTH(tanggal) = '$bulan'
      AND YEAR(tanggal) = '$tahun'
    ORDER BY tanggal DESC, id DESC
";
$result = mysqli_query($hub, $query);

// hitung total
$q_total = "
    SELECT SUM(jumlah) AS total
    FROM pengeluaran
    WHERE MONTH(tanggal) = '$bulan'
      AND YEAR(tanggal) = '$tahun'
";
$total = mysqli_fetch_assoc(mysqli_query($hub, $q_total))['total'] ?? 0;

// === Tambahkan ini: Hitung total bulan lalu ===
$bulan_lalu = $bulan == '01' ? '12' : str_pad((int)$bulan - 1, 2, '0', STR_PAD_LEFT);
$tahun_lalu = $bulan == '01' ? (int)$tahun - 1 : $tahun;

$q_bulan_lalu = "
    SELECT SUM(jumlah) AS total
    FROM pengeluaran
    WHERE MONTH(tanggal) = '$bulan_lalu'
      AND YEAR(tanggal) = '$tahun_lalu'
";
$total_bulan_lalu = mysqli_fetch_assoc(mysqli_query($hub, $q_bulan_lalu))['total'] ?? 0;
$total_bulan_ini = $total;

// Hitung selisih dan persentase
$selisih = $total_bulan_ini - $total_bulan_lalu;
if ($total_bulan_lalu > 0) {
    $persen = round(($selisih / $total_bulan_lalu) * 100, 1);
} else {
    $persen = $total_bulan_ini > 0 ? 100 : 0;
}

// Tentukan warna dan arah
if ($selisih > 0) {
    $trend = 'naik';
    $arrow = '↗️';
    $color = '#ef4444'; // merah
} elseif ($selisih < 0) {
    $trend = 'turun';
    $arrow = '↘️';
    $color = '#10b981'; // hijau
} else {
    $trend = 'stabil';
    $arrow = '➡️';
    $color = '#6b7280'; // abu-abu
}

// Nama bulan Indonesia
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Greeting berdasarkan waktu
$jam = (int)date('H');
if ($jam >= 4 && $jam < 11) {
    $salam = "Selamat Pagi";
    $emoji = "🌅";
} elseif ($jam >= 11 && $jam < 15) {
    $salam = "Selamat Siang";
    $emoji = "☀️";
} elseif ($jam >= 15 && $jam < 19) {
    $salam = "Selamat Sore";
    $emoji = "🌤️";
} else {
    $salam = "Selamat Malam";
    $emoji = "🌙";
}

// Format tanggal dan waktu
$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$nama_hari = $hari[date('w')];
$tanggal_sekarang = date('d') . ' ' . $nama_bulan[date('m')] . ' ' . date('Y');
$waktu_sekarang = date('H:i') . ' WIB';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pengeluaran Bulanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }
        
        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
            z-index: 0;
            pointer-events: none;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        /* Header Section */
        .header-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 35px 40px;
            margin-bottom: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .greeting-box {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 16px;
            border-left: 5px solid #667eea;
        }
        
        .greeting-emoji {
            font-size: 40px;
            animation: wave 2s ease-in-out infinite;
        }
        
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }
        
        .greeting-text h3 {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .greeting-text p {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .datetime-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 13px;
            color: #667eea;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .datetime-item {
            display: flex;
            align-items: center;
            gap: 5px;
            background: white;
            padding: 5px 12px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .page-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Filter Section */
        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .filter-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            flex: 1;
            min-width: 150px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        select {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: #f9fafb;
            font-family: 'Inter', Arial, sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
            padding-right: 45px;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        button[type="submit"] {
            padding: 12px 30px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }
        
        /* Stats Card - DIUBAH KE BIRU */
        .stats-card {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.3);
            color: white;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .stats-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .stats-icon {
            font-size: 40px;
        }
        
        .stats-info h4 {
            font-size: 14px;
            font-weight: 600;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .stats-period {
            font-size: 20px;
            font-weight: 700;
        }
        
        .stats-amount {
            font-size: 36px;
            font-weight: 800;
            margin-top: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        /* === BARU: Comparison Card === */
        .comparison-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.6s ease-out;
            border-top: 4px solid #667eea;
        }

        .comparison-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .comparison-item {
            text-align: center;
            padding: 20px;
            border-radius: 16px;
            background: #f9fafb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .comparison-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .comparison-value {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
        }

        .change-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px dashed #e5e7eb;
            text-align: center;
        }

        .change-text {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .change-amount {
            font-size: 22px;
            font-weight: 800;
        }

        /* Table Section */
        .table-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
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
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .data-count {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            color: #667eea;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }
        
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        tr:hover {
            background: #f9fafb;
            transition: all 0.2s ease;
        }
        
        td {
            font-size: 14px;
            color: #374151;
            font-weight: 500;
        }
        
        .rupiah {
            text-align: right;
            font-weight: 700;
            color: #dc2626;
            font-size: 15px;
        }
        
        .kategori-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #f3f4f6;
            color: #4b5563;
        }
        
        .metode-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            color: #2563eb;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-text {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-subtext {
            font-size: 14px;
            color: #9ca3af;
        }
        
        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            transform: translateX(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Print Button Style */
        #printButton {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            margin-left: 10px;
        }

        #printButton:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        /* Styling untuk pencetakan */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .header-section,
            .filter-card,
            .stats-card,
            .comparison-card,
            .back-button,
            #printButton {
                display: none !important;
            }
            .table-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                background: white !important;
                padding: 20px !important;
            }
            .table-wrapper {
                overflow: visible !important;
            }
            table {
                font-size: 10pt !important;
            }
            th, td {
                padding: 6px !important;
                border: 1px solid #999 !important;
            }
            .rupiah {
                color: black !important;
            }
            .money-icon {
                display: none !important;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0;
            }
            
            .header-section,
            .filter-card,
            .table-card,
            .stats-card,
            .comparison-card {
                border-radius: 16px;
                padding: 20px;
            }
            
            .page-title {
                font-size: 22px;
            }
            
            .greeting-text h3 {
                font-size: 20px;
            }
            
            .datetime-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .datetime-item {
                font-size: 12px;
            }
            
            form {
                flex-direction: column;
            }
            
            .form-group {
                width: 100%;
            }
            
            button[type="submit"] {
                width: 100%;
                justify-content: center;
            }
            
            .stats-amount {
                font-size: 28px;
            }
            
            .table-wrapper {
                margin: 0 -20px;
                border-radius: 0;
            }
            
            th, td {
                padding: 12px 8px;
                font-size: 12px;
            }
            
            .comparison-grid {
                grid-template-columns: 1fr;
            }
            
            .comparison-value {
                font-size: 20px;
            }
            
            .change-amount {
                font-size: 20px;
            }
        }
        
        /* Money floating animation */
        @keyframes float-money {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-10px) rotate(5deg); }
            75% { transform: translateY(10px) rotate(-5deg); }
        }
        
        .money-icon {
            animation: float-money 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="greeting-box">
                <div class="greeting-emoji"><?= $emoji ?></div>
                <div class="greeting-text">
                    <h3><?= $salam ?>! 👋</h3>
                    <p>Selamat datang di sistem rekap pengeluaran Anda</p>
                    <div class="datetime-info">
                        <div class="datetime-item">
                            📅 <?= $nama_hari ?>, <?= $tanggal_sekarang ?>
                        </div>
                        <div class="datetime-item">
                            🕐 <?= $waktu_sekarang ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <h1 class="page-title">
                <span class="money-icon">💰</span> Rekap Pengeluaran Bulanan
            </h1>
            <p class="page-subtitle">Kelola dan pantau pengeluaran Anda dengan mudah</p>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <div class="filter-title">
                🔍 Filter Data
            </div>
            <form method="get">
                <div class="form-group">
                    <label class="form-label">📅 Bulan</label>
                    <select name="bulan">
                        <?php
                        for ($b = 1; $b <= 12; $b++) {
                            $val = str_pad($b, 2, '0', STR_PAD_LEFT);
                            $sel = ($val == $bulan) ? 'selected' : '';
                            echo "<option value='$val' $sel>{$nama_bulan[$val]}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">📆 Tahun</label>
                    <select name="tahun">
                        <?php
                        for ($t = date('Y') - 5; $t <= date('Y'); $t++) {
                            $sel = ($t == $tahun) ? 'selected' : '';
                            echo "<option $sel>$t</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit">
                    <span>✨</span>
                    <span>Tampilkan</span>
                </button>
            </form>
        </div>

        <!-- Stats Card -->
        <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="stats-card">
            <div class="stats-header">
                <div class="stats-icon">💵</div>
                <div class="stats-info">
                    <h4>Total Pengeluaran</h4>
                    <div class="stats-period"><?= $nama_bulan[$bulan] ?> <?= $tahun ?></div>
                </div>
            </div>
            <div class="stats-amount">
                Rp <?= number_format($total,0,',','.'); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === BARU: Comparison Card === -->
        <?php if (mysqli_num_rows($result) > 0 || $total_bulan_lalu > 0): ?>
        <div class="comparison-card">
            <div class="comparison-title">
                📊 Perbandingan Pengeluaran
            </div>
            
            <div class="comparison-grid">
                <div class="comparison-item">
                    <div class="comparison-label">Bulan Ini (<?= $nama_bulan[$bulan] ?> <?= $tahun ?>)</div>
                    <div class="comparison-value">Rp <?= number_format($total_bulan_ini, 0, ',', '.') ?></div>
                </div>
                <div class="comparison-item">
                    <div class="comparison-label">Bulan Lalu (<?= $nama_bulan[$bulan_lalu] ?> <?= $tahun_lalu ?>)</div>
                    <div class="comparison-value">Rp <?= number_format($total_bulan_lalu, 0, ',', '.') ?></div>
                </div>
            </div>
            
            <div class="change-section">
                <div class="change-text">
                    <?php if ($trend === 'naik'): ?>
                        Pengeluaran <?= $arrow ?> naik sebesar
                    <?php elseif ($trend === 'turun'): ?>
                        Pengeluaran <?= $arrow ?> turun sebesar
                    <?php else: ?>
                        Pengeluaran <?= $arrow ?> stabil
                    <?php endif; ?>
                </div>
                <?php if ($trend !== 'stabil'): ?>
                <div class="change-amount" style="color: <?= $color ?>;">
                    Rp <?= number_format(abs($selisih), 0, ',', '.') ?> (<?= $persen ?>%)
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Table Card -->
        <div class="table-card">
            <?php if (mysqli_num_rows($result) == 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <div class="empty-text">Tidak Ada Data</div>
                    <div class="empty-subtext">
                        Belum ada pengeluaran yang tercatat pada <?= $nama_bulan[$bulan] ?> <?= $tahun ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-header">
                    <div class="table-title">
                        📋 Data Pengeluaran
                    </div>
                    <div class="data-count">
                        <?= mysqli_num_rows($result) ?> Transaksi
                    </div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                            <th>Kategori</th>
                            <th>Metode</th>
                        </tr>

                        <?php 
                        mysqli_data_seek($result, 0);
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                            <td class="rupiah">Rp <?= number_format($row['jumlah'],0,',','.'); ?></td>
                            <td><?= htmlspecialchars($row['keterangan']); ?></td>
                            <td>
                                <span class="kategori-badge">
                                    <?= htmlspecialchars($row['kategori']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="metode-badge">
                                    💳 <?= $row['metode_bayar']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back Button & Print Button -->
        <a href="png_1.php" class="back-button">
            ⬅️ Kembali ke Data
        </a>

        <!-- Tombol Cetak -->
        <button id="printButton" class="back-button">
            🖨️ Cetak Tabel
        </button>

        <!-- Script untuk Fungsi Cetak -->
        <script>
        document.getElementById('printButton').addEventListener('click', function() {
            // Ambil elemen tabel
            const tableCard = document.querySelector('.table-card');
            
            // Buat jendela cetak baru
            const printWindow = window.open('', '_blank');
            
            // Siapkan konten untuk dicetak (hanya tabel)
            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Rekap Pengeluaran - ${<?= json_encode($nama_bulan[$bulan] . ' ' . $tahun) ?>}</title>
                    <style>
                        body {
                            font-family: 'Inter', Arial, sans-serif;
                            padding: 20px;
                            background: white;
                        }
                        .print-header {
                            text-align: center;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #333;
                            padding-bottom: 10px;
                        }
                        .print-title {
                            font-size: 24px;
                            font-weight: 800;
                            color: #1f2937;
                            margin: 0;
                        }
                        .print-subtitle {
                            font-size: 14px;
                            color: #6b7280;
                            margin: 5px 0 0 0;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                            font-size: 12px;
                        }
                        th, td {
                            padding: 8px;
                            text-align: left;
                            border: 1px solid #ddd;
                        }
                        th {
                            background-color: #f2f2f2;
                            font-weight: 600;
                            text-transform: uppercase;
                            font-size: 13px;
                        }
                        .rupiah {
                            text-align: right;
                            font-weight: 700;
                            color: #dc2626;
                        }
                        .kategori-badge {
                            display: inline-block;
                            padding: 3px 8px;
                            border-radius: 15px;
                            font-size: 10px;
                            font-weight: 600;
                            background: #f3f4f6;
                            color: #4b5563;
                        }
                        .metode-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 3px;
                            padding: 3px 8px;
                            border-radius: 15px;
                            font-size: 10px;
                            font-weight: 600;
                            background: #e0f2fe;
                            color: #0ea5e9;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1 class="print-title">Rekap Pengeluaran Bulanan</h1>
                        <p class="print-subtitle">Periode: <?= $nama_bulan[$bulan] ?> <?= $tahun ?></p>
                    </div>
                    ${tableCard.outerHTML}
                </body>
                </html>
            `;
            
            // Tulis konten ke jendela cetak
            printWindow.document.write(printContent);
            printWindow.document.close();
            
            // Tunggu hingga konten dimuat, lalu cetak
            printWindow.onload = function() {
                printWindow.print();
                // Optional: Tutup jendela setelah cetak (tergantung browser)
                // printWindow.close();
            };
        });
        </script>
    </div>
</body>
</html>