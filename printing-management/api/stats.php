<?php
// api/stats.php

header('Content-Type: application/json');
session_start();

// Validasi otentikasi
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'error' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit;
}

require_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // 1. Hitung Total Pelanggan
    $stmt_customers = $conn->prepare("SELECT COUNT(id) as total FROM customers");
    $stmt_customers->execute();
    $total_customers = $stmt_customers->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Hitung Total Produk
    $stmt_products = $conn->prepare("SELECT COUNT(id) as total FROM products");
    $stmt_products->execute();
    $total_products = $stmt_products->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Hitung Pesanan Bulan Ini
    $stmt_orders = $conn->prepare("SELECT COUNT(id) as total FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())");
    $stmt_orders->execute();
    $orders_this_month = $stmt_orders->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'data' => [
            'customerCount' => $total_customers,
            'productCount' => $total_products,
            'orderCount' => $orders_this_month
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'error' => 'Gagal mengambil data statistik: ' . $e->getMessage()]);
}
?>