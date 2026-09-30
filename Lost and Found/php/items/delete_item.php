<?php
/**
 * Delete Item Handler
 * Only the item owner or an admin can delete.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('my-items.php'));
    exit;
}

requireLogin();
validateCSRF();

$db = getDB();
$itemId = (int)($_POST['item_id'] ?? 0);
$userId = getCurrentUserId();
$isAdmin = isAdmin();

// Load item
$stmt = $db->prepare('SELECT * FROM items WHERE id = ?');
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('error', 'Item not found.');
    header('Location: ' . appUrl('my-items.php'));
    exit;
}

// Ownership check
if ($item['user_id'] != $userId && !$isAdmin) {
    setFlash('error', 'You do not have permission to delete this item.');
    header('Location: ' . appUrl('my-items.php'));
    exit;
}

// Prevent deleting items with approved claims to preserve history
$stmt = $db->prepare('SELECT COUNT(*) FROM claims WHERE item_id = ? AND status = ?');
$stmt->execute([$itemId, 'approved']);
if ($stmt->fetchColumn() > 0) {
    setFlash('error', 'This item has been returned and cannot be deleted. Please contact an administrator.');
    header('Location: ' . appUrl('my-items.php'));
    exit;
}

$stmt = $db->prepare('DELETE FROM items WHERE id = ?');
$stmt->execute([$itemId]);

setFlash('success', 'Item report deleted successfully.');
header('Location: ' . appUrl('my-items.php'));
exit;