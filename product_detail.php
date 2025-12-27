<?php
require_once '../config/config.php';
require_once '../includes/header.php';

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = $_GET['id'];

// 从JSON文件读取产品数据以确保一致性
$products_json = file_get_contents('../data/products.json');
$all_products = json_decode($products_json, true);

// 查找指定ID的产品
$product = null;
foreach ($all_products as $p) {
    if ($p['id'] == $product_id) {
        $product = $p;
        break;
    }
}

if (!$product) {
    echo "Product not found.";
    require_once '../includes/footer.php';
    exit;
}

$finalPrice = $product['price'] * (1 - $product['discount'] / 100);
$specifications = is_array($product['specifications']) ? $product['specifications'] : [];
?>

<section class="product-detail">
    <div class="container">
        <div class="product-detail-container">
            <div class="product-image">
                <img src="<?php echo getImagePath($product['image_url']); ?>" 
                     alt="<?php echo $product['name']; ?>"
                     onerror="this.src='https://via.placeholder.com/400x300/cccccc/969696?text=Image+Loading+Failed'">
            </div>
            <div class="product-info">
                <h1><?php echo $product['name']; ?></h1>
                <div class="price">
                    <?php if ($product['discount'] > 0): ?>
                        <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                        <span class="discounted-price">$<?php echo number_format($finalPrice, 2); ?></span>
                        <span class="discount">-<?php echo $product['discount']; ?>%</span>
                    <?php else: ?>
                        <span class="final-price">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php endif; ?>
                </div>
                <p class="description"><?php echo $product['description']; ?></p>
                
                <?php if (!empty($specifications)): ?>
                <div class="specifications">
                    <h3>Specifications</h3>
                    <ul>
                        <?php foreach ($specifications as $key => $value): ?>
                            <li><strong><?php echo ucfirst($key); ?>:</strong> <?php echo $value; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="stock-info">
                    <p><strong>Availability:</strong> 
                        <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                    </p>
                </div>
                
                <?php if (isLoggedIn() && $product['stock_quantity'] > 0): ?>
                <div class="product-actions">
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                </div>
                <?php elseif (!isLoggedIn()): ?>
                <p>Please <a href="login.php">login</a> to add items to your cart.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Products Section -->
        <div class="related-products">
            <h2>Related Products</h2>
            <div class="products-grid">
                <?php
                // 从同一类别中查找相关产品
                $related_products = [];
                foreach ($all_products as $p) {
                    if ($p['category'] == $product['category'] && $p['id'] != $product_id) {
                        $related_products[] = $p;
                        if (count($related_products) >= 4) break;
                    }
                }
                
                foreach ($related_products as $relatedProduct) {
                    $relatedFinalPrice = $relatedProduct['price'] * (1 - $relatedProduct['discount'] / 100);
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo getImagePath($relatedProduct['image_url']); ?>" 
                                 alt="<?php echo $relatedProduct['name']; ?>"
                                 onerror="this.src='https://via.placeholder.com/300x200/cccccc/969696?text=Image+Loading+Failed'">
                        </div>
                        <div class="product-info">
                            <h3><?php echo $relatedProduct['name']; ?></h3>
                            <div class="price">
                                <?php if ($relatedProduct['discount'] > 0): ?>
                                    <span class="original-price">$<?php echo number_format($relatedProduct['price'], 2); ?></span>
                                    <span class="discounted-price">$<?php echo number_format($relatedFinalPrice, 2); ?></span>
                                <?php else: ?>
                                    <span class="final-price">$<?php echo number_format($relatedProduct['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="product_detail.php?id=<?php echo $relatedProduct['id']; ?>" class="view-details">View Details</a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = document.getElementById('quantity').value;
            
            fetch('../api/add_to_cart.php', {
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
                    alert('Product added to cart successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product to cart.');
            });
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>