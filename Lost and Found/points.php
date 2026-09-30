<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = getDB();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Points balance & transaction history
$transactions = [];
try {
    $stmt = $db->prepare('SELECT * FROM points_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
    $stmt->execute([$userId]);
    $transactions = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Points history failed: ' . $e->getMessage());
}

// Available rewards
$rewards = [];
try {
    $rewards = $db->query('SELECT * FROM rewards WHERE status = "active" ORDER BY points_cost ASC')->fetchAll();
} catch (Exception $e) {
    error_log('Rewards query failed: ' . $e->getMessage());
}

// User's redemptions
$redemptions = [];
try {
    $stmt = $db->prepare(
        'SELECT r.*, w.name AS reward_name FROM redemptions r
         LEFT JOIN rewards w ON w.id = r.reward_id
         WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 20'
    );
    $stmt->execute([$userId]);
    $redemptions = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Redemptions query failed: ' . $e->getMessage());
}

$balance = (int)$currentUser['points_balance'];
$redeemed = isset($_GET['redeemed']);
$redeemedReward = trim((string)($_GET['reward'] ?? ''));

function txnTone(string $type): string {
    return in_array($type, ['earned', 'bonus']) ? 'var(--status-found)' : 'var(--status-lost)';
}
function txnSign(string $type, int $points): string {
    return in_array($type, ['earned', 'bonus']) ? '+' . $points : '-' . abs($points);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Points &amp; Rewards — Campus Lost &amp; Found Portal</title>
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

  <?php require __DIR__ . '/includes/partials/site_header.php'; ?>

  <main class="container py-xl">

    <nav class="breadcrumb mb-lg" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <span style="color:var(--outline-variant);">/</span>
      <span class="current">Points &amp; Rewards</span>
    </nav>

    <?php displayFlash(); ?>

    <!-- Balance Banner -->
    <div class="card-static mb-lg" style="border-radius:1rem; overflow:hidden; background:linear-gradient(120deg, var(--primary), var(--brand-violet)); color:#fff;">
      <div style="padding:1.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div>
          <div class="flex items-center gap-sm">
            <span class="material-symbols-outlined" style="font-size:22px;">stars</span>
            <span class="font-body-sm" style="opacity:0.85;">Your Campus Rewards Balance</span>
          </div>
          <div class="font-headline-lg" style="font-size:2rem; font-weight:800;"><?php echo number_format($balance); ?> points</div>
          <p class="font-body-sm" style="opacity:0.85;">Earn points by helping reunite items, spend them on campus rewards.</p>
        </div>
      </div>
    </div>

    <div class="flex flex-col lg:grid lg:grid-cols-12 gap-xl">

      <!-- Rewards Catalog -->
      <div class="lg:col-span-8" style="display:flex; flex-direction:column; gap:1.5rem;">
        <section>
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:22px; color:var(--primary);">redeem</span>
            <h2 class="font-headline-lg">Redeem Rewards</h2>
          </div>
          <?php if (empty($rewards)): ?>
            <div class="card p-xl flex flex-col items-center gap-md text-center" style="padding:2rem;">
              <span class="material-symbols-outlined" style="font-size:48px; color:var(--primary);">card_giftcard</span>
              <p class="font-body-sm text-on-surface-variant">No rewards are currently available.</p>
            </div>
          <?php else: ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
            <?php foreach ($rewards as $reward): ?>
              <div class="card" style="padding:1.25rem; display:flex; flex-direction:column; gap:0.75rem;">
                <div style="width:52px; height:52px; border-radius:14px; background:var(--surface-container-high); display:flex; align-items:center; justify-content:center;">
                  <span class="material-symbols-outlined" style="font-size:28px; color:var(--primary);">card_giftcard</span>
                </div>
                <div>
                  <h3 class="font-headline-sm"><?php echo sanitize($reward['name']); ?></h3>
                  <p class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize($reward['description']); ?></p>
                </div>
                <span class="badge badge-primary" style="align-self:flex-start;"><?php echo (int)$reward['points_cost']; ?> points</span>
                <?php $canAfford = $balance >= (int)$reward['points_cost']; $inStock = (int)$reward['stock'] > 0; ?>
                <form action="php/points/redeem.php" method="post" class="redeem-form" data-reward-name="<?php echo sanitize($reward['name']); ?>" data-cost="<?php echo (int)$reward['points_cost']; ?>" data-balance="<?php echo $balance; ?>" style="margin-top:0.25rem;">
                  <?php echo csrfField(); ?>
                  <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                  <?php if (!$inStock): ?>
                    <button type="button" class="btn btn-ghost btn-sm btn-pill" disabled>
                      <span class="material-symbols-outlined" style="font-size:16px">block</span>
                      Out of Stock
                    </button>
                  <?php elseif (!$canAfford): ?>
                    <button type="button" class="btn btn-ghost btn-sm btn-pill js-open-insufficient">
                      <span class="material-symbols-outlined" style="font-size:16px">lock</span>
                      Not Enough Points
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn btn-primary btn-sm btn-pill js-open-redeem">
                      <span class="material-symbols-outlined" style="font-size:16px">redeem</span>
                      Redeem Now
                    </button>
                  <?php endif; ?>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </section>

        <!-- How to Earn Points -->
        <section>
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:22px; color:var(--status-found);">school</span>
            <h2 class="font-headline-lg">How to Earn Points</h2>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-md">
            <?php
            $earnSteps = [
                ['person_add', 'Join Foubdly', 'Create an account to start with 10 bonus points.'],
                ['manage_search', 'Find an Item', 'Browse lost &amp; found reports and match an item to its owner.'],
                ['task_alt', 'Verify &amp; Return', 'Confirm your find and help reunite it; earn 50 points once a claim is approved.'],
                ['redeem', 'Spend Them', 'Exchange points for caf&eacute; coupons, campus passes and merch.'],
            ];
            foreach ($earnSteps as $i => $step): ?>
              <div class="card" style="padding:1.25rem; display:flex; flex-direction:column; gap:0.75rem; align-items:flex-start;">
                <div style="width:44px; height:44px; border-radius:12px; background:var(--surface-container-high); display:flex; align-items:center; justify-content:center;">
                  <span class="material-symbols-outlined" style="font-size:24px; color:var(--primary);"><?php echo $step[0]; ?></span>
                </div>
                <div>
                  <div class="font-label-lg" style="color:var(--primary);">Step <?php echo $i + 1; ?></div>
                  <h3 class="font-headline-sm" style="margin:0.125rem 0;"><?php echo $step[1]; ?></h3>
                  <p class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo $step[2]; ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- Redemption History -->
        <?php if (!empty($redemptions)): ?>
        <section>
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:22px; color:var(--status-found);">history</span>
            <h2 class="font-headline-lg">My Redemptions</h2>
          </div>
          <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <?php foreach ($redemptions as $red): ?>
              <div class="card" style="padding:1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                <div>
                  <span class="font-label-lg"><?php echo sanitize($red['reward_name'] ?? 'Reward'); ?></span>
                  <span class="badge badge-primary" style="font-size:0.625rem; margin-left:0.5rem;"><?php echo ucfirst($red['status']); ?></span>
                </div>
                <div style="text-align:right;">
                  <div class="font-label-lg" style="color:var(--status-lost);">-<?php echo (int)$red['points_spent']; ?></div>
                  <div class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($red['created_at']); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>
      </div>

      <!-- Transaction History -->
      <aside class="lg:col-span-4">
        <div class="card-static" style="border-radius:1rem; padding:1.25rem;">
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:22px; color:var(--primary);">receipt_long</span>
            <h2 class="font-headline-md">Point History</h2>
          </div>
          <?php if (empty($transactions)): ?>
            <p class="font-body-sm" style="color:var(--on-surface-variant); text-align:center; padding:1rem 0;">No point activity yet. Earn your first points by helping reunite an item!</p>
          <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:0.5rem; max-height:600px; overflow-y:auto;">
            <?php foreach ($transactions as $txn): ?>
              <div style="padding:0.75rem; border-radius:12px; background:var(--surface-container-low); display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem;">
                <div style="min-width:0;">
                  <span class="font-body-sm" style="color:var(--on-surface);"><?php echo sanitize($txn['description']); ?></span>
                  <div class="font-body-sm" style="color:var(--on-surface-variant); font-size:0.65rem;"><?php echo date('M j, Y g:i A', strtotime($txn['created_at'])); ?></div>
                </div>
                <span class="font-label-lg" style="color:<?php echo txnTone($txn['transaction_type']); ?>; flex-shrink:0;"><?php echo txnSign($txn['transaction_type'], (int)$txn['points']); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </aside>

    </div>

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <!-- Redeem Confirmation Modal -->
  <div class="modal-overlay" id="redeemConfirmModal" role="dialog" aria-modal="true" aria-labelledby="redeemConfirmTitle">
    <div class="modal-content">
      <span class="material-symbols-outlined" style="font-size:44px; color:var(--primary);">redeem</span>
      <h2 id="redeemConfirmTitle" class="font-headline-md">Redeem Reward?</h2>
      <p class="font-body-sm" style="color:var(--on-surface-variant);">
        You are about to redeem <strong id="redeemConfirmName" style="color:var(--on-surface);"></strong> for
        <strong id="redeemConfirmCost" style="color:var(--primary);"></strong> points.
        After redemption your remaining balance will be <strong id="redeemConfirmRemaining" style="color:var(--on-surface);"></strong> points.
      </p>
      <div style="display:flex; gap:0.75rem; width:100%;">
        <button type="button" class="btn btn-ghost btn-pill" onclick="closeModal('redeemConfirmModal')" style="flex:1;">Cancel</button>
        <button type="button" id="redeemConfirmSubmit" class="btn btn-primary btn-pill" style="flex:1;">Confirm Redemption</button>
      </div>
    </div>
  </div>

  <!-- Insufficient Points Modal -->
  <div class="modal-overlay" id="insufficientModal" role="dialog" aria-modal="true" aria-labelledby="insufficientTitle">
    <div class="modal-content">
      <span class="material-symbols-outlined" style="font-size:44px; color:var(--status-lost);">lock</span>
      <h2 id="insufficientTitle" class="font-headline-md">Not Enough Points</h2>
      <p class="font-body-sm" style="color:var(--on-surface-variant);">
        <strong id="insufficientName" style="color:var(--on-surface);"></strong> costs
        <strong id="insufficientCost" style="color:var(--primary);"></strong> points, but your current balance is
        <strong id="insufficientBalance" style="color:var(--on-surface);"></strong>.
        Earn more points by helping reunite lost items.
      </p>
      <div style="display:flex; gap:0.75rem; width:100%;">
        <button type="button" class="btn btn-ghost btn-pill" onclick="closeModal('insufficientModal')" style="flex:1;">Close</button>
        <a href="browse.php" class="btn btn-primary btn-pill" style="flex:1; text-decoration:none; text-align:center;">Find an Item</a>
      </div>
    </div>
  </div>

  <!-- Redemption Success Modal -->
  <div class="modal-overlay<?php echo $redeemed ? ' active' : ''; ?>" id="redeemSuccessModal" role="dialog" aria-modal="true" aria-labelledby="redeemSuccessTitle" data-reward-name="<?php echo $redeemed ? sanitize($redeemedReward) : ''; ?>" data-balance="<?php echo number_format($balance); ?>">
    <div class="modal-content">
      <span class="material-symbols-outlined" style="font-size:44px; color:var(--status-found);">verified</span>
      <h2 id="redeemSuccessTitle" class="font-headline-md">Reward Redeemed!</h2>
      <p class="font-body-sm" style="color:var(--on-surface-variant);">
        <strong id="redeemSuccessName" style="color:var(--on-surface);"></strong> has been reserved for you.
        Show your campus ID to collect it. Your new balance is
        <strong id="redeemSuccessBalance" style="color:var(--primary);"></strong> points.
      </p>
      <div style="display:flex; gap:0.75rem; width:100%;">
        <button type="button" class="btn btn-ghost btn-pill" onclick="closeModal('redeemSuccessModal')" style="flex:1;">Continue</button>
      </div>
    </div>
  </div>

  <script src="js/main.js?v=20260929"></script>
  <script src="js/rewards.js"></script>
</body>
</html>