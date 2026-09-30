<?php
/**
 * Admin - User Detail View
 * Profile overview + points transactions + reported items (never password data).
 */
$activePage = 'users';
$pageTitle  = 'User Profile';
require_once __DIR__ . '/partials/header.php';

$db     = getDB();
$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) {
    setFlash('error', 'Invalid user.');
    header('Location: ' . appUrl('admin/users.php'));
    exit;
}

$stmt = $db->prepare(
    'SELECT id, full_name, email, mobile, role, status, points_balance, created_at
     FROM users WHERE id = ?'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'User not found.');
    header('Location: ' . appUrl('admin/users.php'));
    exit;
}

//--- Summary counts ---
$summary = ['reports' => 0, 'claims' => 0, 'redemptions' => 0, 'points_earned' => 0];
try {
    $summary['reports']       = (int)$db->query('SELECT COUNT(*) FROM items WHERE user_id = ' . $userId)->fetchColumn();
    $summary['claims']        = (int)$db->query('SELECT COUNT(*) FROM claims WHERE claimant_id = ' . $userId)->fetchColumn();
    $summary['redemptions']   = (int)$db->query('SELECT COUNT(*) FROM redemptions WHERE user_id = ' . $userId)->fetchColumn();
    $stmt = $db->prepare('SELECT COALESCE(SUM(points), 0) FROM points_transactions WHERE user_id = ? AND transaction_type = "earned"');
    $stmt->execute([$userId]);
    $summary['points_earned'] = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    error_log('User summary query failed: ' . $e->getMessage());
}

//--- Points transactions (last 25) ---
$transactions = [];
try {
    $stmt = $db->prepare(
        'SELECT id, points, transaction_type, description, reference_id, reference_type, created_at
         FROM points_transactions WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 25'
    );
    $stmt->execute([$userId]);
    $transactions = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('User transactions query failed: ' . $e->getMessage());
}

//--- Items reported by the user (last 10) ---
$items = [];
try {
    $stmt = $db->prepare(
        'SELECT id, title, item_type, status, campus_zone, created_at
         FROM items WHERE user_id = ? ORDER BY created_at DESC LIMIT 10'
    );
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('User items query failed: ' . $e->getMessage());
}
?>

<div class="admin-head">
  <div>
    <a class="admin-backlink" href="<?php echo appUrl('admin/users.php'); ?>">
      <span class="material-symbols-outlined" style="font-size:16px">arrow_back</span> Back to Users
    </a>
    <h1 class="font-headline-lg">User Profile</h1>
  </div>
  <div style="display:flex;gap:.5rem;">
    <?php if ($user['role'] !== 'admin'): ?>
      <?php if ($user['status'] === 'active'): ?>
        <form action="<?php echo appUrl('php/admin/user_action.php'); ?>" method="post">
          <?php echo csrfField(); ?>
          <input type="hidden" name="action" value="deactivate">
          <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
          <button type="submit" class="btn btn--danger btn-sm">Deactivate</button>
        </form>
      <?php else: ?>
        <form action="<?php echo appUrl('php/admin/user_action.php'); ?>" method="post">
          <?php echo csrfField(); ?>
          <input type="hidden" name="action" value="activate">
          <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
          <button type="submit" class="btn btn-primary btn-sm">Reactivate</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- ===== Profile Card ===== -->
<section class="admin-card admin-profile-card">
  <div class="admin-profile-avatar">
    <div class="avatar avatar--lg"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
    <h2 style="margin:.75rem 0 .25rem;" class="font-headline-md"><?php echo sanitize($user['full_name']); ?></h2>
    <div style="display:flex;gap:.375rem;justify-content:center;align-items:center;flex-wrap:wrap;">
      <span class="status-tag status-tag--<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
      <span class="status-tag status-tag--<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
    </div>
  </div>
  <div class="admin-profile-details">
    <div class="detail-item">
      <span class="material-symbols-outlined">mail</span>
      <div><span class="detail-label">Email</span><span><?php echo sanitize($user['email']); ?></span></div>
    </div>
    <div class="detail-item">
      <span class="material-symbols-outlined">phone</span>
      <div><span class="detail-label">Mobile</span><span><?php echo sanitize($user['mobile'] ?? '—'); ?></span></div>
    </div>
    <div class="detail-item">
      <span class="material-symbols-outlined">event</span>
      <div><span class="detail-label">Joined</span><span><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span></div>
    </div>
    <div class="detail-item">
      <span class="material-symbols-outlined">stars</span>
      <div><span class="detail-label">Points Balance</span><span class="points-chip"><?php echo number_format((int)$user['points_balance']); ?> pts</span></div>
    </div>
  </div>
</section>

<!-- ===== Summary Stats ===== -->
<div class="admin-stats admin-stats--4">
  <div class="admin-stat">
    <div class="admin-stat__icon kpi-card__icon--primary"><span class="material-symbols-outlined">inventory_2</span></div>
    <div class="admin-stat__value"><?php echo number_format($summary['reports']); ?></div>
    <div class="admin-stat__label">Reports</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat__icon kpi-card__icon--primary"><span class="material-symbols-outlined">gavel</span></div>
    <div class="admin-stat__value"><?php echo number_format($summary['claims']); ?></div>
    <div class="admin-stat__label">Claims</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat__icon admin-stat__icon--warning"><span class="material-symbols-outlined">redeem</span></div>
    <div class="admin-stat__value"><?php echo number_format($summary['redemptions']); ?></div>
    <div class="admin-stat__label">Redemptions</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat__icon kpi-card__icon--found"><span class="material-symbols-outlined">trending_up</span></div>
    <div class="admin-stat__value"><?php echo number_format($summary['points_earned']); ?></div>
    <div class="admin-stat__label">Points Earned</div>
  </div>
</div>

<div class="admin-grid">
  <!-- ===== Points Transactions ===== -->
  <section class="admin-card">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Points History</h2>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Description</th>
            <th>Type</th>
            <th>Points</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($transactions)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--on-surface-variant);padding:2rem;">No points activity yet.</td></tr>
          <?php else: ?>
            <?php foreach ($transactions as $tx): ?>
              <tr>
                <td style="max-width:220px;">
                  <div style="font-weight:600;"><?php echo sanitize($tx['description']); ?></div>
                  <?php if ($tx['reference_type']): ?>
                    <div class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize(ucwords(str_replace('_', ' ', $tx['reference_type']))); ?> #<?php echo (int)$tx['reference_id']; ?></div>
                  <?php endif; ?>
                </td>
                <td><span class="status-tag status-tag--<?php echo $tx['transaction_type']; ?>"><?php echo ucfirst($tx['transaction_type']); ?></span></td>
                <td class="font-label-md" style="font-weight:700;<?php echo ((int)$tx['points'] >= 0) ? 'color:var(--success, #16a34a);' : 'color:var(--danger, #dc2626);'; ?>"><?php echo ((int)$tx['points'] >= 0 ? '+' : '') . number_format((int)$tx['points']); ?></td>
                <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($tx['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- ===== Reported Items ===== -->
  <section class="admin-card">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Reported Items</h2>
      <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/items.php?u=' . $userId); ?>">View All</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Type</th>
            <th>Status</th>
            <th>Zone</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--on-surface-variant);padding:2rem;">This user hasn't reported any items.</td></tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <tr>
                <td style="font-weight:600;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo sanitize($item['title']); ?></td>
                <td><span class="badge <?php echo $item['item_type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($item['item_type']); ?></span></td>
                <td><span class="status-tag status-tag--<?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize(zoneName($item['campus_zone'])); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>