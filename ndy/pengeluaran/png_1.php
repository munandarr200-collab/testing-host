<?php
require_once __DIR__ . "/../sistem/auth.php";
require_once __DIR__ . "/../sistem/koneksi.php";
$hub = open_connection();
$result = mysqli_query($hub, "SELECT * FROM pengeluaran ORDER BY tanggal DESC");

// Hitung total pengeluaran
$total_query = mysqli_query($hub, "SELECT SUM(jumlah) as total FROM pengeluaran");
$total_data = mysqli_fetch_assoc($total_query);
$total_pengeluaran = $total_data['total'] ?? 0;

// Hitung jumlah data
$count_query = mysqli_query($hub, "SELECT COUNT(*) as jumlah FROM pengeluaran");
$count_data = mysqli_fetch_assoc($count_query);
$jumlah_data = $count_data['jumlah'] ?? 0;

// Data untuk grafik (6 bulan terakhir)
$chart_query = mysqli_query($hub, "SELECT DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(jumlah) as total FROM pengeluaran WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(tanggal, '%Y-%m') ORDER BY bulan ASC");
$chart_labels = [];
$chart_data = [];
while($chart_row = mysqli_fetch_assoc($chart_query)) {
    $chart_labels[] = date('M Y', strtotime($chart_row['bulan'] . '-01'));
    $chart_data[] = $chart_row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengeluaran - Keuangan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 10px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.95), rgba(114, 9, 183, 0.95));
            backdrop-filter: blur(10px);
            color: white;
            padding: 25px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .header h1 {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 2rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .header h1 i {
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 12px;
            font-size: 1.8rem;
        }
        
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .user-info-left {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.15));
            backdrop-filter: blur(10px);
            padding: 12px 20px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .admin-info {
            display: flex;
            flex-direction: column;
        }
        
        .admin-info .role {
            font-size: 0.75rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .admin-info .name {
            font-size: 1rem;
            font-weight: 700;
        }
        
        .datetime-badge {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.15));
            backdrop-filter: blur(10px);
            padding: 12px 20px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .datetime-badge i {
            font-size: 1.2rem;
        }
        
        .datetime-info {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }
        
        .time-display {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .date-display {
            font-size: 0.85rem;
            opacity: 0.95;
        }
        
        .btn-logout {
            background: linear-gradient(135deg, rgba(247, 37, 133, 0.9), rgba(220, 20, 100, 0.9));
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(247, 37, 133, 0.3);
            font-weight: 600;
        }
        
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(247, 37, 133, 0.4);
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .total-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .count-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-info h3 {
            font-size: 2rem;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }
        
        .stat-info p {
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        /* Chart Container */
        .chart-container {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .chart-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .chart-header i {
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .chart-header h2 {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        #expenseChart {
            max-height: 350px;
        }
        
        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .action-bar h2 {
            color: white;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.95);
            color: var(--primary);
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        
        .btn-secondary:hover {
            background: white;
            transform: translateY(-2px);
        }
        
        /* Table Styles */
        .table-container {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow-x: auto;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        
        tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            transform: scale(1.01);
        }
        
        tbody tr:last-child {
            border-bottom: none;
        }
        
        td {
            padding: 18px 15px;
        }
        
        .amount-cell {
            font-weight: 700;
            font-size: 1.1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .action-cell {
            display: flex;
            gap: 10px;
        }
        
        .action-link {
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .edit-link {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.15), rgba(76, 201, 240, 0.1));
            color: #0891b2;
            border: 1px solid rgba(76, 201, 240, 0.3);
        }
        
        .edit-link:hover {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.25), rgba(76, 201, 240, 0.2));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 201, 240, 0.3);
        }
        
        .delete-link {
            background: linear-gradient(135deg, rgba(247, 37, 133, 0.15), rgba(247, 37, 133, 0.1));
            color: #e11d48;
            border: 1px solid rgba(247, 37, 133, 0.3);
        }
        
        .delete-link:hover {
            background: linear-gradient(135deg, rgba(247, 37, 133, 0.25), rgba(247, 37, 133, 0.2));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(247, 37, 133, 0.3);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.4;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        
        .empty-state p {
            margin-bottom: 25px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 25px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin-top: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }
            
            .user-info {
                flex-direction: column;
            }
            
            .user-info-left {
                width: 100%;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-group {
                width: 100%;
            }
            
            .btn {
                flex: 1;
                justify-content: center;
            }
            
            th, td {
                padding: 12px 10px;
            }
            
            .stat-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Dashboard Pengeluaran Keuangan</h1>
            <div class="user-info">
                <div class="user-info-left">
                    <div class="admin-badge">
                        <div class="admin-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="admin-info">
                            <span class="role">Administrator</span>
                            <span class="name"><?= htmlspecialchars($_SESSION['user']) ?></span>
                        </div>
                    </div>
                    
                    <div class="datetime-badge">
                        <i class="far fa-clock"></i>
                        <div class="datetime-info">
                            <span class="time-display" id="timeDisplay">00:00:00</span>
                            <span class="date-display" id="dateDisplay">Loading...</span>
                        </div>
                    </div>
                </div>
                
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon total-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-info">
                    <h3>Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h3>
                    <p>Total Pengeluaran</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon count-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($jumlah_data, 0, ',', '.') ?></h3>
                    <p>Jumlah Transaksi</p>
                </div>
            </div>
        </div>
        
        <!-- Chart Section -->
        <?php if(count($chart_labels) > 0): ?>
        <div class="chart-container">
            <div class="chart-header">
                <i class="fas fa-chart-area"></i>
                <h2>Grafik Pengeluaran 6 Bulan Terakhir</h2>
            </div>
            <canvas id="expenseChart"></canvas>
        </div>
        <?php endif; ?>
        
        <!-- Action Bar -->
        <div class="action-bar">
            <h2><i class="fas fa-table"></i> Data Pengeluaran</h2>
            <div class="btn-group">
                <a href="tambah.php" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Tambah Data Baru</a>
                <a href="rekap.php" class="btn btn-secondary"><i class="fas fa-calendar-alt"></i> Lihat Rekap</a>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="table-container">
            <?php if(mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><i class="far fa-calendar"></i> Tanggal</th>
                        <th><i class="fas fa-money-bill-wave"></i> Jumlah</th>
                        <th><i class="fas fa-cogs"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['id']) ?></td>
                        <td><?= htmlspecialchars($r['tanggal']) ?></td>
                        <td class="amount-cell">Rp <?= number_format($r['jumlah'], 0, ',', '.') ?></td>
                        <td>
                            <div class="action-cell">
                                <a href="edit.php?id=<?= $r['id'] ?>" class="action-link edit-link"><i class="fas fa-edit"></i> Edit</a>
                                <a href="hapus.php?id=<?= $r['id'] ?>" class="action-link delete-link" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>Belum ada data pengeluaran</h3>
                <p>Mulai dengan menambahkan data pengeluaran pertama Anda</p>
                <a href="tambah.php" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Tambah Data</a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?= date('Y') ?> Sistem Pengeluaran Keuangan. Total <?= $jumlah_data ?> data transaksi.</p>
        </div>
    </div>
    
    <script>
        // Real-time Clock
        function updateDateTime() {
            const now = new Date();
            
            // Format time
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('timeDisplay').textContent = `${hours}:${minutes}:${seconds}`;
            
            // Format date
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = now.toLocaleDateString('id-ID', options);
            document.getElementById('dateDisplay').textContent = dateStr;
        }
        
        // Update every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Chart.js Configuration
        <?php if(count($chart_labels) > 0): ?>
        const ctx = document.getElementById('expenseChart');
        const expenseChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Pengeluaran (Rp)',
                    data: <?= json_encode($chart_data) ?>,
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgb(102, 126, 234)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: 'rgb(118, 75, 162)',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 13,
                                weight: '600'
                            },
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Pengeluaran: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Table animations
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('.delete-link');
            
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                        e.preventDefault();
                    }
                });
            });
            
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
        });
    </script>
</body>
</html>