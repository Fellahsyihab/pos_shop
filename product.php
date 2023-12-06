<?php
class Product {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllProducts() {
        $sql = "SELECT * FROM products";
        $result = $this->conn->query($sql);
        $products = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function addProduct($data, $files) {
        // Handle multiple image upload
        $imagePath = $this->handleMultipleImageUpload($files);
    
        // Escape user inputs for security
        $name = isset($data['name']) ? $this->conn->real_escape_string($data['name']) : '';
        $categoryName = isset($data['category']) ? $this->conn->real_escape_string($data['category']) : '';
        $code = isset($data['code']) ? $this->conn->real_escape_string($data['code']) : '';
        $unit = isset($data['unit']) ? $this->conn->real_escape_string($data['unit']) : '';
        $description = isset($data['description']) ? $this->conn->real_escape_string($data['description']) : '';
        $price = isset($data['price']) ? $this->conn->real_escape_string($data['price']) : '';
        $stock = isset($data['stock']) ? $this->conn->real_escape_string($data['stock']) : '';
    
        // Check if the product code already exists
        if ($this->isProductCodeExists($code)) {
            // Modify the code to make it unique
            $code = $this->generateUniqueProductCode($code);
        }
    
        // Ambil ID kategori berdasarkan nama kategori
        $categoryId = $this->getCategoryIdByName($categoryName);
    
        // Periksa apakah kategori ditemukan
        if ($categoryId === null) {
            echo "Error: Kategori tidak ditemukan.";
            exit();
        }
    
        // Insert data into database
        $sql = "INSERT INTO products (product_name, category_id, product_code, unit, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisssdss", $name, $categoryId, $code, $unit, $description, $price, $stock, $imagePath);
    
        if ($stmt->execute()) {
            // Data inserted successfully
            header("Location: index.php");
            exit();
        } else {
            // Error in inserting data
            echo "Error: " . $stmt->error;
        }
    }
    
    private function isProductCodeExists($code) {
        $sql = "SELECT COUNT(*) as count FROM products WHERE product_code = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        return $row['count'] > 0;
    }
    
    private function generateUniqueProductCode($code) {
        // Generate a unique code by appending a timestamp or other unique identifier
        return $code . '_' . time();
    }
    

    private function getCategoryIdByName($categoryName) {
        $sql = "SELECT id FROM product_categories WHERE category_name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        } else {
            return null;
        }
    }

    public function editProduct($data, $files) {
        // Tangani upload gambar ganda dan simpan sebagai JSON
        $imagePath = $this->handleMultipleImageUpload($files);
    
        // Escape user inputs for security
        $id = $this->conn->real_escape_string($data['id']);
        $name = $this->conn->real_escape_string($data['name']);
        $categoryName = $this->conn->real_escape_string($data['category']);
        $code = $this->conn->real_escape_string($data['code']);
        $unit = $this->conn->real_escape_string($data['unit']);
        $description = $this->conn->real_escape_string($data['description']);
        $price = $this->conn->real_escape_string($data['price']);
        $stock = $this->conn->real_escape_string($data['stock']);
    
        // Dapatkan ID kategori berdasarkan nama kategori
        $categoryId = $this->getCategoryIdByName($categoryName);
    
        if ($categoryId === null) {
            // Kategori tidak ditemukan, mungkin perlu menangani ini sesuai kebutuhan Anda
            echo "Error: Category not found.";
            return;
        }
    
        // Dapatkan gambar-gambar lama dari produk
        $oldImages = json_decode($this->getProductById($id)['image'], true);
    
        // Jika ada gambar baru, tambahkan ke gambar-gambar lama
        if ($imagePath) {
            $newImages = json_decode($imagePath, true);
            $allImages = array_merge($oldImages, $newImages);
        } else {
            // Jika tidak ada gambar baru, gunakan gambar-gambar lama
            $allImages = $oldImages;
        }
    
        // Konversi ke format JSON
        $imagePath = json_encode($allImages);
    
       // Update data in database
        $sql = "UPDATE products SET product_name=?, category_id=?, product_code=?, unit=?, description=?, price=?, stock=?, image=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssdsi", $name, $categoryId, $code, $unit, $description, $price, $stock, $imagePath, $id);
    
        // Execute the statement
        if ($stmt->execute()) {
            // Data updated successfully
            $stmt->close();
            header("Location: index.php");
            exit();
        } else {
            // Error in updating data
            echo "Error updating data: " . $stmt->error;
            $stmt->close();
        }
    }    
       
    
    public function deleteProduct($id) {
        // Delete data from database
        $sql = "DELETE FROM products WHERE id=$id";

        if ($this->conn->query($sql) === TRUE) {
            // Data deleted successfully
            header("Location: index.php");
            exit();
        } else {
            // Error in deleting data
            echo "Error: " . $sql . "<br>" . $this->conn->error;
        }
    }


    public function handleMultipleImageUpload($files) {
        $imagePaths = [];

        foreach ($files['image']['tmp_name'] as $key => $tmp_name) {
            $file_name = $files['image']['name'][$key];
            $file_size = $files['image']['size'][$key];
            $file_tmp = $files['image']['tmp_name'][$key];
            $file_type = $files['image']['type'][$key];

            $extensions = array("jpeg", "jpg", "png");
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $extensions) === false) {
                // Invalid file extension
                echo "Extension not allowed, please choose a JPEG or PNG file.";
                exit();
            }

            $newFileName = uniqid() . "." . $file_ext;
            $uploadPath = "uploads/" . $newFileName;

            if (move_uploaded_file($file_tmp, $uploadPath)) {
                $imagePaths[] = $uploadPath;
            } else {
                // Error in uploading file
                echo "Error uploading file.";
                exit();
            }
        }

        return json_encode($imagePaths);
    }
}
?>
