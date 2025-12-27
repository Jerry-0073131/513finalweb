<?php
// Add product to cart API endpoint
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$product_id = intval($input['product_id']);
$quantity = intval($input['quantity']);

// Validate product exists and has sufficient stock
$query = "SELECT * FROM products WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $product_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock.']);
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart or update quantity
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = [
        'quantity' => $quantity,
        'added_at' => date('Y-m-d H:i:s')
    ];
}

// Ensure cart quantity doesn't exceed stock
if ($_SESSION['cart'][$product_id]['quantity'] > $product['stock_quantity']) {
    $_SESSION['cart'][$product_id]['quantity'] = $product['stock_quantity'];
}

echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
?>