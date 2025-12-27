<?php
// Add product API endpoint (for dashboard)
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../includes/auth.php';

// Check if user is admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$required_fields = ['name', 'description', 'price', 'category', 'stock_quantity'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize input
$name = trim($input['name']);
$description = trim($input['description']);
$price = floatval($input['price']);
$discount = isset($input['discount']) ? floatval($input['discount']) : 0;
$category = trim($input['category']);
$stock_quantity = intval($input['stock_quantity']);
$image_url = isset($input['image_url']) ? trim($input['image_url']) : '';
$specifications = isset($input['specifications']) ? $input['specifications'] : [];

// Validate price and stock
if ($price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Price must be greater than 0.']);
    exit;
}

if ($stock_quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Stock quantity cannot be negative.']);
    exit;
}

if ($discount < 0 || $discount > 100) {
    echo json_encode(['success' => false, 'message' => 'Discount must be between 0 and 100.']);
    exit;
}

try {
    // Insert product into database
    $query = "INSERT INTO products (name, description, price, discount, category, stock_quantity, image_url, specifications) 
              VALUES (:name, :description, :price, :discount, :category, :stock_quantity, :image_url, :specifications)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':discount', $discount);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':stock_quantity', $stock_quantity);
    $stmt->bindParam(':image_url', $image_url);
    $stmt->bindParam(':specifications', json_encode($specifications));
    
    if ($stmt->execute()) {
        $product_id = $db->lastInsertId();
        
        // Update products.json file
        updateProductsJSON($db);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product added successfully.',
            'product_id' => $product_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

// Function to update products.json file
function updateProductsJSON($db) {
    $query = "SELECT * FROM products";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    file_put_contents('../data/products.json', json_encode($products, JSON_PRETTY_PRINT));
}
?>