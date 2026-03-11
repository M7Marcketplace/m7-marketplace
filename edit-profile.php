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

// Handle password verification
if (isset($_POST['verify_password'])) {
    $password = $_POST['password'] ?? '';
    
    // Verify password from database
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$currentUser['id']]);
    $user = $stmt->fetch();
    
    if (password_verify($password, $user['password'])) {
        // Password correct - show edit form
        $_SESSION['edit_verified'] = true;
    } else {
        $error = 'Incorrect password';
    }
}

// Handle profile update
if (isset($_POST['update_profile']) && isset($_SESSION['edit_verified'])) {
    $fullName = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    
    if (empty($fullName)) {
        $error = 'Full name is required';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, gender = ? WHERE id = ?");
        if ($stmt->execute([$fullName, $phone, $gender, $currentUser['id']])) {
            // Update session
            $currentUser['full_name'] = $fullName;
            $currentUser['phone'] = $phone;
            $currentUser['gender'] = $gender;
            $_SESSION['user'] = $currentUser;
            
            $success = 'Profile updated successfully!';
            unset($_SESSION['edit_verified']);
        } else {
            $error = 'Failed to update profile';
        }
    }
}

// Handle cancel
if (isset($_GET['cancel'])) {
    unset($_SESSION['edit_verified']);
    header('Location: auth.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .edit-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            animation: fadeInUp 0.6s ease-out;
        }
        
        .edit-container h1 {
            font-size: 36px;
            margin-bottom: 30px;
            text-align: center;
            background: linear-gradient(135deg, #fff, #d96565);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            border: 2px solid transparent;
            background: rgba(255,255,255,0.95);
            color: #333;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #d96565;
            box-shadow: 0 0 0 4px rgba(217,101,101,0.2);
        }
        
        .form-group input[readonly] {
            background: rgba(255,255,255,0.5);
            cursor: not-allowed;
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
        
        .password-verify {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .password-verify p {
            margin-bottom: 20px;
            opacity: 0.8;
        }
        
        .info-box {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        
        .info-box p {
            margin: 10px 0;
        }
        
        .info-box strong {
            color: #d96565;
            min-width: 100px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main>
        <div class="edit-container">
            <h1>✏️ Edit Profile</h1>
            
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
            
            <?php if (!isset($_SESSION['edit_verified'])): ?>
                <!-- Password Verification Form -->
                <div class="password-verify">
                    <p>Please enter your password to edit your profile</p>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" name="verify_password" class="btn" style="flex: 1;">🔐 Verify</button>
                            <a href="auth.php" class="btn" style="background: #666;">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Edit Profile Form -->
                <div class="info-box">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($currentUser['username']); ?></p>
                    <p><small>Email and username cannot be changed</small></p>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" placeholder="+213 XXX XXX XXX">
                    </div>
                    
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="male" <?php echo ($currentUser['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>👨 Male</option>
                            <option value="female" <?php echo ($currentUser['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>👩 Female</option>
                            <option value="other" <?php echo ($currentUser['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>🧑 Other</option>
                        </select>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="update_profile" class="btn btn-success">✅ Save Changes</button>
                        <a href="?cancel=1" class="btn" style="background: #666;">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms</a> | <a href="privacy.php">Privacy</a></p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>