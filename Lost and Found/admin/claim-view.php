<?php
/**
 * Admin - Claim Detail View
 */
$activePage = 'claims';
$pageTitle  = 'Claim Detail';
require_once __DIR__ . '/partials/header.php';

$db = getDB();
$claimId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare(
    'SELECT c.*,
            i.title AS item_title, i.item_type, i.status AS item_status, i.campus_zone,
            i.specific_location, i.image_1, i.user_id AS finder_id,
            u.full_name AS claimant_name, u.email AS claimant_email, u.mobile AS claimant_mobile,
            f.full_name AS finder_name, f.email AS finder_email
     FROM claims c
     LEFT JOIN items i ON i.id = c.item_id
     LEFT JOIN users u ON u.id = c.claimant_id
     LEFT JOIN users f ON f.id = i.user_id
     WHERE c.id = ?'
);
$stmt->execute([$claimId]);
$claim = $stmt->fetch();

if (!$claim) {
    echo '<div class="admin-empty"><span class="material-symbols-outlined">search_off</span><p>Claim not found.</p>';
    echo '<a class="btn btn-primary btn-sm btn-pill" href="' . appUrl('admin/claims.php') . '">Back to Claims</a></div>';
    require_once __DIR__ . '/partials/footer.php';
    exit;
}
?>

<div style="max-width:980px;">

  <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/claims.php'); ?>" style="margin-bottom:1rem;">
    <span class="material-symbols-outlined" style="font-size:16px">arrow_back</span> Back to Claims
  </a>

  <div class="admin-head" style="margin-bottom:1.25rem;">
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
      <h1 class="font-headline-lg">Claim #<?php echo $claim['id']; ?></h1>
      <span class="status-tag status-tag--<?php echo $claim['status']; ?>"><?php echo ucfirst($claim['status']); ?></span>
    </div>
    <?php if ($claim['status'] === 'pending'): ?>
      <div class="admin-actions">
        <form action="<?php echo appUrl('php/admin/claim_review.php'); ?>" method="post">
          <?php echo csrfField(); ?>
          <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
          <input type="hidden" name="action" value="approve">
          <button type="submit" class="btn btn-primary btn-sm"
                  onclick="return confirm('Approve this claim? The item will be marked returned and the finder will earn reward points.');">Approve Claim</button>
        </form>
        <form action="<?php echo appUrl('php/admin/claim_review.php'); ?>" method="post">
          <?php echo csrfField(); ?>
          <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
          <input type="hidden" name="action" value="reject">
          <button type="submit" class="btn btn--danger btn-sm"
                  onclick="return confirm('Reject this claim? The claimant will not receive points.');">Reject Claim</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <div class="admin-card" style="margin-bottom:1.5rem;">
    <div class="admin-card__head">
      <h2 class="admin-card__title">Claim Details</h2>
      <span class="font-body-sm" style="color:var(--on-surface-variant);">Submitted <?php echo timeAgo($claim['created_at']); ?></span>
    </div>
    <div style="padding:1.25rem;">
      <div class="detail-item__label">Claimant's Description</div>
      <p style="margin:.375rem 0 1.25rem; line-height:1.6;"><?php echo nl2br(sanitize($claim['description'])); ?></p>

      <div class="detail-grid">
        <div>
          <div class="detail-item__label">Evidence / Verification Submitted</div>
          <div class="detail-item__value"><?php echo sanitize($claim['evidence_details'] ?? 'None provided'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Last Updated</div>
          <div class="detail-item__value"><?php echo timeAgo($claim['updated_at']); ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:1.5rem;">
    <div class="admin-card__head"><h2 class="admin-card__title">Claimant</h2></div>
    <div style="padding:1.25rem;">
      <div class="detail-grid">
        <div>
          <div class="detail-item__label">Name</div>
          <div class="detail-item__value"><?php echo sanitize($claim['claimant_name'] ?? '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Email</div>
          <div class="detail-item__value"><?php echo sanitize($claim['claimant_email'] ?? '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Mobile</div>
          <div class="detail-item__value"><?php echo sanitize($claim['claimant_mobile'] ?? '—'); ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card__head"><h2 class="admin-card__title">Item Involved</h2></div>
    <div style="padding:1.25rem;">
      <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div class="thumb" style="width:72px;height:72px;">
          <?php if ($claim['image_1']): ?>
            <img src="<?php echo appUrl('uploads/items/' . $claim['image_1']); ?>" alt="">
          <?php else: ?>
            <span class="material-symbols-outlined" style="font-size:28px;color:var(--primary);"><?php echo $claim['item_type'] === 'lost' ? 'search' : 'inventory_2'; ?></span>
          <?php endif; ?>
        </div>
        <div style="flex:1; min-width:0;">
          <div class="font-label-lg" style="font-weight:700;"><?php echo sanitize($claim['item_title'] ?? 'Item'); ?></div>
          <div class="font-body-sm" style="color:var(--on-surface-variant);">
            <span class="badge <?php echo $claim['item_type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>" style="margin-right:.375rem;"><?php echo strtoupper($claim['item_type']); ?></span>
            Status: <span class="status-tag status-tag--<?php echo $claim['item_status']; ?>" style="margin-left:.125rem;"><?php echo ucfirst($claim['item_status']); ?></span>
          </div>
          <div class="font-body-sm" style="color:var(--on-surface-variant);">
            <?php echo sanitize(zoneName($claim['campus_zone'] ?? '')); ?>
            <?php if ($claim['specific_location']): ?> &middot; <?php echo sanitize($claim['specific_location']); ?><?php endif; ?>
          </div>
          <div class="font-body-sm" style="color:var(--on-surface-variant);">Found by <?php echo sanitize($claim['finder_name'] ?? '—'); ?> (<?php echo sanitize($claim['finder_email'] ?? '—'); ?>)</div>
        </div>
        <?php if ($claim['item_id']): ?>
          <a class="btn btn-outline btn-sm" href="<?php echo appUrl('admin/item-view.php?id=' . $claim['item_id']); ?>">View Item</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>