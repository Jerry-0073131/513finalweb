<?php
require_once '../config/config.php';
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$total = 0;
?>

<section class="cart-page">
    <div class="container">
        <h1>Your Shopping Cart</h1>
        
        <?php if (empty($cart)): ?>
            <div class="empty-cart">
                <p>Your cart is empty.</p>
                <a href="products.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($cart as $product_id => $item): 
                        // Fetch product details from database
                        $query = "SELECT * FROM products WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':id', $product_id);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            $product = $stmt->fetch(PDO::FETCH_ASSOC);
                            $finalPrice = $product['price'] * (1 - $product['discount'] / 100);
                            $itemTotal = $finalPrice * $item['quantity'];
                            $total += $itemTotal;
                        ?>
                        <div class="cart-item" data-product-id="<?php echo $product_id; ?>">
                            <div class="item-image">
                                <img src="<?php echo $product['image_url'] ?: 'images/placeholder.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo $product['name']; ?></h3>
                                <p class="item-price">$<?php echo number_format($finalPrice, 2); ?></p>
                                <?php if ($product['discount'] > 0): ?>
                                    <p class="item-original-price">$<?php echo number_format($product['price'], 2); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="item-quantity">
                                <button class="quantity-btn minus" data-action="decrease">-</button>
                                <span class="quantity"><?php echo $item['quantity']; ?></span>
                                <button class="quantity-btn plus" data-action="increase">+</button>
                            </div>
                            <div class="item-total">
                                $<?php echo number_format($itemTotal, 2); ?>
                            </div>
                            <div class="item-actions">
                                <button class="remove-btn" data-product-id="<?php echo $product_id; ?>">Remove</button>
                            </div>
                        </div>
                    <?php } endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-line">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Shipping:</span>
                        <span>$<?php echo $total > 100 ? '0.00' : '10.00'; ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Tax:</span>
                        <span>$<?php echo number_format($total * 0.08, 2); ?></span>
                    </div>
                    <div class="summary-line total">
                        <span>Total:</span>
                        <span>$<?php 
                            $shipping = $total > 100 ? 0 : 10;
                            $tax = $total * 0.08;
                            echo number_format($total + $shipping + $tax, 2); 
                        ?></span>
                    </div>
                    <div class="cart-actions">
                        <a href="products.php" class="continue-shopping">Continue Shopping</a>
                        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity buttons
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const productId = this.closest('.cart-item').getAttribute('data-product-id');
            const quantityElement = this.parentElement.querySelector('.quantity');
            let quantity = parseInt(quantityElement.textContent);
            
            if (action === 'increase') {
                quantity++;
            } else if (action === 'decrease' && quantity > 1) {
                quantity--;
            }
            
            if (action === 'increase' || (action === 'decrease' && quantity >= 1)) {
                // Update cart via API
                fetch('../api/update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to update totals
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the cart.');
                });
            }
        });
    });
    
    // Remove buttons
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                fetch('../api/remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove item from DOM
                        document.querySelector(`.cart-item[data-product-id="${productId}"]`).remove();
                        // Reload page to update totals
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the item.');
                });
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>