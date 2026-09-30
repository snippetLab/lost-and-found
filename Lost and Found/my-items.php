<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = getDB();
$userId = getCurrentUserId();
$currentUser = getCurrentUser();

// User's reported items
$myItems = [];
try {
    $stmt = $db->prepare('SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    $myItems = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('My items query failed: ' . $e->getMessage());
}

// Claims the user has submitted (on other people's found items)
$myClaims = [];
try {
    $stmt = $db->prepare(
        'SELECT c.*, i.title AS item_title, i.image_1, i.campus_zone, u.full_name AS finder_name
         FROM claims c
         LEFT JOIN items i ON i.id = c.item_id
         LEFT JOIN users u ON u.id = i.user_id
         WHERE c.claimant_id = ?
         ORDER BY c.created_at DESC'
    );
    $stmt->execute([$userId]);
    $myClaims = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('My claims query failed: ' . $e->getMessage());
}

// Claims made against the user's own found items
$incomingClaims = [];
try {
    $stmt = $db->prepare(
        'SELECT c.*, i.title AS item_title, i.image_1, u.full_name AS claimant_name
         FROM claims c
         LEFT JOIN items i ON i.id = c.item_id
         LEFT JOIN users u ON u.id = c.claimant_id
         WHERE i.user_id = ?
         ORDER BY c.created_at DESC'
    );
    $stmt->execute([$userId]);
    $incomingClaims = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Incoming claims query failed: ' . $e->getMessage());
}

// Dashboard summary counts
$lostReportCount = 0; $foundReportCount = 0;
foreach ($myItems as $it) {
    if ($it['item_type'] === 'lost') { $lostReportCount++; } else { $foundReportCount++; }
}
$activityClaimCount = count($myClaims) + count($incomingClaims);

function statusTone(string $status): string {
    switch ($status) {
        case 'active':   return 'var(--primary)';
        case 'matched':  return 'var(--brand-violet)';
        case 'returned': return 'var(--status-found)';
        default:         return 'var(--on-surface-variant)';
    }
}
function claimTone(string $status): string {
    switch ($status) {
        case 'pending':  return 'var(--status-lost)';
        case 'approved': return 'var(--status-found)';
        case 'rejected': return 'var(--status-lost)';
        default:         return 'var(--on-surface-variant)';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reports — Campus Lost &amp; Found Portal</title>
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
      <span class="current">My Reports</span>
    </nav>

    <?php displayFlash(); ?>

    <!-- Your Activity -->
    <div class="kpi-grid kpi-grid--4" style="margin-bottom:1.5rem;">
      <div class="kpi-card">
        <div class="kpi-card__header">
          <span class="kpi-card__icon kpi-card__icon--warning"><span class="material-symbols-outlined">search</span></span>
        </div>
        <div class="kpi-card__value"><?php echo $lostReportCount; ?></div>
        <div class="kpi-card__label">Lost Reports</div>
        <div class="kpi-card__sub">Your lost items</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-card__header">
          <span class="kpi-card__icon kpi-card__icon--primary"><span class="material-symbols-outlined">inventory_2</span></span>
        </div>
        <div class="kpi-card__value"><?php echo $foundReportCount; ?></div>
        <div class="kpi-card__label">Found Reports</div>
        <div class="kpi-card__sub">Items you picked up</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-card__header">
          <span class="kpi-card__icon kpi-card__icon--info"><span class="material-symbols-outlined">pending_actions</span></span>
        </div>
        <div class="kpi-card__value"><?php echo $activityClaimCount; ?></div>
        <div class="kpi-card__label">Claims</div>
        <div class="kpi-card__sub">Made &amp; received</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-card__header">
          <span class="kpi-card__icon kpi-card__icon--success"><span class="material-symbols-outlined">star</span></span>
          <a href="points.php" class="font-body-sm" style="color:var(--primary); font-weight:600;">View Rewards</a>
        </div>
        <div class="kpi-card__value"><?php echo number_format((int)$currentUser['points_balance']); ?></div>
        <div class="kpi-card__label">Points</div>
        <div class="kpi-card__sub">Reward balance</div>
      </div>
    </div>

    <!-- Section: My Reported Items -->
    <section>
      <div class="flex items-center justify-between flex-wrap gap-sm mb-md">
        <h1 class="font-headline-lg">My Reported Items</h1>
        <div class="flex gap-sm">
          <a href="report-lost.php" class="btn btn-lost btn-sm btn-pill">
            <span class="material-symbols-outlined" style="font-size:16px">add</span> Report Lost
          </a>
          <a href="report-found.php" class="btn btn-found btn-sm btn-pill">
            <span class="material-symbols-outlined" style="font-size:16px">add</span> Report Found
          </a>
        </div>
      </div>

      <?php if (empty($myItems)): ?>
        <div class="card p-xl flex flex-col items-center gap-md text-center" style="padding:2rem;">
          <span class="material-symbols-outlined" style="font-size:48px; color:var(--primary);">inbox</span>
          <h3 class="font-headline-md">No reports yet</h3>
          <p class="font-body-sm text-on-surface-variant">Report a lost or found item to get started.</p>
        </div>
      <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-lg">
        <?php foreach ($myItems as $item): ?>
        <article class="card">
          <div style="aspect-ratio:16/9; position:relative; border-radius:1rem 1rem 0 0; overflow:hidden; background:var(--surface-container-high);">
            <?php if ($item['image_1']): ?>
              <img src="uploads/items/<?php echo sanitize($item['image_1']); ?>" alt="<?php echo sanitize($item['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <div class="flex items-center justify-center" style="height:100%;">
                <span class="material-symbols-outlined" style="font-size:48px; color:var(--primary);"><?php echo $item['item_type'] === 'lost' ? 'search' : 'inventory_2'; ?></span>
              </div>
            <?php endif; ?>
            <div style="position:absolute; top:10px; left:10px; display:flex; gap:6px;">
              <span class="badge <?php echo $item['item_type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($item['item_type']); ?></span>
              <span class="badge badge-primary"><?php echo ucfirst($item['status']); ?></span>
            </div>
          </div>
          <div style="padding:1rem; display:flex; flex-direction:column; gap:0.5rem;">
            <h3 class="font-headline-sm"><?php echo sanitize($item['title']); ?></h3>
            <p class="font-body-sm" style="color:var(--on-surface-variant);">
              <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">place</span>
              <?php echo sanitize(zoneName($item['campus_zone'])); ?>
            </p>
            <p class="font-body-sm" style="color:var(--on-surface-variant);">Submitted <?php echo timeAgo($item['created_at']); ?></p>
            <div class="flex items-center justify-between gap-sm" style="margin-top:0.5rem;">
              <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm btn-pill">View</a>
              <form action="php/items/delete_item.php" method="post" onsubmit="return confirm('Delete this item?');" style="display:inline;">
                <?php echo csrfField(); ?>
                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                <button type="submit" class="btn btn-ghost btn-sm">
                  <span class="material-symbols-outlined" style="font-size:16px">delete</span>
                </button>
              </form>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>

    <!-- Section: Claims on my items -->
    <?php if (!empty($incomingClaims)): ?>
    <section style="margin-top:2rem;">
      <h2 class="font-headline-lg">Claims on My Items</h2>
      <div style="display:flex; flex-direction:column; gap:0.75rem; margin-top:1rem;">
        <?php foreach ($incomingClaims as $claim): ?>
        <div class="card" style="padding:1rem; display:flex; align-items:start; justify-content:space-between; gap:1rem;">
          <div style="flex:1; min-width:0;">
            <div class="flex items-center gap-sm">
              <span class="font-label-lg"><?php echo sanitize($claim['item_title']); ?></span>
              <span class="badge badge-primary" style="font-size:0.625rem;"><?php echo ucfirst($claim['status']); ?></span>
            </div>
            <p class="font-body-sm" style="color:var(--on-surface-variant); margin-top:0.375rem;"><?php echo sanitize($claim['description']); ?></p>
            <span class="font-body-sm" style="color:var(--on-surface-variant);">By <?php echo sanitize($claim['claimant_name'] ?? 'Anonymous'); ?> &middot; <?php echo timeAgo($claim['created_at']); ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- Section: My Submitted Claims -->
    <section style="margin-top:2rem;">
      <h2 class="font-headline-lg">My Submitted Claims</h2>
      <?php if (empty($myClaims)): ?>
        <div class="card p-xl flex flex-col items-center gap-md text-center" style="padding:2rem; margin-top:1rem;">
          <span class="material-symbols-outlined" style="font-size:48px; color:var(--primary);">gavel</span>
          <h3 class="font-headline-md">No claims submitted</h3>
          <p class="font-body-sm text-on-surface-variant">Browse found items and submit a claim on something that belongs to you.</p>
          <a href="browse.php" class="btn btn-primary btn-sm btn-pill">Browse Found Items</a>
        </div>
      <?php else: ?>
      <div style="display:flex; flex-direction:column; gap:0.75rem; margin-top:1rem;">
        <?php foreach ($myClaims as $claim): ?>
        <div class="card" style="padding:1rem; display:flex; align-items:start; justify-content:space-between; gap:1rem;">
          <div style="flex:1; min-width:0;">
            <div class="flex items-center gap-sm">
              <span class="font-label-lg"><?php echo sanitize($claim['item_title'] ?? 'Item'); ?></span>
              <span class="badge" style="font-size:0.625rem; background:<?php echo claimTone($claim['status']); ?>; color:#fff;"><?php echo strtoupper($claim['status']); ?></span>
            </div>
            <p class="font-body-sm" style="color:var(--on-surface-variant); margin-top:0.375rem;"><?php echo sanitize($claim['description']); ?></p>
            <span class="font-body-sm" style="color:var(--on-surface-variant);">Submitted <?php echo timeAgo($claim['created_at']); ?></span>
          </div>
          <div style="flex-shrink:0; display:flex; flex-direction:column; align-items:flex-end; gap:0.375rem;">
            <a href="item-detail.php?id=<?php echo $claim['item_id']; ?>" class="btn btn-outline btn-sm btn-pill">View Item</a>
            <span class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize(zoneName($claim['campus_zone'] ?? '')); ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
</body>
</html>