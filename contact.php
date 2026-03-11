<?php
require_once 'config.php';
$currentUser = getCurrentUser();

$success = '';
$error = '';
$mailtoLink = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (empty($name) || empty($subject) || empty($message)) {
        $error = 'Please fill all required fields';
    } else if (empty($email) && !isLoggedIn()) {
        $error = 'Please enter your email address';
    } else {
        // If user is logged in and email is empty, use their account email
        if (isLoggedIn() && empty($email)) {
            $email = $currentUser['email'];
        }
        
        // Save to database
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (name, email, phone, subject, message, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$name, $email, $phone, $subject, $message])) {
            // Create email content
            $body = "
Name: $name
Email: $email
Phone: " . ($phone ?: 'Not provided') . "
Subject: $subject

Message:
$message
            ";
            
            // Create mailto link
            $mailtoLink = "mailto:m7.contact.us@gmail.com?subject=" . urlencode($subject) . "&body=" . urlencode($body);
            
            // Show success message with link
            $success = true;
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .page-header h1 {
            font-size: 56px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }
        
        .contact-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .contact-info::before {
            content: '📞';
            position: absolute;
            font-size: 150px;
            opacity: 0.05;
            bottom: -30px;
            right: -30px;
            transform: rotate(-15deg);
        }
        
        .contact-info h2 {
            color: #d96565;
            font-size: 32px;
            margin-bottom: 30px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .contact-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(15px);
            border-color: #d96565;
        }
        
        .contact-icon {
            font-size: 28px;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .contact-details {
            flex: 1;
        }
        
        .contact-details h3 {
            margin: 0 0 5px 0;
            color: #d96565;
            font-size: 18px;
        }
        
        .contact-details p {
            margin: 0;
            opacity: 0.9;
        }
        
        .contact-details a {
            color: white;
            text-decoration: none;
        }
        
        .contact-details a:hover {
            color: #d96565;
            text-decoration: underline;
        }
        
        .social-section {
            margin-top: 40px;
        }
        
        .social-section h3 {
            color: #d96565;
            margin-bottom: 20px;
            font-size: 22px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-link {
            width: 55px;
            height: 55px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-decoration: none;
            color: white;
        }
        
        .social-link:hover {
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            transform: translateY(-8px) rotate(360deg);
        }
        
        .contact-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .contact-form::before {
            content: '✉️';
            position: absolute;
            font-size: 150px;
            opacity: 0.05;
            top: -30px;
            right: -30px;
            transform: rotate(15deg);
        }
        
        .contact-form h2 {
            color: #d96565;
            font-size: 32px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            text-align: left;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px 18px;
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
            border-color: #d96565;
            box-shadow: 0 0 0 4px rgba(217, 101, 101, 0.2);
        }
        
        .submit-btn {
            width: 100%;
            padding: 18px;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            border: none;
            color: white;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(217, 101, 101, 0.4);
        }
        
        .success-message {
            text-align: center;
            padding: 40px;
            background: rgba(76, 175, 80, 0.2);
            border: 2px solid #4CAF50;
            border-radius: 30px;
            margin: 20px 0;
        }
        
        .success-message h3 {
            color: #4CAF50;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .error-message {
            text-align: center;
            padding: 40px;
            background: rgba(217, 101, 101, 0.2);
            border: 2px solid #d96565;
            border-radius: 30px;
            margin: 20px 0;
        }
        
        .error-message h3 {
            color: #d96565;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main>
    <div class="page-header">
        <h1>📞 Get in Touch</h1>
        <p>Have questions? Send us a message and we'll get back to you!</p>
    </div>
    
    <div id="message-status">
        <?php if ($success): ?>
            <div class="success-message">
                <h3>✅ Message saved! Your email client is opening...</h3>
                <p>Just click send in your email app to complete.</p>
                <script>
                    // Automatically open email client after 1 second
                    setTimeout(function() {
                        window.location.href = "<?php echo $mailtoLink; ?>";
                    }, 1000);
                </script>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message">
                <h3>❌ <?php echo $error; ?></h3>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="contact-grid">
        <!-- Contact Information -->
        <div class="contact-info">
            <h2>Contact Information</h2>
            
            <div class="contact-item">
                <div class="contact-icon">✉️</div>
                <div class="contact-details">
                    <h3>Email</h3>
                    <p><a href="mailto:m7.contact.us@gmail.com">m7.contact.us@gmail.com</a></p>
                </div>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">⏰</div>
                <div class="contact-details">
                    <h3>Working Hours</h3>
                    <p>Sunday - Thursday: 10:00 - 15:00</p>
                    <p style="font-size: 14px;">Friday & Saturday: Closed</p>
                </div>
            </div>
            
            <div class="social-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="https://www.instagram.com/m7__brands/" target="_blank" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://web.facebook.com/profile.php?id=61583718757550" target="_blank" class="social-link">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <!-- NEW TIKTOK LINK -->
                    <a href="https://www.tiktok.com/@m7__brands" target="_blank" class="social-link">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="contact-form">
            <h2>Send us a Message</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="name" placeholder="Enter your name" required>
                </div>
                
                <div class="form-group">
                    <label>Your Email <?php echo isLoggedIn() ? '' : '*'; ?></label>
                    <input type="email" name="email" 
                        value="<?php echo isLoggedIn() ? htmlspecialchars($currentUser['email']) : ''; ?>" 
                        placeholder="<?php echo isLoggedIn() ? 'Your account email' : 'you@example.com'; ?>" 
                        <?php echo isLoggedIn() ? 'readonly' : ''; ?>
                        <?php echo !isLoggedIn() ? 'required' : ''; ?>>
                    <?php if (isLoggedIn()): ?>
                        <small style="color: #4CAF50; display: block; margin-top: 5px;">✅ Using your account email</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" placeholder="+213 XXX XXX XXX">
                </div>
                
                <div class="form-group">
                    <label>Subject *</label>
                    <select name="subject" required>
                        <option value="">-- Select a subject --</option>
                        <option value="General Question">📋 General Question</option>
                        <option value="Technical Support">🛠️ Technical Support</option>
                        <option value="Become a Seller">📦 Become a Seller</option>
                        <option value="Buyer Help">🛍️ Buyer Help</option>
                        <option value="Partnership">🤝 Partnership</option>
                        <option value="Other">🔄 Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Your Message *</label>
                    <textarea name="message" rows="5" placeholder="Write your message..." required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">📨 Send Message</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; font-size: 14px; background: rgba(76, 175, 80, 0.2); padding: 10px; border-radius: 10px;">
                📧 This will open your email app. The message is also saved in our database!
            </p>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
<script>
function sendByEmail() {
    let name = document.querySelector('input[name="name"]')?.value.trim();
    let email = document.querySelector('input[name="email"]')?.value.trim();
    let phone = document.querySelector('input[name="phone"]')?.value.trim();
    let subject = document.querySelector('select[name="subject"]')?.value;
    let message = document.querySelector('textarea[name="message"]')?.value.trim();
    
    // Check if email field is readonly (user is logged in)
    let emailField = document.querySelector('input[name="email"]');
    let isEmailReadonly = emailField && emailField.hasAttribute('readonly');
    
    if (!name || !subject || !message) {
        showNotification('❌ Please fill all required fields', 'error');
        return false;
    }
    
    if (!isEmailReadonly && !email) {
        showNotification('❌ Please enter your email address', 'error');
        return false;
    }
    
    let body = `
Name: ${name}
Email: ${email}
Phone: ${phone || 'Not provided'}
Subject: ${subject}

Message:
${message}
    `;
    
    let mailtoLink = `mailto:m7.contact.us@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = mailtoLink;
    
    let statusDiv = document.getElementById('message-status');
    if (statusDiv) {
        statusDiv.innerHTML = `
            <div class="success-message">
                <h3>✅ Your email client has been opened!</h3>
                <p>Just click send in your email app.</p>
            </div>
        `;
    }
    
    return false;
}

// Override form submission
document.addEventListener('DOMContentLoaded', function() {
    let form = document.querySelector('.contact-form form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            return sendByEmail();
        };
    }
});
</script>
</body>
</html>