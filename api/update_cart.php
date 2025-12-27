<?php
// Update cart item quantity API endpoint
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to update cart.']);
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

// Check if product is in cart
if (!isset($_SESSION['cart'][$product_id])) {
    echo json_encode(['success' => false, 'message' => 'Product not in cart.']);
    exit;
}

// Validate quantity
if ($quantity <= 0) {
    // Remove item from cart if quantity is 0 or negative
    unset($_SESSION['cart'][$product_id]);
    echo json_encode(['success' => true, 'message' => 'Product removed from cart.']);
    exit;
}

if ($quantity > $product['stock_quantity']) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock.']);
    exit;
}

// Update cart quantity
$_SESSION['cart'][$product_id]['quantity'] = $quantity;

echo json_encode(['success' => true, 'message' => 'Cart updated.']);
?>