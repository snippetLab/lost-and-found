<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$currentUser = isLoggedIn() ? getCurrentUser() : null;

$stats = [];
try {
    $stats['total_items'] = (int)$db->query('SELECT COUNT(*) FROM items')->fetchColumn();
    $stats['returned']    = (int)$db->query('SELECT COUNT(*) FROM items WHERE status = "returned"')->fetchColumn();
    $stats['total_users'] = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Exception $e) {
    error_log('About stats query failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#4648d4" />
  <meta name="description" content="About Foubdly — the campus lost and found portal." />
  <title>About Us — Campus Lost &amp; Found Portal</title>
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

  <?php $activeNav = 'about'; require __DIR__ . '/includes/partials/site_header.php'; ?>

  <main class="container py-xl">

    <nav class="breadcrumb mb-lg" aria-label="Breadcrumb">
      <a href="<?php echo appUrl('index.php'); ?>">Home</a>
      <span style="color:var(--outline-variant);">/</span>
      <span class="current">About Us</span>
    </nav>

    <?php displayFlash(); ?>

    <section class="flex flex-col items-center gap-md text-center mb-xl">
      <span class="badge badge-primary">
        <span class="material-symbols-outlined" style="font-size:14px;">group</span>
        About Foubdly
      </span>
      <h1 class="font-display-hero">Reuniting campus with what it lost</h1>
      <p class="font-body-lg text-on-surface-variant max-w-2xl">Foubdly is the official lost and found portal for the university campus. We keep every lost and found report in one searchable registry, so nothing stays missing for long.</p>
    </section>

    <section class="grid grid-cols-1 gap-md sm:grid-cols-3 mb-xl">
      <div class="card p-lg flex flex-col items-center gap-xs text-center">
        <p class="font-headline-lg font-extrabold"><?php echo number_format($stats['total_items']); ?></p>
        <p class="font-label-md font-semibold text-on-surface">Items Reported</p>
      </div>
      <div class="card p-lg flex flex-col items-center gap-xs text-center">
        <p class="font-headline-lg font-extrabold"><?php echo number_format($stats['returned']); ?></p>
        <p class="font-label-md font-semibold text-on-surface">Items Recovered</p>
      </div>
      <div class="card p-lg flex flex-col items-center gap-xs text-center">
        <p class="font-headline-lg font-extrabold"><?php echo number_format($stats['total_users']); ?></p>
        <p class="font-label-md font-semibold text-on-surface">Campus Users</p>
      </div>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-lg mb-xl">

      <div class="card-static" style="border-radius:1rem; padding:1.5rem;">
        <div class="flex items-center gap-sm mb-md">
          <span class="material-symbols-outlined" style="font-size:22px; color:var(--primary);">flag</span>
          <h2 class="font-headline-md">Our mission</h2>
        </div>
        <p class="font-body-md text-on-surface-variant" style="line-height:1.8;">
          When something is lost on campus, every minute matters. Foubdly was built to give students, staff, and faculty a single place to report what they lost or found, and to connect both sides through verified custody handovers at the campus desk.
        </p>
      </div>

      <div class="card-static" style="border-radius:1rem; padding:1.5rem;">
        <div class="flex items-center gap-sm mb-md">
          <span class="material-symbols-outlined" style="font-size:22px; color:var(--primary);">how_to_reg</span>
          <h2 class="font-headline-md">How it works</h2>
        </div>
        <ul class="flex flex-col gap-md" style="list-style:none;">
          <li class="flex items-start gap-sm">
            <span class="material-symbols-outlined text-status-found" style="font-size:20px;">edit_note</span>
            <span class="font-body-md text-on-surface-variant">Report a lost or found item with details, photos, and the campus zone.</span>
          </li>
          <li class="flex items-start gap-sm">
            <span class="material-symbols-outlined text-status-found" style="font-size:20px;">manage_search</span>
            <span class="font-body-md text-on-surface-variant">Search or browse the registry to spot a potential match.</span>
          </li>
          <li class="flex items-start gap-sm">
            <span class="material-symbols-outlined text-status-found" style="font-size:20px;">task_alt</span>
            <span class="font-body-md text-on-surface-variant">Verify ownership at the custody desk and recover your item.</span>
          </li>
        </ul>
      </div>

    </section>

    <section class="card overflow-hidden" style="border-radius:1.25rem;">
      <div style="background:linear-gradient(120deg, var(--primary) 0%, var(--brand-violet) 100%); padding:2rem; display:flex; flex-direction:column; align-items:center; gap:0.875rem; text-align:center;">
        <h2 class="font-headline-lg text-white">Want to help?</h2>
        <p class="font-body-md text-white opacity-85 max-w-xl">A found item reported on Foubdly is an item with a real chance of getting home. Take two minutes to log what you find.</p>
        <div class="flex flex-wrap items-center justify-center gap-sm">
          <a href="report-found.php" class="btn btn-pill" style="background:var(--surface-card); color:var(--primary);">Report a Found Item</a>
          <a href="browse.php" class="btn btn-pill" style="background:rgba(255,255,255,0.18); color:#fff;">Browse the Registry</a>
        </div>
      </div>
    </section>

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
</body>
</html>