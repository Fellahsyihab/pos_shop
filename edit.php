<?php
include 'koneksi.php';
include 'product.php';

// Membuat instance dari kelas Product dengan parameter koneksi database
$product = new Product($conn);

// Mengecek apakah permintaan HTTP adalah GET dan apakah terdapat parameter 'id' pada URL
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];
    $editedProduct = $product->getProductById($id);

    // Mengecek apakah produk dengan ID yang diberikan ditemukan
    if ($editedProduct) {
        include 'edit_form.php'; // Separate HTML and PHP logic
    } else {
        // Produk tidak ditemukan, handle sesuai kebutuhan
        echo "Produk tidak ditemukan.";
    }
}
?>
