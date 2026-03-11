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

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_delete'] ?? '';
    
    if (empty($password)) {
        $error = 'Please enter your password';
    } elseif ($confirm !== 'DELETE') {
        $error = 'Please type DELETE to confirm';
    } else {
        // Verify password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $user = $stmt->fetch();
        
        if (password_verify($password, $user['password'])) {
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Delete user's cart items
                $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$currentUser['id']]);
                
                // Delete user's products (if seller)
                if ($currentUser['role'] === 'seller') {
                    $pdo->prepare("DELETE FROM products WHERE seller_id = ?")->execute([$currentUser['id']]);
                    $pdo->prepare("DELETE FROM seller_stores WHERE seller_id = ?")->execute([$currentUser['id']]);
                }
                
                // Delete user's messages
                $pdo->prepare("DELETE FROM contact_messages WHERE email = ?")->execute([$currentUser['email']]);
                
                // Finally delete the user
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$currentUser['id']]);
                
                $pdo->commit();
                
                // Clear session
                session_destroy();
                
                $success = true;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Failed to delete account. Please try again.';
            }
        } else {
            $error = 'Incorrect password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Account - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .delete-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            animation: fadeInUp 0.6s ease-out;
        }
        
        .delete-container h1 {
            font-size: 36px;
            margin-bottom: 20px;
            text-align: center;
            background: linear-gradient(135deg, #fff, #d96565);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .warning-box {
            background: rgba(217,101,101,0.2);
            border: 2px solid #d96565;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .warning-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%,100%{transform:translateX(0)}
            10%,30%,50%,70%,90%{transform:translateX(-5px)}
            20%,40%,60%,80%{transform:translateX(5px)}
        }
        
        .warning-box h2 {
            color: #d96565;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .warning-box p {
            margin: 10px 0;
            opacity: 0.9;
        }
        
        .warning-box ul {
            text-align: left;
            margin: 20px 0;
            padding-left: 20px;
        }
        
        .warning-box li {
            margin: 10px 0;
            color: #d96565;
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
        
        .confirm-text {
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
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
        
        .btn-delete {
            background: #d96565;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-delete:hover {
            background: #b84343;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(217,101,101,0.4);
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
            text-align: center;
            padding: 50px;
        }
        
        .success-message h2 {
            color: #4CAF50;
            font-size: 32px;
            margin: 20px 0;
        }
        
        .success-message p {
            margin: 20px 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main>
        <?php if ($success): ?>
            <div class="delete-container">
                <div class="success-message">
                    <div style="font-size: 100px;">👋</div>
                    <h2>Account Deleted</h2>
                    <p>Your account has been permanently deleted.</p>
                    <p>Thank you for using M7 Marketplace. We're sorry to see you go.</p>
                    <a href="home.php" class="btn" style="margin-top: 30px;">Return to Home</a>
                </div>
            </div>
        <?php else: ?>
        
        <div class="delete-container">
            <h1>🗑️ Delete Account</h1>
            
            <?php if ($error): ?>
                <div class="error-message">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="warning-box">
                <div class="warning-icon">⚠️</div>
                <h2>WARNING! This action cannot be undone.</h2>
                <p>Deleting your account will permanently remove:</p>
                <ul>
                    <li>✅ Your profile information</li>
                    <?php if ($currentUser['role'] === 'seller'): ?>
                        <li>✅ All your products</li>
                        <li>✅ Your store information</li>
                    <?php endif; ?>
                    <li>✅ Your order history</li>
                    <li>✅ All your personal data</li>
                </ul>
                <p style="color: #d96565; font-weight: bold;">This is permanent and cannot be recovered!</p>
            </div>
            
            <form method="POST" action="" onsubmit="return confirm('Are you ABSOLUTELY sure you want to delete your account? This cannot be undone!');">
                <div class="form-group">
                    <label>Enter your password to confirm</label>
                    <input type="password" name="password" placeholder="Your password" required>
                </div>
                
                <div class="form-group">
                    <label>Type <span class="confirm-text">DELETE</span> to confirm</label>
                    <input type="text" name="confirm_delete" placeholder="DELETE" required pattern="DELETE" title='Please type "DELETE" exactly'>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn-delete">🗑️ Permanently Delete Account</button>
                    <a href="auth.php" class="btn" style="background: #666;">Cancel</a>
                </div>
            </form>
            
            <p style="text-align: center; margin-top: 30px; opacity: 0.6; font-size: 14px;">
                ⚠️ This action is irreversible. All your data will be lost.
            </p>
        </div>
        <?php endif; ?>
    </main>
    
    <footer>
        <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms</a> | <a href="privacy.php">Privacy</a></p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>