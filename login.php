<?php
require_once '../config/config.php';
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$first_name = '';
$last_name = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check user credentials - 使用 first_name 和 last_name 作为账号，email 作为密码验证
        $query = "SELECT * FROM wpov_fc_subscribers WHERE first_name = :first_name AND last_name = :last_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 验证邮箱是否匹配
            if ($email === $user['email']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                // 关键修复：从数据库读取 is_admin 字段
                $_SESSION['is_admin'] = (bool)$user['is_admin'];

                // Redirect to home page
                header("Location: index.php");
                exit;
            } else {
                $error = 'Invalid email.';
            }
        } else {
            $error = 'Invalid first name or last name.';
        }
    }
}
?>  <!-- 添加这行缺失的 PHP 结束标记 -->

<section class="auth-page">
    <div class="container">
        <div class="auth-form-container">
            <h1>Login to Your Account</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit" class="auth-button">Login</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>