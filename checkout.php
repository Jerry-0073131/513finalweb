<?php
require_once '../config/config.php';
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$cart = $_SESSION['cart'];
$total = 0;
$userId = $_SESSION['user_id'];

// 首先验证用户是否存在于 wpov_fc_subscribers 表
$userCheckQuery = "SELECT id, email, first_name, last_name FROM wpov_fc_subscribers WHERE id = :user_id";
$userCheckStmt = $db->prepare($userCheckQuery);
$userCheckStmt->bindParam(':user_id', $userId);
$userCheckStmt->execute();

if ($userCheckStmt->rowCount() == 0) {
    // 用户不存在于 wpov_fc_subscribers 表，重定向到登录页面
    session_destroy();
    header("Location: login.php?error=user_not_found");
    exit;
}

$userData = $userCheckStmt->fetch(PDO::FETCH_ASSOC);
$userEmail = $userData['email'];

// Calculate totals
foreach ($cart as $product_id => $item) {
    $query = "SELECT * FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $finalPrice = $product['price'] * (1 - $product['discount'] / 100);
        $total += $finalPrice * $item['quantity'];
    }
}

$shipping = $total > 100 ? 0 : 10;
$tax = $total * 0.08;
$grandTotal = $total + $shipping + $tax;

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    $payment_method = $_POST['payment_method'];
    
    if (empty($shipping_address)) {
        $error = 'Please provide a shipping address.';
    } else {
        // Generate unique order number
        $order_number = 'TP' . date('Ymd') . strtoupper(uniqid());
        
        try {
            // 开始事务之前，先禁用外键检查（如果外键仍然存在）
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            $db->beginTransaction();
            
            // 验证用户是否存在于 wpov_fc_subscribers 表（再次验证）
            $verifyUserQuery = "SELECT id FROM wpov_fc_subscribers WHERE id = :user_id";
            $verifyUserStmt = $db->prepare($verifyUserQuery);
            $verifyUserStmt->bindParam(':user_id', $userId);
            $verifyUserStmt->execute();
            
            if ($verifyUserStmt->rowCount() == 0) {
                throw new Exception("User not found in subscribers table. Please log in again.");
            }
            
            // Create order - 这里我们添加所有必要的字段，包括 payment_method
            $query = "INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method) 
                      VALUES (:user_id, :order_number, :total_amount, :shipping_address, :payment_method)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':order_number', $order_number);
            $stmt->bindParam(':total_amount', $grandTotal);
            $stmt->bindParam(':shipping_address', $shipping_address);
            $stmt->bindParam(':payment_method', $payment_method);
            $stmt->execute();
            
            $order_id = $db->lastInsertId();
            
            // Create order items and update product stock
            foreach ($cart as $product_id => $item) {
                $productQuery = "SELECT * FROM products WHERE id = :id";
                $productStmt = $db->prepare($productQuery);
                $productStmt->bindParam(':id', $product_id);
                $productStmt->execute();
                $product = $productStmt->fetch(PDO::FETCH_ASSOC);
                
                $finalPrice = $product['price'] * (1 - $product['discount'] / 100);
                
                // Add order item
                $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                              VALUES (:order_id, :product_id, :quantity, :unit_price)";
                $itemStmt = $db->prepare($itemQuery);
                $itemStmt->bindParam(':order_id', $order_id);
                $itemStmt->bindParam(':product_id', $product_id);
                $itemStmt->bindParam(':quantity', $item['quantity']);
                $itemStmt->bindParam(':unit_price', $finalPrice);
                $itemStmt->execute();
                
                // Update product stock
                $newStock = $product['stock_quantity'] - $item['quantity'];
                $updateQuery = "UPDATE products SET stock_quantity = :stock WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':stock', $newStock);
                $updateStmt->bindParam(':id', $product_id);
                $updateStmt->execute();
            }
            
            // 重新启用外键检查
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            // Generate order JSON file
            // 修改查询以获取用户信息
            $orderQuery = "SELECT o.*, oi.*, p.name 
                           FROM orders o 
                           JOIN order_items oi ON o.id = oi.order_id 
                           JOIN products p ON oi.product_id = p.id 
                           WHERE o.id = :order_id";
            $orderStmt = $db->prepare($orderQuery);
            $orderStmt->bindParam(':order_id', $order_id);
            $orderStmt->execute();
            $orderDetails = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 获取订阅者信息
            $subscriberQuery = "SELECT * FROM wpov_fc_subscribers WHERE id = :user_id";
            $subscriberStmt = $db->prepare($subscriberQuery);
            $subscriberStmt->bindParam(':user_id', $userId);
            $subscriberStmt->execute();
            $subscriber = $subscriberStmt->fetch(PDO::FETCH_ASSOC);
            
            $orderData = [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'customer_id' => $userId,
                'customer_email' => $userEmail,
                'customer_name' => $subscriber['firstname'] . ' ' . $subscriber['last_name'],
                'order_date' => date('Y-m-d H:i:s'),
                'total_amount' => $grandTotal,
                'shipping_address' => $shipping_address,
                'payment_method' => $payment_method,
                'items' => []
            ];
            
            foreach ($orderDetails as $item) {
                $orderData['items'][] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price']
                ];
            }
            
            // Save order to JSON file
            $ordersDir = '../data/orders/';
            if (!is_dir($ordersDir)) {
                mkdir($ordersDir, 0777, true);
            }
            
            $orderFile = $ordersDir . 'order_' . $order_number . '.json';
            file_put_contents($orderFile, json_encode($orderData, JSON_PRETTY_PRINT));
            
            $db->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to success page
            $_SESSION['order_success'] = $order_number;
            header("Location: order_success.php");
            exit;
            
        } catch (Exception $e) {
            // 确保在回滚后也重新启用外键检查
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            $db->rollBack();
            $error = 'An error occurred during checkout: ' . $e->getMessage();
        }
    }
}
?>

<section class="checkout-page">
    <div class="container">
        <h1>Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <div class="checkout-form">
                <h2>Shipping Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="shipping_address">Shipping Address</label>
                        <textarea id="shipping_address" name="shipping_address" rows="4" required placeholder="Enter your complete shipping address"><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                        </div>
                        
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123">
                        </div>
                    </div>
                    
                    <button type="submit" class="place-order-btn">Place Order</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="summary-items">
                    <?php foreach ($cart as $product_id => $item): 
                        $query = "SELECT * FROM products WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':id', $product_id);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            $product = $stmt->fetch(PDO::FETCH_ASSOC);
                            $finalPrice = $product['price'] * (1 - $product['discount'] / 100);
                            $itemTotal = $finalPrice * $item['quantity'];
                        ?>
                        <div class="summary-item">
                            <span class="item-name"><?php echo $product['name']; ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span class="item-price">$<?php echo number_format($itemTotal, 2); ?></span>
                        </div>
                    <?php } endforeach; ?>
                </div>
                
                <div class="summary-totals">
                    <div class="summary-line">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Shipping:</span>
                        <span>$<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Tax:</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="summary-line total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($grandTotal, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>