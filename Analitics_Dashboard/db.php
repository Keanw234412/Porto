<?php
/**
 * Database Configuration
 * Perpustakaan Digital - Analytics Dashboard
 */

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "new_perpus";

// Create connection without selecting database first
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8 for Indonesian characters support
$conn->set_charset("utf8");

// Create database if not exists
$create_db_sql = "CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8 COLLATE utf8_general_ci";
if ($conn->query($create_db_sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($db);

// Create table if not exists
$create_table_sql = "CREATE TABLE IF NOT EXISTS buku (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(255) NOT NULL UNIQUE,
    judul VARCHAR(255) NOT NULL,
    kategori ENUM('fiksi', 'non-fiksi') NOT NULL,
    halaman INT UNSIGNED NOT NULL,
    pengarang VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_judul (judul),
    INDEX idx_kategori (kategori),
    INDEX idx_pengarang (pengarang),
    INDEX idx_created_at (created_at)
)";

if ($conn->query($create_table_sql) === FALSE) {
    die("Error creating table: " . $conn->error);
}

// Insert sample data if table is empty
$check_data = $conn->query("SELECT COUNT(*) as count FROM buku");
if ($check_data) {
    $row = $check_data->fetch_assoc();
    
    if ($row['count'] == 0) {
        $sample_data = "INSERT INTO buku (isbn, judul, kategori, halaman, pengarang) VALUES
            ('978-602-06-4123-4', 'Laskar Pelangi', 'fiksi', 529, 'Andrea Hirata'),
            ('978-979-22-3456-7', 'Sejarah Indonesia Modern', 'non-fiksi', 450, 'M.C. Ricklefs'),
            ('978-602-291-789-0', 'Ronggeng Dukuh Paruk', 'fiksi', 198, 'Ahmad Tohari'),
            ('978-979-22-1234-5', 'Filosofi Teras', 'non-fiksi', 320, 'Henry Manampiring'),
            ('978-602-03-5678-9', 'Ayat-Ayat Cinta', 'fiksi', 418, 'Habiburrahman El Shirazy'),
            ('978-979-22-9876-1', 'Sapiens: A Brief History of Humankind', 'non-fiksi', 512, 'Yuval Noah Harari'),
            ('978-602-06-1357-2', 'Cantik Itu Luka', 'fiksi', 518, 'Eka Kurniawan'),
            ('978-979-22-2468-3', 'The Art of War', 'non-fiksi', 273, 'Sun Tzu'),
            ('978-602-03-8642-1', 'Negeri 5 Menara', 'fiksi', 416, 'Ahmad Fuadi'),
            ('978-979-22-7531-9', 'Rich Dad Poor Dad', 'non-fiksi', 336, 'Robert Kiyosaki')";
        
        if ($conn->query($sample_data) === FALSE) {
            error_log("Error inserting sample data: " . $conn->error);
        }
    }
}

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to validate ISBN format
function validate_isbn($isbn) {
    // Remove hyphens and spaces
    $isbn = preg_replace('/[\-\s]/', '', $isbn);
    
    // Check if it's 10 or 13 digits
    if (strlen($isbn) == 10 || strlen($isbn) == 13) {
        return preg_match('/^\d+$/', $isbn);
    }
    
    return false;
}

// Error reporting for development (disable in production)
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
}
?>