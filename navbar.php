<?php
$currentUser = getCurrentUser();
?>

<style>
/* DIRECT STYLES - NO EXTERNAL CSS DEPENDENCY */
.new-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 50px;
    background: rgba(15, 15, 15, 0.95);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-family: 'Poppins', sans-serif;
}

.new-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.new-logo-img {
    width: 50px !important;
    height: 50px !important;
    border-radius: 50% !important;
    object-fit: cover !important;
    border: 3px solid #d96565 !important;
    transition: all 0.3s ease;
}

.new-logo-img:hover {
    transform: scale(1.1);
    border-color: white !important;
    box-shadow: 0 0 15px rgba(217, 101, 101, 0.7);
}

@keyframes new-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.new-logo-img.spinning {
    animation: new-spin 0.6s ease-in-out !important;
}

.new-logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #fff, #d96565);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.new-nav ul {
    display: flex;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.new-nav a {
    color: white;
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 30px;
    font-weight: 500;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.new-nav a:hover {
    background: rgba(217, 101, 101, 0.2);
    color: #d96565;
}

.new-nav a.active {
    background: #d96565;
    color: white;
}

/* Favicon style - this makes the browser tab icon round */
link[rel="icon"] {
    border-radius: 50% !important;
}

/* Responsive */
@media (max-width: 768px) {
    .new-header {
        flex-direction: column;
        padding: 15px 20px;
        gap: 15px;
    }
    .new-nav ul {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>

<!-- NEW SITE NOTIFICATION -->
<div style="background: rgba(217, 101, 101, 0.2); backdrop-filter: blur(10px); border-bottom: 1px solid #d96565; color: white; padding: 12px 20px; text-align: center; position: relative; z-index: 1000;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; gap: 15px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="background: #d96565; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">🚀</span>
            <span style="font-weight: 600;">Brand New Marketplace!</span>
        </div>
        <span style="opacity: 0.9;">We're just starting out - you might find some small bugs. Thanks for your support!</span>
        <button onclick="this.parentElement.parentElement.style.display='none'" style="background: #d96565; border: none; color: white; padding: 5px 15px; border-radius: 50px; cursor: pointer; font-size: 13px; font-weight: 600;">Got it</button>
    </div>
</div>

<header class="new-header">
    <a href="home.php" class="new-logo" id="newLogo">
        <img src="M7shooping.png" alt="M7 Shopping Logo" class="logo-img" id="navbarLogo">
        <span class="new-logo-text">M7 Marketplace</span>
    </a>
    <nav class="new-nav">
        <ul>
            <li><a href="home.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">🏠 Home</a></li>
            <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">🛍️ Products</a></li>
            <li><a href="cart.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">🛒 Cart</a></li>
            <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">📖 About</a></li>
            <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">📞 Contact</a></li>
            
            <?php if ($currentUser): ?>
                <?php if ($currentUser['role'] === 'seller'): ?>
                    <li><a href="seller-dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'seller-dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
                <?php endif; ?>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <li><a href="admin-panel.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-panel.php' ? 'active' : ''; ?>">⚙️ Admin</a></li>
                <?php endif; ?>
                <li><a href="auth.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'auth.php' ? 'active' : ''; ?>">👤 <?php echo htmlspecialchars(explode(' ', $currentUser['full_name'])[0]); ?></a></li>
                <li><a href="logout.php">🚪</a></li>
            <?php else: ?>
                <li><a href="login.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">🔐 Login</a></li>
                <li><a href="register.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">📝 Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <script>
    // Make logo spin when clicked
    document.getElementById('navbarLogo').addEventListener('click', function(e) {
        // Add spinning animation
        this.style.transition = 'transform 0.5s ease';
        this.style.transform = 'rotate(360deg)';
        
        // Remove the spin after animation completes
        setTimeout(() => {
            this.style.transform = 'rotate(0deg)';
        }, 500);
        
        // Don't prevent the link from working
        // The logo will still take you to home page
    });
    </script>
</header>

<script>
document.getElementById('newLogo').addEventListener('click', function(e) {
    const logo = document.getElementById('newLogoImg');
    logo.classList.add('spinning');
    setTimeout(() => {
        logo.classList.remove('spinning');
    }, 600);
    // The link still works normally
});
</script>