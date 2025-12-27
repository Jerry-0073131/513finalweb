<?php
// Get analytics data API endpoint
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../includes/auth.php';

// Check if user is admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    // Basic analytics
    $analytics = [];
    
    // Total products
    $query = "SELECT COUNT(*) as total FROM products";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total orders
    $query = "SELECT COUNT(*) as total FROM orders";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total customers
    $query = "SELECT COUNT(*) as total FROM users WHERE is_admin = 0";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total revenue
    $query = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    // Monthly revenue (last 6 months)
    $query = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_amount) as revenue
              FROM orders 
              WHERE status != 'cancelled' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY month DESC
              LIMIT 6";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['monthly_revenue'] = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Top selling products
    $query = "SELECT 
                p.name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.unit_price) as total_revenue
              FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              JOIN orders o ON oi.order_id = o.id
              WHERE o.status != 'cancelled'
              GROUP BY p.id, p.name
              ORDER BY total_sold DESC
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['top_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Orders by status
    $query = "SELECT 
                status,
                COUNT(*) as count
              FROM orders 
              GROUP BY status";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['orders_by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Products by category
    $query = "SELECT 
                category,
                COUNT(*) as count
              FROM products 
              GROUP BY category";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['products_by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent orders (last 10)
    $query = "SELECT 
                o.*,
                u.first_name,
                u.last_name
              FROM orders o
              JOIN users u ON o.user_id = u.id
              ORDER BY o.created_at DESC
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['recent_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Low stock products
    $query = "SELECT 
                name,
                stock_quantity
              FROM products 
              WHERE stock_quantity <= 10
              ORDER BY stock_quantity ASC
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $analytics['low_stock'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'analytics' => $analytics
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>