<?php
/**
 * Analytics Dashboard
 * Perpustakaan Digital - Complete Analytics Dashboard
 */

include 'db.php';

// Helper Functions for Analytics

function getGeneralStats($conn) {
    $stats = [];
    
    // Total books
    $result = $conn->query("SELECT COUNT(*) as total FROM buku");
    $stats['total_books'] = $result->fetch_assoc()['total'];
    
    // Total pages
    $result = $conn->query("SELECT SUM(halaman) as total_pages FROM buku");
    $stats['total_pages'] = $result->fetch_assoc()['total_pages'] ?: 0;
    
    // Average pages per book
    $stats['avg_pages'] = $stats['total_books'] > 0 ? round($stats['total_pages'] / $stats['total_books'], 1) : 0;
    
    // Total unique authors
    $result = $conn->query("SELECT COUNT(DISTINCT pengarang) as total_authors FROM buku");
    $stats['total_authors'] = $result->fetch_assoc()['total_authors'];
    
    // Books added this month
    $result = $conn->query("SELECT COUNT(*) as this_month FROM buku WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stats['books_this_month'] = $result->fetch_assoc()['this_month'];
    
    return $stats;
}

function getCategoryDistribution($conn) {
    $result = $conn->query("
        SELECT kategori, 
               COUNT(*) as jumlah,
               ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM buku), 1) as persentase,
               SUM(halaman) as total_halaman
        FROM buku 
        GROUP BY kategori
        ORDER BY jumlah DESC
    ");
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function getTopAuthors($conn, $limit = 10) {
    $result = $conn->query("
        SELECT pengarang, 
               COUNT(*) as jumlah_buku,
               SUM(halaman) as total_halaman,
               AVG(halaman) as rata_halaman,
               MIN(created_at) as first_book,
               MAX(created_at) as latest_book
        FROM buku 
        GROUP BY pengarang 
        ORDER BY jumlah_buku DESC, total_halaman DESC 
        LIMIT $limit
    ");
    
    $authors = [];
    while ($row = $result->fetch_assoc()) {
        $authors[] = $row;
    }
    return $authors;
}

function getPageDistribution($conn) {
    $result = $conn->query("
        SELECT 
            CASE 
                WHEN halaman < 100 THEN 'Tipis (< 100)'
                WHEN halaman BETWEEN 100 AND 300 THEN 'Sedang (100-300)'
                WHEN halaman BETWEEN 301 AND 500 THEN 'Tebal (301-500)'
                ELSE 'Sangat Tebal (> 500)'
            END as kategori_halaman,
            COUNT(*) as jumlah,
            ROUND(AVG(halaman), 1) as rata_halaman
        FROM buku 
        GROUP BY kategori_halaman
        ORDER BY 
            CASE 
                WHEN halaman < 100 THEN 1
                WHEN halaman BETWEEN 100 AND 300 THEN 2
                WHEN halaman BETWEEN 301 AND 500 THEN 3
                ELSE 4
            END
    ");
    
    $distribution = [];
    while ($row = $result->fetch_assoc()) {
        $distribution[] = $row;
    }
    return $distribution;
}

function getRecentBooks($conn, $limit = 10) {
    $result = $conn->query("
        SELECT judul, pengarang, kategori, halaman, isbn, created_at
        FROM buku 
        ORDER BY created_at DESC 
        LIMIT $limit
    ");
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    return $books;
}

function getAdvancedAnalytics($conn) {
    $analytics = [];
    
    // Longest book
    $result = $conn->query("SELECT judul, pengarang, halaman, isbn FROM buku ORDER BY halaman DESC LIMIT 1");
    $analytics['longest_book'] = $result->fetch_assoc();
    
    // Shortest book
    $result = $conn->query("SELECT judul, pengarang, halaman, isbn FROM buku ORDER BY halaman ASC LIMIT 1");
    $analytics['shortest_book'] = $result->fetch_assoc();
    
    // Most productive author
    $result = $conn->query("
        SELECT pengarang, SUM(halaman) as total_halaman, COUNT(*) as jumlah_buku,
               ROUND(AVG(halaman), 1) as rata_halaman
        FROM buku 
        GROUP BY pengarang 
        ORDER BY total_halaman DESC 
        LIMIT 1
    ");
    $analytics['most_productive_author'] = $result->fetch_assoc();
    
    // Monthly trend (last 6 months)
    $result = $conn->query("
        SELECT 
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            COUNT(*) as jumlah,
            MONTHNAME(created_at) as month_name
        FROM buku 
        WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY year DESC, month DESC
    ");
    
    $monthly_trend = [];
    while ($row = $result->fetch_assoc()) {
        $monthly_trend[] = $row;
    }
    $analytics['monthly_trend'] = $monthly_trend;
    
    return $analytics;
}

function getSearchStats($conn) {
    $stats = [];
    
    // Most common words in titles
    $result = $conn->query("
        SELECT 
            SUBSTRING_INDEX(SUBSTRING_INDEX(LOWER(judul), ' ', 1), ' ', -1) as word,
            COUNT(*) as frequency
        FROM buku 
        WHERE LENGTH(TRIM(judul)) > 0
        GROUP BY word
        HAVING LENGTH(word) > 3
        ORDER BY frequency DESC
        LIMIT 10
    ");
    
    $common_words = [];
    while ($row = $result->fetch_assoc()) {
        $common_words[] = $row;
    }
    $stats['common_title_words'] = $common_words;
    
    return $stats;
}

// Get all analytics data
$generalStats = getGeneralStats($conn);
$categoryDistribution = getCategoryDistribution($conn);
$topAuthors = getTopAuthors($conn, 10);
$pageDistribution = getPageDistribution($conn);
$recentBooks = getRecentBooks($conn, 8);
$advancedAnalytics = getAdvancedAnalytics($conn);
$searchStats = getSearchStats($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Perpustakaan Digital</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 2.8em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header p {
            color: #6c757d;
            font-size: 1.2em;
        }
        
        .navigation {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-links a {
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .nav-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }
        
        .nav-secondary {
            background: linear-gradient(45deg, #6c757d, #545b62);
            color: white;
        }
        
        .nav-success {
            background: linear-gradient(45deg, #28a745, #1e7e34);
            color: white;
        }
        
        .nav-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .stat-number {
            font-size: 3em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            font-size: 1.1em;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }
        
        .stat-change {
            font-size: 0.9em;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 600;
        }
        
        .positive {
            background: #d4edda;
            color: #155724;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 1.4em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 15px;
        }
        
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, #495057, #6c757d);
            color: white;
            padding: 25px;
            font-size: 1.3em;
            font-weight: 700;
        }
        
        .table-content {
            padding: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.9em;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.9em;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .insight-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .insight-title {
            font-size: 1.5em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 10px;
        }
        
        .insight-item {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 5px solid #007bff;
            transition: all 0.3s ease;
        }
        
        .insight-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .insight-item strong {
            color: #2c3e50;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 700;
        }
        
        .badge-fiksi {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-nonfiksi {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .trend-up {
            color: #28a745;
        }
        
        .trend-down {
            color: #dc3545;
        }
        
        .quick-actions {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .quick-actions h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-primary { background: linear-gradient(45deg, #007bff, #0056b3); color: white; }
        .btn-success { background: linear-gradient(45deg, #28a745, #1e7e34); color: white; }
        .btn-info { background: linear-gradient(45deg, #17a2b8, #138496); color: white; }
        .btn-warning { background: linear-gradient(45deg, #ffc107, #e0a800); color: #212529; }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .charts-grid,
            .tables-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .nav-links {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>📊 Analytics Dashboard</h1>
        <p>Analisis Komprehensif Perpustakaan Digital</p>
        <div style="margin-top: 15px; font-size: 0.9em; color: #6c757d;">
            🕒 Last Updated: <?= date('d F Y, H:i:s') ?>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="navigation">
        <div class="nav-links">
            <a href="index.php" class="nav-primary">📚 Kelola Buku</a>
            <a href="dashboard.php" class="nav-success">📊 Dashboard</a>
            <a href="#insights" class="nav-secondary">🔍 Insights</a>
            <span style="margin-left: auto; color: #6c757d; font-size: 0.9em;">
                📈 Total Records: <?= number_format($generalStats['total_books']) ?>
            </span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>🚀 Quick Actions</h3>
        <div class="action-buttons">
            <a href="index.php#add-book" class="btn btn-primary">➕ Tambah Buku</a>
            <a href="index.php" class="btn btn-success">📋 Lihat Semua Buku</a>
            <button onclick="window.print()" class="btn btn-info">🖨️ Print Dashboard</button>
            <button onclick="exportData()" class="btn btn-warning">📥 Export Data</button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-number"><?= number_format($generalStats['total_books']) ?></div>
            <div class="stat-label">Total Buku</div>
            <div class="stat-change positive">+<?= $generalStats['books_this_month'] ?> bulan ini</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-number"><?= number_format($generalStats['total_pages']) ?></div>
            <div class="stat-label">Total Halaman</div>
            <div class="stat-change positive">📖 Koleksi Lengkap</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✍️</div>
            <div class="stat-number"><?= number_format($generalStats['total_authors']) ?></div>
            <div class="stat-label">Pengarang Unik</div>
            <div class="stat-change positive">🌟 Beragam Penulis</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-number"><?= number_format($generalStats['avg_pages']) ?></div>
            <div class="stat-label">Rata-rata Halaman</div>
            <div class="stat-change positive">📏 Per Buku</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <!-- Category Distribution Chart -->
        <div class="chart-container">
            <div class="chart-title">📂 Distribusi Kategori Buku</div>
            <canvas id="categoryChart" width="400" height="300"></canvas>
            <div style="margin-top: 15px; text-align: center; font-size: 0.9em; color: #6c757d;">
                Total: <?= array_sum(array_column($categoryDistribution, 'jumlah')) ?> buku
            </div>
        </div>
        
        <!-- Page Distribution Chart -->
        <div class="chart-container">
            <div class="chart-title">📊 Distribusi Ketebalan Buku</div>
            <canvas id="pageChart" width="400" height="300"></canvas>
        </div>
        
        <!-- Monthly Trend Chart -->
        <?php if (!empty($advancedAnalytics['monthly_trend'])): ?>
        <div class="chart-container">
            <div class="chart-title">📈 Trend Penambahan Buku (6 Bulan Terakhir)</div>
            <canvas id="trendChart" width="400" height="300"></canvas>
        </div>
        <?php endif; ?>
        
        <!-- Author Productivity Chart -->
        <div class="chart-container">
            <div class="chart-title">🏆 Top 5 Pengarang Terpopuler</div>
            <canvas id="authorChart" width="400" height="300"></canvas>
        </div>
    </div>

    <!-- Tables -->
    <div class="tables-grid">
        <!-- Top Authors -->
        <div class="table-container">
            <div class="table-header">
                🏆 Top 10 Pengarang Terpopuler
            </div>
            <div class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Pengarang</th>
                            <th>Buku</th>
                            <th>Total Hal</th>
                            <th>Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topAuthors as $index => $author): ?>
                        <tr>
                            <td><strong>#<?= $index + 1 ?></strong></td>
                            <td><?= htmlspecialchars($author['pengarang']) ?></td>
                            <td><?= $author['jumlah_buku'] ?> buku</td>
                            <td><?= number_format($author['total_halaman']) ?> hal</td>
                            <td><?= number_format($author['rata_halaman']) ?> hal</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Books -->
        <div class="table-container">
            <div class="table-header">
                🕒 8 Buku Terbaru Ditambahkan
            </div>
            <div class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBooks as $book): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($book['judul']) ?></strong></td>
                            <td><?= htmlspecialchars($book['pengarang']) ?></td>
                            <td>
                                <span class="badge badge-<?= $book['kategori'] == 'fiksi' ? 'fiksi' : 'nonfiksi' ?>">
                                    <?= $book['kategori'] == 'fiksi' ? '📖 Fiksi' : '📚 Non-Fiksi' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($book['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Advanced Insights -->
    <div class="insight-card" id="insights">
        <div class="insight-title">🔍 Insights & Analisis Mendalam</div>
        
        <?php if ($advancedAnalytics['longest_book']): ?>
        <div class="insight-item">
            <strong>📏 Buku Terpanjang:</strong> 
            "<?= htmlspecialchars($advancedAnalytics['longest_book']['judul']) ?>" 
            oleh <?= htmlspecialchars($advancedAnalytics['longest_book']['pengarang']) ?> 
            (<?= number_format($advancedAnalytics['longest_book']['halaman']) ?> halaman)
        </div>
        <?php endif; ?>
        
        <?php if ($advancedAnalytics['shortest_book']): ?>
        <div class="insight-item">
            <strong>📑 Buku Terpendek:</strong> 
            "<?= htmlspecialchars($advancedAnalytics['shortest_book']['judul']) ?>" 
            oleh <?= htmlspecialchars($advancedAnalytics['shortest_book']['pengarang']) ?> 
            (<?= number_format($advancedAnalytics['shortest_book']['halaman']) ?> halaman)
        </div>
        <?php endif; ?>
        
        <?php if ($advancedAnalytics['most_productive_author']): ?>
        <div class="insight-item">
            <strong>✍️ Pengarang Paling Produktif:</strong> 
            <?= htmlspecialchars($advancedAnalytics['most_productive_author']['pengarang']) ?> 
            dengan <?= $advancedAnalytics['most_productive_author']['jumlah_buku'] ?> buku 
            (total <?= number_format($advancedAnalytics['most_productive_author']['total_halaman']) ?> halaman, 
            rata-rata <?= number_format($advancedAnalytics['most_productive_author']['rata_halaman']) ?> hal/buku)
        </div>
        <?php endif; ?>
        
        <div class="insight-item">
            <strong>📊 Distribusi Kategori:</strong>
            <?php foreach ($categoryDistribution as $cat): ?>
                <?= ucfirst($cat['kategori']) ?>: <?= $cat['jumlah'] ?> buku (<?= $cat['persentase'] ?>%, <?= number_format($cat['total_halaman']) ?> halaman)
                <?= $cat !== end($categoryDistribution) ? ' • ' : '' ?>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($advancedAnalytics['monthly_trend'])): ?>
        <div class="insight-item">
            <strong>📈 Trend Bulanan:</strong>
            <?php 
            $latest_month = $advancedAnalytics['monthly_trend'][0];
            $total_recent = array_sum(array_column($advancedAnalytics['monthly_trend'], 'jumlah'));
            ?>
            Bulan terakhir: <?= $latest_month['jumlah'] ?> buku ditambahkan di <?= $latest_month['month_name'] ?> <?= $latest_month['year'] ?>. 
            Total 6 bulan terakhir: <?= $total_recent ?> buku.
        </div>
        <?php endif; ?>
        
        <div class="insight-item">
            <strong>🎯 Rekomendasi:</strong>
            <?php 
            $fiksi_count = 0;
            $nonfiksi_count = 0;
            foreach ($categoryDistribution as $cat) {
                if ($cat['kategori'] == 'fiksi') $fiksi_count = $cat['jumlah'];
                if ($cat['kategori'] == 'non-fiksi') $nonfiksi_count = $cat['jumlah'];
            }
            
            if ($fiksi_count > $nonfiksi_count) {
                echo "Koleksi didominasi buku fiksi. Pertimbangkan menambah lebih banyak buku non-fiksi untuk keseimbangan.";
            } elseif ($nonfiksi_count > $fiksi_count) {
                echo "Koleksi didominasi buku non-fiksi. Pertimbangkan menambah lebih banyak buku fiksi untuk variasi.";
            } else {
                echo "Koleksi sudah seimbang antara fiksi dan non-fiksi. Pertahankan keseimbangan ini.";
            }
            ?>
        </div>
    </div>
</div>

<script>
// Category Distribution Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: [
            <?php foreach ($categoryDistribution as $cat): ?>
            '<?= $cat['kategori'] == 'fiksi' ? '📖 Fiksi' : '📚 Non-Fiksi' ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            data: [
                <?php foreach ($categoryDistribution as $cat): ?>
                <?= $cat['jumlah'] ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e'
            ],
            borderWidth: 3,
            borderColor: '#ffffff',
            hoverBorderWidth: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: {
                        size: 14,
                        weight: '600'
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} buku (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Page Distribution Chart
const pageCtx = document.getElementById('pageChart').getContext('2d');
const pageChart = new Chart(pageCtx, {
    type: 'bar',
    data: {
        labels: [
            <?php foreach ($pageDistribution as $dist): ?>
            '<?= $dist['kategori_halaman'] ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Jumlah Buku',
            data: [
                <?php foreach ($pageDistribution as $dist): ?>
                <?= $dist['jumlah'] ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#4e73df',
                '#1cc88a', 
                '#36b9cc',
                '#f6c23e'
            ],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    afterLabel: function(context) {
                        const index = context.dataIndex;
                        const avgPages = [
                            <?php foreach ($pageDistribution as $dist): ?>
                            <?= $dist['rata_halaman'] ?>,
                            <?php endforeach; ?>
                        ];
                        return `Rata-rata: ${avgPages[index]} halaman`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

<?php if (!empty($advancedAnalytics['monthly_trend'])): ?>
// Monthly Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: [
            <?php foreach (array_reverse($advancedAnalytics['monthly_trend']) as $trend): ?>
            '<?= $trend['month_name'] ?> <?= $trend['year'] ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Buku Ditambahkan',
            data: [
                <?php foreach (array_reverse($advancedAnalytics['monthly_trend']) as $trend): ?>
                <?= $trend['jumlah'] ?>,
                <?php endforeach; ?>
            ],
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#4e73df',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
<?php endif; ?>

// Author Productivity Chart
const authorCtx = document.getElementById('authorChart').getContext('2d');
const authorChart = new Chart(authorCtx, {
    type: 'horizontalBar',
    data: {
        labels: [
            <?php foreach (array_slice($topAuthors, 0, 5) as $author): ?>
            '<?= htmlspecialchars($author['pengarang']) ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Jumlah Buku',
            data: [
                <?php foreach (array_slice($topAuthors, 0, 5) as $author): ?>
                <?= $author['jumlah_buku'] ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc', 
                '#f6c23e',
                '#e74a3b'
            ],
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    afterLabel: function(context) {
                        const index = context.dataIndex;
                        const totalPages = [
                            <?php foreach (array_slice($topAuthors, 0, 5) as $author): ?>
                            <?= $author['total_halaman'] ?>,
                            <?php endforeach; ?>
                        ];
                        return `Total halaman: ${totalPages[index].toLocaleString()}`;
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Export functionality
function exportData() {
    const data = {
        timestamp: new Date().toISOString(),
        stats: {
            total_books: <?= $generalStats['total_books'] ?>,
            total_pages: <?= $generalStats['total_pages'] ?>,
            total_authors: <?= $generalStats['total_authors'] ?>,
            avg_pages: <?= $generalStats['avg_pages'] ?>
        },
        categories: <?= json_encode($categoryDistribution) ?>,
        top_authors: <?= json_encode(array_slice($topAuthors, 0, 10)) ?>,
        recent_books: <?= json_encode($recentBooks) ?>
    };
    
    const jsonString = JSON.stringify(data, null, 2);
    const blob = new Blob([jsonString], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `perpustakaan_analytics_${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// Auto refresh every 5 minutes
setTimeout(function() {
    if (confirm('Dashboard akan di-refresh untuk data terbaru. Lanjutkan?')) {
        window.location.reload();
    }
}, 300000); // 5 minutes

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add loading animation for charts
window.addEventListener('load', function() {
    const charts = document.querySelectorAll('canvas');
    charts.forEach(chart => {
        chart.style.opacity = '0';
        chart.style.animation = 'fadeIn 1s ease-in-out forwards';
    });
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>

<?php
$conn->close();
?>