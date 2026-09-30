<?php
/* Shared site header. Expects: $currentUser (may be null), $activeNav (optional string). */
$siteUser = $currentUser ?? (isLoggedIn() ? getCurrentUser() : null);
$activeNav = $activeNav ?? '';
?>
<header class="site-header site-header--float">
  <div class="header-inner">
    <a href="<?php echo appUrl('index.php'); ?>" class="header-logo">
      <div class="header-logo-icon"><span class="material-symbols-outlined" style="font-size:20px">search</span></div>
      <div>
        <div class="header-logo-text">Foubdly</div>
        <div class="header-logo-sub">Lost &amp; Found Portal</div>
      </div>
    </a>
    <nav class="header-nav">
      <a href="<?php echo appUrl('index.php'); ?>" class="<?php echo $activeNav === 'home' ? 'active' : ''; ?>">Home</a>
      <a href="<?php echo appUrl('browse.php'); ?>" class="<?php echo $activeNav === 'browse' ? 'active' : ''; ?>">Browse</a>
      <a href="<?php echo appUrl('report-lost.php'); ?>" class="<?php echo $activeNav === 'report-lost' ? 'active' : ''; ?>">Report Lost</a>
      <a href="<?php echo appUrl('report-found.php'); ?>" class="<?php echo $activeNav === 'report-found' ? 'active' : ''; ?>">Report Found</a>
      <a href="<?php echo appUrl('about.php'); ?>" class="<?php echo $activeNav === 'about' ? 'active' : ''; ?>">About Us</a>
      <a href="<?php echo appUrl('contact.php'); ?>" class="<?php echo $activeNav === 'contact' ? 'active' : ''; ?>">Contact</a>
    </nav>
    <div class="header-actions">
      <?php if ($siteUser): ?>
        <?php if (isAdmin()): ?>
          <a href="<?php echo appUrl('admin/index.php'); ?>" class="btn btn-ghost btn-sm max-sm:hidden">Admin</a>
        <?php endif; ?>
        <a href="<?php echo appUrl('points.php'); ?>" class="btn btn-ghost btn-sm max-sm:hidden" title="Reward points">
          <span class="material-symbols-outlined" style="font-size:16px">star</span>
          <?php echo number_format((int)$siteUser['points_balance']); ?>
        </a>
        <a href="<?php echo appUrl('profile.php'); ?>" class="btn btn-primary btn-sm max-sm:hidden"><?php echo sanitize($siteUser['full_name']); ?></a>
        <a href="<?php echo appUrl('php/auth/logout.php'); ?>" class="btn btn-ghost btn-sm max-sm:hidden">Logout</a>
      <?php else: ?>
        <a href="<?php echo appUrl('sign-in.php'); ?>" class="btn btn-ghost btn-sm max-sm:hidden">Login</a>
        <a href="<?php echo appUrl('sign-in.php?mode=register'); ?>" class="btn btn-primary btn-sm max-sm:hidden">Sign Up</a>
      <?php endif; ?>
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
      <button class="header-hamburger" aria-label="Toggle menu">
        <span class="material-symbols-outlined">menu</span>
      </button>
    </div>
  </div>
</header>