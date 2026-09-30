<?php
/**
 * Admin Panel - Shared Header Shell
 * Every admin page must set $activePage and $pageTitle before including this file.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$currentUser = getCurrentUser();
$pageTitle   = $pageTitle ?? 'Admin Dashboard';
$activePage  = $activePage ?? 'dashboard';

$db = getDB();

// Live counts for the notification dropdown (all real DB values)
$notify = ['claims' => 0, 'redemptions' => 0];
try {
    $notify['claims']       = (int)$db->query('SELECT COUNT(*) FROM claims WHERE status = "pending"')->fetchColumn();
    $notify['redemptions']  = (int)$db->query('SELECT COUNT(*) FROM redemptions WHERE status = "pending"')->fetchColumn();
} catch (Exception $e) {
    error_log('Admin notification counts failed: ' . $e->getMessage());
}
$notifyTotal = $notify['claims'] + $notify['redemptions'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo sanitize($pageTitle); ?> — Admin | Foubdly</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo appUrl('css/style.css?v=20260929'); ?>">
  <link rel="stylesheet" href="<?php echo appUrl('css/admin.css?v=20260929'); ?>">
  <link rel="stylesheet" href="<?php echo appUrl('css/responsive.css?v=20260929'); ?>">
  <script>
    (function(){try{var t=localStorage.getItem('foubdly-theme');var s=(t==='light'||t==='dark')?t:(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',s);if('style' in document.documentElement&&'colorScheme' in document.documentElement.style)document.documentElement.style.colorScheme=s;}catch(e){}})();
  </script>
  <link rel="icon" type="image/svg+xml" href="<?php echo appUrl('assets/images/icons/favicon.svg'); ?>">   <link rel="icon" type="image/png" sizes="32x32" href="<?php echo appUrl('assets/images/icons/favicon-32x32.png'); ?>">   <link rel="apple-touch-icon" href="<?php echo appUrl('assets/images/icons/apple-touch-icon.png'); ?>">
</head>
<body class="admin-body">

  <header class="admin-topbar">
    <a class="admin-topbar__brand" href="<?php echo appUrl('admin/index.php'); ?>">
      <div class="admin-brand-icon"><span class="material-symbols-outlined">search</span></div>
      <div>
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:.9375rem;line-height:1.2;">Foubdly</div>
        <div style="font-size:.6875rem;color:var(--on-surface-variant);">Lost &amp; Found Portal</div>
      </div>
    </a>

    <form class="admin-topbar__search" action="<?php echo appUrl('admin/items.php'); ?>" method="get" role="search">
      <span class="material-symbols-outlined" style="font-size:18px;color:var(--on-surface-variant);">search</span>
      <input type="text" name="q" placeholder="Search item reports&hellip;" aria-label="Search item reports"
             value="<?php echo sanitize($_GET['q'] ?? ''); ?>">
      <input type="hidden" name="s" value="<?php echo sanitize($_GET['s'] ?? 'all'); ?>">
    </form>

    <div class="admin-topbar__actions">
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch to dark mode" aria-pressed="false" title="Switch to dark mode">
        <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="4"></circle>
          <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
        </svg>
        <span class="theme-toggle__track" aria-hidden="true"></span>
        <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>

      <a class="btn btn-ghost btn-sm max-sm:hidden" href="<?php echo appUrl('index.php'); ?>">
        <span class="material-symbols-outlined" style="font-size:16px">public</span> View Site
      </a>

      <div class="admin-drop">
        <button type="button" class="admin-bell" data-drop-toggle aria-label="Notifications"
                title="<?php echo $notifyTotal; ?> pending action(s)">
          <span class="material-symbols-outlined" style="font-size:20px">notifications</span>
          <?php if ($notifyTotal > 0): ?><span class="admin-bell__badge"><?php echo $notifyTotal > 9 ? '9+' : $notifyTotal; ?></span><?php endif; ?>
        </button>
        <div class="admin-drop__panel admin-drop__panel--right">
          <div class="admin-drop__title">Pending Actions</div>
          <a class="admin-drop__item" href="<?php echo appUrl('admin/claims.php?s=pending'); ?>">
            <span class="material-symbols-outlined">gavel</span>
            <span><span class="admin-drop__count"><?php echo $notify['claims']; ?></span> claims awaiting review</span>
          </a>
          <a class="admin-drop__item" href="<?php echo appUrl('admin/rewards.php#redemptions'); ?>">
            <span class="material-symbols-outlined">redeem</span>
            <span><span class="admin-drop__count"><?php echo $notify['redemptions']; ?></span> redemptions pending</span>
          </a>
        </div>
      </div>

      <div class="admin-drop">
        <button type="button" class="admin-avatar-btn" data-drop-toggle aria-label="Admin profile">
          <span class="avatar" style="width:30px;height:30px;font-size:.6875rem;"><?php echo strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?></span>
          <span class="material-symbols-outlined" style="font-size:16px;color:var(--on-surface-variant);">expand_more</span>
        </button>
        <div class="admin-drop__panel admin-drop__panel--right">
          <div class="admin-drop__user">
            <div style="font-weight:700;font-size:.8125rem;"><?php echo sanitize($currentUser['full_name'] ?? ''); ?></div>
            <div style="font-size:.6875rem;color:var(--on-surface-variant);"><?php echo sanitize($currentUser['email'] ?? ''); ?></div>
          </div>
          <a class="admin-drop__item" href="<?php echo appUrl('admin/user-view.php?id=' . (int)$currentUser['id']); ?>">
            <span class="material-symbols-outlined">person</span> My Profile
          </a>
          <a class="admin-drop__item" href="<?php echo appUrl('index.php'); ?>">
            <span class="material-symbols-outlined">public</span> View Site
          </a>
          <a class="admin-drop__item" href="<?php echo appUrl('php/auth/logout.php'); ?>">
            <span class="material-symbols-outlined">logout</span> Logout
          </a>
        </div>
      </div>
    </div>
  </header>

  <div class="admin-shell">
    <nav class="admin-mobilenav" aria-label="Admin sections">
      <a class="admin-mchip <?php echo $activePage === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/index.php'); ?>">Dashboard</a>
      <a class="admin-mchip <?php echo $activePage === 'items' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/items.php'); ?>">Items</a>
      <a class="admin-mchip <?php echo $activePage === 'claims' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/claims.php'); ?>">Claims</a>
      <a class="admin-mchip <?php echo $activePage === 'users' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/users.php'); ?>">Users</a>
      <a class="admin-mchip <?php echo $activePage === 'rewards' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/rewards.php'); ?>">Rewards</a>
    </nav>

    <aside class="admin-sidebar">
      <a class="admin-nav__link <?php echo $activePage === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/index.php'); ?>">
        <span class="material-symbols-outlined">dashboard</span> Dashboard
      </a>
      <a class="admin-nav__link <?php echo $activePage === 'items' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/items.php'); ?>">
        <span class="material-symbols-outlined">inventory_2</span> Items
      </a>
      <a class="admin-nav__link <?php echo $activePage === 'claims' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/claims.php'); ?>">
        <span class="material-symbols-outlined">gavel</span> Claims
      </a>
      <a class="admin-nav__link <?php echo $activePage === 'users' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/users.php'); ?>">
        <span class="material-symbols-outlined">group</span> Users
      </a>
      <a class="admin-nav__link <?php echo $activePage === 'rewards' ? 'is-active' : ''; ?>" href="<?php echo appUrl('admin/rewards.php'); ?>">
        <span class="material-symbols-outlined">redeem</span> Rewards
      </a>

      <div class="admin-sidebar__spacer"></div>

      <a class="admin-nav__link" href="<?php echo appUrl('php/auth/logout.php'); ?>">
        <span class="material-symbols-outlined">logout</span> Logout
      </a>

      <div class="admin-sidebar__user">
        <div class="avatar"><?php echo strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?></div>
        <div style="min-width:0;">
          <div style="font-weight:700;font-size:.8125rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo sanitize($currentUser['full_name'] ?? ''); ?></div>
          <div style="font-size:.6875rem;color:var(--on-surface-variant);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo sanitize($currentUser['email'] ?? ''); ?></div>
        </div>
      </div>
    </aside>

    <main class="admin-main">
      <?php displayFlash(); ?>