<?php
require_once '../config/config.php';

// 修复图片路径和文件
function fixImagePaths() {
    $products_file = '../data/products.json';
    $images_dir = '../images/';
    
    // 读取产品数据
    $products = json_decode(file_get_contents($products_file), true);
    
    // 定义正确的图片文件名映射
    $correct_filenames = [
        'iphone15pro.jpg' => 'iPhone 15 Pro.jpg',
        'macbookairm2.jpg' => 'macbookairm2.jpg', 
        'galaxys24.jpg' => 'galaxys24.jpg',
        'sonyheadphones.jpg' => 'sonyheadphones.jpg'
    ];
    
    // 修复产品数据中的图片路径
    foreach ($products as &$product) {
        $current_filename = $product['image_url'];
        
        // 如果路径包含 images/，移除它
        if (strpos($current_filename, 'images/') === 0) {
            $current_filename = str_replace('images/', '', $current_filename);
        }
        
        // 更新为正确的文件名
        if (isset($correct_filenames[$current_filename])) {
            $product['image_url'] = $correct_filenames[$current_filename];
        } else {
            $product['image_url'] = $current_filename;
        }
    }
    
    // 保存修复后的产品数据
    file_put_contents($products_file, json_encode($products, JSON_PRETTY_PRINT));
    
    // 检查并创建缺失的图片文件
    foreach ($correct_filenames as $filename) {
        $filepath = $images_dir . $filename;
        if (!file_exists($filepath)) {
            createPlaceholderImage($filepath, str_replace('.jpg', '', $filename));
            echo "创建占位图片: $filename<br>";
        }
    }
    
    return $products;
}

// 创建占位图片
function createPlaceholderImage($filename, $text) {
    $width = 300;
    $height = 200;
    
    $image = imagecreate($width, $height);
    $background = imagecolorallocate($image, 100, 150, 200);
    $text_color = imagecolorallocate($image, 255, 255, 255);
    
    // 添加文字
    imagestring($image, 5, 50, 80, $text, $text_color);
    imagestring($image, 3, 80, 100, 'TechPioneer', $text_color);
    
    // 保存图片
    imagejpeg($image, $filename, 90);
    imagedestroy($image);
}

// 执行修复
echo "<h1>修复图片路径</h1>";
$products = fixImagePaths();
echo "<h2>修复完成！</h2>";

// 显示修复后的产品信息
foreach ($products as $product) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin:10px;'>";
    echo "<strong>产品: {$product['name']}</strong><br>";
    echo "图片路径: {$product['image_url']}<br>";
    $image_url = 'https://jerrysweb.lovestoblog.com/542-Jerry/513/week7last/images/' . $product['image_url'];
    echo "完整URL: <a href='$image_url' target='_blank'>$image_url</a><br>";
    echo "<img src='$image_url' alt='{$product['name']}' style='max-width:200px;'><br>";
    echo "</div>";
}
?>