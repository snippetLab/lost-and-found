<?php
/**
 * Registration Handler
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

validateCSRF();

$fullName    = trim($_POST['fullName'] ?? '');
$email       = trim($_POST['email'] ?? '');
$mobile      = trim($_POST['mobile'] ?? '');
$password    = $_POST['password'] ?? '';
$confirmPass = $_POST['confirmPassword'] ?? '';

$errors = [];

// Validate full name
if (empty($fullName)) {
    $errors[] = 'Full name is required.';
} elseif (strlen($fullName) < 2 || strlen($fullName) > 100) {
    $errors[] = 'Full name must be between 2 and 100 characters.';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Email address is required.';
} elseif (!isValidEmail($email)) {
    $errors[] = 'Please enter a valid email address.';
}

// Validate mobile (optional but must be valid if provided)
if (!empty($mobile) && !isValidMobile($mobile)) {
    $errors[] = 'Please enter a valid mobile number.';
}

// Validate password
if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long.';
}

// Validate confirm password
if ($password !== $confirmPass) {
    $errors[] = 'Passwords do not match.';
}

// Check for duplicate email
if (empty($errors)) {
    $db = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'An account with this email already exists.';
    }
}

// If errors, redirect back with error message
if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ' . appUrl('sign-in.php?mode=register'));
    exit;
}

// Create the user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO users (full_name, email, mobile, password, role, points_balance) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute([$fullName, $email, $mobile, $hashedPassword, 'student', 0]);

// Get the new user and log them in
$newUserId = $db->lastInsertId();
$stmt = $db->prepare('SELECT id, full_name, email, role FROM users WHERE id = ?');
$stmt->execute([$newUserId]);
$newUser = $stmt->fetch();

loginUser($newUser);

// Award 10 points for joining
$db->prepare('UPDATE users SET points_balance = points_balance + 10 WHERE id = ?')->execute([$newUserId]);
$db->prepare('INSERT INTO points_transactions (user_id, points, transaction_type, description) VALUES (?, ?, ?, ?)')
   ->execute([$newUserId, 10, 'bonus', 'Welcome bonus for joining the Campus Lost & Found Portal']);

setFlash('success', 'Account created successfully! Welcome to Campus Lost & Found. You earned 10 bonus points!');
header('Location: ' . appUrl('index.php'));
exit;
