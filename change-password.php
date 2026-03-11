<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } elseif (strlen($newPassword) < 4) {
        $error = 'Password must be at least 4 characters';
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $user = $stmt->fetch();
        
        if (password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($update->execute([$hashedPassword, $currentUser['id']])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to update password';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .password-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            animation: fadeInUp 0.6s ease-out;
        }
        
        .password-container h1 {
            font-size: 36px;
            margin-bottom: 10px;
            text-align: center;
            background: linear-gradient(135deg, #fff, #d96565);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .password-container p {
            text-align: center;
            margin-bottom: 30px;
            opacity: 0.8;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #d96565;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            border: 2px solid transparent;
            background: rgba(255,255,255,0.95);
            color: #333;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #d96565;
            box-shadow: 0 0 0 4px rgba(217,101,101,0.2);
        }
        
        .password-strength {
            height: 5px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            margin: 8px 0 0;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-group .btn {
            flex: 1;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            text-align: center;
            text-decoration: none;
        }
        
        .error-message {
            background: rgba(217,101,101,0.2);
            border: 1px solid #d96565;
            color: #d96565;
            padding: 15px;
            border-radius: 50px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            background: rgba(76,175,80,0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
            padding: 15px;
            border-radius: 50px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .info-box {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        
        .info-box i {
            color: #d96565;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main>
        <div class="password-container">
            <h1>🔑 Change Password</h1>
            <p>Update your password to keep your account secure</p>
            
            <?php if ($error): ?>
                <div class="error-message">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">✅ <?php echo $success; ?></div>
                <script>
                    setTimeout(function() {
                        window.location.href = 'auth.php';
                    }, 2000);
                </script>
            <?php endif; ?>
            
            <div class="info-box">
                <i>ℹ️</i> Password must be at least 4 characters long
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password" required>
                </div>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required onkeyup="checkPasswordStrength()">
                    <div class="password-strength">
                        <div class="strength-bar" id="strength-bar"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-success">✅ Update Password</button>
                    <a href="auth.php" class="btn" style="background: #666;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms</a> | <a href="privacy.php">Privacy</a></p>
    </footer>
    
    <script src="script.js"></script>
    <script>
    function checkPasswordStrength() {
        let password = document.getElementById('new_password').value;
        let strengthBar = document.getElementById('strength-bar');
        let strength = 0;
        
        if (password.length >= 4) strength += 25;
        if (password.length >= 6) strength += 25;
        if (password.match(/[a-z]+/) && password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9]+/)) strength += 25;
        
        strengthBar.style.width = strength + '%';
        
        if (strength < 50) {
            strengthBar.style.background = '#d96565';
        } else if (strength < 75) {
            strengthBar.style.background = '#FF9800';
        } else {
            strengthBar.style.background = '#4CAF50';
        }
    }
    </script>
</body>
</html>