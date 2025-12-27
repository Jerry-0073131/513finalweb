<?php
/**
 * Email sender class for TechPioneer
 * Handles sending emails via SMTP or PHP mail()
 */
class EmailSender {
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpSecure;
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        $this->loadEmailConfig();
    }
    
    /**
     * 加载邮件配置 - 修改为使用config.php中的配置
     */
    private function loadEmailConfig() {
        // 使用config.php中定义的配置
        $this->smtpHost = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.qq.com';
        $this->smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->smtpUsername = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
        $this->smtpPassword = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
        $this->smtpSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
        $this->fromEmail = defined('FROM_EMAIL') ? FROM_EMAIL : '';
        $this->fromName = defined('FROM_NAME') ? FROM_NAME : 'TechPioneer';
    }
    
    /**
     * 发送邮件
     */
    public function sendEmail($to, $subject, $message, $contentType = 'text/plain') {
        // 简化：直接使用mail()函数，因为InfinityFree可能不支持SMTP
        return $this->sendViaMailFunction($to, $subject, $message);
    }
    
    /**
     * 使用PHP内置mail()函数发送邮件
     */
    private function sendViaMailFunction($to, $subject, $message) {
        $headers = [
            'From: ' . $this->fromEmail,
            'Reply-To: ' . $this->fromEmail,
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}
?>