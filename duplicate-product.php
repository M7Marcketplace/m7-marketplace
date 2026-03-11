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
    // Get original product
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$productId, $currentUser['id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Create duplicate
        $stmt = $pdo->prepare("
            INSERT INTO products (seller_id, category_id, name, price, quantity, `condition`, phone_contact, description, image_url, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $newName = $product['name'] . ' (Copy)';
        
        $stmt->execute([
            $currentUser['id'],
            $product['category_id'],
            $newName,
            $product['price'],
            $product['quantity'],
            $product['condition'],
            $product['phone_contact'],
            $product['description'],
            $product['image_url']
        ]);
        
        $_SESSION['success'] = 'Product duplicated successfully';
    } else {
        $_SESSION['error'] = 'Product not found or you do not have permission to duplicate it';
    }
}

// Redirect back to my products page
header('Location: my-products.php');
exit;
?>