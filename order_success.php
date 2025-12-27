<?php
require_once '../config/config.php';
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Check if order was successful
if (!isset($_SESSION['order_success'])) {
    header("Location: index.php");
    exit;
}

$order_number = $_SESSION['order_success'];
unset($_SESSION['order_success']);
?>

<section class="order-success">
    <div class="container">
        <div class="success-container">
            <div class="success-icon">✓</div>
            <h1>Order Successful!</h1>
            <p>Thank you for your purchase. Your order has been placed successfully.</p>
            <div class="order-details">
                <p><strong>Order Number:</strong> <?php echo $order_number; ?></p>
                <p>You will receive a confirmation email shortly.</p>
            </div>
            <div class="success-actions">
                <a href="index.php" class="btn-primary">Continue Shopping</a>
                <a href="products.php" class="btn-secondary">View More Products</a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>