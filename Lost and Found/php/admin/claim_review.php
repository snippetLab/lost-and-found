<?php
/**
 * Admin - Review Claim (Approve / Reject)
 * Approving a claim:
 *   1. Updates claim status to approved
 *   2. Updates item status to returned
 *   3. Creates a points transaction for the finder
 *   4. Updates the finder's points balance
 * All in a single DB transaction.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('admin/index.php'));
    exit;
}

requireAdmin();
validateCSRF();

$db = getDB();
$claimId = (int)($_POST['claim_id'] ?? 0);
$action  = $_POST['action'] ?? '';
$points  = 50; // reward points for successful return

if (!in_array($action, ['approve', 'reject'], true)) {
    setFlash('error', 'Invalid action.');
    header('Location: ' . appUrl('admin/index.php'));
    exit;
}

// Load claim with related item and finder
$stmt = $db->prepare(
    'SELECT c.id AS claim_id, c.item_id, c.claimant_id, c.status AS claim_status,
            i.user_id AS finder_id, i.title AS item_title,
            c.description AS claim_description, c.evidence_details AS claim_evidence, c.created_at AS claim_date
     FROM claims c
     LEFT JOIN items i ON i.id = c.item_id
     WHERE c.id = ?'
);
$stmt->execute([$claimId]);
$claim = $stmt->fetch();

if (!$claim) {
    setFlash('error', 'Claim not found.');
    header('Location: ' . appUrl('admin/index.php'));
    exit;
}

if ($claim['claim_status'] !== 'pending') {
    setFlash('error', 'This claim has already been reviewed.');
    header('Location: ' . appUrl('admin/index.php'));
    exit;
}

$db->beginTransaction();

try {
    if ($action === 'approve') {
        $finderId = (int)$claim['finder_id'];
        $itemTitle = $claim['item_title'];

        // 1. Update claim status
        $db->prepare('UPDATE claims SET status = ? WHERE id = ?')->execute(['approved', $claimId]);

        // 2. Update item status to returned
        $db->prepare('UPDATE items SET status = ? WHERE id = ?')->execute(['returned', $claim['item_id']]);

        // 3. Check the finder is not the admin and award points
        $stmt = $db->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$finderId]);
        $finderRole = $stmt->fetchColumn();

        if ($finderId > 0 && $finderRole !== 'admin') {
            // 4. Prevent duplicate rewards: check for existing earned transaction referencing this item
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM points_transactions WHERE reference_type = ? AND reference_id = ? AND transaction_type = ?'
            );
            $stmt->execute(['item_return', $claim['item_id'], 'earned']);
            if ($stmt->fetchColumn() == 0) {
                $db->prepare('INSERT INTO points_transactions (user_id, points, transaction_type, description, reference_id, reference_type) VALUES (?, ?, ?, ?, ?, ?)')
                   ->execute([$finderId, $points, 'earned', 'Reward for returning item: ' . $itemTitle, $claim['item_id'], 'item_return']);

                $db->prepare('UPDATE users SET points_balance = points_balance + ? WHERE id = ?')
                   ->execute([$points, $finderId]);
            }
        }

        $message = 'Claim approved and item marked as returned. Finder rewarded with ' . $points . ' points.';
    } else {
        // Reject
        $db->prepare('UPDATE claims SET status = ? WHERE id = ?')->execute(['rejected', $claimId]);
        $message = 'Claim rejected.';
    }

    $db->commit();
    setFlash('success', $message);
} catch (Exception $e) {
    $db->rollBack();
    error_log('Claim review failed: ' . $e->getMessage());
    setFlash('error', 'An error occurred while reviewing the claim. Please try again.');
}

header('Location: ' . appUrl('admin/index.php'));
exit;