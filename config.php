<?php
$host = 'localhost';
$dbname = 'm7_marketplace';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isSeller() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'seller';
}

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}
?>