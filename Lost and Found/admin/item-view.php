<?php
/**
 * Admin - Item Detail View
 */
$activePage = 'items';
$pageTitle  = 'Item Detail';
require_once __DIR__ . '/partials/header.php';

$db = getDB();
$itemId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare(
    'SELECT i.*, u.full_name AS reporter_name, u.email AS reporter_email, u.mobile AS reporter_mobile
     FROM items i
     LEFT JOIN users u ON u.id = i.user_id
     WHERE i.id = ?'
);
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    echo '<div class="admin-empty"><span class="material-symbols-outlined">search_off</span><p>Item not found.</p>';
    echo '<a class="btn btn-primary btn-sm btn-pill" href="' . appUrl('admin/items.php') . '">Back to Items</a></div>';
    require_once __DIR__ . '/partials/footer.php';
    exit;
}

// --- Claims made on this item ---
$stmt = $db->prepare(
    'SELECT c.*, u.full_name AS claimant_name, u.email AS claimant_email
     FROM claims c
     LEFT JOIN users u ON u.id = c.claimant_id
     WHERE c.item_id = ?
     ORDER BY c.created_at DESC'
);
$stmt->execute([$itemId]);
$claims = $stmt->fetchAll();

$images = array_filter([$item['image_1'], $item['image_2'], $item['image_3']]);
?>

<div style="max-width:980px;">

  <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/items.php'); ?>" style="margin-bottom:1rem;">
    <span class="material-symbols-outlined" style="font-size:16px">arrow_back</span> Back to Items
  </a>

  <div class="admin-head" style="margin-bottom:1.25rem;">
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
      <h1 class="font-headline-lg"><?php echo sanitize($item['title']); ?></h1>
      <span class="badge <?php echo $item['item_type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($item['item_type']); ?></span>
      <span class="status-tag status-tag--<?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span>
    </div>
    <div class="admin-actions">
      <form action="<?php echo appUrl('php/admin/item_action.php'); ?>" method="post">
        <?php echo csrfField(); ?>
        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
        <input type="hidden" name="action" value="<?php echo ($item['status'] === 'active' || $item['status'] === 'matched') ? 'resolve' : 'reopen'; ?>">
        <button type="submit" class="btn <?php echo ($item['status'] === 'active' || $item['status'] === 'matched') ? 'btn-primary' : 'btn-ghost'; ?> btn-sm">
          <?php echo ($item['status'] === 'active' || $item['status'] === 'matched') ? 'Mark Resolved' : 'Reopen'; ?>
        </button>
      </form>
      <form action="<?php echo appUrl('php/admin/item_action.php'); ?>" method="post" onsubmit="return confirm('Delete this item report? This cannot be undone.');">
        <?php echo csrfField(); ?>
        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn btn--danger btn-sm">Delete</button>
      </form>
    </div>
  </div>

  <?php if (!empty($images)): ?>
    <div class="detail-media" style="margin-bottom:1.5rem;">
      <?php foreach ($images as $img): ?>
        <img src="<?php echo appUrl('uploads/items/' . $img); ?>" alt="Item photo">
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="admin-card" style="margin-bottom:1.5rem;">
    <div class="admin-card__head"><h2 class="admin-card__title">Item Details</h2></div>
    <div style="padding:1.25rem;">
      <div class="detail-grid">
        <div>
          <div class="detail-item__label">Category</div>
          <div class="detail-item__value"><?php echo sanitize(categoryName($item['category'])); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Campus Zone</div>
          <div class="detail-item__value"><?php echo sanitize(zoneName($item['campus_zone'])); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Specific Location</div>
          <div class="detail-item__value"><?php echo sanitize($item['specific_location'] ?? '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Date Reported</div>
          <div class="detail-item__value"><?php echo sanitize($item['date_lost_found']); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Time Period</div>
          <div class="detail-item__value"><?php echo sanitize(ucfirst(str_replace('-', ' ', $item['time_period'] ?? 'unknown'))); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Brand / Model</div>
          <div class="detail-item__value"><?php echo sanitize($item['brand_model'] ?? '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Color</div>
          <div class="detail-item__value"><?php echo sanitize($item['color'] ?? '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Serial Number</div>
          <div class="detail-item__value"><?php echo sanitize($item['serial_number'] ?? '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Custody Status</div>
          <div class="detail-item__value"><?php echo sanitize($item['custody_status'] ? ucfirst(str_replace('-', ' ', $item['custody_status'])) : '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Views</div>
          <div class="detail-item__value"><?php echo number_format((int)$item['views']); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Created</div>
          <div class="detail-item__value"><?php echo timeAgo($item['created_at']); ?></div>
        </div>
      </div>
      <div style="margin-top:1.25rem;">
        <div class="detail-item__label">Description</div>
        <p style="margin-top:.375rem; line-height:1.6;"><?php echo nl2br(sanitize($item['description'])); ?></p>
      </div>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:1.5rem;">
    <div class="admin-card__head"><h2 class="admin-card__title">Reporter</h2></div>
    <div style="padding:1.25rem;">
      <div class="detail-grid">
        <div>
          <div class="detail-item__label">Name</div>
          <div class="detail-item__value"><?php echo sanitize($item['reporter_name'] ?? '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Email</div>
          <div class="detail-item__value"><?php echo sanitize($item['reporter_email'] ?? '—'); ?></div>
        </div>
        <div>
          <div class="detail-item__label">Mobile</div>
          <div class="detail-item__value"><?php echo sanitize($item['reporter_mobile'] ?? '—'); ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card__head"><h2 class="admin-card__title">Claims on this Item</h2></div>
    <?php if (empty($claims)): ?>
      <div class="admin-empty"><span class="material-symbols-outlined">gavel</span><p>No claims have been made on this item.</p></div>
    <?php else: ?>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Claimant</th>
              <th>Status</th>
              <th>Submitted</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($claims as $claim): ?>
              <tr>
                <td>
                  <div class="font-body-sm"><?php echo sanitize($claim['claimant_name'] ?? 'Unknown'); ?></div>
                  <div class="font-body-sm" style="color:var(--on-surface-variant); font-size:.6875rem;"><?php echo sanitize($claim['claimant_email'] ?? ''); ?></div>
                </td>
                <td><span class="status-tag status-tag--<?php echo $claim['status']; ?>"><?php echo ucfirst($claim['status']); ?></span></td>
                <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($claim['created_at']); ?></td>
                <td>
                  <div class="admin-actions" style="justify-content:flex-end;">
                    <a class="btn btn-outline btn-sm" href="<?php echo appUrl('admin/claim-view.php?id=' . $claim['id']); ?>">View</a>
                    <?php if ($claim['status'] === 'pending'): ?>
                      <form action="<?php echo appUrl('php/admin/claim_review.php'); ?>" method="post">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                      </form>
                      <form action="<?php echo appUrl('php/admin/claim_review.php'); ?>" method="post">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn--danger btn-sm">Reject</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>