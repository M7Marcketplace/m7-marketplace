<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$adminUser = getCurrentUser();
$userId = $_POST['user_id'] ?? 0;
$password = $_POST['password'] ?? '';

// Verify admin password
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$adminUser['id']]);
$admin = $stmt->fetch();

if (!password_verify($password, $admin['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    exit;
}

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Get user info before deleting
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Save to deleted accounts log
    $pdo->prepare("
        INSERT INTO deleted_accounts (user_id, full_name, email, role, deleted_by, deleted_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ")->execute([$userId, $user['full_name'], $user['email'], $user['role'], $adminUser['id']]);
    
    // Delete user's products (if seller)
    if ($user['role'] === 'seller') {
        $pdo->prepare("DELETE FROM products WHERE seller_id = ?")->execute([$userId]);
        $pdo->prepare("DELETE FROM seller_stores WHERE seller_id = ?")->execute([$userId]);
    }
    
    // Delete user's cart items
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);
    
    // Delete user's messages
    $pdo->prepare("DELETE FROM contact_messages WHERE email = ?")->execute([$user['email']]);
    
    // Delete user's orders (as buyer)
    $pdo->prepare("UPDATE orders SET buyer_id = NULL WHERE buyer_id = ?")->execute([$userId]);
    
    // Finally delete the user
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>