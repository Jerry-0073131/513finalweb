<?php
// Authentication functions for TechPioneer

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}


function checkAdminByEmail($email, $db) {
    $query = "SELECT * FROM wpov_fc_subscribers WHERE email = :email AND is_admin = TRUE";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}




function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: index.php");
        exit;
    }
}

// Password hashing function
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Check if user can access resource
function canAccessResource($user_id, $resource_owner_id) {
    return $user_id == $resource_owner_id || isAdmin();
}

// Redirect if logged in
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}

// Set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get and clear flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Display flash message
function displayFlashMessage() {
    $message = getFlashMessage();
    if ($message) {
        $type = $message['type'];
        $text = $message['message'];
        echo "<div class='flash-message flash-$type'>$text</div>";
        
        // Add CSS for flash messages if not already in style.css
        echo "<style>
            .flash-message {
                padding: 1rem;
                margin: 1rem 0;
                border-radius: 4px;
                font-weight: bold;
            }
            .flash-success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .flash-error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .flash-warning {
                background-color: #fff3cd;
                color: #856404;
                border: 1px solid #ffeaa7;
            }
        </style>";
    }
}
?>