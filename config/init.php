<?php
// Initialize application settings and auto-load classes for TechPioneer

// Set timezone
date_default_timezone_set('America/Los_Angeles');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once 'database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Check if we need to create data directories
$data_dirs = ['../data', '../data/orders', '../images'];
foreach ($data_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        // Create .htaccess for data directory protection
        if (strpos($dir, 'data') !== false) {
            file_put_contents($dir . '/.htaccess', "Allow from all\n");
        }
    }
}

// Check if products.json exists, if not create from database
$products_json_path = '../data/products.json';
if (!file_exists($products_json_path)) {
    // 先检查products表是否存在
    try {
        $checkTable = $db->query("SHOW TABLES LIKE 'products'");
        if ($checkTable->rowCount() > 0) {
            $query = "SELECT * FROM products";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            file_put_contents($products_json_path, json_encode($products, JSON_PRETTY_PRINT));
        } else {
            // 如果表不存在，创建一个空数组
            file_put_contents($products_json_path, json_encode([], JSON_PRETTY_PRINT));
        }
    } catch (PDOException $e) {
        // 如果查询失败，创建空数组
        file_put_contents($products_json_path, json_encode([], JSON_PRETTY_PRINT));
    }
}

// Auto-create admin user if not exists in wpov_fc_subscribers table
try {
    $admin_email = '2160502612@qq.com';
    $admin_first_name = 'Ying';
    $admin_last_name = 'Jerry';

    $query = "SELECT id FROM wpov_fc_subscribers WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $admin_email);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $query = "INSERT INTO wpov_fc_subscribers (first_name, last_name, email) 
                  VALUES (:first_name, :last_name, :email)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':first_name', $admin_first_name);
        $stmt->bindParam(':last_name', $admin_last_name);
        $stmt->bindParam(':email', $admin_email);
        $stmt->execute();
    }
} catch (PDOException $e) {
    // 如果插入失败，记录错误但不中断
    error_log("Failed to create admin user: " . $e->getMessage());
}
?>