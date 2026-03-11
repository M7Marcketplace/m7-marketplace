<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .privacy-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .privacy-container h1 {
            font-size: 42px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff, #d96565);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .privacy-container h2 {
            color: #d96565;
            margin: 30px 0 15px;
            font-size: 24px;
        }
        
        .privacy-container p {
            line-height: 1.8;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .privacy-container ul {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        
        .privacy-container li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .last-updated {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-style: italic;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main>
        <div class="privacy-container">
            <h1>🔒 Privacy Policy</h1>
            <p class="last-updated">Last Updated: March 2026</p>
            
            <h2>1. Information We Collect</h2>
            <p>We collect information you provide when you:</p>
            <ul>
                <li>Register for an account (name, email, phone number)</li>
                <li>Create a seller profile (store name, business information)</li>
                <li>Make a purchase (shipping address, payment information)</li>
                <li>Contact us (messages, inquiries)</li>
            </ul>
            
            <h2>2. How We Use Your Information</h2>
            <p>We use your information to:</p>
            <ul>
                <li>Provide and improve our services</li>
                <li>Process transactions and send order confirmations</li>
                <li>Communicate with you about your account or orders</li>
                <li>Prevent fraud and ensure platform security</li>
                <li>Comply with legal obligations</li>
            </ul>
            
            <h2>3. Information Sharing</h2>
            <p>We do not sell your personal information. We may share information with:</p>
            <ul>
                <li><strong>Other users:</strong> Sellers see buyer shipping information to fulfill orders; buyers see seller contact information.</li>
                <li><strong>Service providers:</strong> Payment processors, shipping partners (only as necessary).</li>
                <li><strong>Legal authorities:</strong> When required by law.</li>
            </ul>
            
            <h2>4. Data Security</h2>
            <p>We implement security measures to protect your information, including encryption and secure servers. However, no method of transmission over the Internet is 100% secure.</p>
            
            <h2>5. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access the personal information we hold about you</li>
                <li>Correct inaccurate information</li>
                <li>Request deletion of your account and data</li>
                <li>Opt out of marketing communications</li>
            </ul>
            
            <h2>6. Cookies</h2>
            <p>We use cookies to enhance your browsing experience, remember your preferences, and analyze site traffic. You can disable cookies in your browser settings.</p>
            
            <h2>7. Third-Party Links</h2>
            <p>Our site may contain links to external websites. We are not responsible for their privacy practices.</p>
            
            <h2>8. Children's Privacy</h2>
            <p>Our services are not intended for users under 18. We do not knowingly collect information from minors.</p>
            
            <h2>9. Changes to This Policy</h2>
            <p>We may update this Privacy Policy periodically. We will notify you of significant changes via email or site notice.</p>
            
            <h2>10. Contact Us</h2>
            <p>For privacy-related questions, contact us at: <a href="mailto:m7.contact.us@gmail.com" style="color: #d96565;">m7.contact.us@gmail.com</a></p>
            
            <div class="last-updated">
                <p>By using M7 Marketplace, you consent to this Privacy Policy.</p>
            </div>
        </div>
    </main>
    
    <footer>
        <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>