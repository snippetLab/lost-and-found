<?php
/**
 * Login Handler
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

validateCSRF();

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    setFlash('error', 'Please enter both email and password.');
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT id, full_name, email, password, role, status FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    setFlash('error', 'Invalid email or password. Please try again.');
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

if ($user['status'] !== 'active') {
    setFlash('error', 'Your account has been suspended. Please contact support.');
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

loginUser($user);

setFlash('success', 'Welcome back, ' . sanitize($user['full_name']) . '!');
header('Location: ' . appUrl('index.php'));
exit;
