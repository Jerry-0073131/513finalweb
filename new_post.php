<?php
// pages/new_post.php

// 确保会话开始
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/database.php');

// 检查登录状态
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?return_to=new_post.php");
    exit;
}

// 设置变量
$error = null;
$success = null;
$categories = [];

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    
    // 验证输入
    if (empty($title) || empty($content) || empty($category_id)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($title) > 200) {
        $error = "Title cannot exceed 200 characters.";
    } else {
        try {
            // 获取数据库连接
            $pdo = Database::getPDO();
            
            // 获取用户信息
            $user_id = $_SESSION['user_id'];
            $user_email = $_SESSION['email'] ?? 'user@example.com';
            $user_name = $_SESSION['name'] ?? 'User';
            
            // 插入新帖子
            $query = "INSERT INTO wpov_forum_posts 
                     (title, content, category_id, user_id, user_email, user_name, created_at) 
                     VALUES (:title, :content, :category_id, :user_id, :user_email, :user_name, NOW())";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':category_id' => $category_id,
                ':user_id' => $user_id,
                ':user_email' => $user_email,
                ':user_name' => $user_name
            ]);
            
            $post_id = $pdo->lastInsertId();
            $success = "Post created successfully!";
            
            // 重定向到新帖子
            header("Location: view_post.php?id=" . $post_id);
            exit;
            
        } catch (PDOException $e) {
            $error = "Failed to create post: " . $e->getMessage();
        }
    }
}

try {
    // 获取数据库连接
    $pdo = Database::getPDO();
    
    // 获取所有分类
    $catQuery = "SELECT id, name FROM wpov_forum_categories ORDER BY name";
    $catStmt = $pdo->query($catQuery);
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post - TechPioneer Forum</title>
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
        
        .new-post-container {
            max-width: 800px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .post-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e6ef;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }
        
        .btn-secondary {
            background: #e0e6ef;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #d1d9e6;
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
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .form-help {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .new-post-container {
                margin-top: 60px;
                padding: 0 15px;
            }
            
            .page-header {
                padding: 20px;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .post-form {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="new-post-container">
        <div class="page-header">
            <h1>Create New Post</h1>
            <p>Share your thoughts, questions, or ideas with the community</p>
            <a href="forum.php" class="back-link">← Back to Forum</a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="post-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" placeholder="Enter a descriptive title for your post" 
                           maxlength="200" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    <div class="form-help">Maximum 200 characters</div>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category <span class="required">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content">Content <span class="required">*</span></label>
                    <textarea id="content" name="content" placeholder="Write your post content here..." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    <div class="form-help">You can use basic HTML formatting</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Post</button>
                    <a href="forum.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include('../includes/footer.php'); ?>
    
    <script>
        // 字符计数器
        const titleInput = document.getElementById('title');
        const charCount = document.createElement('div');
        charCount.className = 'form-help';
        charCount.style.marginTop = '5px';
        titleInput.parentNode.insertBefore(charCount, titleInput.nextSibling.nextSibling);
        
        function updateCharCount() {
            const currentLength = titleInput.value.length;
            charCount.textContent = `${currentLength}/200 characters`;
            
            if (currentLength > 180) {
                charCount.style.color = '#e74c3c';
            } else if (currentLength > 150) {
                charCount.style.color = '#f39c12';
            } else {
                charCount.style.color = '#64748b';
            }
        }
        
        titleInput.addEventListener('input', updateCharCount);
        updateCharCount();
        
        // 表单提交确认
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>