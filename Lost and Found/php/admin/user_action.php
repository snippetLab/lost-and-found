<?php
/**
 * Admin - User Actions (Activate / Deactivate)
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('admin/users.php'));
    exit;
}

requireAdmin();
validateCSRF();

$db     = getDB();
$userId = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!in_array($action, ['activate', 'deactivate'], true)) {
    setFlash('error', 'Invalid action.');
    header('Location: ' . appUrl('admin/users.php'));
    exit;
}

$stmt = $db->prepare('SELECT id, full_name, role, status FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'User not found.');
    header('Location: ' . appUrl('admin/users.php'));
    exit;
}

$currentUserId = (int)getCurrentUserId();

if ($action === 'deactivate') {
    if ($userId === $currentUserId) {
        setFlash('error', 'You cannot deactivate your own account.');
        header('Location: ' . appUrl('admin/users.php'));
        exit;
    }
    if ($user['role'] === 'admin') {
        setFlash('error', 'You cannot deactivate an admin account.');
        header('Location: ' . appUrl('admin/users.php'));
        exit;
    }
    if ($user['status'] === 'suspended') {
        setFlash('error', 'This account is already deactivated.');
    } else {
        $db->prepare("UPDATE users SET status = 'suspended' WHERE id = ?")->execute([$userId]);
        setFlash('success', 'Account of "' . $user['full_name'] . '" deactivated. The user can no longer sign in.');
    }
} else {
    if ($user['status'] === 'active') {
        setFlash('error', 'This account is already active.');
    } else {
        $db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$userId]);
        setFlash('success', 'Account of "' . $user['full_name'] . '" reactivated.');
    }
}

header('Location: ' . appUrl('admin/users.php'));
exit;