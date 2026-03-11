<?php
// save-user.php
$data = json_decode(file_get_contents('php://input'), true);

// Save to text file
$filename = 'users-' . date('Y-m-d') . '.txt';
$logEntry = date('Y-m-d H:i:s') . " - " . json_encode($data) . "\n";
file_put_contents($filename, $logEntry, FILE_APPEND);

// Send email to you
$to = "YOUR_EMAIL@example.com";
$subject = "New User: " . $data['fullName'];
$message = "New user registered:\n\n" . print_r($data, true);
$headers = "From: website@m7shopping.com";

mail($to, $subject, $message, $headers);

echo json_encode(['success' => true]);
?>