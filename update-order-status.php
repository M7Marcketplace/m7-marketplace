<?php
require_once 'config.php';

// Check if user is logged in and is a seller
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
if ($currentUser['role'] !== 'seller') {
    header('Location: auth.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $newStatus = isset($_POST['status']) ? $_POST['status'] : '';
    
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if ($orderId > 0 && in_array($newStatus, $validStatuses)) {
        // Verify this order contains products from this seller
        $stmt = $pdo->prepare("
            SELECT id FROM order_items 
            WHERE order_id = ? AND seller_id = ?
        ");
        $stmt->execute([$orderId, $currentUser['id']]);
        
        if ($stmt->fetch()) {
            // Update order status
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            
            $_SESSION['success'] = 'Order status updated successfully';
        } else {
            $_SESSION['error'] = 'You do not have permission to update this order';
        }
    }
}

// Redirect back to orders page
header('Location: orders.php');
exit;
?>