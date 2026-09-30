<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$currentUser = isLoggedIn() ? getCurrentUser() : null;
$itemId = (int)($_GET['id'] ?? 0);

if ($itemId <= 0) {
    setFlash('error', 'Item not found.');
    header('Location: ' . appUrl('browse.php'));
    exit;
}

$stmt = $db->prepare('SELECT i.*, u.full_name AS reporter_name, u.email AS reporter_email FROM items i LEFT JOIN users u ON u.id = i.user_id WHERE i.id = ?');
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('error', 'Item not found.');
    header('Location: ' . appUrl('browse.php'));
    exit;
}

// Increment view count (best effort)
$db->prepare('UPDATE items SET views = views + 1 WHERE id = ?')->execute([$itemId]);

// Load claims for this item (only if item owner or admin is viewing)
$claims = [];
$isOwner = $currentUser && ($item['user_id'] == $currentUser['id']);
$canManage = isAdmin() || $isOwner;
if ($canManage) {
    $stmt = $db->prepare('SELECT c.*, u.full_name AS claimant_name FROM claims c LEFT JOIN users u ON u.id = c.claimant_id WHERE c.item_id = ? ORDER BY c.created_at DESC');
    $stmt->execute([$itemId]);
    $claims = $stmt->fetchAll();
}

// Check if current user has already claimed this item
$hasPendingClaim = false;
if ($currentUser) {
    $stmt = $db->prepare('SELECT id FROM claims WHERE item_id = ? AND claimant_id = ? AND status = ?');
    $stmt->execute([$itemId, $currentUser['id'], 'pending']);
    $hasPendingClaim = (bool)$stmt->fetch();
}

$itemType = $item['item_type'];
$pageTitle = ucfirst($itemType) . ' Item: ' . $item['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo sanitize($pageTitle); ?> — Campus Lost &amp; Found Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260929">
  <link rel="stylesheet" href="css/responsive.css?v=20260929">
<script>
    (function(){try{var t=localStorage.getItem('foubdly-theme');var s=(t==='light'||t==='dark')?t:(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',s);if('style' in document.documentElement&&'colorScheme' in document.documentElement.style)document.documentElement.style.colorScheme=s;}catch(e){}})();
  </script>
  <link rel="icon" type="image/svg+xml" href="assets/images/icons/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/icons/favicon-32x32.png">
  <link rel="apple-touch-icon" href="assets/images/icons/apple-touch-icon.png">
</head>
<body>

  <?php $activeNav = 'browse'; require __DIR__ . '/includes/partials/site_header.php'; ?>

  <main class="container py-xl">

    <nav class="breadcrumb mb-lg" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <span style="color:var(--outline-variant);">/</span>
      <a href="browse.php">Browse</a>
      <span style="color:var(--outline-variant);">/</span>
      <span class="current"><?php echo sanitize($item['title']); ?></span>
    </nav>

    <?php displayFlash(); ?>

    <div class="flex flex-col lg:grid lg:grid-cols-12 gap-xl">

      <!-- Main Detail Column -->
      <div class="lg:col-span-8" style="display:flex; flex-direction:column; gap:1.5rem;">

        <!-- Hero Image / Placeholder -->
        <div class="card-static" style="border-radius:1rem; overflow:hidden;">
          <?php if ($item['image_1']): ?>
            <div style="position:relative; aspect-ratio:16/10; background:var(--surface-container);">
              <img src="uploads/items/<?php echo sanitize($item['image_1']); ?>" alt="<?php echo sanitize($item['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
              <div style="position:absolute; top:12px; left:12px; display:flex; gap:8px;">
                <span class="badge <?php echo $itemType === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($itemType); ?></span>
                <?php if ($item['status'] !== 'active'): ?>
                  <span class="badge badge-verified"><?php echo ucfirst($item['status']); ?></span>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div style="aspect-ratio:16/10; background:var(--surface-container-high); display:flex; align-items:center; justify-content:center; position:relative;">
              <span class="material-symbols-outlined" style="font-size:72px; color:var(--primary);"><?php echo $itemType === 'lost' ? 'search' : 'inventory_2'; ?></span>
              <div style="position:absolute; top:12px; left:12px; display:flex; gap:8px;">
                <span class="badge <?php echo $itemType === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($itemType); ?></span>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Item Details Card -->
        <div class="card-static" style="border-radius:1rem; padding:1.5rem;">
          <div style="display:flex; flex-direction:column; gap:1rem;">
            <div class="flex items-start justify-between gap-md">
              <div>
                <h1 class="font-headline-lg"><?php echo sanitize($item['title']); ?></h1>
                <div class="flex flex-wrap gap-sm mt-1">
                  <span class="badge badge-primary"><span class="material-symbols-outlined" style="font-size:12px">sell</span><?php echo sanitize(categoryName($item['category'])); ?></span>
                  <span class="badge badge-verified"><span class="material-symbols-outlined" style="font-size:12px">place</span><?php echo sanitize(zoneName($item['campus_zone'])); ?></span>
                </div>
              </div>
            </div>

            <p class="font-body-md" style="color:var(--on-surface-variant); line-height:1.7;">
              <?php echo nl2br(sanitize($item['description'])); ?>
            </p>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:0.75rem; padding-top:0.5rem; border-top:1px solid var(--border-subtle);">
              <?php if ($item['brand_model']): ?>
                <div class="flex items-center gap-sm">
                  <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">info</span>
                  <div>
                    <div class="font-label-sm" style="color:var(--on-surface-variant);">Brand / Model</div>
                    <div class="font-body-sm"><?php echo sanitize($item['brand_model']); ?></div>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($item['color']): ?>
                <div class="flex items-center gap-sm">
                  <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">palette</span>
                  <div>
                    <div class="font-label-sm" style="color:var(--on-surface-variant);">Color</div>
                    <div class="font-body-sm"><?php echo sanitize($item['color']); ?></div>
                  </div>
                </div>
              <?php endif; ?>
              <div class="flex items-center gap-sm">
                <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">calendar_today</span>
                <div>
                  <div class="font-label-sm" style="color:var(--on-surface-variant);"><?php echo $itemType === 'lost' ? 'Date Lost' : 'Date Found'; ?></div>
                  <div class="font-body-sm"><?php echo sanitize($item['date_lost_found']); ?></div>
                </div>
              </div>
              <?php if ($item['time_period']): ?>
                <div class="flex items-center gap-sm">
                  <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">schedule</span>
                  <div>
                    <div class="font-label-sm" style="color:var(--on-surface-variant);">Time Period</div>
                    <div class="font-body-sm"><?php echo sanitize(ucfirst($item['time_period'])); ?></div>
                  </div>
                </div>
              <?php endif; ?>
              <div class="flex items-center gap-sm">
                <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">place</span>
                <div>
                  <div class="font-label-sm" style="color:var(--on-surface-variant);">Location</div>
                  <div class="font-body-sm"><?php echo sanitize(zoneName($item['campus_zone'])); ?><?php if ($item['specific_location']): ?>, <?php echo sanitize($item['specific_location']); ?><?php endif; ?></div>
                </div>
              </div>
              <?php if ($item['serial_number']): ?>
                <div class="flex items-center gap-sm">
                  <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">tag</span>
                  <div>
                    <div class="font-label-sm" style="color:var(--on-surface-variant);">Serial / ID</div>
                    <div class="font-body-sm"><?php echo sanitize($item['serial_number']); ?></div>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($itemType === 'found' && $item['custody_status']): ?>
                <div class="flex items-center gap-sm">
                  <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">lock</span>
                  <div>
                    <div class="font-label-sm" style="color:var(--on-surface-variant);">Custody</div>
                    <div class="font-body-sm"><?php echo sanitize(ucfirst(str_replace('-', ' ', $item['custody_status']))); ?></div>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="flex items-center justify-between flex-wrap gap-sm" style="padding-top:0.5rem; border-top:1px solid var(--border-subtle);">
              <span class="font-body-sm" style="color:var(--on-surface-variant);">
                Reported <?php echo timeAgo($item['created_at']); ?> by <?php echo sanitize($item['reporter_name'] ?? 'Anonymous'); ?>
                &middot; <?php echo number_format($item['views']); ?> views
              </span>
            </div>
          </div>
        </div>

        <!-- Claim Form (only for found items) -->
        <?php if ($itemType === 'found' && $item['status'] === 'active'): ?>
        <div class="card-static" style="border-radius:1rem; padding:1.5rem;">
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:22px; color:var(--primary);">handshake</span>
            <h2 class="font-headline-md">Claim This Item</h2>
          </div>

          <?php if (!$currentUser): ?>
            <div style="padding:1.25rem; border-radius:12px; background:var(--surface-container-high); display:flex; flex-direction:column; align-items:center; gap:0.75rem; text-align:center;">
              <span class="material-symbols-outlined" style="font-size:36px; color:var(--primary);">login</span>
              <p class="font-body-md" style="color:var(--on-surface-variant);">You must be signed in to submit a claim.</p>
              <a href="sign-in.php" class="btn btn-primary btn-pill">Sign In to Claim</a>
            </div>
          <?php elseif ($hasPendingClaim): ?>
            <div style="padding:1.25rem; border-radius:12px; background:var(--status-found-bg); display:flex; align-items:center; gap:0.75rem;">
              <span class="material-symbols-outlined" style="font-size:22px; color:var(--status-found);">check_circle</span>
              <p class="font-body-md" style="color:var(--status-found); font-weight:600;">You already have a pending claim for this item. You will be notified once reviewed.</p>
            </div>
          <?php elseif ($currentUser && ($item['user_id'] == $currentUser['id'])): ?>
            <div style="padding:1.25rem; border-radius:12px; background:var(--surface-container-high); display:flex; align-items:center; gap:0.75rem;">
              <span class="material-symbols-outlined" style="font-size:22px; color:var(--on-surface-variant);">info</span>
              <p class="font-body-md" style="color:var(--on-surface-variant);">This is your own found report. You cannot claim your own item.</p>
            </div>
          <?php else: ?>
            <form action="php/items/claim.php" method="post" style="display:flex; flex-direction:column; gap:1rem;">
              <?php echo csrfField(); ?>
              <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
              <div class="form-group">
                <label class="form-label" for="claim_description">
                  Why does this belong to you? <span style="color:var(--status-lost);">*</span>
                </label>
                <textarea name="claim_description" id="claim_description" class="form-textarea" rows="4" placeholder="Describe distinguishing features, serial numbers, or details that prove ownership (min 10 characters)." minlength="10" required></textarea>
              </div>
              <div class="form-group">
                <label class="form-label" for="evidence_details">Evidence / Reference (optional)</label>
                <input type="text" name="evidence_details" id="evidence_details" class="form-input" placeholder="e.g., Screenshot of purchase receipt, photo of item in your room...">
              </div>
              <button type="submit" class="btn btn-primary btn-pill" style="align-self:flex-start;">
                <span class="material-symbols-outlined" style="font-size:18px;">send</span>
                Submit Claim
              </button>
            </form>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Admin/Owner: Claims Management -->
        <?php if ($canManage && !empty($claims)): ?>
        <div class="card-static" style="border-radius:1rem; padding:1.5rem;">
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:22px; color:var(--primary);">gavel</span>
            <h2 class="font-headline-md">Claims (<?php echo count($claims); ?>)</h2>
          </div>
          <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <?php foreach ($claims as $claim): ?>
              <div style="display:flex; align-items:start; justify-content:space-between; gap:1rem; padding:0.75rem; border-radius:12px; background:var(--surface-container-low);">
                <div style="display:flex; flex-direction:column; gap:0.25rem; flex:1; min-width:0;">
                  <div class="flex items-center gap-sm">
                    <span class="font-label-lg"><?php echo sanitize($claim['claimant_name'] ?? 'Anonymous'); ?></span>
                    <span class="badge <?php echo $claim['status'] === 'pending' ? 'badge-lost' : ($claim['status'] === 'approved' ? 'badge-found' : 'badge-primary'); ?>" style="font-size:0.625rem; padding:0.125rem 0.375rem;"><?php echo ucfirst($claim['status']); ?></span>
                  </div>
                  <p class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize($claim['description']); ?></p>
                  <?php if ($claim['evidence_details']): ?>
                    <span class="font-body-sm" style="color:var(--primary);">Evidence: <?php echo sanitize($claim['evidence_details']); ?></span>
                  <?php endif; ?>
                  <span class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($claim['created_at']); ?></span>
                </div>
                <?php if (isAdmin() && $claim['status'] === 'pending'): ?>
                  <div class="flex gap-sm" style="flex-shrink:0;">
                    <form action="php/admin/claim_review.php" method="post" style="display:inline;">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
                      <input type="hidden" name="action" value="approve">
                      <button type="submit" class="btn btn-found btn-sm btn-pill">Approve</button>
                    </form>
                    <form action="php/admin/claim_review.php" method="post" style="display:inline;">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
                      <input type="hidden" name="action" value="reject">
                      <button type="submit" class="btn btn-lost btn-sm btn-pill">Reject</button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

      </div>

      <!-- Sidebar Column -->
      <div class="lg:col-span-4" style="display:flex; flex-direction:column; gap:1.5rem;">

        <!-- Reporter Info -->
        <div class="card-static" style="border-radius:1rem; padding:1.25rem;">
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:20px; color:var(--primary);">person</span>
            <h3 class="font-headline-sm">Reported by</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:0.375rem;">
            <div class="font-label-lg"><?php echo sanitize($item['reporter_name'] ?? 'Anonymous'); ?></div>
            <div class="font-body-sm" style="color:var(--on-surface-variant);">
              Reported <?php echo timeAgo($item['created_at']); ?>
            </div>
            <div class="font-body-sm" style="color:var(--on-surface-variant);">
              Status: <span style="font-weight:600; color:<?php echo $item['status'] === 'active' ? 'var(--primary)' : ($item['status'] === 'returned' ? 'var(--status-found)' : 'var(--on-surface)'); ?>;"><?php echo ucfirst($item['status']); ?></span>
            </div>
          </div>
        </div>

        <!-- Custody Desk -->
        <div style="border-radius:1rem; padding:1.25rem; background:var(--inverse-surface); color:var(--inverse-on-surface); display:flex; flex-direction:column; gap:0.75rem;">
          <div class="flex items-center gap-sm">
            <span class="material-symbols-outlined" style="font-size:20px;">shield</span>
            <h3 class="font-headline-sm" style="color:#fff;">Physical Custody Desk</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:0.5rem;">
            <div class="flex items-center gap-sm">
              <span class="material-symbols-outlined" style="font-size:16px; color:var(--inverse-primary);">location_on</span>
              <span class="font-body-sm" style="color:var(--inverse-on-surface);">IT Admin Building Room 12</span>
            </div>
            <div class="flex items-center gap-sm">
              <span class="material-symbols-outlined" style="font-size:16px; color:var(--inverse-primary);">call</span>
              <span class="font-body-sm" style="color:var(--inverse-on-surface);">03000000000</span>
            </div>
            <div class="flex items-center gap-sm">
              <span class="material-symbols-outlined" style="font-size:16px; color:var(--inverse-primary);">schedule</span>
              <span class="font-body-sm" style="color:var(--inverse-on-surface);">Mon&ndash;Fri, 8 AM &ndash; 7 PM</span>
            </div>
          </div>
          <a href="tel:03000000000" class="btn btn-pill" style="background:var(--inverse-primary); color:var(--inverse-surface); font-size:0.75rem; padding:0.5rem 1rem; text-align:center; display:block;">
            <span class="material-symbols-outlined" style="font-size:16px;">call</span>
            Call Custody Desk
          </a>
        </div>

        <!-- Actions -->
        <div class="card-static" style="border-radius:1rem; padding:1.25rem;">
          <div class="flex flex-col gap-sm">
            <a href="browse.php" class="btn btn-ghost btn-sm btn-pill" style="justify-content:center;">
              <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
              Back to Browse
            </a>
            <?php if ($isOwner): ?>
              <form action="php/items/delete_item.php" method="post" onsubmit="return confirm('Are you sure you want to delete this item?');">
                <?php echo csrfField(); ?>
                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                <button type="submit" class="btn btn-lost btn-sm btn-pill" style="width:100%;">
                  <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                  Delete Report
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
</body>
</html>