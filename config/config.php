<?php
// Website configuration for TechPioneer

// 首先定义基础常量
define('SITE_NAME', 'TechPioneer');
define('BASE_URL', 'https://jerrysweb.lovestoblog.com');
define('BASE_PATH', '/542-Jerry/513/week7last');
define('SITE_PATH', realpath(dirname(__FILE__) . '/../'));

// Email configuration
define('SMTP_HOST', 'smtp.qq.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '2160502612@qq.com');
define('SMTP_PASSWORD', 'tcgeaejwenaydjfd');
define('SMTP_SECURE', 'tls');
define('FROM_EMAIL', '2160502612@qq.com');
define('FROM_NAME', 'TechPioneer');

// 简化的会话管理
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // 设置会话参数
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        
        session_set_cookie_params([
            'lifetime' => 86400,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
    }
}

// 启动会话
startSecureSession();

// Include initialization
require_once 'init.php';
?>