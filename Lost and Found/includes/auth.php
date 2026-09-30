<?php
/**
 * Authentication & Session Management
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Require login - redirect to sign-in if not authenticated
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . appUrl('sign-in.php'));
        exit;
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user info from DB
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare('SELECT id, full_name, email, mobile, profile_image, role, points_balance, status, created_at FROM users WHERE id = ? AND status = ?');
    $stmt->execute([$_SESSION['user_id'], 'active']);
    return $stmt->fetch() ?: null;
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require admin role
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . appUrl('index.php'));
        exit;
    }
}

/**
 * Login user - set session variables
 */
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email']= $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

/**
 * Logout user - destroy session
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
