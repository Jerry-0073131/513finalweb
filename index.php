<?php
require_once '../config/config.php';
require_once '../includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h1>Welcome to TechPioneer</h1>
        <p>Discover the latest in electronics and technology</p>
        <a href="products.php" class="cta-button">Shop Now</a>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php
            // 从JSON文件读取产品数据以确保一致性
            $products_json = file_get_contents('../data/products.json');
            $products = json_decode($products_json, true);
            
            foreach ($products as $product) {
                $finalPrice = $product['price'] * (1 - $product['discount'] / 100);
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <!-- 使用新的图片路径函数 -->
                        <img src="<?php echo getImagePath($product['image_url']); ?>" 
                             alt="<?php echo $product['name']; ?>"
                             onerror="this.src='https://via.placeholder.com/300x200/cccccc/969696?text=Image+Loading+Failed'">
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <div class="price">
                            <?php if ($product['discount'] > 0): ?>
                                <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <span class="discounted-price">$<?php echo number_format($finalPrice, 2); ?></span>
                                <span class="discount">-<?php echo $product['discount']; ?>%</span>
                            <?php else: ?>
                                <span class="final-price">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo substr($product['description'], 0, 100) . '...'; ?></p>
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="view-details">View Details</a>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature">
                <h3>Free Shipping</h3>
                <p>Free shipping on orders over $100</p>
            </div>
            <div class="feature">
                <h3>30-Day Returns</h3>
                <p>30-day money back guarantee</p>
            </div>
            <div class="feature">
                <h3>Secure Payment</h3>
                <p>Your payment information is safe with us</p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>