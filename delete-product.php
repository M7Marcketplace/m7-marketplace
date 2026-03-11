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

// Get product ID
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId > 0) {
    // Verify product belongs to this seller
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$productId, $currentUser['id']]);
    
    if ($stmt->fetch()) {
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$productId, $currentUser['id']]);
        
        $_SESSION['success'] = 'Product deleted successfully';
    } else {
        $_SESSION['error'] = 'Product not found or you do not have permission to delete it';
    }
}

// Redirect back to my products page
header('Location: my-products.php');
exit;
?>