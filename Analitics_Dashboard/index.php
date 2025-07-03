<?php
/**
 * CRUD Perpustakaan Digital
 * Main Page with Search, Filter, Sort & Pagination
 */

include 'db.php';

$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Process add new book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $isbn      = trim($_POST['isbn']);
    $judul     = trim($_POST['judul']);
    $kategori  = $_POST['kategori'];
    $halaman   = (int)$_POST['halaman'];
    $pengarang = trim($_POST['pengarang']);

    // Validation
    if (empty($isbn) || empty($judul) || empty($pengarang) || $halaman <= 0) {
        $error = "Semua field harus diisi dengan benar!";
    } elseif (!validate_isbn($isbn)) {
        $error = "Format ISBN tidak valid!";
    } else {
        $stmt = $conn->prepare("INSERT INTO buku (isbn, judul, kategori, halaman, pengarang) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $isbn, $judul, $kategori, $halaman, $pengarang);
        
        if ($stmt->execute()) {
            $message = "Buku berhasil ditambahkan!";
            // Clear form
            $isbn = $judul = $pengarang = '';
            $halaman = 0;
            $kategori = '';
        } else {
            if ($conn->errno == 1062) {
                $error = "ISBN sudah ada dalam database!";
            } else {
                $error = "Gagal menambahkan buku: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Search, filter, and sorting parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';

// Pagination
$limit = 10; // Records per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(isbn LIKE ? OR judul LIKE ? OR pengarang LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if (!empty($kategori_filter)) {
    $where_conditions[] = "kategori = ?";
    $params[] = $kategori_filter;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Valid sort columns
$valid_sorts = ['id', 'judul', 'pengarang', 'kategori', 'halaman', 'created_at'];
if (!in_array($sort_by, $valid_sorts)) {
    $sort_by = 'id';
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM buku $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_sql);
    $total_records = $count_result->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $limit);

// Main query with pagination
$sql = "SELECT * FROM buku $where_clause ORDER BY $sort_by $order LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($param_types)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Helper function to build query string
function build_query_string($params) {
    $query_params = [];
    foreach ($params as $key => $value) {
        if (!empty($value)) {
            $query_params[] = urlencode($key) . '=' . urlencode($value);
        }
    }
    return !empty($query_params) ? '?' . implode('&', $query_params) : '';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital - Sistem Manajemen Buku</title>
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
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .nav-buttons {
            margin-top: 20px;
        }
        
        .content {
            padding: 30px;
        }
        
        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .section {
            margin-bottom: 40px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 5px solid #007bff;
        }
        
        .section h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.8em;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #1e7e34);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #545b62);
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .search-filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: linear-gradient(135deg, #495057, #6c757d);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            position: relative;
        }
        
        th a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        th a:hover {
            color: #ffc107;
        }
        
        .sort-arrow {
            margin-left: 5px;
            font-size: 12px;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .pagination {
            margin-top: 20px;
            text-align: center;
            padding: 20px;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 5px;
            text-decoration: none;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            color: #007bff;
            transition: all 0.3s;
        }
        
        .pagination .current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .pagination a:hover {
            background-color: #e9ecef;
        }
        
        .stats {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
            font-size: 18px;
        }
        
        .actions {
            white-space: nowrap;
        }
        
        .actions .btn {
            padding: 8px 16px;
            font-size: 12px;
            margin: 2px;
        }
        
        .category-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 600;
        }
        
        .badge-fiksi {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-nonfiksi {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 12px;
            }
            
            .actions .btn {
                display: block;
                margin: 2px 0;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>📚 Perpustakaan Digital</h1>
        <p>Sistem Manajemen Buku dengan Analytics Dashboard</p>
        <div class="nav-buttons">
            <a href="dashboard.php" class="btn btn-info">📊 Analytics Dashboard</a>
        </div>
    </div>
    
    <div class="content">
        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Form Add New Book -->
        <div class="section">
            <h2>➕ Tambah Buku Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" required maxlength="255" 
                               placeholder="978-xxx-xxx-xxx-x" value="<?= isset($isbn) ? htmlspecialchars($isbn) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="judul">Judul Buku</label>
                        <input type="text" id="judul" name="judul" required maxlength="255" 
                               placeholder="Masukkan judul buku" value="<?= isset($judul) ? htmlspecialchars($judul) : '' ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="fiksi" <?= (isset($kategori) && $kategori == 'fiksi') ? 'selected' : '' ?>>📖 Fiksi</option>
                            <option value="non-fiksi" <?= (isset($kategori) && $kategori == 'non-fiksi') ? 'selected' : '' ?>>📚 Non-Fiksi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="halaman">Jumlah Halaman</label>
                        <input type="number" id="halaman" name="halaman" required min="1" 
                               placeholder="0" value="<?= isset($halaman) && $halaman > 0 ? $halaman : '' ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pengarang">Pengarang</label>
                        <input type="text" id="pengarang" name="pengarang" required maxlength="255" 
                               placeholder="Nama pengarang" value="<?= isset($pengarang) ? htmlspecialchars($pengarang) : '' ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">➕ Tambah Buku</button>
            </form>
        </div>

        <!-- Search & Filter -->
        <div class="search-filter-section">
            <h3>🔍 Pencarian & Filter</h3>
            <form method="GET">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Pencarian (ISBN, Judul, Pengarang)</label>
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Ketik kata kunci...">
                    </div>
                    <div class="form-group">
                        <label for="kategori_filter">Filter Kategori</label>
                        <select id="kategori_filter" name="kategori">
                            <option value="">Semua Kategori</option>
                            <option value="fiksi" <?= $kategori_filter == 'fiksi' ? 'selected' : '' ?>>📖 Fiksi</option>
                            <option value="non-fiksi" <?= $kategori_filter == 'non-fiksi' ? 'selected' : '' ?>>📚 Non-Fiksi</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_select">Urutkan Berdasarkan</label>
                        <select id="sort_select" name="sort">
                            <option value="id" <?= $sort_by == 'id' ? 'selected' : '' ?>>ID</option>
                            <option value="judul" <?= $sort_by == 'judul' ? 'selected' : '' ?>>Judul</option>
                            <option value="pengarang" <?= $sort_by == 'pengarang' ? 'selected' : '' ?>>Pengarang</option>
                            <option value="kategori" <?= $sort_by == 'kategori' ? 'selected' : '' ?>>Kategori</option>
                            <option value="halaman" <?= $sort_by == 'halaman' ? 'selected' : '' ?>>Halaman</option>
                            <option value="created_at" <?= $sort_by == 'created_at' ? 'selected' : '' ?>>Tanggal Ditambahkan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="order_select">Urutan</label>
                        <select id="order_select" name="order">
                            <option value="asc" <?= $order == 'ASC' ? 'selected' : '' ?>>A-Z / 1-9 (Ascending)</option>
                            <option value="desc" <?= $order == 'DESC' ? 'selected' : '' ?>>Z-A / 9-1 (Descending)</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">🔍 Cari & Filter</button>
                <a href="index.php" class="btn btn-secondary">🔄 Reset</a>
            </form>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <strong>📊 Statistik: </strong>
            Menampilkan <?= $result->num_rows ?> dari <?= number_format($total_records) ?> buku
            <?php if (!empty($search) || !empty($kategori_filter)): ?>
                (hasil pencarian/filter)
            <?php endif; ?>
            | Halaman <?= $page ?> dari <?= $total_pages ?>
        </div>

        <!-- Book List Table -->
        <div class="table-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>
                                <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => 'isbn', 'order' => ($sort_by == 'isbn' && $order == 'ASC') ? 'desc' : 'asc', 'page' => $page]) ?>">
                                    ISBN
                                    <?php if ($sort_by == 'isbn'): ?>
                                        <span class="sort-arrow"><?= $order == 'ASC' ? '▲' : '▼' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => 'judul', 'order' => ($sort_by == 'judul' && $order == 'ASC') ? 'desc' : 'asc', 'page' => $page]) ?>">
                                    Judul
                                    <?php if ($sort_by == 'judul'): ?>
                                        <span class="sort-arrow"><?= $order == 'ASC' ? '▲' : '▼' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => 'kategori', 'order' => ($sort_by == 'kategori' && $order == 'ASC') ? 'desc' : 'asc', 'page' => $page]) ?>">
                                    Kategori
                                    <?php if ($sort_by == 'kategori'): ?>
                                        <span class="sort-arrow"><?= $order == 'ASC' ? '▲' : '▼' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => 'halaman', 'order' => ($sort_by == 'halaman' && $order == 'ASC') ? 'desc' : 'asc', 'page' => $page]) ?>">
                                    Halaman
                                    <?php if ($sort_by == 'halaman'): ?>
                                        <span class="sort-arrow"><?= $order == 'ASC' ? '▲' : '▼' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => 'pengarang', 'order' => ($sort_by == 'pengarang' && $order == 'ASC') ? 'desc' : 'asc', 'page' => $page]) ?>">
                                    Pengarang
                                    <?php if ($sort_by == 'pengarang'): ?>
                                        <span class="sort-arrow"><?= $order == 'ASC' ? '▲' : '▼' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1; 
                        while ($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['isbn']) ?></td>
                            <td><strong><?= htmlspecialchars($row['judul']) ?></strong></td>
                            <td>
                                <span class="category-badge badge-<?= $row['kategori'] == 'fiksi' ? 'fiksi' : 'nonfiksi' ?>">
                                    <?= $row['kategori'] == 'fiksi' ? '📖 Fiksi' : '📚 Non-Fiksi' ?>
                                </span>
                            </td>
                            <td><?= number_format($row['halaman']) ?> hal</td>
                            <td><?= htmlspecialchars($row['pengarang']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-success">✏️ Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger" 
                                   onclick="return confirm('⚠️ Yakin ingin menghapus buku \'<?= htmlspecialchars($row['judul']) ?>\'?')">🗑️ Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => $sort_by, 'order' => strtolower($order), 'page' => $page-1]) ?>">« Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1): ?>
                            <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => $sort_by, 'order' => strtolower($order), 'page' => 1]) ?>">1</a>
                            <?php if ($start_page > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => $sort_by, 'order' => strtolower($order), 'page' => $i]) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => $sort_by, 'order' => strtolower($order), 'page' => $total_pages]) ?>"><?= $total_pages ?></a>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="<?= build_query_string(['search' => $search, 'kategori' => $kategori_filter, 'sort' => $sort_by, 'order' => strtolower($order), 'page' => $page+1]) ?>">Selanjutnya »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-data">
                    <?php if (!empty($search) || !empty($kategori_filter)): ?>
                        🔍 Tidak ada buku yang sesuai dengan kriteria pencarian/filter.
                        <br><br>
                        <a href="index.php" class="btn btn-primary">🔄 Tampilkan Semua Buku</a>
                    <?php else: ?>
                        📭 Belum ada data buku. Silakan tambahkan buku pertama Anda!
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-submit form when search/filter changes
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const kategoriSelect = document.getElementById('kategori_filter');
    const sortSelect = document.getElementById('sort_select');
    const orderSelect = document.getElementById('order_select');
    
    // Add event listeners for auto-submit (optional)
    // Uncomment if you want auto-submit on change
    /*
    [kategoriSelect, sortSelect, orderSelect].forEach(element => {
        element.addEventListener('change', function() {
            this.form.submit();
        });
    });
    */
    
    // Clear search on Escape key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
        }
    });
    
    // Form validation
    const addForm = document.querySelector('form[method="POST"]');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const isbn = document.getElementById('isbn').value.trim();
            const judul = document.getElementById('judul').value.trim();
            const kategori = document.getElementById('kategori').value;
            const halaman = parseInt(document.getElementById('halaman').value);
            const pengarang = document.getElementById('pengarang').value.trim();
            
            if (!isbn || !judul || !kategori || !halaman || halaman <= 0 || !pengarang) {
                e.preventDefault();
                alert('Semua field harus diisi dengan benar!');
                return false;
            }
            
            // Basic ISBN validation
            const isbnPattern = /^[\d\-\s]+$/;
            if (!isbnPattern.test(isbn)) {
                e.preventDefault();
                alert('Format ISBN tidak valid! Gunakan angka, spasi, dan tanda hubung saja.');
                return false;
            }
        });
    }
});

// Smooth scroll to top function
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Add scroll to top button if page is long
if (document.body.scrollHeight > window.innerHeight * 2) {
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '↑';
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        background: #007bff;
        color: white;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        display: none;
        z-index: 1000;
    `;
    scrollBtn.onclick = scrollToTop;
    document.body.appendChild(scrollBtn);
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    });
}
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>