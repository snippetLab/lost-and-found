<?php
/**
 * Admin - Claims
 */
$activePage = 'claims';
$pageTitle  = 'Claims';
require_once __DIR__ . '/partials/header.php';

$db = getDB();

$filters = ['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];
$currentFilter = $_GET['s'] ?? 'pending';
if (!array_key_exists($currentFilter, $filters)) {
    $currentFilter = 'pending';
}

$where = '1 = 1';
if ($currentFilter !== 'all') {
    $where .= ' AND c.status = ?';
}

$perPage = 12;
$page = max(1, (int)($_GET['p'] ?? 1));

$stmt = $db->prepare('SELECT COUNT(*) FROM claims c WHERE ' . $where);
$stmt->execute($currentFilter !== 'all' ? [$currentFilter] : []);
$totalClaims = (int)$stmt->fetchColumn();
$pager = paginate($totalClaims, $perPage, $page);

$stmt = $db->prepare(
    'SELECT c.*, i.title AS item_title, i.item_type, i.campus_zone, u.full_name AS claimant_name, u.email AS claimant_email
     FROM claims c
     LEFT JOIN items i ON i.id = c.item_id
     LEFT JOIN users u ON u.id = c.claimant_id
     WHERE ' . $where . '
     ORDER BY c.created_at DESC
     LIMIT ' . (int)$pager['per_page'] . ' OFFSET ' . (int)$pager['offset']
);
$stmt->execute($currentFilter !== 'all' ? [$currentFilter] : []);
$claims = $stmt->fetchAll();
?>

<div class="admin-head">
  <div>
    <h1 class="font-headline-lg">Claims</h1>
    <p class="font-body-sm" style="color:var(--on-surface-variant);">Ownership claims on found items. Approving rewards the finder with points.</p>
  </div>
</div>

<div class="filter-tabs" style="margin-bottom:1rem;">
  <?php foreach ($filters as $key => $label): ?>
    <a class="<?php echo $currentFilter === $key ? 'is-active' : ''; ?>" href="?s=<?php echo $key; ?>"><?php echo $label; ?></a>
  <?php endforeach; ?>
</div>

<section class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Claim</th>
          <th>Item</th>
          <th>Claimant</th>
          <th>Status</th>
          <th>Submitted</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($claims)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--on-surface-variant);padding:2.5rem;">No claims match this filter.</td></tr>
        <?php else: ?>
          <?php foreach ($claims as $claim): ?>
            <tr>
              <td style="font-weight:600;">#<?php echo $claim['id']; ?></td>
              <td>
                <div style="font-weight:600;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo sanitize($claim['item_title'] ?? 'Item'); ?></div>
                <div class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize(zoneName($claim['campus_zone'] ?? '')); ?></div>
              </td>
              <td>
                <div class="font-body-sm"><?php echo sanitize($claim['claimant_name'] ?? 'Unknown'); ?></div>
                <div class="font-body-sm" style="color:var(--on-surface-variant); font-size:.6875rem;"><?php echo sanitize($claim['claimant_email'] ?? ''); ?></div>
              </td>
              <td>
                <span class="status-tag status-tag--<?php echo $claim['status']; ?>"><?php echo ucfirst($claim['status']); ?></span>
              </td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($claim['created_at']); ?></td>
              <td>
                <div class="admin-actions" style="justify-content:flex-end;">
                  <a class="btn btn-outline btn-sm" href="<?php echo appUrl('admin/claim-view.php?id=' . $claim['id']); ?>">View</a>
                  <?php if ($claim['status'] === 'pending'): ?>
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
                      <button type="submit" class="btn btn--danger btn-sm"
                              onclick="return confirm('Reject this claim? The claimant will not receive points.');">Reject</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pager['total_pages'] > 1): ?>
    <div class="admin-pagination">
      <span style="color:var(--on-surface-variant);">Page <?php echo $pager['current_page']; ?> of <?php echo $pager['total_pages']; ?></span>
      <?php if ($pager['current_page'] > 1): ?>
        <a href="?s=<?php echo $currentFilter; ?>&p=<?php echo $pager['current_page'] - 1; ?>">&larr; Prev</a>
      <?php endif; ?>
      <?php if ($pager['current_page'] < $pager['total_pages']): ?>
        <a href="?s=<?php echo $currentFilter; ?>&p=<?php echo $pager['current_page'] + 1; ?>">Next &rarr;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>