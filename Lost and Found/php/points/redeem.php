<?php
/**
 * Points Redemption Handler
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('points.php'));
    exit;
}

requireLogin();
validateCSRF();

$db = getDB();
$rewardId = (int)($_POST['reward_id'] ?? 0);
$userId = getCurrentUserId();

// Load reward
$stmt = $db->prepare('SELECT * FROM rewards WHERE id = ? AND status = ?');
$stmt->execute([$rewardId, 'active']);
$reward = $stmt->fetch();

if (!$reward) {
    setFlash('error', 'Reward not found or inactive.');
    header('Location: ' . appUrl('points.php'));
    exit;
}

if ($reward['stock'] <= 0) {
    setFlash('error', 'This reward is currently out of stock.');
    header('Location: ' . appUrl('points.php'));
    exit;
}

// Load user's current points
$stmt = $db->prepare('SELECT points_balance FROM users WHERE id = ?');
$stmt->execute([$userId]);
$balance = (int)$stmt->fetchColumn();

if ($balance < (int)$reward['points_cost']) {
    setFlash('error', 'You do not have enough points to redeem this reward.');
    header('Location: ' . appUrl('points.php'));
    exit;
}

$db->beginTransaction();

try {
    // Deduct points
    $db->prepare('UPDATE users SET points_balance = points_balance - ? WHERE id = ?')
       ->execute([$reward['points_cost'], $userId]);

    // Record points transaction
    $db->prepare('INSERT INTO points_transactions (user_id, points, transaction_type, description, reference_id, reference_type) VALUES (?, ?, ?, ?, ?, ?)')
       ->execute([$userId, -$reward['points_cost'], 'spent', 'Redeemed: ' . $reward['name'], $rewardId, 'reward']);

    // Create redemption record
    $db->prepare('INSERT INTO redemptions (user_id, reward_id, points_spent) VALUES (?, ?, ?)')
       ->execute([$userId, $rewardId, $reward['points_cost']]);

    // Decrement stock
    $db->prepare('UPDATE rewards SET stock = stock - 1 WHERE id = ?')
       ->execute([$rewardId]);

    $db->commit();
    header('Location: ' . appUrl('points.php?redeemed=1&reward=' . urlencode($reward['name'])));
    exit;
} catch (Exception $e) {
    $db->rollBack();
    error_log('Redemption failed: ' . $e->getMessage());
    setFlash('error', 'An error occurred while redeeming the reward. Please try again.');
}

header('Location: ' . appUrl('points.php'));
exit;