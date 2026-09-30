<?php
/**
 * Admin - Dashboard
 */
$activePage = 'dashboard';
$pageTitle  = 'Dashboard';
require_once __DIR__ . '/partials/header.php';

$db = getDB();

// --- Live statistics from the database ---
$stats = [
    'total_users'        => 0,
    'active_users'       => 0,
    'lost_items'         => 0,
    'found_items'        => 0,
    'pending_claims'     => 0,
    'active_items'       => 0,
    'returned_items'     => 0,
    'pending_redemptions'=> 0,
    'rewards_total'      => 0,
];
try {
    $stats['total_users']         = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stats['active_users']        = (int)$db->query('SELECT COUNT(*) FROM users WHERE status = "active"')->fetchColumn();
    $stats['lost_items']          = (int)$db->query('SELECT COUNT(*) FROM items WHERE item_type = "lost"')->fetchColumn();
    $stats['found_items']         = (int)$db->query('SELECT COUNT(*) FROM items WHERE item_type = "found"')->fetchColumn();
    $stats['pending_claims']      = (int)$db->query('SELECT COUNT(*) FROM claims WHERE status = "pending"')->fetchColumn();
    $stats['active_items']        = (int)$db->query('SELECT COUNT(*) FROM items WHERE status = "active"')->fetchColumn();
    $stats['returned_items']      = (int)$db->query('SELECT COUNT(*) FROM items WHERE status = "returned"')->fetchColumn();
    $stats['pending_redemptions'] = (int)$db->query('SELECT COUNT(*) FROM redemptions WHERE status = "pending"')->fetchColumn();
    $stats['rewards_total']       = (int)$db->query('SELECT COUNT(*) FROM rewards WHERE status = "active"')->fetchColumn();
} catch (Exception $e) {
    error_log('Admin stats query failed: ' . $e->getMessage());
}

// --- Real-time analytics: items by status ---
$itemByStatus = ['active' => 0, 'matched' => 0, 'returned' => 0, 'closed' => 0];
try {
    foreach ($db->query('SELECT status, COUNT(*) AS c FROM items GROUP BY status')->fetchAll() as $row) {
        if (array_key_exists($row['status'], $itemByStatus)) {
            $itemByStatus[$row['status']] = (int)$row['c'];
        }
    }
} catch (Exception $e) {
    error_log('Admin item analytics failed: ' . $e->getMessage());
}

// --- Real-time analytics: claims by status ---
$claimByStatus = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled' => 0];
try {
    foreach ($db->query('SELECT status, COUNT(*) AS c FROM claims GROUP BY status')->fetchAll() as $row) {
        if (array_key_exists($row['status'], $claimByStatus)) {
            $claimByStatus[$row['status']] = (int)$row['c'];
        }
    }
} catch (Exception $e) {
    error_log('Admin claim analytics failed: ' . $e->getMessage());
}

// --- Real-time analytics: reports last 7 days ---
$reportSeries = [];
try {
    $days = [];
    for ($i = 6; $i >= 0; $i--) {
        $key             = date('Y-m-d', strtotime('-' . $i . ' days'));
        $days[$key]      = 0;
        $reportSeries[$key] = 0;
    }
    foreach ($db->query('SELECT DATE(created_at) AS d, COUNT(*) AS c FROM items WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at)')->fetchAll() as $row) {
        if (isset($days[$row['d']])) {
            $reportSeries[$row['d']] = (int)$row['c'];
        }
    }
} catch (Exception $e) {
    error_log('Admin report series failed: ' . $e->getMessage());
}
$reportMax = max(array_merge([1], array_values($reportSeries)));

// --- Claims awaiting review ---
$pendingClaims = [];
try {
    $stmt = $db->query(
        'SELECT c.id, c.item_id, c.created_at, i.title AS item_title, i.campus_zone, u.full_name AS claimant_name
         FROM claims c
         LEFT JOIN items i ON i.id = c.item_id
         LEFT JOIN users u ON u.id = c.claimant_id
         WHERE c.status = "pending"
         ORDER BY c.created_at DESC
         LIMIT 6'
    );
    $pendingClaims = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Admin pending claims query failed: ' . $e->getMessage());
}

// --- Redemptions awaiting fulfillment ---
$pendingRedemptions = [];
try {
    $stmt = $db->query(
        'SELECT r.id, r.points_spent, r.created_at, u.full_name AS user_name, w.name AS reward_name
         FROM redemptions r
         LEFT JOIN users u ON u.id = r.user_id
         LEFT JOIN rewards w ON w.id = r.reward_id
         WHERE r.status = "pending"
         ORDER BY r.created_at DESC
         LIMIT 6'
    );
    $pendingRedemptions = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Admin pending redemptions query failed: ' . $e->getMessage());
}

// --- Most recent item reports ---
$recentItems = [];
try {
    $stmt = $db->query(
        'SELECT i.*, u.full_name AS submitter
         FROM items i
         LEFT JOIN users u ON u.id = i.user_id
         ORDER BY i.created_at DESC
         LIMIT 8'
    );
    $recentItems = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Admin recent items query failed: ' . $e->getMessage());
}

// --- Most recent claim activity ---
$recentClaims = [];
try {
    $stmt = $db->query(
        'SELECT c.id, c.status, c.created_at, i.title AS item_title, u.full_name AS claimant_name
         FROM claims c
         LEFT JOIN items i ON i.id = c.item_id
         LEFT JOIN users u ON u.id = c.claimant_id
         ORDER BY c.created_at DESC
         LIMIT 6'
    );
    $recentClaims = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Admin recent claims query failed: ' . $e->getMessage());
}

// --- Most recent users ---
$recentUsers = [];
try {
    $stmt = $db->query(
        'SELECT id, full_name, email, role, status, points_balance, created_at
         FROM users
         ORDER BY created_at DESC
         LIMIT 6'
    );
    $recentUsers = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Admin recent users query failed: ' . $e->getMessage());
}

// --- Helper to render a bar chart row ---
function adminBar(string $label, int $count, int $max, string $tone): string {
    $pct = $max > 0 ? (int)round($count / $max * 100) : 0;
    return '<div class="bar-row"><span class="bar-label">' . $label . '</span>'
         . '<span class="bar-track"><span class="bar-fill bar-fill--' . $tone . '" style="width:' . $pct . '%"></span></span>'
         . '<span class="bar-value">' . number_format($count) . '</span></div>';
}
?>

<div class="admin-head">
  <div>
    <h1 class="font-headline-lg">Welcome Back, Admin!</h1>
    <p class="font-body-sm" style="color:var(--on-surface-variant);">Here's what's happening across your lost &amp; found portal.</p>
  </div>
  <a class="btn btn-primary btn-sm btn-pill" href="<?php echo appUrl('admin/items.php'); ?>">
    <span class="material-symbols-outlined" style="font-size:16px">list_alt</span> View All Items
  </a>
</div>

<div class="admin-stats">
  <div class="admin-stat">
    <div class="admin-stat__icon kpi-card__icon--primary"><span class="material-symbols-outlined">group</span></div>
    <div class="admin-stat__value"><?php echo number_format($stats['total_users']); ?></div>
    <div class="admin-stat__label">Total Users</div>
    <div class="admin-stat__sub"><?php echo number_format($stats['active_users']); ?> active</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat__icon kpi-card__icon--primary"><span class="material-symbols-outlined">inventory_2</span></div>
    <div class="admin-stat__value"><?php echo number_format($stats['active_items']); ?></div>
    <div class="admin-stat__label">Active Items</div>
    <div class="admin-stat__sub"><?php echo number_format($stats['returned_items']); ?> returned</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat__icon kpi-card__icon--lost"><span class="material-symbols-outlined">search</span></div>
    <div class="admin-stat__value"><?php echo number_format($stats['lost_items']); ?></div>
    <div class="admin-stat__label">Lost Reports</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat__icon kpi-card__icon--found"><span class="material-symbols-outlined">task_alt</span></div>
    <div class="admin-stat__value"><?php echo number_format($stats['found_items']); ?></div>
    <div class="admin-stat__label">Found Reports</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat__icon admin-stat__icon--warning"><span class="material-symbols-outlined">gavel</span></div>
    <div class="admin-stat__value"><?php echo number_format($stats['pending_claims']); ?></div>
    <div class="admin-stat__label">Pending Claims</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat__icon admin-stat__icon--warning"><span class="material-symbols-outlined">redeem</span></div>
    <div class="admin-stat__value"><?php echo number_format($stats['pending_redemptions']); ?></div>
    <div class="admin-stat__label">Pending Redemptions</div>
    <div class="admin-stat__sub"><?php echo number_format($stats['rewards_total']); ?> active rewards</div>
  </div>
</div>

<!-- ===== Quick Actions ===== -->
<div class="admin-quick">
  <a class="admin-quick__item" href="<?php echo appUrl('admin/claims.php?s=pending'); ?>">
    <span class="material-symbols-outlined">gavel</span> Review Claims
    <span class="admin-quick__count"><?php echo number_format($stats['pending_claims']); ?></span>
  </a>
  <a class="admin-quick__item" href="<?php echo appUrl('admin/items.php'); ?>">
    <span class="material-symbols-outlined">inventory_2</span> Manage Items
    <span class="admin-quick__count"><?php echo number_format($stats['active_items']); ?></span>
  </a>
  <a class="admin-quick__item" href="<?php echo appUrl('admin/users.php'); ?>">
    <span class="material-symbols-outlined">group</span> Manage Users
    <span class="admin-quick__count"><?php echo number_format($stats['active_users']); ?></span>
  </a>
  <a class="admin-quick__item" href="<?php echo appUrl('admin/rewards.php'); ?>">
    <span class="material-symbols-outlined">redeem</span> Manage Rewards
    <span class="admin-quick__count"><?php echo number_format($stats['rewards_total']); ?></span>
  </a>
</div>

<!-- ===== Real-time Analytics ===== -->
<div class="admin-analytics">
  <section class="admin-card admin-analytics__card">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Reports by Status</h2>
      <span class="admin-card__hint">Real-time</span>
    </div>
    <?php if (array_sum($itemByStatus) === 0): ?>
      <div class="admin-empty">
        <span class="material-symbols-outlined">bar_chart</span>
        <p>No item reports yet — the chart will fill in as reports are submitted.</p>
      </div>
    <?php else: ?>
      <?php
      $itemMax = max($itemByStatus);
      foreach ($itemByStatus as $key => $count):
        echo adminBar(ucfirst($key), $count, $itemMax, 'primary');
      endforeach;
      ?>
    <?php endif; ?>
  </section>

  <section class="admin-card admin-analytics__card">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Claims by Status</h2>
      <span class="admin-card__hint">Real-time</span>
    </div>
    <?php if (array_sum($claimByStatus) === 0): ?>
      <div class="admin-empty">
        <span class="material-symbols-outlined">bar_chart</span>
        <p>No claims yet — statuses will appear as students claim items.</p>
      </div>
    <?php else: ?>
      <?php
      $claimMax = max($claimByStatus);
      foreach ($claimByStatus as $key => $count):
        $tone = $key === 'approved' ? 'success' : ($key === 'rejected' ? 'danger' : ($key === 'pending' ? 'warning' : 'muted'));
        echo adminBar(ucfirst($key), $count, $claimMax, $tone);
      endforeach;
      ?>
    <?php endif; ?>
  </section>

  <section class="admin-card admin-analytics__card admin-analytics__card--wide">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Reports — Last 7 Days</h2>
      <span class="admin-card__hint">Daily count</span>
    </div>
    <?php if (array_sum($reportSeries) === 0): ?>
      <div class="admin-empty">
        <span class="material-symbols-outlined">event_upcoming</span>
        <p>No reports were submitted in the last 7 days.</p>
      </div>
    <?php else: ?>
      <div class="mini-bars">
        <?php foreach ($reportSeries as $day => $count):
          $pct = (int)round($count / $reportMax * 100);
          $label = date('D', strtotime($day));
        ?>
          <div class="mini-bar">
            <span class="mini-bar__value"><?php echo $count; ?></span>
            <span class="mini-bar__track"><span class="mini-bar__fill" style="height:<?php echo $pct; ?>%"></span></span>
            <span class="mini-bar__label"><?php echo $label; ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- ===== Pending Actions ===== -->
<div class="admin-grid">
  <section class="admin-card">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Claims Awaiting Review</h2>
      <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/claims.php?s=pending'); ?>">View All</a>
    </div>
    <?php if (empty($pendingClaims)): ?>
      <div class="admin-empty">
        <span class="material-symbols-outlined">check_circle</span>
        <p>No claims waiting for review — all caught up!</p>
      </div>
    <?php else: ?>
      <?php foreach ($pendingClaims as $claim): ?>
        <div class="admin-claim-row">
          <div class="avatar"><?php echo strtoupper(substr($claim['claimant_name'] ?? '?', 0, 1)); ?></div>
          <div style="flex:1; min-width:0;">
            <div class="font-label-md" style="font-weight:600;"><?php echo sanitize($claim['item_title'] ?? 'Item'); ?></div>
            <div class="font-body-sm" style="color:var(--on-surface-variant);">
              Claimed by <?php echo sanitize($claim['claimant_name'] ?? 'Anonymous'); ?>
              &middot; <?php echo sanitize(zoneName($claim['campus_zone'] ?? '')); ?>
              &middot; <?php echo timeAgo($claim['created_at']); ?>
            </div>
          </div>
          <div class="admin-actions">
            <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/claim-view.php?id=' . $claim['id']); ?>">View</a>
            <form action="<?php echo appUrl('php/admin/claim_review.php'); ?>" method="post" style="display:inline;">
              <?php echo csrfField(); ?>
              <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="btn btn-primary btn-sm">Approve</button>
            </form>
            <form action="<?php echo appUrl('php/admin/claim_review.php'); ?>" method="post" style="display:inline;">
              <?php echo csrfField(); ?>
              <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
              <input type="hidden" name="action" value="reject">
              <button type="submit" class="btn btn--danger btn-sm">Reject</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <section class="admin-card">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Redemptions Pending</h2>
      <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/rewards.php#redemptions'); ?>">View All</a>
    </div>
    <?php if (empty($pendingRedemptions)): ?>
      <div class="admin-empty">
        <span class="material-symbols-outlined">check_circle</span>
        <p>No redemptions waiting for fulfillment.</p>
      </div>
    <?php else: ?>
      <?php foreach ($pendingRedemptions as $r): ?>
        <div class="admin-claim-row">
          <div class="avatar"><?php echo strtoupper(substr($r['user_name'] ?? '?', 0, 1)); ?></div>
          <div style="flex:1; min-width:0;">
            <div class="font-label-md" style="font-weight:600;"><?php echo sanitize($r['reward_name'] ?? 'Reward'); ?></div>
            <div class="font-body-sm" style="color:var(--on-surface-variant);">
              by <?php echo sanitize($r['user_name'] ?? 'Unknown'); ?>
              &middot; <?php echo number_format((int)$r['points_spent']); ?> pts
              &middot; <?php echo timeAgo($r['created_at']); ?>
            </div>
          </div>
          <a class="btn btn-outline btn-sm" href="<?php echo appUrl('admin/rewards.php#redemptions'); ?>">View</a>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</div>

<!-- ===== Recent Tables ===== -->
<div class="admin-grid">
  <section class="admin-card">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Recent Reports</h2>
      <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/items.php'); ?>">View All</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Type</th>
            <th>Status</th>
            <th>Zone</th>
            <th>Reporter</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentItems)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--on-surface-variant);padding:2rem;">No item reports yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentItems as $item): ?>
              <tr>
                <td>
                  <div class="cell-strong">
                    <div class="thumb">
                      <?php if ($item['image_1']): ?>
                        <img src="<?php echo appUrl('uploads/items/' . $item['image_1']); ?>" alt="">
                      <?php else: ?>
                        <span class="material-symbols-outlined" style="font-size:20px;color:var(--primary);"><?php echo $item['item_type'] === 'lost' ? 'search' : 'inventory_2'; ?></span>
                      <?php endif; ?>
                    </div>
                    <div style="min-width:0;">
                      <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;"><?php echo sanitize($item['title']); ?></div>
                      <div class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($item['created_at']); ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge <?php echo $item['item_type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($item['item_type']); ?></span>
                </td>
                <td>
                  <span class="status-tag status-tag--<?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span>
                </td>
                <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize(zoneName($item['campus_zone'])); ?></td>
                <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize($item['submitter'] ?? '—'); ?></td>
                <td>
                  <a class="btn btn-outline btn-sm" href="<?php echo appUrl('admin/item-view.php?id=' . $item['id']); ?>">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="admin-card">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Recent Claim Activity</h2>
      <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/claims.php'); ?>">View All</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Claimant</th>
            <th>Status</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentClaims)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--on-surface-variant);padding:2rem;">No claims yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentClaims as $claim): ?>
              <tr>
                <td style="font-weight:600;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo sanitize($claim['item_title'] ?? 'Item'); ?></td>
                <td class="font-body-sm"><?php echo sanitize($claim['claimant_name'] ?? 'Unknown'); ?></td>
                <td>
                  <span class="status-tag status-tag--<?php echo $claim['status']; ?>"><?php echo ucfirst($claim['status']); ?></span>
                </td>
                <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($claim['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="admin-card admin-grid__wide">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Recent Users</h2>
      <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/users.php'); ?>">View All</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Points</th>
            <th>Status</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentUsers)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--on-surface-variant);padding:2rem;">No users yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentUsers as $user): ?>
              <tr>
                <td>
                  <div class="cell-strong">
                    <div class="avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                    <div style="min-width:0;">
                      <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;"><?php echo sanitize($user['full_name']); ?></div>
                      <div class="font-body-sm" style="color:var(--on-surface-variant); font-size:.6875rem;"><?php echo sanitize($user['email']); ?></div>
                    </div>
                  </div>
                </td>
                <td><span class="status-tag status-tag--<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                <td class="font-label-md" style="font-weight:700;"><?php echo number_format((int)$user['points_balance']); ?></td>
                <td><span class="status-tag status-tag--<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($user['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>