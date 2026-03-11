// ==================== M7 MARKETPLACE - COMPLETE JAVASCRIPT ====================
// All UI interactions, cart management, and frontend functionality

// ==================== GLOBAL VARIABLES ====================
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 M7 Marketplace - UI Ready');
    
    updateNavbarForUser();
    updateCartCount();
    
    let path = window.location.pathname.split('/').pop();
    
    // Load appropriate functions based on page
    switch(path) {
        case 'cart.php':
            displayCart();
            break;
        case 'checkout.php':
            loadCheckoutPage();
            break;
        case 'products_details.php':
            // Handled by PHP
            break;
        case 'seller-dashboard.php':
            loadSellerDashboard();
            break;
        case 'my-products.php':
            loadMyProductsPage();
            break;
        case 'seller-add-product.php':
            initAddProductForm();
            break;
        case 'orders.php':
            loadSellerOrders();
            break;
        case 'auth.php':
            // Handled by PHP
            break;
        default:
            console.log('📄 Page:', path || 'home');
    }
});

// ==================== NAVBAR FUNCTIONS ====================
function updateNavbarForUser() {
    // This is now handled by PHP in navbar.php
    // Keeping for JavaScript-based updates if needed
    console.log('Navbar updated by PHP');
}

function getCartCount() {
    return cart.reduce((total, item) => total + item.quantity, 0);
}

function updateCartCount() {
    let count = getCartCount();
    document.querySelectorAll('.cart-count').forEach(link => {
        link.textContent = count > 0 ? `(${count})` : '';
    });
}

// ==================== NOTIFICATION SYSTEM ====================
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.premium-notification');
    existingNotifications.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = 'premium-notification';
    
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const colors = {
        success: 'linear-gradient(135deg, #10b981, #059669)',
        error: 'linear-gradient(135deg, #ef4444, #dc2626)',
        warning: 'linear-gradient(135deg, #f59e0b, #d97706)',
        info: 'linear-gradient(135deg, #3b82f6, #2563eb)'
    };
    
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 320px;
        padding: 16px 20px; border-radius: 12px; background: ${colors[type]};
        color: white; font-weight: 500; font-size: 15px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        display: flex; align-items: center; gap: 12px;
        transform: translateX(400px);
        animation: slideInNotification 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        border: 1px solid rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
    `;
    
    notification.innerHTML = `
        <span style="font-size: 24px;">${icons[type]}</span>
        <span style="flex: 1;">${message}</span>
        <span onclick="this.parentElement.remove()" style="cursor:pointer; opacity:0.7; font-size:18px;">✕</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutNotification 0.3s ease forwards';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

// Add animation styles if not already present
if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideInNotification {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutNotification {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
}

// ==================== CART FUNCTIONS ====================
function addToCart(productId, productName, productPrice, productImage, sellerId, sellerName) {
    let existingItem = cart.find(item => item.id == productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
        showNotification(`➕ Increased ${productName} quantity`, 'success');
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: 1,
            image: productImage,
            sellerId: sellerId,
            sellerName: sellerName
        });
        showNotification(`✅ ${productName} added to cart!`, 'success');
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

function removeFromCart(index) {
    let item = cart[index];
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    displayCart();
    updateCartCount();
    showNotification(`🗑️ ${item.name} removed from cart`, 'info');
}

function updateQuantity(index, change) {
    let item = cart[index];
    let newQuantity = item.quantity + change;
    
    if (newQuantity <= 0) { 
        removeFromCart(index); 
        return; 
    }
    
    item.quantity = newQuantity;
    localStorage.setItem('cart', JSON.stringify(cart));
    displayCart();
    updateCartCount();
}

function displayCart() {
    let container = document.getElementById('cart-items-container');
    let summaryContainer = document.getElementById('summary-details');
    
    if (!container) return;
    
    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <div class="empty-cart-icon" style="font-size: 80px; margin-bottom: 20px;">🛒</div>
                <h2 style="font-size: 32px; margin-bottom: 20px; background: linear-gradient(135deg, #fff 0%, #d96565 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Your cart is empty</h2>
                <p style="margin: 20px 0; opacity: 0.8;">Looks like you haven't added any items yet.</p>
                <a href="products.php" class="continue-shopping-btn" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #d96565 0%, #b84343 100%); color: white; text-decoration: none; border-radius: 50px; margin-top: 30px;">Browse Products</a>
            </div>
        `;
        if (summaryContainer) summaryContainer.innerHTML = '';
        return;
    }
    
    let html = '';
    let subtotal = 0;
    let totalItems = 0;
    
    for (let i = 0; i < cart.length; i++) {
        let item = cart[i];
        let itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        totalItems += item.quantity;
        
        html += `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image" onerror="this.src='https://via.placeholder.com/100'">
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <p class="cart-item-price">${item.price.toLocaleString()} DZD</p>
                    <p class="cart-item-seller">Sold by: ${item.sellerName || 'Unknown'}</p>
                </div>
                <div class="cart-item-actions">
                    <div class="quantity-control">
                        <button onclick="updateQuantity(${i}, -1)" class="quantity-btn">−</button>
                        <span class="quantity-value">${item.quantity}</span>
                        <button onclick="updateQuantity(${i}, 1)" class="quantity-btn">+</button>
                    </div>
                    <p class="item-total">${itemTotal.toLocaleString()} DZD</p>
                    <button onclick="removeFromCart(${i})" class="remove-btn">Remove</button>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
    
    if (summaryContainer) {
        let tax = Math.round(subtotal * 0.05);
        let total = subtotal + tax;
        
        summaryContainer.innerHTML = `
            <h3 style="margin-top: 0; color: #d96565; margin-bottom: 20px;">Order Summary</h3>
            <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <span class="summary-label">Subtotal (${totalItems} items)</span>
                <span class="summary-value">${subtotal.toLocaleString()} DZD</span>
            </div>
            <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <span class="summary-label">Tax (5%)</span>
                <span class="summary-value">${tax.toLocaleString()} DZD</span>
            </div>
            <hr style="border-color: #d96565; margin: 15px 0;">
            <div class="summary-row total" style="display: flex; justify-content: space-between; padding: 10px 0; font-size: 1.3rem; font-weight: 700; color: #4CAF50;">
                <span class="summary-label">Total</span>
                <span class="summary-value">${total.toLocaleString()} DZD</span>
            </div>
            <button onclick="proceedToCheckout()" class="checkout-btn" style="width: 100%; padding: 18px; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; border: none; border-radius: 50px; font-size: 1.2rem; font-weight: 600; cursor: pointer; margin-top: 25px;">Proceed to Checkout</button>
        `;
    }
}

function proceedToCheckout() {
    window.location.href = 'checkout.php';
}

// ==================== CHECKOUT FUNCTIONS ====================
function loadCheckoutPage() {
    let container = document.getElementById('checkout-container');
    if (!container) return;
    
    // Check if user is logged in (PHP will handle redirect)
    // This is just for UI
    
    if (cart.length === 0) {
        window.location.href = 'products.php';
        return;
    }
    
    let subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    let tax = Math.round(subtotal * 0.05);
    let total = subtotal + tax;
    
    // Get current user info from PHP (passed via data attributes)
    const userName = document.body.getAttribute('data-user-name') || '';
    const userPhone = document.body.getAttribute('data-user-phone') || '';
    const userEmail = document.body.getAttribute('data-user-email') || '';
    
    container.innerHTML = `
        <h1 class="text-center" style="margin-bottom: 40px;">Checkout</h1>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <div>
                <div class="step" style="background: rgba(255,255,255,0.1); border-radius: 20px; padding: 30px; margin-bottom: 30px;">
                    <h2 style="display: flex; align-items: center; gap: 10px; margin-top: 0; color: #d96565;">
                        <span style="background: #d96565; color: white; width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">1</span>
                        Shipping Information
                    </h2>
                    
                    <form id="checkout-form" method="POST" action="place-order.php">
                        <input type="hidden" name="cart_data" id="cart-data" value='${JSON.stringify(cart)}'>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; color: #d96565;">Full Name *</label>
                            <input type="text" name="shipping_name" class="form-input" value="${userName}" placeholder="Enter your full name" style="width: 100%; padding: 12px; border-radius: 10px; border: none;" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; color: #d96565;">Address *</label>
                            <input type="text" name="shipping_address" class="form-input" placeholder="Street, City, Wilaya" style="width: 100%; padding: 12px; border-radius: 10px; border: none;" required>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 5px; color: #d96565;">Phone Number *</label>
                                <input type="tel" name="shipping_phone" class="form-input" value="${userPhone}" placeholder="+213 XXX XXX XXX" style="width: 100%; padding: 12px; border-radius: 10px; border: none;" required>
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 5px; color: #d96565;">Email</label>
                                <input type="email" name="shipping_email" class="form-input" value="${userEmail}" readonly style="width: 100%; padding: 12px; border-radius: 10px; border: none; background: rgba(255,255,255,0.2);">
                            </div>
                        </div>
                        
                        <div class="step" style="background: rgba(255,255,255,0.1); border-radius: 20px; padding: 30px; margin: 30px 0;">
                            <h2 style="display: flex; align-items: center; gap: 10px; margin-top: 0; color: #d96565;">
                                <span style="background: #d96565; color: white; width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">2</span>
                                Payment Method
                            </h2>
                            
                            <div class="payment-methods">
                                <div class="payment-method selected" onclick="selectPayment('ccp')" id="payment-ccp" style="border: 2px solid #4CAF50; background: rgba(76,175,80,0.1); padding: 20px; border-radius: 15px; margin-bottom: 15px; cursor: pointer; display: flex; align-items: center; gap: 15px;">
                                    <span style="font-size: 24px;">💳</span>
                                    <div>
                                        <h3 style="margin: 0 0 5px 0;">CCP / Bank Transfer</h3>
                                        <p style="margin: 0; opacity: 0.7;">Pay directly from your CCP account</p>
                                    </div>
                                </div>
                                
                                <div class="payment-method" onclick="selectPayment('cash')" id="payment-cash" style="border: 2px solid transparent; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 15px; margin-bottom: 15px; cursor: pointer; display: flex; align-items: center; gap: 15px;">
                                    <span style="font-size: 24px;">💵</span>
                                    <div>
                                        <h3 style="margin: 0 0 5px 0;">Cash on Delivery</h3>
                                        <p style="margin: 0; opacity: 0.7;">Pay when you receive your order</p>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="payment_method" id="payment-method" value="ccp">
                        </div>
                        
                        <button type="submit" class="btn btn-success" style="width: 100%; padding: 18px; font-size: 18px; font-weight: bold; border-radius: 50px; cursor: pointer; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; border: none;">✅ Place Order</button>
                    </form>
                </div>
            </div>
            
            <div>
                <div class="summary-card" style="background: rgba(255,255,255,0.1); border-radius: 20px; padding: 25px; position: sticky; top: 100px;">
                    <h3 style="margin-top: 0; color: #d96565; margin-bottom: 20px;">Order Summary</h3>
                    
                    ${cart.map(item => `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <span>${item.name} x${item.quantity}</span>
                            <span>${(item.price * item.quantity).toLocaleString()} DZD</span>
                        </div>
                    `).join('')}
                    
                    <div style="display: flex; justify-content: space-between; margin-top: 20px; font-size: 20px; font-weight: bold; color: #4CAF50;">
                        <span>Total</span>
                        <span>${total.toLocaleString()} DZD</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

let selectedPayment = 'ccp';
function selectPayment(method) {
    selectedPayment = method;
    let paymentInput = document.getElementById('payment-method');
    if (paymentInput) paymentInput.value = method;
    
    ['ccp', 'cash'].forEach(m => {
        let el = document.getElementById('payment-' + m);
        if (el) {
            if (m === method) {
                el.style.border = '2px solid #4CAF50';
                el.style.background = 'rgba(76,175,80,0.1)';
            } else {
                el.style.border = '2px solid transparent';
                el.style.background = 'rgba(255,255,255,0.05)';
            }
        }
    });
}

// ==================== PRODUCT FUNCTIONS ====================
function viewProductDetails(productId) {
    window.location.href = 'products_details.php?id=' + productId;
}

// ==================== SELLER DASHBOARD FUNCTIONS ====================
function loadSellerDashboard() {
    console.log('Seller dashboard loaded - data handled by PHP');
    // UI enhancements can be added here
}

function viewSales() {
    // This will be handled by PHP
    console.log('View sales');
}

// ==================== MY PRODUCTS PAGE FUNCTIONS ====================
function loadMyProductsPage() {
    console.log('My products page loaded - data handled by PHP');
}

function filterMyProducts(filter) {
    let tabs = document.querySelectorAll('.filter-tab');
    tabs.forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    
    let rows = document.querySelectorAll('.product-table tbody tr');
    rows.forEach(row => {
        let stock = parseInt(row.dataset.stock);
        switch(filter) {
            case 'all': row.style.display = ''; break;
            case 'in-stock': row.style.display = stock > 0 ? '' : 'none'; break;
            case 'low-stock': row.style.display = (stock > 0 && stock <= 5) ? '' : 'none'; break;
            case 'out-of-stock': row.style.display = stock === 0 ? '' : 'none'; break;
        }
    });
}

function searchMyProducts() {
    let search = document.getElementById('searchProduct').value.toLowerCase();
    let rows = document.querySelectorAll('.product-table tbody tr');
    rows.forEach(row => {
        let productName = row.cells[1].textContent.toLowerCase();
        row.style.display = productName.includes(search) ? '' : 'none';
    });
}

function editProduct(productId) {
    if (confirm('Edit product?')) {
        window.location.href = `seller-add-product.php?edit=${productId}`;
    }
}

function duplicateProduct(productId) {
    if (confirm('Duplicate this product?')) {
        window.location.href = `seller-add-product.php?duplicate=${productId}`;
    }
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        window.location.href = `delete-product.php?id=${productId}`;
    }
}

// ==================== ADD PRODUCT FORM ====================
let currentStep = 1;

function initAddProductForm() {
    console.log('Initializing add product form...');
    
    // Check if we're on the add product page
    if (!document.getElementById('add-product-form')) return;
    
    // Initialize step display
    showStep(1);
}

function showStep(step) {
    // Hide all steps
    for (let i = 1; i <= 4; i++) {
        let stepContent = document.getElementById(`step${i}-content`);
        let stepIndicator = document.getElementById(`step${i}`);
        if (stepContent) stepContent.style.display = 'none';
        if (stepIndicator) stepIndicator.classList.remove('active');
    }
    
    // Show current step
    let currentStepContent = document.getElementById(`step${step}-content`);
    let currentStepIndicator = document.getElementById(`step${step}`);
    if (currentStepContent) currentStepContent.style.display = 'block';
    if (currentStepIndicator) currentStepIndicator.classList.add('active');
    
    // Update progress bar
    let progressFill = document.getElementById('progress-fill');
    if (progressFill) progressFill.style.width = (step * 25) + '%';
    
    // Update review if on step 4
    if (step === 4) updateReview();
    
    currentStep = step;
}

function nextStep(step) {
    // Validate current step before proceeding
    if (currentStep === 1 && step > 1) {
        let name = document.getElementById('productName')?.value;
        let category = document.getElementById('productCategory')?.value;
        
        if (!name || name.trim() === '') {
            showNotification('❌ Product name is required', 'error');
            return;
        }
        if (!category || category === '') {
            showNotification('❌ Please select a category', 'error');
            return;
        }
    }
    
    if (currentStep === 2 && step > 2) {
        let price = document.getElementById('productPrice')?.value;
        let phone = document.getElementById('productPhone')?.value;
        
        if (!price || price <= 0) {
            showNotification('❌ Please enter a valid price', 'error');
            return;
        }
        if (!phone || phone.trim() === '') {
            showNotification('❌ Phone number is required', 'error');
            return;
        }
    }
    
    showStep(step);
}

function prevStep(step) {
    showStep(step);
}

function skipImage() {
    showNotification('✅ No image will be used - placeholder will appear', 'success');
    nextStep(4);
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        // Check file size (max 5MB)
        if (input.files[0].size > 5 * 1024 * 1024) {
            showNotification('❌ Image too large. Max 5MB', 'error');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.getElementById('imagePreview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            showNotification('✅ Image loaded', 'success');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateReview() {
    let name = document.getElementById('productName')?.value || 'Not provided';
    let category = document.getElementById('productCategory')?.value || 'Not selected';
    let condition = document.getElementById('productCondition')?.value || 'new';
    let price = document.getElementById('productPrice')?.value || '0';
    let quantity = document.getElementById('productQuantity')?.value || '1';
    let phone = document.getElementById('productPhone')?.value || 'Not provided';
    let description = document.getElementById('productDescription')?.value || 'No description provided';
    let hasImage = document.getElementById('productImage')?.files.length > 0;
    
    // Category icons
    const categoryIcons = {
        'electronics': '📱', 'fashion': '👕', 'clothing': '👖', 'home': '🏠',
        'garden': '🌱', 'crafts': '🎨', 'handmade': '✂️', 'sports': '⚽',
        'books': '📚', 'food': '🍕', 'bakery': '🥐', 'desserts': '🍰',
        'beauty': '💄', 'health': '💊', 'toys': '🧸', 'games': '🎮',
        'automotive': '🚗', 'pets': '🐶', 'music': '🎵', 'art': '🖼️',
        'office': '📎', 'baby': '👶', 'jewelry': '💍', 'shoes': '👟',
        'bags': '👜', 'watches': '⌚', 'other': '🔄'
    };
    
    let categoryDisplay = category !== 'Not selected' ? 
        `${categoryIcons[category] || '📦'} ${category.charAt(0).toUpperCase() + category.slice(1)}` : 
        'Not selected';
    
    let imageStatus = hasImage ? '✅ Image uploaded' : '⏭️ No image (will use placeholder)';
    
    let reviewHTML = `
        <p><strong>📝 Name:</strong> ${name}</p>
        <p><strong>📂 Category:</strong> ${categoryDisplay}</p>
        <p><strong>🏷️ Condition:</strong> ${condition}</p>
        <p><strong>💰 Price:</strong> ${parseInt(price).toLocaleString()} DZD</p>
        <p><strong>📦 Quantity:</strong> ${quantity}</p>
        <p><strong>📞 Contact:</strong> ${phone}</p>
        <p><strong>📋 Description:</strong> ${description}</p>
        <p><strong>🖼️ Image:</strong> ${imageStatus}</p>
    `;
    
    let reviewContent = document.getElementById('reviewContent');
    if (reviewContent) reviewContent.innerHTML = reviewHTML;
}

// ==================== ORDERS FUNCTIONS ====================
function loadSellerOrders() {
    console.log('Orders page loaded - data handled by PHP');
}

function filterOrders(status) {
    let btns = document.querySelectorAll('.filter-btn');
    btns.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    let orders = document.querySelectorAll('.order-card');
    orders.forEach(order => {
        if (status === 'all') {
            order.style.display = 'block';
        } else {
            order.style.display = order.dataset.status === status ? 'block' : 'none';
        }
    });
}

function updateOrderStatus(orderId, newStatus) {
    if (confirm(`Update order status to ${newStatus}?`)) {
        // This will be handled by PHP form submission
        document.getElementById(`status-form-${orderId}`)?.submit();
    }
}

// ==================== PAYMENT FUNCTIONS ====================
function showCCPPayment() {
    let commissionAmount = document.getElementById('commissionAmount')?.textContent || '0';
    let sellerRef = document.getElementById('sellerRef')?.textContent || 'M7-XXX';
    
    let paymentHTML = `
        <div class="modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div style="background: #1a1a1a; padding: 40px; border-radius: 30px; max-width: 500px; text-align: center;">
                <h2 style="color: #d96565;">💳 CCP Mobile Payment</h2>
                
                <div style="margin: 30px auto; width: 250px; height: 250px; background: white; border-radius: 20px; padding: 20px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=CCP:${commissionAmount} - Ref:${sellerRef}" alt="QR Code" style="width: 100%;">
                </div>
                
                <p style="font-size: 18px; margin: 20px 0;">
                    <strong>Amount:</strong> ${commissionAmount}
                </p>
                
                <div style="background: rgba(76, 175, 80, 0.1); padding: 20px; border-radius: 15px; margin: 20px 0;">
                    <h3 style="color: #4CAF50;">📋 BaridiMob Instructions</h3>
                    <ol style="text-align: left; line-height: 2;">
                        <li>Open <strong>BaridiMob</strong> app</li>
                        <li>Click on <strong>"Paiement"</strong> tab</li>
                        <li>Select <strong>"Ajouter un bénéficiaire"</strong></li>
                        <li>Enter our CCP: <strong>88 0042745945</strong></li>
                        <li>Amount: <strong>${commissionAmount}</strong></li>
                        <li>Reference: <strong>${sellerRef}</strong></li>
                        <li>Enter your OTP code</li>
                        <li>Save the receipt</li>
                    </ol>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button onclick="copyPaymentDetails()" class="btn" style="flex: 1;">📋 Copy Details</button>
                    <button onclick="closePaymentModal()" class="btn" style="flex: 1; background: #666;">Close</button>
                </div>
            </div>
        </div>
    `;
    
    let modal = document.createElement('div');
    modal.id = 'payment-modal';
    modal.innerHTML = paymentHTML;
    document.body.appendChild(modal);
}

function copyPaymentDetails() {
    let commissionAmount = document.getElementById('commissionAmount')?.textContent || '0 DZD';
    let sellerRef = document.getElementById('sellerRef')?.textContent || 'M7-XXX';
    
    let text = `M7 MARKETPLACE PAYMENT\n`;
    text += `━━━━━━━━━━━━━━━━━━━\n`;
    text += `CCP: 0042745945\n`;
    text += `Key: 88\n`;
    text += `Amount: ${commissionAmount}\n`;
    text += `Reference: ${sellerRef}\n`;
    text += `Date: ${new Date().toLocaleDateString()}\n\n`;
    text += `After payment, send screenshot to: m7.contact.us@gmail.com`;
    
    navigator.clipboard.writeText(text);
    showNotification('✅ Payment details copied!', 'success');
}

function closePaymentModal() {
    let modal = document.getElementById('payment-modal');
    if (modal) modal.remove();
}

function showPaymentInstructions() {
    let commissionAmount = document.getElementById('commissionAmount')?.textContent || '0 DZD';
    let sellerRef = document.getElementById('sellerRef')?.textContent || 'M7-XXX';
    
    alert(`
📝 PAYMENT INSTRUCTIONS
━━━━━━━━━━━━━━━━━━━━━━━

💰 Amount Due: ${commissionAmount}

🏦 Transfer to:
━━━━━━━━━━━━━━━
Bank: CCP / CPA
Account: 88 0042745945
Name: M7 Marketplace

📋 Reference: ${sellerRef}
(Use this so we know it's you!)

📧 After payment:
Send screenshot to: m7.contact.us@gmail.com

✅ We'll mark it as paid within 24 hours
    `);
}

function calculateAndCopy() {
    let commissionAmount = document.getElementById('commissionAmount')?.textContent || '0 DZD';
    let sellerRef = document.getElementById('sellerRef')?.textContent || 'M7-XXX';
    
    let details = `M7 MARKETPLACE PAYMENT
━━━━━━━━━━━━━━━━━━━
CCP: 0042745945
Key: 88
Amount: ${commissionAmount}
Reference: ${sellerRef}
Date: ${new Date().toLocaleDateString()}

After payment, send screenshot to: m7.contact.us@gmail.com`;

    navigator.clipboard.writeText(details);
    showNotification('✅ Payment details copied!', 'success');
}

// ==================== ACCOUNT FUNCTIONS ====================
function showEditProfileForm() {
    alert('Edit profile feature coming soon!');
}

function showChangePasswordForm() {
    alert('Change password feature coming soon!');
}

function changeProfilePic() {
    alert('Profile picture upload coming soon!');
}

function confirmDeleteAccount() {
    if (confirm('⚠️ ARE YOU SURE?\n\nDeleting your account is permanent and cannot be undone!\n\nDo you want to continue?')) {
        let password = prompt('Please enter your password to confirm deletion:');
        if (password) {
            // Submit form to delete account
            document.getElementById('delete-account-form')?.submit();
        }
    }
}

// ==================== CONTACT FUNCTIONS ====================
function sendByEmail() {
    let name = document.getElementById('name')?.value.trim();
    let email = document.getElementById('email')?.value.trim();
    let phone = document.getElementById('phone')?.value.trim();
    let subject = document.getElementById('subject')?.value;
    let message = document.getElementById('message')?.value.trim();
    
    // Check if email field is readonly (user is logged in)
    let emailField = document.getElementById('email');
    let isEmailReadonly = emailField && emailField.hasAttribute('readonly');
    
    // Validate fields
    if (!name || !subject || !message) {
        showNotification('❌ Please fill all required fields', 'error');
        return;
    }
    
    // Only check email if field is NOT readonly
    if (!isEmailReadonly && !email) {
        showNotification('❌ Please enter your email address', 'error');
        return;
    }
    
    // If email is readonly but empty (shouldn't happen), use a default
    if (isEmailReadonly && !email) {
        email = 'user@example.com'; // fallback
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
            <div class="success-message" style="background: rgba(76,175,80,0.2); border: 2px solid #4CAF50; padding: 40px; border-radius: 30px; margin: 20px 0;">
                <h3 style="color: #4CAF50;">✅ Your email client has been opened!</h3>
                <p>Just click send in your email app.</p>
            </div>
        `;
    }
}

// ==================== NEWSLETTER ====================
function subscribeNewsletter() {
    let email = document.getElementById('newsletter-email')?.value;
    if (email) {
        showNotification('✅ Thanks for subscribing!', 'success');
        document.getElementById('newsletter-email').value = '';
        
        // You can add AJAX call here to save to database
    } else {
        showNotification('❌ Please enter your email address', 'error');
    }
}

// ==================== LOGOUT ====================
function logout() {
    window.location.href = 'logout.php';
}