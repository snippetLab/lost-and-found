<?php
/**
 * Admin - Reward Actions (create / update / activate / deactivate / delete / fulfill / cancel)
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('admin/rewards.php'));
    exit;
}

requireAdmin();
validateCSRF();

$db     = getDB();
$action = $_POST['action'] ?? '';

$allowed = ['create', 'update', 'activate', 'deactivate', 'delete', 'fulfill', 'cancel'];
if (!in_array($action, $allowed, true)) {
    setFlash('error', 'Invalid action.');
    header('Location: ' . appUrl('admin/rewards.php'));
    exit;
}

try {
    if ($action === 'create' || $action === 'update') {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $pointsCost  = (int)($_POST['points_cost'] ?? 0);
        $stock       = (int)($_POST['stock'] ?? 0);
        $status      = in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

        if ($name === '') {
            setFlash('error', 'Reward name is required.');
            header('Location: ' . appUrl('admin/rewards.php'));
            exit;
        }
        if ($pointsCost < 0) {
            $pointsCost = 0;
        }
        if ($stock < 0) {
            $stock = 0;
        }

        if ($action === 'create') {
            $db->prepare('INSERT INTO rewards (name, description, points_cost, stock, status) VALUES (?, ?, ?, ?, ?)')
               ->execute([$name, $description, $pointsCost, $stock, $status]);
            setFlash('success', 'Reward created successfully.');
        } else {
            $rewardId = (int)($_POST['reward_id'] ?? 0);
            $db->prepare('UPDATE rewards SET name = ?, description = ?, points_cost = ?, stock = ?, status = ? WHERE id = ?')
               ->execute([$name, $description, $pointsCost, $stock, $status, $rewardId]);
            setFlash('success', 'Reward updated successfully.');
        }

        header('Location: ' . appUrl('admin/rewards.php'));
        exit;
    }

    if ($action === 'activate' || $action === 'deactivate') {
        $rewardId = (int)($_POST['reward_id'] ?? 0);
        $newStatus = $action === 'activate' ? 'active' : 'inactive';
        $db->prepare('UPDATE rewards SET status = ? WHERE id = ?')->execute([$newStatus, $rewardId]);
        setFlash('success', 'Reward ' . ($newStatus === 'active' ? 'activated' : 'deactivated') . '.');
        header('Location: ' . appUrl('admin/rewards.php'));
        exit;
    }

    if ($action === 'delete') {
        $rewardId = (int)($_POST['reward_id'] ?? 0);

        // Block deletion if there is redemption history to preserve the audit trail
        $stmt = $db->prepare('SELECT COUNT(*) FROM redemptions WHERE reward_id = ?');
        $stmt->execute([$rewardId]);
        if ($stmt->fetchColumn() > 0) {
            setFlash('error', 'Cannot delete a reward with redemption history — deactivate it instead.');
            header('Location: ' . appUrl('admin/rewards.php'));
            exit;
        }

        $db->prepare('DELETE FROM rewards WHERE id = ?')->execute([$rewardId]);
        setFlash('success', 'Reward deleted.');
        header('Location: ' . appUrl('admin/rewards.php'));
        exit;
    }

    if ($action === 'fulfill' || $action === 'cancel') {
        $redemptionId = (int)($_POST['redemption_id'] ?? 0);

        $stmt = $db->prepare('SELECT status FROM redemptions WHERE id = ?');
        $stmt->execute([$redemptionId]);
        $status = $stmt->fetchColumn();

        if (!$status) {
            setFlash('error', 'Redemption not found.');
            header('Location: ' . appUrl('admin/rewards.php#redemptions'));
            exit;
        }
        if ($status !== 'pending') {
            setFlash('error', 'This redemption has already been reviewed.');
            header('Location: ' . appUrl('admin/rewards.php#redemptions'));
            exit;
        }

        $db->beginTransaction();
        if ($action === 'fulfill') {
            $db->prepare('UPDATE redemptions SET status = ? WHERE id = ?')->execute(['fulfilled', $redemptionId]);

            $stmt = $db->prepare('SELECT reward_id FROM redemptions WHERE id = ?');
            $stmt->execute([$redemptionId]);
            $db->prepare('UPDATE rewards SET stock = GREATEST(stock - 1, 0) WHERE id = ?')->execute([$stmt->fetchColumn()]);

            $message = 'Redemption fulfilled and stock updated.';
        } else {
            $db->prepare('UPDATE redemptions SET status = ? WHERE id = ?')->execute(['cancelled', $redemptionId]);
            $message = 'Redemption cancelled.';
        }
        $db->commit();
        setFlash('success', $message);

        header('Location: ' . appUrl('admin/rewards.php#redemptions'));
        exit;
    }
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Reward action failed: ' . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again.');
    header('Location: ' . appUrl('admin/rewards.php'));
    exit;
}