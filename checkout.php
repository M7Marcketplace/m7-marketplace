<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php
require_once 'config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php?redirect=checkout');
    exit;
}

$currentUser = getCurrentUser();
?>

<?php include 'navbar.php'; ?>

<main>
    <div id="checkout-container">
        <!-- Checkout will be loaded by JavaScript -->
        <div class="loading-state" style="text-align: center; padding: 80px;">
            <div class="spinner" style="width: 60px; height: 60px; border: 4px solid rgba(255,255,255,0.1); border-top: 4px solid #d96565; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
            <h2>Loading Checkout</h2>
            <p style="opacity: 0.7;">Please wait while we prepare your checkout...</p>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
<script>
    // Load checkout page
    document.addEventListener('DOMContentLoaded', function() {
        loadCheckoutPage();
    });
</script>
</body>
</html>