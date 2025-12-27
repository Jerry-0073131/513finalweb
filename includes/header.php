<?php
// includes/header.php

// 确保会话开始
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 动态获取基础路径
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);

// 如果脚本在 pages 目录下，向上退一级
if (strpos($script_path, '/pages') !== false) {
    $base_path = dirname($script_path);
} else {
    $base_path = $script_path;
}

// 完整基础URL
$base_url = $protocol . $host . $base_path;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Get cart item count
$cartItemCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartItemCount += $item['quantity'];
    }
}

// 改进的图片路径函数 - 处理路径重复问题
function getImagePath($image_path) {
    global $base_url;
    
    if (empty($image_path)) {
        return 'https://via.placeholder.com/300x200/cccccc/969696?text=No+Image';
    }
    
    // 如果已经是完整URL，直接返回
    if (strpos($image_path, 'http') === 0) {
        return $image_path;
    }
    
    // 清理路径：移除开头的 images/ 如果存在
    $clean_path = $image_path;
    if (strpos($clean_path, 'images/') === 0) {
        $clean_path = substr($clean_path, 7); // 移除开头的 "images/"
    }
    
    // 返回完整的绝对URL
    return $base_url . '/images/' . $clean_path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechPioneer - Electronics Store</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <style>
        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            background: white;
        }
        /* 图片加载失败的备用样式 */
        .product-image img[src*="placeholder.com"] {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="<?php echo $base_url; ?>/index.php">TechPioneer</a>
                </div>
                <div class="nav-menu">
                    <a href="<?php echo $base_url; ?>/index.php" class="nav-link">Home</a>
                    <a href="<?php echo $base_url; ?>/pages/products.php" class="nav-link">Products</a>
                    <a href="<?php echo $base_url; ?>/pages/about.php" class="nav-link">About Us</a>
                    <a href="<?php echo $base_url; ?>/pages/contact.php" class="nav-link">Contact</a>
                    <a href="<?php echo $base_url; ?>/pages/customers.php" class="nav-link">Customers</a>
                    <a href="<?php echo $base_url; ?>/pages/recruitment.php" class="nav-link">Recruitment</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo $base_url; ?>/pages/cart.php" class="nav-link">Cart (<?php echo $cartItemCount; ?>)</a>
                        <a href="<?php echo $base_url; ?>/pages/forum.php" class="nav-link">Forum</a>
                        <?php if ($isAdmin): ?>
                            <a href="<?php echo $base_url; ?>/pages/dashboard.php" class="nav-link">Dashboard</a>
                        <?php endif; ?>
                        <a href="<?php echo $base_url; ?>/pages/logout.php" class="nav-link">Logout (<?php echo $_SESSION['first_name'] ?? 'User'; ?>)</a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>/pages/login.php" class="nav-link">Login</a>
                        <a href="https://jerrysweb.lovestoblog.com/wordpress/register/" class="nav-link">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    <main>