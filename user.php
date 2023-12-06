<?php

class User
{
    private $conn;

    // Constructor untuk mendapatkan koneksi database
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Metode untuk melakukan login
    public function login($phone, $password)
    {
        $query = "SELECT * FROM users WHERE phone_number=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return $user;
            } else {
                return false;
            }
        } else {
            return false;
        }

        $stmt->close();
    }

    // Metode untuk melakukan registrasi
    public function register($name, $email, $phone, $password, $group_id)
    {
        // Hash password (gunakan password_hash untuk keamanan)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (name, email, phone_number, username, password, group_id) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssi", $name, $email, $phone, $phone, $hashedPassword, $group_id);

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}
?>
