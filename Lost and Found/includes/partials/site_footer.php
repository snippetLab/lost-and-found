<?php /* Shared site footer. Include after </main>, before the main.js script on pages that load it. */ ?>
<footer class="site-footer site-footer--v2">
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <a href="<?php echo appUrl('index.php'); ?>" class="header-logo">
          <span class="header-logo-icon"><span class="material-symbols-outlined" style="font-size:18px">search</span></span>
          <span class="font-headline-sm">Foubdly</span>
        </a>
        <p class="font-body-sm text-on-surface-variant" style="max-width:280px; margin-top:0.5rem; line-height:1.6">The official lost and found portal for your campus community. Report, track, and recover belongings.</p>
      </div>
      <div>
        <h4 class="font-label-md uppercase tracking-wider text-on-surface-variant" style="margin-bottom:0.75rem;">Quick Links</h4>
        <div class="flex flex-col gap-sm">
          <a href="<?php echo appUrl('browse.php'); ?>" class="font-body-sm text-on-surface-variant">Browse Items</a>
          <a href="<?php echo appUrl('report-lost.php'); ?>" class="font-body-sm text-on-surface-variant">Report Lost</a>
          <a href="<?php echo appUrl('report-found.php'); ?>" class="font-body-sm text-on-surface-variant">Report Found</a>
          <a href="<?php echo appUrl('points.php'); ?>" class="font-body-sm text-on-surface-variant">Points &amp; Rewards</a>
        </div>
      </div>
      <div>
        <h4 class="font-label-md uppercase tracking-wider text-on-surface-variant" style="margin-bottom:0.75rem;">Explore</h4>
        <div class="flex flex-col gap-sm">
          <a href="<?php echo appUrl('about.php'); ?>" class="font-body-sm text-on-surface-variant">About Us</a>
          <a href="<?php echo appUrl('contact.php'); ?>" class="font-body-sm text-on-surface-variant">Contact</a>
          <a href="<?php echo appUrl('my-items.php'); ?>" class="font-body-sm text-on-surface-variant">My Reports</a>
          <?php if (isLoggedIn() && isAdmin()): ?>
            <a href="<?php echo appUrl('admin/index.php'); ?>" class="font-body-sm text-on-surface-variant">Admin Panel</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?php echo date('Y'); ?> Foubdly. All rights reserved.</span>
    <span class="flex items-center gap-xs">
      <span class="status-dot"></span>
      System Operational
    </span>
  </div>
</footer>