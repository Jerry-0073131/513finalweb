<?php
require_once '../config/config.php';
require_once '../includes/header.php';

// Get category filter
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 从JSON文件读取产品数据以确保一致性
$products_json = file_get_contents('../data/products.json');
$all_products = json_decode($products_json, true);

// 过滤产品
$filtered_products = [];
foreach ($all_products as $product) {
    // 分类过滤
    if ($category != 'all' && $product['category'] != $category) {
        continue;
    }
    
    // 搜索过滤
    if (!empty($search)) {
        $search_lower = strtolower($search);
        $name_lower = strtolower($product['name']);
        $desc_lower = strtolower($product['description']);
        
        if (strpos($name_lower, $search_lower) === false && 
            strpos($desc_lower, $search_lower) === false) {
            continue;
        }
    }
    
    $filtered_products[] = $product;
}
?>

<section class="products-page">
    <div class="container">
        <h1>Our Products</h1>
        
        <div class="filters">
            <form method="GET" class="filter-form">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="category">
                    <option value="all" <?php echo $category == 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <option value="Smartphones" <?php echo $category == 'Smartphones' ? 'selected' : ''; ?>>Smartphones</option>
                    <option value="Laptops" <?php echo $category == 'Laptops' ? 'selected' : ''; ?>>Laptops</option>
                    <option value="Audio" <?php echo $category == 'Audio' ? 'selected' : ''; ?>>Audio</option>
                    <option value="Tablets" <?php echo $category == 'Tablets' ? 'selected' : ''; ?>>Tablets</option>
                    <option value="Accessories" <?php echo $category == 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="products-grid">
            <?php
            if (count($filtered_products) > 0) {
                foreach ($filtered_products as $product) {
                    $finalPrice = $product['price'] * (1 - $product['discount'] / 100);
                    ?>
                    <div class="product-card">
                        <div class="product-image">
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
                            <div class="product-actions">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="view-details">View Details</a>
                                <?php if (isLoggedIn()): ?>
                                    <button class="add-to-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 添加购物车功能
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            fetch('../api/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart successfully!');
                    // 可以在这里更新购物车数量显示
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product to cart.');
            });
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>