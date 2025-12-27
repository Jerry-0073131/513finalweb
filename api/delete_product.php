<?php
// Delete product API endpoint (for dashboard)
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
if (!isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$product_id = intval($input['product_id']);

try {
    // Check if product exists
    $query = "SELECT * FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }
    
    // Check if product is in any orders
    $orderQuery = "SELECT COUNT(*) as order_count FROM order_items WHERE product_id = :product_id";
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->bindParam(':product_id', $product_id);
    $orderStmt->execute();
    $orderCount = $orderStmt->fetch(PDO::FETCH_ASSOC)['order_count'];
    
    if ($orderCount > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete product that has been ordered.']);
        exit;
    }
    
    // Delete product from database
    $deleteQuery = "DELETE FROM products WHERE id = :id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':id', $product_id);
    
    if ($deleteStmt->execute()) {
        // Update products.json file
        updateProductsJSON($db);
        
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete product.']);
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