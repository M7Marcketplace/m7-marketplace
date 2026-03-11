<?php
require_once 'config.php';

$error = '';

// Check if already logged in
if (isLoggedIn()) {
    header('Location: home.php');
    exit;
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from session data
            unset($user['password']);
            $_SESSION['user'] = $user;
            
            // Redirect based on role
            if ($user['role'] === 'seller') {
                header('Location: seller-dashboard.php');
            } else {
                header('Location: home.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Keep all your existing CSS exactly as is */
        .login-wrapper {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            max-width: 480px;
            width: 100%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 50px 40px;
            border-radius: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .login-container::before {
            content: '🔐';
            position: absolute;
            font-size: 150px;
            opacity: 0.05;
            bottom: -40px;
            right: -30px;
            transform: rotate(-15deg);
        }
        
        .login-container::after {
            content: '🛒';
            position: absolute;
            font-size: 100px;
            opacity: 0.05;
            top: -30px;
            left: -30px;
            transform: rotate(15deg);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }
        
        .login-header h1 {
            font-size: 48px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .login-header p {
            font-size: 16px;
            opacity: 0.8;
        }
        
        .input-group {
            margin-bottom: 25px;
            text-align: left;
            position: relative;
            z-index: 1;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #d96565;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .input-group input {
            width: 100%;
            padding: 16px 20px;
            border-radius: 15px;
            border: 2px solid transparent;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #d96565;
            box-shadow: 0 0 0 4px rgba(217, 101, 101, 0.2);
            transform: scale(1.02);
        }
        
        .input-group input:hover {
            background: #ffffff;
        }
        
        .input-group input::placeholder {
            color: #999;
            opacity: 0.7;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #d96565;
            font-size: 18px;
        }
        
        .input-icon input {
            padding-left: 45px;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0 30px;
            position: relative;
            z-index: 1;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #d96565;
        }
        
        .remember-me label {
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .forgot-link {
            color: #d96565;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            opacity: 0.8;
        }
        
        .forgot-link:hover {
            color: #fff;
            opacity: 1;
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            padding: 18px;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            border: none;
            color: white;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #b84343 0%, #d96565 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }
        
        .login-btn:hover::before {
            left: 0;
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(217, 101, 101, 0.4);
        }
        
        .login-btn:active {
            transform: translateY(-1px);
        }
        
        .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px 0;
            position: relative;
            z-index: 1;
        }
        
        .divider-line {
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        }
        
        .divider span {
            color: #fff;
            opacity: 0.7;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .social-login {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }
        
        .social-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
        }
        
        .social-btn:hover {
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            transform: translateY(-8px) scale(1.1);
            box-shadow: 0 10px 25px rgba(217, 101, 101, 0.4);
            border-color: transparent;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }
        
        .register-link p {
            color: #fff;
            opacity: 0.9;
            font-size: 15px;
        }
        
        .register-link a {
            color: #d96565;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 5px;
        }
        
        .register-link a:hover {
            color: #fff;
            text-decoration: underline;
        }
        
        .error-message {
            background: rgba(217, 101, 101, 0.2);
            border: 1px solid #d96565;
            color: #d96565;
            padding: 12px 20px;
            border-radius: 50px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .success-message {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
            padding: 12px 20px;
            border-radius: 50px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 36px;
            }
            
            .remember-forgot {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <h1>🔐 Welcome Back</h1>
                <p>Please login to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-icon">
                        <i>📧</i>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label>Password</label>
                    <div class="input-icon">
                        <i>🔑</i>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> 
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="login-btn">Login</button>
                
                <div class="divider">
                    <span class="divider-line"></span>
                    <span>or</span>
                    <span class="divider-line"></span>
                </div>
                
                <div class="register-link">
                    <p>Don't have an account?<a href="register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
</body>
</html>