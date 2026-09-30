<?php
/**
 * Admin - Item Actions (Mark Resolved / Reopen / Delete)
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('admin/items.php'));
    exit;
}

requireAdmin();
validateCSRF();

$db     = getDB();
$itemId = (int)($_POST['item_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!in_array($action, ['resolve', 'reopen', 'delete'], true)) {
    setFlash('error', 'Invalid action.');
    header('Location: ' . appUrl('admin/items.php'));
    exit;
}

$stmt = $db->prepare('SELECT id, title, status FROM items WHERE id = ?');
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('error', 'Item not found.');
    header('Location: ' . appUrl('admin/items.php'));
    exit;
}

$backUrl = appUrl('admin/items.php');

if ($action === 'resolve') {
    if ($item['status'] === 'returned' || $item['status'] === 'closed') {
        setFlash('error', 'This item is already resolved.');
    } else {
        $db->prepare('UPDATE items SET status = ? WHERE id = ?')->execute(['returned', $itemId]);
        setFlash('success', 'Item "' . $item['title'] . '" marked as resolved (returned).');
    }
} elseif ($action === 'reopen') {
    if ($item['status'] === 'active') {
        setFlash('error', 'This item is already active.');
    } else {
        $db->prepare('UPDATE items SET status = ? WHERE id = ?')->execute(['active', $itemId]);
        setFlash('success', 'Item "' . $item['title'] . '" reopened as active.');
    }
} elseif ($action === 'delete') {
    $stmt = $db->prepare('SELECT COUNT(*) FROM claims WHERE item_id = ? AND status = ?');
    $stmt->execute([$itemId, 'approved']);
    if ($stmt->fetchColumn() > 0) {
        setFlash('error', 'This item has been returned and cannot be deleted. To preserve history it must stay on record.');
    } else {
        $db->prepare('DELETE FROM items WHERE id = ?')->execute([$itemId]);
        setFlash('success', 'Item report deleted successfully.');
    }
}

header('Location: ' . $backUrl);
exit;