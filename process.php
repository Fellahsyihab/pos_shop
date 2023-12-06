<?php
include 'koneksi.php';
include 'product.php';

// Membuat instance dari kelas Database
$database = new Database();
$conn = $database->getConnection();

// Mengecek apakah form telah disubmit melalui metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Membuat instance dari kelas Product dengan parameter koneksi database
    $product = new Product($conn);

    // Jika tombol "Tambah Produk" ditekan
    if (isset($_POST["add"])) {
        $product->addProduct($_POST, $_FILES);
    } 
    // Jika tombol "Edit Produk" ditekan
    elseif (isset($_POST["edit"])) {
        // Proses penyimpanan perubahan
        $product->editProduct($_POST, $_FILES);
    }
    // Jika tombol "Hapus Produk" ditekan
    elseif (isset($_POST["delete"])) {
        $product->deleteProduct($_POST['id']);
    }
}
?>
