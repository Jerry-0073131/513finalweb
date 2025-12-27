<?php
// pages/forum.php

// 确保会话开始
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/database.php');

// 设置变量
$posts = [];
$categories = [];
$currentCategory = $_GET['category'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$error = null;

// 检查并创建表的函数
function checkAndCreateTables($pdo) {
    // 检查分类表是否存在
    $tables = ['wpov_forum_categories', 'wpov_forum_posts', 'wpov_forum_replies'];
    
    foreach ($tables as $table) {
        $checkTable = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($checkTable->rowCount() == 0) {
            createTables($pdo);
            break;
        }
    }
}

// 创建表的函数
function createTables($pdo) {
    // 创建分类表
    $pdo->exec("CREATE TABLE IF NOT EXISTS wpov_forum_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        slug VARCHAR(100) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // 创建帖子表
    $pdo->exec("CREATE TABLE IF NOT EXISTS wpov_forum_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        content TEXT NOT NULL,
        category_id INT,
        user_id INT,
        user_email VARCHAR(255),
        user_name VARCHAR(200),
        views INT DEFAULT 0,
        replies_count INT DEFAULT 0,
        last_reply_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_sticky BOOLEAN DEFAULT FALSE,
        is_locked BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (category_id) REFERENCES wpov_forum_categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // 创建回复表
    $pdo->exec("CREATE TABLE IF NOT EXISTS wpov_forum_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT,
        user_email VARCHAR(255),
        user_name VARCHAR(200),
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES wpov_forum_posts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// 创建默认分类
function createDefaultCategories($pdo) {
    $defaultCategories = [
        ['General Discussion', 'Talk about anything and everything', 'general'],
        ['Product Support', 'Get help with TechPioneer products', 'support'],
        ['Feature Requests', 'Suggest new features for our products', 'feature-requests'],
        ['Announcements', 'Official announcements from TechPioneer', 'announcements'],
        ['Off Topic', 'Non-tech related discussions', 'off-topic']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO wpov_forum_categories (name, description, slug) VALUES (?, ?, ?)");
    
    foreach ($defaultCategories as $category) {
        $stmt->execute($category);
    }
}

// 插入示例帖子的函数
function insertSamplePosts($pdo) {
    $samplePosts = [
        [
            'title' => 'Welcome to TechPioneer Forum!',
            'content' => 'Welcome everyone to our community forum. Feel free to introduce yourself and share your experiences with our products.',
            'category_id' => 1,
            'user_name' => 'Admin',
            'user_email' => 'admin@techpioneer.com',
            'is_sticky' => 1
        ],
        [
            'title' => 'How to connect wireless headphones?',
            'content' => "I'm having trouble connecting my new wireless headphones to my laptop. Can anyone help? I've tried Bluetooth pairing but it doesn't show up in the device list.",
            'category_id' => 2,
            'user_name' => 'Sarah Johnson',
            'user_email' => 'sarah@example.com'
        ],
        [
            'title' => 'New product suggestion: Smart watch',
            'content' => 'I think TechPioneer should launch a smart watch with health monitoring features. Who else would be interested?',
            'category_id' => 3,
            'user_name' => 'Michael Chen',
            'user_email' => 'michael@example.com'
        ],
        [
            'title' => 'Website maintenance scheduled',
            'content' => 'We will perform website maintenance this Sunday from 2 AM to 4 AM. The site may be temporarily unavailable.',
            'category_id' => 4,
            'user_name' => 'Admin',
            'user_email' => 'admin@techpioneer.com'
        ],
        [
            'title' => 'Favorite tech movies?',
            'content' => "Let's discuss our favorite technology-themed movies! I'll start: The Social Network and Ex Machina.",
            'category_id' => 5,
            'user_name' => 'Alex Turner',
            'user_email' => 'alex@example.com'
        ],
        [
            'title' => 'Battery life issues with laptop',
            'content' => "My TechPioneer laptop's battery only lasts 2 hours. Is this normal or should I contact support?",
            'category_id' => 2,
            'user_name' => 'David Wilson',
            'user_email' => 'david@example.com'
        ],
        [
            'title' => 'Feature request: Dark mode',
            'content' => 'Can we please get a dark mode option for the TechPioneer website? My eyes would thank you!',
            'category_id' => 3,
            'user_name' => 'Emma Rodriguez',
            'user_email' => 'emma@example.com'
        ],
        [
            'title' => 'Monthly meetup announcement',
            'content' => 'Join us for our monthly TechPioneer user meetup this Saturday at 3 PM at the community center.',
            'category_id' => 4,
            'user_name' => 'Admin',
            'user_email' => 'admin@techpioneer.com'
        ],
        [
            'title' => 'Best programming languages in 2024',
            'content' => 'What are your thoughts on the most useful programming languages to learn in 2024?',
            'category_id' => 1,
            'user_name' => 'Robert Kim',
            'user_email' => 'robert@example.com'
        ],
        [
            'title' => 'Headphones not charging properly',
            'content' => 'My wireless headphones show charging but the battery percentage doesn\'t increase. Any solutions?',
            'category_id' => 2,
            'user_name' => 'Lisa Wang',
            'user_email' => 'lisa@example.com'
        ]
    ];
    
    $insertQuery = "INSERT INTO wpov_forum_posts 
                   (title, content, category_id, user_name, user_email, is_sticky, created_at) 
                   VALUES (:title, :content, :category_id, :user_name, :user_email, :is_sticky, NOW())";
    
    $stmt = $pdo->prepare($insertQuery);
    
    foreach ($samplePosts as $post) {
        $stmt->execute($post);
    }
}

try {
    // 获取数据库连接
    $pdo = Database::getPDO();
    
    // 检查表是否存在，如果不存在则创建
    checkAndCreateTables($pdo);
    
    // 获取分类
    $catQuery = "SELECT id, name, slug FROM wpov_forum_categories ORDER BY name";
    $catStmt = $pdo->query($catQuery);
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 如果没有分类，创建默认分类
    if (empty($categories)) {
        createDefaultCategories($pdo);
        $catStmt = $pdo->query($catQuery);
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 构建查询条件
    $whereConditions = [];
    $params = [];
    
    if ($currentCategory !== 'all' && is_numeric($currentCategory)) {
        $whereConditions[] = "fp.category_id = :category_id";
        $params[':category_id'] = $currentCategory;
    }
    
    if (!empty($searchQuery)) {
        $whereConditions[] = "(fp.title LIKE :search OR fp.content LIKE :search)";
        $params[':search'] = "%{$searchQuery}%";
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // 获取帖子
    $query = "SELECT 
                fp.id,
                fp.title,
                fp.content,
                fp.user_name,
                fp.views,
                fp.replies_count,
                fp.created_at,
                fp.is_sticky,
                fp.is_locked,
                fc.name as category_name,
                fc.slug as category_slug
              FROM wpov_forum_posts fp
              LEFT JOIN wpov_forum_categories fc ON fp.category_id = fc.id
              {$whereClause}
              ORDER BY fp.is_sticky DESC, fp.last_reply_at DESC, fp.created_at DESC
              LIMIT 20";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 如果没有帖子，插入示例数据
    if (empty($posts) && empty($searchQuery) && $currentCategory === 'all') {
        insertSamplePosts($pdo);
        // 重新查询
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取帖子总数
    $totalPostsQuery = "SELECT COUNT(*) as total FROM wpov_forum_posts";
    $totalStmt = $pdo->query($totalPostsQuery);
    $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);
    $totalPosts = $totalResult ? $totalResult['total'] : 0;
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Forum - TechPioneer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
        }
        
        .forum-container {
            max-width: 1200px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }
        
        .forum-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .forum-header h1 {
            font-size: 2.5rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .new-post-btn {
            background: white;
            color: #667eea;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .new-post-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            background: #f8f9fa;
        }
        
        .forum-stats {
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            font-weight: 500;
            color: #4a5568;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .forum-stats p {
            margin: 0;
        }
        
        .forum-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
        }
        
        .forum-sidebar {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 90px;
        }
        
        .forum-sidebar h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            font-size: 1.3rem;
        }
        
        .category-list {
            list-style: none;
            padding: 0;
            margin-bottom: 25px;
        }
        
        .category-list li {
            margin-bottom: 8px;
        }
        
        .category-list a {
            display: block;
            padding: 12px 16px;
            border-radius: 6px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid transparent;
        }
        
        .category-list a:hover {
            background-color: #f8fafc;
            border-color: #e0e6ef;
        }
        
        .category-list a.active {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border-color: #667eea;
        }
        
        .search-box {
            margin: 25px 0;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e6ef;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .forum-rules {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e6ef;
        }
        
        .forum-rules h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .forum-rules ul {
            list-style: none;
            padding: 0;
        }
        
        .forum-rules li {
            padding: 5px 0;
            color: #64748b;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .forum-rules li:before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
        }
        
        .forum-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .posts-list {
            margin-top: 20px;
        }
        
        .post-item {
            padding: 25px;
            border-bottom: 1px solid #e0e6ef;
            transition: all 0.3s;
        }
        
        .post-item:hover {
            background-color: #f8fafc;
            transform: translateX(5px);
        }
        
        .post-item:last-child {
            border-bottom: none;
        }
        
        .post-item.sticky {
            background-color: #fff9e6;
            border-left: 4px solid #f39c12;
        }
        
        .post-item.sticky:hover {
            background-color: #fff4d6;
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .post-title {
            font-size: 1.3rem;
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            flex: 1;
            margin-right: 15px;
        }
        
        .post-title:hover {
            color: #667eea;
        }
        
        .post-category {
            background: #e0e6ef;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            color: #4a5568;
            white-space: nowrap;
        }
        
        .post-meta {
            display: flex;
            gap: 20px;
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .post-excerpt {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .post-stats {
            display: flex;
            gap: 20px;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .post-stats span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .post-stats a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .post-stats a:hover {
            text-decoration: underline;
        }
        
        .no-posts {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .no-posts p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .locked-icon {
            color: #e74c3c;
            margin-left: 8px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .pagination a {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e0e6ef;
            border-radius: 6px;
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        @media (max-width: 992px) {
            .forum-layout {
                grid-template-columns: 1fr;
            }
            
            .forum-sidebar {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .forum-container {
                margin-top: 60px;
                padding: 0 15px;
            }
            
            .forum-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 20px;
            }
            
            .forum-header h1 {
                font-size: 2rem;
            }
            
            .forum-stats {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="forum-container">
        <div class="forum-header">
            <h1>TechPioneer Discussion Forum</h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="new_post.php" class="new-post-btn">
                    <span>✏️</span> New Post
                </a>
            <?php else: ?>
                <a href="login.php?return_to=forum.php" class="new-post-btn">
                    <span>🔒</span> Login to Post
                </a>
            <?php endif; ?>
        </div>
        
        <div class="forum-stats">
            <p>Total Posts: <?php echo $totalPosts; ?></p>
            <p>Categories: <?php echo count($categories); ?></p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="forum-layout">
            <div class="forum-sidebar">
                <h3>Categories</h3>
                <ul class="category-list">
                    <li>
                        <a href="?category=all" class="<?php echo $currentCategory === 'all' ? 'active' : ''; ?>">
                            All Categories
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="?category=<?php echo $category['id']; ?>" 
                               class="<?php echo $currentCategory == $category['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="search-box">
                    <form method="GET" action="">
                        <input type="text" name="search" placeholder="Search forum..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <input type="hidden" name="category" value="<?php echo $currentCategory; ?>">
                    </form>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="forum-rules">
                        <h4>Forum Rules</h4>
                        <ul>
                            <li>Be respectful to other members</li>
                            <li>No spam or self-promotion</li>
                            <li>Stay on topic</li>
                            <li>Use appropriate language</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="forum-content">
                <?php if (!empty($searchQuery)): ?>
                    <div class="search-results" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e0e6ef;">
                        <h3 style="color: #2c3e50;">Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                    </div>
                <?php endif; ?>
                
                <div class="posts-list">
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-item <?php echo $post['is_sticky'] ? 'sticky' : ''; ?>">
                                <div class="post-header">
                                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="post-title">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                    <span class="post-category">
                                        <?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                </div>
                                
                                <div class="post-meta">
                                    <span>By <?php echo htmlspecialchars($post['user_name']); ?></span>
                                    <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                    <?php if ($post['is_sticky']): ?>
                                        <span style="color: #f39c12; font-weight: 500;">📌 Pinned</span>
                                    <?php endif; ?>
                                    <?php if ($post['is_locked']): ?>
                                        <span class="locked-icon">🔒 Locked</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="post-excerpt">
                                    <?php 
                                        $excerpt = strip_tags($post['content']);
                                        if (strlen($excerpt) > 200) {
                                            $excerpt = substr($excerpt, 0, 200) . '...';
                                        }
                                        echo htmlspecialchars($excerpt);
                                    ?>
                                </div>
                                
                                <div class="post-stats">
                                    <span>👁️ <?php echo $post['views']; ?> views</span>
                                    <span>💬 <?php echo $post['replies_count']; ?> replies</span>
                                    <span><a href="view_post.php?id=<?php echo $post['id']; ?>">Read more →</a></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-posts">
                            <p>No posts found in this category.</p>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <p><a href="login.php?return_to=forum.php" style="color: #667eea;">Login</a> to create the first post!</p>
                            <?php else: ?>
                                <p><a href="new_post.php" style="color: #667eea;">Create the first post</a> in this category!</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('../includes/footer.php'); ?>
    
    <script>
        // 自动提交搜索表单
        document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
        
        // 添加点击效果到帖子
        document.querySelectorAll('.post-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (!e.target.closest('a') && !e.target.closest('.post-category')) {
                    const link = this.querySelector('.post-title');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });
    </script>
</body>
</html>