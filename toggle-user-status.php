<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_POST['user_id'] ?? 0;
$action = $_POST['action'] ?? '';

if (!$userId || !in_array($action, ['suspend', 'unsuspend'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // First, check if users table has is_suspended column, if not add it
    $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_suspended'");
    if ($checkColumn->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_suspended BOOLEAN DEFAULT FALSE");
    }
    
    $newStatus = ($action === 'suspend') ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE users SET is_suspended = ? WHERE id = ?");
    $stmt->execute([$newStatus, $userId]);
    
    $message = $action === 'suspend' ? 'User blocked successfully' : 'User activated successfully';
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>