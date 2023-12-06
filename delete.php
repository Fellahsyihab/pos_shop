<?php
include 'koneksi.php';
include 'product.php';

// Membuat instance dari kelas Product dengan parameter koneksi database
$database = new Database();
$conn = $database->getConnection();
$product = new Product($conn);

// Mengecek apakah permintaan HTTP adalah POST dan apakah terdapat parameter 'id' pada formulir
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $product->deleteProduct($id);

    // Redirect kembali ke halaman utama setelah menghapus
    header("Location: index.php");
    exit();
} else {
    // Handle jika permintaan tidak sesuai kebutuhan
    echo "Permintaan tidak valid.";
}
?>
