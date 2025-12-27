<?php
require_once '../config/config.php';

// 改进的图片URL函数
function getImageUrl($image_path) {
    $base_path = '/542-Jerry/513/week7last';
    $base_path = rtrim($base_path, '/');
    $image_path = ltrim($image_path, '/');
    return $base_path . '/' . $image_path;
}

// 检查图片是否存在
function checkImageExists($image_path) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/542-Jerry/513/week7last/' . $image_path;
    return file_exists($full_path);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>图片调试</title>
    <style>
        .product { margin: 20px; padding: 10px; border: 1px solid #ccc; }
        .exists { color: green; }
        .missing { color: red; }
        img { max-width: 200px; border: 2px solid; }
        .exists img { border-color: green; }
        .missing img { border-color: red; }
    </style>
</head>
<body>
    <h1>图片调试页面</h1>
    
    <?php
    $products = json_decode(file_get_contents('../data/products.json'), true);
    foreach ($products as $product): 
        $image_exists = checkImageExists($product['image_url']);
        $class = $image_exists ? 'exists' : 'missing';
    ?>
        <div class="product <?php echo $class; ?>">
            <h3><?php echo $product['name']; ?></h3>
            <p>图片路径: <?php echo $product['image_url']; ?></p>
            <p>完整URL: <?php echo getImageUrl($product['image_url']); ?></p>
            <p>文件存在: <?php echo $image_exists ? '是' : '否'; ?></p>
            <p>服务器路径: <?php echo $_SERVER['DOCUMENT_ROOT'] . '/542-Jerry/513/week7last/' . $product['image_url']; ?></p>
            <img src="<?php echo getImageUrl($product['image_url']); ?>" 
                 alt="<?php echo $product['name']; ?>"
                 onerror="this.style.display='none'">
            <?php if (!$image_exists): ?>
                <p style="color: red;">⚠️ 图片文件不存在！</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>