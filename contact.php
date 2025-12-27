<?php
require_once '../config/config.php';
require_once '../includes/header.php';

$success = '';
$error = '';
$name = '';
$email = '';
$subject = '';
$message = '';

// Process contact form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // 准备邮件内容
        $thankYouSubject = "Thank you for contacting TechPioneer";
        $thankYouMessage = "
Dear {$name},

Thank you for contacting TechPioneer! We have received your message and appreciate you taking the time to write to us.

Here is a copy of the message you sent:
Subject: {$subject}
Message: {$message}

Our team will review your inquiry and get back to you within 24 hours. We strive to provide the best possible service to our customers.

If you have any urgent questions, please don't hesitate to contact us at info@techpioneer.com or call us at +1 (555) 123-4567.

Best regards,
The TechPioneer Team
www.techpioneer.com
        ";
        
        // 发送邮件
        $headers = [
            'From: ' . FROM_EMAIL,
            'Reply-To: ' . FROM_EMAIL,
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $encodedSubject = '=?UTF-8?B?' . base64_encode($thankYouSubject) . '?=';
        
        if (mail($email, $encodedSubject, $thankYouMessage, implode("\r\n", $headers))) {
            $success = 'Thank you for your message! We have sent a confirmation email to your address and will get back to you within 24 hours.';
        } else {
            $success = 'Thank you for your message! We will get back to you within 24 hours.';
        }
        
        // Clear form
        $name = $email = $subject = $message = '';
    }
}
?>

<!-- HTML部分保持不变 -->
<section class="contact-page">
    <div class="container">
        <h1>Contact Us</h1>
        
        <div class="contact-container">
            <div class="contact-info">
                <h2>Get In Touch</h2>
                <p>We'd love to hear from you. Please fill out the form or use the contact information below.</p>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <h3>Email</h3>
                        <p>info@techpioneer.com</p>
                    </div>
                    <div class="contact-item">
                        <h3>Phone</h3>
                        <p>+1 (555) 123-4567</p>
                    </div>
                    <div class="contact-item">
                        <h3>Address</h3>
                        <p>123 Tech Street<br>San Francisco, CA 94107<br>United States</p>
                    </div>
                    <div class="contact-item">
                        <h3>Business Hours</h3>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM PST<br>Saturday: 10:00 AM - 4:00 PM PST<br>Sunday: Closed</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-container">
                <h2>Send Us a Message</h2>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Your Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-button">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>