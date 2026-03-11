<?php
require_once 'config.php';

$error = '';
$success = '';

// Check if already logged in
if (isLoggedIn()) {
    header('Location: home.php');
    exit;
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? 'buyer';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';
    
    // Validate
    if (empty($fullName) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 4) {
        $error = 'Password must be at least 4 characters';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already exists';
        } else {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already taken';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, email, username, password, role, phone, gender, date_of_birth, registration_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$fullName, $email, $username, $hashedPassword, $role, $phone, $gender, $dob])) {
                    $userId = $pdo->lastInsertId();
                    
                    // If seller, create store
                    if ($role === 'seller') {
                        $storeName = $_POST['storeName'] ?? $fullName . "'s Store";
                        $storeDesc = $_POST['storeDescription'] ?? '';
                        $storeAddress = $_POST['businessAddress'] ?? '';
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO seller_stores (seller_id, store_name, store_description, business_address) 
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([$userId, $storeName, $storeDesc, $storeAddress]);
                    }
                    
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .register-wrapper {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            max-width: 700px;
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
        
        .register-container::before {
            content: '📝';
            position: absolute;
            font-size: 200px;
            opacity: 0.03;
            bottom: -50px;
            right: -30px;
            transform: rotate(-15deg);
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
        
        .register-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }
        
        .register-header h1 {
            font-size: 48px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff 0%, #4CAF50 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .register-header p {
            font-size: 16px;
            opacity: 0.8;
        }
        
        /* Role Selector */
        .role-selector {
            display: flex;
            gap: 20px;
            margin: 30px 0 40px;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        .role-card {
            flex: 1;
            padding: 25px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid transparent;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .role-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
        }
        
        .role-card.selected {
            border-color: #4CAF50;
            background: rgba(76, 175, 80, 0.1);
            box-shadow: 0 10px 30px rgba(76, 175, 80, 0.2);
        }
        
        .role-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .role-card h3 {
            margin-bottom: 5px;
        }
        
        .role-card.selected h3 {
            color: #4CAF50;
        }
        
        /* Form Styles */
        .form-section {
            position: relative;
            z-index: 1;
        }
        
        .form-section h3 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4CAF50;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border-radius: 15px;
            border: 2px solid transparent;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.2);
            transform: scale(1.02);
        }
        
        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            background: #ffffff;
        }
        
        /* Password Strength */
        .password-strength {
            height: 5px;
            background: rgba(255, 255, 255, 0.1);
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
        
        /* Seller Fields */
        .seller-fields {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 25px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }
        
        .seller-fields::before {
            content: '🏪';
            position: absolute;
            font-size: 100px;
            opacity: 0.1;
            bottom: -20px;
            right: -20px;
            transform: rotate(-10deg);
        }
        
        .seller-fields h3 {
            color: #4CAF50;
            margin-bottom: 25px;
        }
        
        /* Terms Section */
        .terms-section {
            margin: 30px 0 20px;
        }
        
        .terms {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 15px;
        }
        
        .terms input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #4CAF50;
        }
        
        .terms label {
            font-size: 15px;
            cursor: pointer;
        }
        
        .terms a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
        }
        
        .terms a:hover {
            text-decoration: underline;
        }
        
        /* Policy Buttons */
        .policy-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 15px 0 25px;
        }
        
        .policy-btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .policy-btn:hover {
            background: rgba(76, 175, 80, 0.2);
            border-color: #4CAF50;
            transform: translateY(-2px);
        }
        
        .policy-btn i {
            font-size: 16px;
        }
        
        .policy-btn.terms:hover {
            background: rgba(217, 101, 101, 0.2);
            border-color: #d96565;
        }
        
        .policy-btn.privacy:hover {
            background: rgba(76, 175, 80, 0.2);
            border-color: #4CAF50;
        }
        
        /* Register Button */
        .register-btn {
            width: 100%;
            padding: 18px;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border: none;
            color: white;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #45a049 0%, #4CAF50 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }
        
        .register-btn:hover::before {
            left: 0;
        }
        
        .register-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(76, 175, 80, 0.4);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        /* Message Container */
        #message-container {
            margin-bottom: 20px;
        }
        
        .error-message {
            background: rgba(217, 101, 101, 0.2);
            border: 1px solid #d96565;
            color: #d96565;
            padding: 12px 20px;
            border-radius: 50px;
            text-align: center;
            font-size: 14px;
            animation: shake 0.5s ease;
        }
        
        .success-message {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
            padding: 12px 20px;
            border-radius: 50px;
            text-align: center;
            font-size: 14px;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        @media (max-width: 768px) {
            .register-container {
                padding: 30px 20px;
            }
            
            .register-header h1 {
                font-size: 36px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .role-selector {
                flex-direction: column;
            }
            
            .policy-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main>
    <div class="register-wrapper">
        <div class="register-container">
            <div class="register-header">
                <h1>📝 Create Account</h1>
                <p>Join M7 Marketplace today!</p>
            </div>
            
            <div id="message-container">
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                    <script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 3000);
                    </script>
                <?php endif; ?>
            </div>
            
            <!-- Role Selection -->
            <div class="role-selector">
                <div class="role-card" onclick="selectRole('buyer')" id="buyer-card">
                    <div class="role-icon">🛍️</div>
                    <h3>Buyer</h3>
                    <p>Shop for products</p>
                </div>
                <div class="role-card" onclick="selectRole('seller')" id="seller-card">
                    <div class="role-icon">📦</div>
                    <h3>Seller</h3>
                    <p>Sell your products</p>
                </div>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="role" name="role" value="buyer">
                
                <!-- Personal Information -->
                <div class="form-section">
                    <h3>👤 Personal Information</h3>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="fullName" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" placeholder="your@email.com" required>
                        </div>
                        <div class="form-group">
                            <label>Username *</label>
                            <input type="text" name="username" placeholder="Choose username" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" id="password" placeholder="••••••••" required onkeyup="checkPasswordStrength()">
                            <div class="password-strength">
                                <div class="strength-bar" id="strength-bar"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="confirmPassword" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="male">👨 Male</option>
                                <option value="female">👩 Female</option>
                                <option value="other">🧑 Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" placeholder="+213 XXX XXX XXX">
                    </div>
                </div>
                
                <!-- Seller Fields -->
                <div class="seller-fields" id="seller-fields" style="display: none;">
                    <h3>🏪 Store Information</h3>
                    
                    <div class="form-group">
                        <label>Store Name *</label>
                        <input type="text" name="storeName" placeholder="Your store name">
                    </div>
                    
                    <div class="form-group">
                        <label>Store Description</label>
                        <textarea name="storeDescription" rows="3" placeholder="Describe your store..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Business Address</label>
                        <input type="text" name="businessAddress" placeholder="Your business address">
                    </div>
                </div>
                
                <!-- Terms and Policies Section -->
                <div class="terms-section">
                    <!-- Terms Checkbox -->
                    <div class="terms">
                        <input type="checkbox" id="terms" required>
                        <label for="terms">I agree to the Terms and Conditions and Privacy Policy</label>
                    </div>
                    
                    <!-- Policy Buttons -->
                    <div class="policy-buttons">
                        <a href="terms.php" target="_blank" class="policy-btn terms">
                            <span>📜</span> Terms of Service
                        </a>
                        <a href="privacy.php" target="_blank" class="policy-btn privacy">
                            <span>🔒</span> Privacy Policy
                        </a>
                    </div>
                </div>
                
                <button type="submit" class="register-btn">Create Account</button>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
<script>
// Role selection
function selectRole(role) {
    document.getElementById('role').value = role;
    
    const buyerCard = document.getElementById('buyer-card');
    const sellerCard = document.getElementById('seller-card');
    const sellerFields = document.getElementById('seller-fields');
    
    if (role === 'buyer') {
        buyerCard.classList.add('selected');
        sellerCard.classList.remove('selected');
        sellerFields.style.display = 'none';
    } else {
        sellerCard.classList.add('selected');
        buyerCard.classList.remove('selected');
        sellerFields.style.display = 'block';
    }
}

// Password strength checker
function checkPasswordStrength() {
    let password = document.getElementById('password').value;
    let strengthBar = document.getElementById('strength-bar');
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]+/)) strength += 25;
    if (password.match(/[A-Z]+/)) strength += 25;
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

// Select buyer by default
selectRole('buyer');
</script>
</body>
</html>