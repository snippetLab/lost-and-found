<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$currentUser = isLoggedIn() ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#4648d4" />
  <meta name="description" content="Contact the campus lost and found desk." />
  <title>Contact — Campus Lost &amp; Found Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css?v=20260929">
  <link rel="stylesheet" href="css/responsive.css?v=20260929" />
<script>
    (function(){try{var t=localStorage.getItem('foubdly-theme');var s=(t==='light'||t==='dark')?t:(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',s);if('style' in document.documentElement&&'colorScheme' in document.documentElement.style)document.documentElement.style.colorScheme=s;}catch(e){}})();
  </script>
  <link rel="icon" type="image/svg+xml" href="assets/images/icons/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/icons/favicon-32x32.png">
  <link rel="apple-touch-icon" href="assets/images/icons/apple-touch-icon.png">
</head>
<body>

  <?php $activeNav = 'contact'; require __DIR__ . '/includes/partials/site_header.php'; ?>

  <main class="container py-xl">

    <nav class="breadcrumb mb-lg" aria-label="Breadcrumb">
      <a href="<?php echo appUrl('index.php'); ?>">Home</a>
      <span style="color:var(--outline-variant);">/</span>
      <span class="current">Contact</span>
    </nav>

    <?php displayFlash(); ?>

    <section class="flex flex-col items-center gap-md text-center mb-xl">
      <span class="badge badge-primary">
        <span class="material-symbols-outlined" style="font-size:14px;">contact_support</span>
        Contact Us
      </span>
      <h1 class="font-display-hero">We're here to help</h1>
      <p class="font-body-lg text-on-surface-variant max-w-2xl">Questions about a report, a match, or a custody handover? Reach the campus lost and found desk during operating hours.</p>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-md mb-xl">
      <div class="card p-lg flex flex-col items-center gap-sm text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-surface-container-high text-primary">
          <span class="material-symbols-outlined">meeting_room</span>
        </div>
        <h3 class="font-headline-sm">Visit the Desk</h3>
        <p class="font-body-sm text-on-surface-variant">Room 12, Main IT Admin Block, Campus</p>
      </div>
      <div class="card p-lg flex flex-col items-center gap-sm text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-surface-container-high text-primary">
          <span class="material-symbols-outlined">mail</span>
        </div>
        <h3 class="font-headline-sm">Email</h3>
        <p class="font-body-sm text-on-surface-variant">helpdesk@campus.edu</p>
      </div>
      <div class="card p-lg flex flex-col items-center gap-sm text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-surface-container-high text-primary">
          <span class="material-symbols-outlined">schedule</span>
        </div>
        <h3 class="font-headline-sm">Operating Hours</h3>
        <p class="font-body-sm text-on-surface-variant">Mon–Fri, 8 AM – 6 PM</p>
      </div>
      <div class="card p-lg flex flex-col items-center gap-sm text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-surface-container-high text-primary">
          <span class="material-symbols-outlined">list_alt</span>
        </div>
        <h3 class="font-headline-sm">Track a Report</h3>
        <p class="font-body-sm text-on-surface-variant">Sign in and open My Reports for status updates.</p>
      </div>
    </section>

    <section class="card-static" style="border-radius:1rem; padding:1.5rem;">
      <div class="flex flex-col gap-md">
        <div class="flex items-start gap-md">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:var(--surface-container-high);">
            <span class="material-symbols-outlined text-primary" style="font-size:22px;">help_center</span>
          </div>
          <div>
            <h3 class="font-headline-sm">Before you visit</h3>
            <p class="font-body-md text-on-surface-variant mt-2">Bring a valid student or staff ID. For password or account issues, visit the IT Help Desk in person; account support is not handled via inbox.</p>
          </div>
        </div>
        <div class="flex items-center gap-sm flex-wrap">
          <a href="<?php echo appUrl('browse.php'); ?>" class="btn btn-outline btn-sm">
            <span class="material-symbols-outlined" style="font-size:16px;">manage_search</span>
            Browse Items
          </a>
          <a href="<?php echo appUrl('about.php'); ?>" class="btn btn-outline btn-sm">
            <span class="material-symbols-outlined" style="font-size:16px;">info</span>
            About Us
          </a>
        </div>
      </div>
    </section>

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
</body>
</html>