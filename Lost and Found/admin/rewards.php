<?php
/**
 * Admin - Rewards Management
 * Add / edit / enable-disable rewards + monitor redemptions.
 */
$activePage = 'rewards';
$pageTitle  = 'Rewards';
require_once __DIR__ . '/partials/header.php';

$db = getDB();

// Reward being edited (via ?edit=ID), otherwise the empty "add" form is shown
$editId   = (int)($_GET['edit'] ?? 0);
$editing  = null;
if ($editId > 0) {
    $stmt = $db->prepare('SELECT * FROM rewards WHERE id = ?');
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
    if (!$editing) {
        setFlash('error', 'Reward not found.');
        header('Location: ' . appUrl('admin/rewards.php'));
        exit;
    }
}

// All rewards with redemption counts
$rewards = [];
try {
    $stmt = $db->query(
        'SELECT r.*, (SELECT COUNT(*) FROM redemptions rd WHERE rd.reward_id = r.id) AS redemption_count
         FROM rewards r
         ORDER BY r.status ASC, r.points_cost ASC'
    );
    $rewards = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Rewards list query failed: ' . $e->getMessage());
}

// Redemptions monitor (with optional status filter)
$redFilter = $_GET['rf'] ?? 'all';
$redWhere  = '';
$redParams = [];
if (in_array($redFilter, ['pending', 'fulfilled', 'cancelled'], true)) {
    $redWhere  = 'WHERE r.status = ?';
    $redParams = [$redFilter];
}
$redemptions = [];
try {
    $stmt = $db->prepare(
        'SELECT r.id, r.points_spent, r.status, r.created_at,
                u.full_name AS user_name, u.email AS user_email,
                w.name AS reward_name
         FROM redemptions r
         LEFT JOIN users u ON u.id = r.user_id
         LEFT JOIN rewards w ON w.id = r.reward_id
         ' . $redWhere . '
         ORDER BY r.created_at DESC
         LIMIT 40'
    );
    $stmt->execute($redParams);
    $redemptions = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Redemptions list query failed: ' . $e->getMessage());
}
?>

<div class="admin-head">
  <div>
    <h1 class="font-headline-lg">Rewards</h1>
    <p class="font-body-sm" style="color:var(--on-surface-variant);">Create rewards students can redeem with earned points.</p>
  </div>
  <?php if ($editing): ?>
    <a class="btn btn-ghost btn-sm btn-pill" href="<?php echo appUrl('admin/rewards.php'); ?>">
      <span class="material-symbols-outlined" style="font-size:16px">add</span> Add New Reward
    </a>
  <?php endif; ?>
</div>

<!-- ===== Add / Edit Reward Form ===== -->
<section class="admin-card admin-reward-form">
  <div class="admin-card__head">
    <h2 class="admin-card__title"><?php echo $editing ? 'Edit Reward' : 'Add New Reward'; ?></h2>
    <?php if ($editing): ?>
      <span class="admin-card__hint">ID: #<?php echo $editing['id']; ?></span>
    <?php endif; ?>
  </div>

  <form class="admin-form-grid" action="<?php echo appUrl('php/admin/reward_action.php'); ?>" method="post">
    <?php echo csrfField(); ?>
    <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'create'; ?>">
    <?php if ($editing): ?><input type="hidden" name="reward_id" value="<?php echo $editing['id']; ?>"><?php endif; ?>

    <div class="admin-form-field admin-form-field--wide">
      <label for="reward-name">Reward Name</label>
      <input type="text" id="reward-name" name="name" required maxlength="100"
             value="<?php echo sanitize($editing['name'] ?? ''); ?>" placeholder="e.g. Meal Voucher at the Cafeteria">
    </div>

    <div class="admin-form-field admin-form-field--wide">
      <label for="reward-desc">Description</label>
      <input type="text" id="reward-desc" name="description" maxlength="255"
             value="<?php echo sanitize($editing['description'] ?? ''); ?>" placeholder="Short description shown to students">
    </div>

    <div class="admin-form-field">
      <label for="reward-cost">Points Cost</label>
      <input type="number" id="reward-cost" name="points_cost" min="0" required
             value="<?php echo (int)($editing['points_cost'] ?? 0); ?>" placeholder="e.g. 50">
    </div>

    <div class="admin-form-field">
      <label for="reward-stock">Stock</label>
      <input type="number" id="reward-stock" name="stock" min="0" required
             value="<?php echo (int)($editing['stock'] ?? 0); ?>" placeholder="e.g. 10">
    </div>

    <div class="admin-form-field">
      <label for="reward-status">Status</label>
      <select id="reward-status" name="status">
        <option value="active" <?php echo ($editing['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
        <option value="inactive" <?php echo ($editing['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
      </select>
    </div>

    <div class="admin-form-actions admin-form-field--wide">
      <?php if ($editing): ?>
        <a class="btn btn-ghost" href="<?php echo appUrl('admin/rewards.php'); ?>">Cancel</a>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary btn-pill"><?php echo $editing ? 'Save Changes' : 'Create Reward'; ?></button>
    </div>
  </form>
</section>

<!-- ===== Rewards Table ===== -->
<section class="admin-card">
  <div class="admin-card__head">
    <h2 class="admin-card__title">Reward Catalog</h2>
    <span class="admin-card__hint"><?php echo count($rewards); ?> reward(s)</span>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Reward</th>
          <th>Points Cost</th>
          <th>Stock</th>
          <th>Redeemed</th>
          <th>Status</th>
          <th>Created</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rewards)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--on-surface-variant);padding:2rem;">No rewards yet — create your first reward above.</td></tr>
        <?php else: ?>
          <?php foreach ($rewards as $reward): ?>
            <tr class="admin-reward-row <?php echo $reward['status'] === 'inactive' ? 'is-dimmed' : ''; ?>">
              <td>
                <div class="cell-strong">
                  <div class="thumb thumb--reward"><span class="material-symbols-outlined">redeem</span></div>
                  <div style="min-width:0;">
                    <div style="font-weight:600;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo sanitize($reward['name']); ?></div>
                    <?php if ($reward['description']): ?>
                      <div class="font-body-sm" style="color:var(--on-surface-variant);max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo sanitize($reward['description']); ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="font-label-md" style="font-weight:700;"><?php echo number_format((int)$reward['points_cost']); ?> pts</td>
              <td class="font-label-md"><?php echo number_format((int)$reward['stock']); ?></td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo number_format((int)$reward['redemption_count']); ?></td>
              <td>
                <span class="status-tag status-tag--<?php echo $reward['status']; ?>"><?php echo ucfirst($reward['status']); ?></span>
              </td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($reward['created_at']); ?></td>
              <td>
                <div class="admin-actions">
                  <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/rewards.php?edit=' . $reward['id']); ?>">Edit</a>
                  <?php if ($reward['status'] === 'active'): ?>
                    <form action="<?php echo appUrl('php/admin/reward_action.php'); ?>" method="post" style="display:inline;">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="action" value="deactivate">
                      <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                      <button type="submit" class="btn btn-ghost btn-sm">Deactivate</button>
                    </form>
                  <?php else: ?>
                    <form action="<?php echo appUrl('php/admin/reward_action.php'); ?>" method="post" style="display:inline;">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="action" value="activate">
                      <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                      <button type="submit" class="btn btn-outline btn-sm">Activate</button>
                    </form>
                  <?php endif; ?>
                  <form action="<?php echo appUrl('php/admin/reward_action.php'); ?>" method="post" style="display:inline;"
                        onsubmit="return confirm('Delete this reward permanently?');">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                    <button type="submit" class="btn btn--danger btn-sm">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- ===== Redemptions Monitor ===== -->
<section class="admin-card" id="redemptions">
  <div class="admin-card__head">
    <h2 class="admin-card__title">Redemption Requests</h2>
    <div class="admin-filters">
      <a class="admin-chip <?php echo $redFilter === 'all' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/rewards.php#redemptions'); ?>">All</a>
      <a class="admin-chip <?php echo $redFilter === 'pending' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/rewards.php?rf=pending#redemptions'); ?>">Pending</a>
      <a class="admin-chip <?php echo $redFilter === 'fulfilled' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/rewards.php?rf=fulfilled#redemptions'); ?>">Fulfilled</a>
      <a class="admin-chip <?php echo $redFilter === 'cancelled' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/rewards.php?rf=cancelled#redemptions'); ?>">Cancelled</a>
    </div>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Student</th>
          <th>Reward</th>
          <th>Points</th>
          <th>Status</th>
          <th>Requested</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($redemptions)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--on-surface-variant);padding:2rem;">No redemptions in this view.</td></tr>
        <?php else: ?>
          <?php foreach ($redemptions as $r): ?>
            <tr>
              <td>
                <div class="cell-strong">
                  <div class="avatar"><?php echo strtoupper(substr($r['user_name'] ?? '?', 0, 1)); ?></div>
                  <div style="min-width:0;">
                    <div style="font-weight:600;"><?php echo sanitize($r['user_name'] ?? 'Unknown'); ?></div>
                    <div class="font-body-sm" style="color:var(--on-surface-variant); font-size:.6875rem;"><?php echo sanitize($r['user_email'] ?? ''); ?></div>
                  </div>
                </div>
              </td>
              <td style="font-weight:600;"><?php echo sanitize($r['reward_name'] ?? 'Reward'); ?></td>
              <td class="font-label-md" style="font-weight:700;"><?php echo number_format((int)$r['points_spent']); ?> pts</td>
              <td>
                <span class="status-tag status-tag--<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span>
              </td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($r['created_at']); ?></td>
              <td>
                <?php if ($r['status'] === 'pending'): ?>
                  <div class="admin-actions">
                    <form action="<?php echo appUrl('php/admin/reward_action.php'); ?>" method="post" style="display:inline;">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="action" value="fulfill">
                      <input type="hidden" name="redemption_id" value="<?php echo $r['id']; ?>">
                      <button type="submit" class="btn btn-primary btn-sm">Fulfill</button>
                    </form>
                    <form action="<?php echo appUrl('php/admin/reward_action.php'); ?>" method="post" style="display:inline;">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="action" value="cancel">
                      <input type="hidden" name="redemption_id" value="<?php echo $r['id']; ?>">
                      <button type="submit" class="btn btn--danger btn-sm">Cancel</button>
                    </form>
                  </div>
                <?php else: ?>
                  <span class="font-body-sm" style="color:var(--on-surface-variant);">Reviewed</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>