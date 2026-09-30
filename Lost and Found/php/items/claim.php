<?php
/**
 * Claim Submission Handler
 * A logged-in user claims a found item.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('browse.php'));
    exit;
}

requireLogin();
validateCSRF();

$db = getDB();
$itemId       = (int)($_POST['item_id'] ?? 0);
$description  = trim($_POST['claim_description'] ?? '');
$evidenceRefs = trim($_POST['evidence_details'] ?? '');

// Validate claim description
if (empty($description)) {
    setFlash('error', 'Please describe why this item belongs to you.');
    header('Location: ' . appUrl('item-detail.php?id=' . $itemId));
    exit;
}
if (strlen($description) < 10) {
    setFlash('error', 'Claim description must be at least 10 characters. Provide enough evidence so the owner can verify.');
    header('Location: ' . appUrl('item-detail.php?id=' . $itemId));
    exit;
}

$userId = getCurrentUserId();

// Load the item
$stmt = $db->prepare('SELECT * FROM items WHERE id = ? AND status = ?');
$stmt->execute([$itemId, 'active']);
$item = $stmt->fetch();

if (!$item) {
    setFlash('error', 'Item not found or no longer available for claims.');
    header('Location: ' . appUrl('browse.php'));
    exit;
}

// Items of type 'found' can be claimed; lost items cannot
if ($item['item_type'] !== 'found') {
    setFlash('error', 'This is a lost report, not a found item. You can only claim found items.');
    header('Location: ' . appUrl('item-detail.php?id=' . $itemId));
    exit;
}

// Prevent the finder from claiming their own found item
if ($item['user_id'] == $userId) {
    setFlash('error', 'You cannot claim an item that you reported as found.');
    header('Location: ' . appUrl('item-detail.php?id=' . $itemId));
    exit;
}

// Prevent duplicate active claims for the same user and item
$stmt = $db->prepare('SELECT id FROM claims WHERE item_id = ? AND claimant_id = ? AND status IN (?)');
$stmt->execute([$itemId, $userId, 'pending']);
if ($stmt->fetch()) {
    setFlash('error', 'You already have a pending claim for this item.');
    header('Location: ' . appUrl('item-detail.php?id=' . $itemId));
    exit;
}

// Insert the claim
$stmt = $db->prepare('INSERT INTO claims (item_id, claimant_id, description, evidence_details) VALUES (?, ?, ?, ?)');
$stmt->execute([$itemId, $userId, $description, $evidenceRefs ?: null]);

setFlash('success', 'Your claim has been submitted. The verification team will review your evidence soon.');
header('Location: ' . appUrl('my-items.php'));
exit;