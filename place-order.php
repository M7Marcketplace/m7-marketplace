<?php
require_once 'config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php?redirect=checkout');
    exit;
}

$currentUser = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $cart = json_decode($_POST['cart_data'], true);
    
    if (empty($cart)) {
        header('Location: cart.php');
        exit;
    }
    
    // Get shipping info
    $shippingName = $_POST['shipping_name'] ?? $currentUser['full_name'];
    $shippingAddress = $_POST['shipping_address'] ?? '';
    $shippingPhone = $_POST['shipping_phone'] ?? $currentUser['phone'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? 'ccp';
    
    if (empty($shippingAddress) || empty($shippingPhone)) {
        $_SESSION['checkout_error'] = 'Please fill all shipping information';
        header('Location: checkout.php');
        exit;
    }
    
    // Calculate totals
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = round($subtotal * 0.05);
    $total = $subtotal + $tax;
    
    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, buyer_id, status, payment_method, subtotal, tax, total, 
                               shipping_name, shipping_address, shipping_phone, shipping_email, created_at)
            VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $orderNumber,
            $currentUser['id'],
            $paymentMethod,
            $subtotal,
            $tax,
            $total,
            $shippingName,
            $shippingAddress,
            $shippingPhone,
            $currentUser['email']
        ]);
        $orderId = $pdo->lastInsertId();
        
        // Insert order items and update product stock
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, seller_id, product_name, product_price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $updateStmt = $pdo->prepare("
            UPDATE products SET quantity = quantity - ?, sold = sold + ? 
            WHERE id = ? AND quantity >= ?
        ");
        
        foreach ($cart as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            
            // Insert order item
            $itemStmt->execute([
                $orderId,
                $item['id'],
                $item['sellerId'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $itemSubtotal
            ]);
            
            // Update product stock
            $updateStmt->execute([$item['quantity'], $item['quantity'], $item['id'], $item['quantity']]);
        }
        
        $pdo->commit();
        
        // Clear cart cookie
        setcookie('cart', '', time() - 3600, '/');
        
        // Redirect to success page
        header('Location: order-success.php?order=' . $orderNumber);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Order error: ' . $e->getMessage());
        $_SESSION['checkout_error'] = 'Failed to place order. Please try again.';
        header('Location: checkout.php');
        exit;
    }
} else {
    header('Location: cart.php');
    exit;
}
?>