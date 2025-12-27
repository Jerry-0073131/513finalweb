<?php
// Remove product from cart API endpoint
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to modify cart.']);
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

// Check if product is in cart
if (!isset($_SESSION['cart'][$product_id])) {
    echo json_encode(['success' => false, 'message' => 'Product not in cart.']);
    exit;
}

// Remove product from cart
unset($_SESSION['cart'][$product_id]);

echo json_encode(['success' => true, 'message' => 'Product removed from cart.']);
?>