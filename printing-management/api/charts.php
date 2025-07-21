<?php
// api/charts.php
header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Akses ditolak']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // 1. Data untuk Grafik Pendapatan 6 Bulan Terakhir
    $revenue_data = [];
    $month_labels = [];
    for ($i = 5; $i >= 0; $i--) {
        $date = new DateTime("first day of -$i months");
        $month = $date->format('m');
        $year = $date->format('Y');
        $month_labels[] = $date->format('M Y');

        $stmt = $conn->prepare("SELECT SUM(total_price) as monthly_revenue FROM orders WHERE MONTH(order_date) = ? AND YEAR(order_date) = ?");
        $stmt->execute([$month, $year]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $revenue_data[] = $result['monthly_revenue'] ?? 0;
    }

    // 2. Data untuk Grafik Status Pesanan (Doughnut Chart)
    $status_counts = [];
    $statuses = ['pending', 'processing', 'completed', 'cancelled'];
    foreach ($statuses as $status) {
        $stmt = $conn->prepare("SELECT COUNT(id) as count FROM orders WHERE status = ?");
        $stmt->execute([$status]);
        $status_counts[] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'revenueChart' => [
                'labels' => $month_labels,
                'data' => $revenue_data,
            ],
            'statusChart' => [
                'labels' => ['Pending', 'Processing', 'Completed', 'Cancelled'],
                'data' => $status_counts,
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>