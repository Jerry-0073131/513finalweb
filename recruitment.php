<?php
// pages/recruitment.php
session_start();
require_once('../config/database.php');

// 处理表单提交
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证输入
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $position = $_POST['position'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $coverLetter = $_POST['cover_letter'] ?? '';
    
    // 基本验证
    if (empty($firstName)) $errors[] = "First name is required.";
    if (empty($lastName)) $errors[] = "Last name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($position)) $errors[] = "Please select a position.";
    
    // 处理文件上传
    $resumeFileName = '';
    $resumePath = '';
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $fileType = $_FILES['resume']['type'];
        $fileSize = $_FILES['resume']['size'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Only PDF and Word documents are allowed.";
        } elseif ($fileSize > $maxSize) {
            $errors[] = "File size must be less than 5MB.";
        } else {
            // 生成唯一文件名
            $extension = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
            $resumeFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['resume']['name']);
            $uploadDir = '../uploads/resumes/';
            
            // 确保上传目录存在
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $resumePath = $uploadDir . $resumeFileName;
            
            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resumePath)) {
                $errors[] = "Failed to upload resume.";
            }
        }
    } else {
        $errors[] = "Please upload your resume.";
    }
    
    // 如果没有错误，保存到数据库
    if (empty($errors)) {
        try {
            $pdo = Database::getConnection();
            
            $query = "INSERT INTO wpov_job_applications 
                     (first_name, last_name, email, phone, position, experience_years, cover_letter, resume_filename, resume_path) 
                     VALUES (:first_name, :last_name, :email, :phone, :position, :experience, :cover_letter, :resume_filename, :resume_path)";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone,
                ':position' => $position,
                ':experience' => (int)$experience,
                ':cover_letter' => $coverLetter,
                ':resume_filename' => $resumeFileName,
                ':resume_path' => $resumePath
            ]);
            
            $success = true;
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// 获取职位列表（从数据库或硬编码）
$positions = [
    'Software Developer',
    'Web Designer',
    'Marketing Manager',
    'Sales Representative',
    'Customer Support Specialist',
    'Product Manager',
    'Data Analyst',
    'System Administrator'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application - TechPioneer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .recruitment-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .application-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .file-upload {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .file-upload:hover {
            border-color: #3498db;
        }
        
        .file-upload input[type="file"] {
            display: none;
        }
        
        .file-name {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .submit-btn {
            background-color: #27ae60;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #219653;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .requirements {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        
        .requirements h3 {
            color: #333;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="recruitment-container">
        <div class="page-header">
            <h1>Join Our Team</h1>
            <p>We're looking for talented individuals to join TechPioneer</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <h3>Application Submitted Successfully!</h3>
                <p>Thank you for applying to TechPioneer. We will review your application and contact you soon.</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h3>Please fix the following errors:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="requirements">
            <h3>Requirements:</h3>
            <ul>
                <li>Resume must be in PDF or Word format</li>
                <li>Maximum file size: 5MB</li>
                <li>All fields are required</li>
            </ul>
        </div>
        
        <?php if (!$success): ?>
            <form class="application-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group" style="flex: 1; margin-left: 15px;">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group" style="flex: 1; margin-left: 15px;">
                        <label for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" required 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="position">Position Applied For *</label>
                    <select id="position" name="position" required>
                        <option value="">Select a position</option>
                        <?php foreach ($positions as $pos): ?>
                            <option value="<?php echo $pos; ?>" 
                                <?php echo ($_POST['position'] ?? '') === $pos ? 'selected' : ''; ?>>
                                <?php echo $pos; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="experience">Years of Experience</label>
                    <input type="number" id="experience" name="experience" min="0" max="50" 
                           value="<?php echo htmlspecialchars($_POST['experience'] ?? '0'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="cover_letter">Cover Letter</label>
                    <textarea id="cover_letter" name="cover_letter" 
                              placeholder="Tell us why you're the right candidate..."><?php echo htmlspecialchars($_POST['cover_letter'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Upload Resume *</label>
                    <div class="file-upload" onclick="document.getElementById('resume').click()">
                        <p>Click to upload resume (PDF or Word)</p>
                        <p class="file-name" id="fileName">No file chosen</p>
                        <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Submit Application</button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include('../includes/footer.php'); ?>
    
    <script>
        document.getElementById('resume').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            document.getElementById('fileName').textContent = fileName;
        });
    </script>
</body>
</html>