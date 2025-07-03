<?php
/**
 * Delete Book Handler
 * Perpustakaan Digital
 */

include 'db.php';

// Security check: only allow GET requests with valid ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=" . urlencode("ID buku tidak valid!"));
    exit;
}

$id = (int)$_GET['id'];

if ($id <= 0) {
    header("Location: index.php?error=" . urlencode("ID buku tidak valid!"));
    exit;
}

try {
    // Get book data first for confirmation message
    $stmt = $conn->prepare("SELECT judul, pengarang, isbn FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $buku = $result->fetch_assoc();
        $judul_buku = $buku['judul'];
        $pengarang_buku = $buku['pengarang'];
        $isbn_buku = $buku['isbn'];
        
        // Delete the book
        $delete_stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                // Log the deletion (optional)
                error_log("Book deleted: ID=$id, Title='$judul_buku', Author='$pengarang_buku', ISBN='$isbn_buku'");
                
                // Redirect with success message
                $success_message = "Buku '$judul_buku' oleh $pengarang_buku berhasil dihapus!";
                header("Location: index.php?message=" . urlencode($success_message));
            } else {
                // No rows affected - book might have been deleted already
                header("Location: index.php?error=" . urlencode("Buku tidak ditemukan atau sudah dihapus!"));
            }
        } else {
            // Database error during deletion
            error_log("Failed to delete book ID=$id: " . $conn->error);
            header("Location: index.php?error=" . urlencode("Gagal menghapus buku! Silakan coba lagi."));
        }
        
        $delete_stmt->close();
    } else {
        // Book not found
        header("Location: index.php?error=" . urlencode("Buku tidak ditemukan!"));
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Handle any unexpected errors
    error_log("Error in delete.php: " . $e->getMessage());
    header("Location: index.php?error=" . urlencode("Terjadi kesalahan sistem! Silakan coba lagi."));
} finally {
    // Always close the connection
    if (isset($conn)) {
        $conn->close();
    }
}

exit;
?>