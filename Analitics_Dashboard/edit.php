<?php
/**
 * Edit Book Form
 * Perpustakaan Digital
 */

include 'db.php';

$buku = null;
$error = '';
$message = '';

// Get book data by ID
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($id <= 0) {
        header("Location: index.php?error=" . urlencode("ID buku tidak valid!"));
        exit;
    }
    
    // Using prepared statement
    $stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $buku = $result->fetch_assoc();
    } else {
        header("Location: index.php?error=" . urlencode("Buku tidak ditemukan!"));
        exit;
    }
    $stmt->close();
} else {
    header("Location: index.php?error=" . urlencode("ID buku tidak valid!"));
    exit;
}

// Process update when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && $buku) {
    $id        = (int)$_POST['id'];
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
        // Check if ISBN already exists for other books
        $check_stmt = $conn->prepare("SELECT id FROM buku WHERE isbn = ? AND id != ?");
        $check_stmt->bind_param("si", $isbn, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "ISBN sudah digunakan oleh buku lain!";
        } else {
            // Update book data
            $stmt = $conn->prepare("UPDATE buku SET isbn=?, judul=?, kategori=?, halaman=?, pengarang=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $stmt->bind_param("sssiis", $isbn, $judul, $kategori, $halaman, $pengarang, $id);
            
            if ($stmt->execute()) {
                $message = "Buku berhasil diperbarui!";
                // Update local data for form display
                $buku['isbn'] = $isbn;
                $buku['judul'] = $judul;
                $buku['kategori'] = $kategori;
                $buku['halaman'] = $halaman;
                $buku['pengarang'] = $pengarang;
            } else {
                $error = "Gagal memperbarui buku: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan Digital</title>
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
            max-width: 800px;
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
            font-size: 2.2em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
            color: #6c757d;
        }
        
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
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
        
        .form-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            border-left: 5px solid #28a745;
        }
        
        .form-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            min-width: 250px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background: white;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin: 5px 10px 5px 0;
        }
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #1e7e34);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #545b62);
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .book-info {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .book-info h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .book-detail {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            font-size: 14px;
        }
        
        .book-detail div {
            background: white;
            padding: 10px;
            border-radius: 5px;
        }
        
        .book-detail strong {
            color: #495057;
        }
        
        .form-actions {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }
        
        .required {
            color: #dc3545;
        }
        
        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            .book-detail {
                grid-template-columns: 1fr;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>✏️ Edit Buku</h1>
        <p>Perbarui informasi buku di perpustakaan digital</p>
    </div>
    
    <div class="content">
        <div class="breadcrumb">
            <a href="index.php">📚 Daftar Buku</a> / <a href="dashboard.php">📊 Dashboard</a> / Edit Buku
        </div>
        
        <?php if ($message): ?>
            <div class="message success">
                ✅ <?= htmlspecialchars($message) ?>
                <div style="margin-top: 10px;">
                    <a href="index.php" class="btn btn-primary">🔙 Kembali ke Daftar</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($buku): ?>
            <!-- Current Book Info -->
            <div class="book-info">
                <h3>📖 Informasi Buku Saat Ini</h3>
                <div class="book-detail">
                    <div><strong>ID:</strong> #<?= $buku['id'] ?></div>
                    <div><strong>ISBN:</strong> <?= htmlspecialchars($buku['isbn']) ?></div>
                    <div><strong>Kategori:</strong> <?= $buku['kategori'] == 'fiksi' ? '📖 Fiksi' : '📚 Non-Fiksi' ?></div>
                    <div><strong>Dibuat:</strong> <?= date('d/m/Y H:i', strtotime($buku['created_at'])) ?></div>
                    <?php if ($buku['updated_at'] && $buku['updated_at'] != $buku['created_at']): ?>
                    <div><strong>Diperbarui:</strong> <?= date('d/m/Y H:i', strtotime($buku['updated_at'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="form-section">
                <div class="form-title">✏️ Form Edit Buku</div>
                
                <form method="POST" id="editForm">
                    <input type="hidden" name="id" value="<?= $buku['id'] ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="isbn">ISBN <span class="required">*</span></label>
                            <input type="text" id="isbn" name="isbn" 
                                   value="<?= htmlspecialchars($buku['isbn']) ?>" 
                                   required maxlength="255" 
                                   placeholder="978-xxx-xxx-xxx-x">
                            <div class="help-text">Format: 978-xxx-xxx-xxx-x atau xxxxxxxxxx</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="kategori">Kategori <span class="required">*</span></label>
                            <select id="kategori" name="kategori" required>
                                <option value="fiksi" <?= $buku['kategori'] == 'fiksi' ? 'selected' : '' ?>>📖 Fiksi</option>
                                <option value="non-fiksi" <?= $buku['kategori'] == 'non-fiksi' ? 'selected' : '' ?>>📚 Non-Fiksi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="judul">Judul Buku <span class="required">*</span></label>
                            <input type="text" id="judul" name="judul" 
                                   value="<?= htmlspecialchars($buku['judul']) ?>" 
                                   required maxlength="255" 
                                   placeholder="Masukkan judul buku">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pengarang">Pengarang <span class="required">*</span></label>
                            <input type="text" id="pengarang" name="pengarang" 
                                   value="<?= htmlspecialchars($buku['pengarang']) ?>" 
                                   required maxlength="255" 
                                   placeholder="Nama lengkap pengarang">
                        </div>
                        
                        <div class="form-group">
                            <label for="halaman">Jumlah Halaman <span class="required">*</span></label>
                            <input type="number" id="halaman" name="halaman" 
                                   value="<?= $buku['halaman'] ?>" 
                                   required min="1" max="9999" 
                                   placeholder="0">
                            <div class="help-text">Minimal 1 halaman, maksimal 9999 halaman</div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                        <a href="index.php" class="btn btn-secondary">❌ Batal</a>
                        <a href="dashboard.php" class="btn btn-primary">📊 Lihat Dashboard</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="message error">
                ❌ Data buku tidak dapat dimuat.
                <div style="margin-top: 10px;">
                    <a href="index.php" class="btn btn-primary">🔙 Kembali ke Daftar Buku</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editForm');
    
    if (form) {
        // Form validation
        form.addEventListener('submit', function(e) {
            const isbn = document.getElementById('isbn').value.trim();
            const judul = document.getElementById('judul').value.trim();
            const kategori = document.getElementById('kategori').value;
            const halaman = parseInt(document.getElementById('halaman').value);
            const pengarang = document.getElementById('pengarang').value.trim();
            
            // Basic validation
            if (!isbn || !judul || !kategori || !halaman || halaman <= 0 || !pengarang) {
                e.preventDefault();
                alert('❌ Semua field harus diisi dengan benar!');
                return false;
            }
            
            // ISBN validation
            const isbnPattern = /^[\d\-\s]+$/;
            if (!isbnPattern.test(isbn)) {
                e.preventDefault();
                alert('❌ Format ISBN tidak valid! Gunakan angka, spasi, dan tanda hubung saja.');
                document.getElementById('isbn').focus();
                return false;
            }
            
            // Halaman validation
            if (halaman > 9999) {
                e.preventDefault();
                alert('❌ Jumlah halaman tidak boleh lebih dari 9999!');
                document.getElementById('halaman').focus();
                return false;
            }
            
            // Confirmation
            const confirmation = confirm('📝 Apakah Anda yakin ingin menyimpan perubahan ini?');
            if (!confirmation) {
                e.preventDefault();
                return false;
            }
        });
        
        // Auto-format ISBN
        const isbnInput = document.getElementById('isbn');
        isbnInput.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            if (value.length >= 3) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }
            if (value.length >= 7) {
                value = value.substring(0, 7) + '-' + value.substring(7);
            }
            if (value.length >= 11) {
                value = value.substring(0, 11) + '-' + value.substring(11);
            }
            if (value.length >= 15) {
                value = value.substring(0, 15) + '-' + value.substring(15);
            }
            this.value = value;
        });
        
        // Real-time validation feedback
        const inputs = form.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = '#dc3545';
                } else {
                    this.style.borderColor = '#28a745';
                }
            });
            
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#28a745';
                }
            });
        });
        
        // Prevent accidental form submission
        window.addEventListener('beforeunload', function(e) {
            const formData = new FormData(form);
            let hasChanges = false;
            
            // Check if form has changes (simplified check)
            const originalValues = {
                isbn: '<?= htmlspecialchars($buku['isbn']) ?>',
                judul: '<?= htmlspecialchars($buku['judul']) ?>',
                kategori: '<?= $buku['kategori'] ?>',
                halaman: '<?= $buku['halaman'] ?>',
                pengarang: '<?= htmlspecialchars($buku['pengarang']) ?>'
            };
            
            for (let [key, value] of formData.entries()) {
                if (key !== 'id' && originalValues[key] && originalValues[key] !== value) {
                    hasChanges = true;
                    break;
                }
            }
            
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // Remove beforeunload listener when form is submitted
        form.addEventListener('submit', function() {
            window.removeEventListener('beforeunload', function() {});
        });
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S to save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('editForm').submit();
    }
    
    // Escape to cancel
    if (e.key === 'Escape') {
        if (confirm('❌ Apakah Anda yakin ingin membatalkan edit?')) {
            window.location.href = 'index.php';
        }
    }
});
</script>

</body>
</html>

<?php
$conn->close();
?>