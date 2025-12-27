<?php
// General utility functions for TechPioneer

// Function to generate order number
function generateOrderNumber() {
    return 'TP' . date('YmdHis') . mt_rand(1000, 9999);
}

// Function to calculate final price with discount
function calculateFinalPrice($price, $discount) {
    return $price * (1 - $discount / 100);
}

// Function to format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to check if product is in stock
function isProductInStock($product_id, $db) {
    $query = "SELECT stock_quantity FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    return $product && $product['stock_quantity'] > 0;
}

// Function to get cart total
function getCartTotal($db) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $query = "SELECT price, discount FROM products WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $finalPrice = calculateFinalPrice($product['price'], $product['discount']);
            $total += $finalPrice * $item['quantity'];
        }
    }
    
    return $total;
}

// Function to get cart item count
function getCartItemCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

// Function to update products JSON file
function updateProductsJSON($db) {
    $query = "SELECT * FROM products";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    file_put_contents('../data/products.json', json_encode($products, JSON_PRETTY_PRINT));
}

// Function to log errors
function logError($message) {
    $log_file = '../data/error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

// Function to validate password strength
function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

// Function to get user by email
function getUserByEmail($email, $db) {
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get product by ID
function getProductById($id, $db) {
    $query = "SELECT * FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>