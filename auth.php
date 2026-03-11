<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .account-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        /* Guest View Styles */
        .guest-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .guest-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 60px 40px;
            border-radius: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.6s ease-out;
        }
        
        .guest-card::before {
            content: '👋';
            position: absolute;
            font-size: 200px;
            opacity: 0.05;
            bottom: -50px;
            right: -30px;
            transform: rotate(-10deg);
        }
        
        .guest-icon {
            font-size: 120px;
            margin-bottom: 20px;
            animation: wave 2s infinite;
        }
        
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }
        
        .guest-card h1 {
            font-size: 48px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .guest-card p {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .guest-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            position: relative;
            z-index: 2;
        }
        
        .guest-actions .btn {
            flex: 1;
            padding: 16px 30px;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .guest-actions .btn:first-child {
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
        }
        
        .guest-actions .btn:last-child {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }
        
        .guest-actions .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        
        /* Profile Card Styles (for logged in users) */
        .profile-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 40px;
            padding: 50px;
            margin: 0 auto;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .profile-card::before {
            content: '👤';
            position: absolute;
            font-size: 200px;
            opacity: 0.03;
            bottom: -50px;
            right: -50px;
            transform: rotate(-15deg);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 40px;
            margin-bottom: 50px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        
        .avatar-container {
            position: relative;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 70px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(217, 101, 101, 0.3);
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .edit-avatar {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            font-size: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        
        .edit-avatar:hover {
            transform: rotate(360deg) scale(1.1);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.6);
        }
        
        .profile-title {
            flex: 1;
        }
        
        .profile-title h1 {
            font-size: 42px;
            margin: 0 0 10px 0;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .profile-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .profile-badge.buyer {
            background: rgba(33, 150, 243, 0.2);
            color: #2196F3;
            border-color: #2196F3;
        }
        
        .profile-badge.seller {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border-color: #4CAF50;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 40px 0;
            position: relative;
            z-index: 1;
        }
        
        .info-item {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 20px;
            border-radius: 20px;
            text-align: left;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            background: rgba(0, 0, 0, 0.4);
            transform: translateX(5px);
            border-color: #d96565;
        }
        
        .info-label {
            color: #d96565;
            font-size: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .info-value {
            font-size: 18px;
            font-weight: 600;
            word-break: break-word;
        }
        
        .store-section {
            background: rgba(76, 175, 80, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 25px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .store-section::before {
            content: '🏪';
            position: absolute;
            font-size: 120px;
            opacity: 0.1;
            bottom: -20px;
            right: -20px;
            transform: rotate(-10deg);
        }
        
        .store-section h3 {
            color: #4CAF50;
            font-size: 28px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .store-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .store-item {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 15px;
        }
        
        .store-item .label {
            color: #4CAF50;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 5px;
            opacity: 0.8;
        }
        
        .store-item .value {
            font-size: 16px;
            font-weight: 600;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 40px;
            position: relative;
            z-index: 1;
        }
        
        .action-btn {
            padding: 16px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-decoration: none;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .action-btn.edit {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }
        
        .action-btn.password {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
        }
        
        .action-btn.logout {
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
        }
        
        .action-btn.dashboard {
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
        }
        
        /* Admin Panel */
        .admin-panel {
            margin-top: 60px;
            border-top: 2px dashed #d96565;
            padding-top: 40px;
            text-align: center;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
        
        .admin-panel:hover {
            opacity: 1;
        }
        
        .admin-panel h3 {
            color: #d96565;
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .admin-btn {
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid #d96565;
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
        }
        
        .admin-btn:hover {
            background: #d96565;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(217, 101, 101, 0.4);
        }
        
        /* Loading State */
        .loading-state {
            text-align: center;
            padding: 80px;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid #d96565;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .guest-card {
                padding: 40px 20px;
            }
            
            .guest-icon {
                font-size: 80px;
            }
            
            .guest-card h1 {
                font-size: 36px;
            }
            
            .guest-actions {
                flex-direction: column;
            }
            
            .profile-card {
                padding: 30px;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .store-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php
require_once 'config.php';
$currentUser = getCurrentUser();
?>

<?php include 'navbar.php'; ?>

<main>
    <div class="account-container">
        <div id="account-container">
            <?php if ($currentUser): ?>
                <?php
                // Get store info if seller
                $store = null;
                if ($currentUser['role'] === 'seller') {
                    $stmt = $pdo->prepare("SELECT * FROM seller_stores WHERE seller_id = ?");
                    $stmt->execute([$currentUser['id']]);
                    $store = $stmt->fetch();
                }
                ?>
                
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="avatar-container">
                            <div class="profile-avatar"><?php echo $currentUser['profile_pic'] ?? '👤'; ?></div>
                            <div class="edit-avatar" onclick="alert('Profile picture upload coming soon!')">✏️</div>
                        </div>
                        <div class="profile-title">
                            <h1><?php echo htmlspecialchars($currentUser['full_name']); ?></h1>
                            <span class="profile-badge <?php echo $currentUser['role']; ?>">
                                <?php echo $currentUser['role'] === 'seller' ? '🛒 SELLER' : '👤 BUYER'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">📧 Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🔑 Username</div>
                            <div class="info-value"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">⚥ Gender</div>
                            <div class="info-value"><?php echo htmlspecialchars($currentUser['gender'] ?? 'Not specified'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">📅 Joined</div>
                            <div class="info-value"><?php echo date('d M Y', strtotime($currentUser['registration_date'])); ?></div>
                        </div>
                        <?php if ($currentUser['phone']): ?>
                        <div class="info-item">
                            <div class="info-label">📱 Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($currentUser['phone']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($currentUser['role'] === 'seller' && $store): ?>
                    <div class="store-section">
                        <h3>🏪 Store Information</h3>
                        <div class="store-grid">
                            <div class="store-item">
                                <div class="label">Store Name</div>
                                <div class="value"><?php echo htmlspecialchars($store['store_name']); ?></div>
                            </div>
                            <div class="store-item">
                                <div class="label">Description</div>
                                <div class="value"><?php echo htmlspecialchars($store['store_description'] ?? 'No description'); ?></div>
                            </div>
                            <div class="store-item">
                                <div class="label">Address</div>
                                <div class="value"><?php echo htmlspecialchars($store['business_address'] ?? 'No address'); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <a href="edit-profile.php" class="action-btn edit">✏️ Edit Profile</a>
                        <a href="change-password.php" class="action-btn password">🔑 Change Password</a>
                        <?php if ($currentUser['role'] === 'seller'): ?>
                        <a href="seller-dashboard.php" class="action-btn dashboard">📊 Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="action-btn logout">🚪 Logout</a>
                        <a href="delete-account.php" class="action-btn delete" style="background: #d96565; grid-column: span 2;">🗑️ Delete Account</a>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Guest View -->
                <div class="guest-container">
                    <div class="guest-card">
                        <div class="guest-icon">👋</div>
                        <h1>Welcome!</h1>
                        <p>Join M7 Marketplace today to start shopping or selling. Create an account or login to continue.</p>
                        <div class="guest-actions">
                            <a href="login.php" class="btn">🔐 Login</a>
                            <a href="register.php" class="btn">📝 Register</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
</body>
</html>