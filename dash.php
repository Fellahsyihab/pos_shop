<?php

class Dashboard
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getCurrentDateTime()
    {
        date_default_timezone_set('Asia/Jakarta');
        return date('l, j F Y H:i:s');
    }

    public function getTotalProducts()
    {
        $sqlProducts = "SELECT COUNT(*) AS totalProducts FROM products";
        $resultProducts = $this->conn->query($sqlProducts);
        $rowProducts = $resultProducts->fetch_assoc();
        return $rowProducts['totalProducts'];
    }

    public function getTotalCustomers()
    {
        $sqlCustomers = "SELECT COUNT(*) AS totalCustomers FROM customers";
        $resultCustomers = $this->conn->query($sqlCustomers);
        $rowCustomers = $resultCustomers->fetch_assoc();
        return $rowCustomers['totalCustomers'];
    }

    public function getTotalVendors()
    {
        $sqlVendors = "SELECT COUNT(*) AS totalVendors FROM vendors";
        $resultVendors = $this->conn->query($sqlVendors);
        $rowVendors = $resultVendors->fetch_assoc();
        return $rowVendors['totalVendors'];
    }
}
?>
