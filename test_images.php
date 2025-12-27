<?php
require_once '../config/config.php';

function getImageUrl($image_path) {
    $base_path = '/542-Jerry/513/week7last';
    return $base_path . '/' . $image_path;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>图片测试</title>
</head>
<body>
    <h1>图片显示测试</h1>
    
    <?php
    $products = json_decode(file_get_contents('../data/products.json'), true);
    foreach ($products as $product): ?>
        <div style="margin: 20px; padding: 10px; border: 1px solid #ccc;">
            <h3><?php echo $product['name']; ?></h3>
            <p>图片路径: <?php echo $product['image_url']; ?></p>
            <p>完整路径: <?php echo getImageUrl($product['image_url']); ?></p>
            <img src="<?php echo getImageUrl($product['image_url']); ?>" 
                 alt="<?php echo $product['name']; ?>" 
                 style="max-width: 200px; border: 2px solid red;">
        </div>
    <?php endforeach; ?>
</body>
</html>