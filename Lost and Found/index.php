<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$currentUser = isLoggedIn() ? getCurrentUser() : null;

// Fetch live stats (all real DB counts)
$stats = ['total_items' => 0, 'found_items' => 0, 'returned' => 0, 'total_users' => 0];
try {
    $stats['total_items']  = (int)$db->query('SELECT COUNT(*) FROM items')->fetchColumn();
    $stats['found_items']  = (int)$db->query('SELECT COUNT(*) FROM items WHERE item_type = "found"')->fetchColumn();
    $stats['returned']     = (int)$db->query('SELECT COUNT(*) FROM items WHERE status = "returned"')->fetchColumn();
    $stats['total_users']  = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Exception $e) {
    error_log('Stats query failed: ' . $e->getMessage());
}

// Fetch recent reports (up to 20)
$recentItems = [];
try {
    $stmt = $db->query('SELECT i.*, u.full_name FROM items i LEFT JOIN users u ON u.id = i.user_id ORDER BY i.created_at DESC LIMIT 20');
    $recentItems = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Recent items query failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#4648d4" />
  <meta name="description" content="The official Campus Lost & Found Portal. Search the campus-wide registry, report lost or found items, and recover what's yours — fast, verified, and secure." />
  <title>Campus Lost & Found Portal — Find What You Lost</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="css/style.css?v=20260929">
  <link rel="stylesheet" href="css/responsive.css?v=20260929" />
  

  <style>
    .material-symbols-outlined {
      font-family: 'Material Symbols Outlined';
      font-weight: normal;
      font-style: normal;
      font-size: 24px;
      line-height: 1;
      letter-spacing: normal;
      text-transform: none;
      display: inline-block;
      white-space: nowrap;
      word-wrap: normal;
      direction: ltr;
      -webkit-font-smoothing: antialiased;
      vertical-align: middle;
font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
  </style>
  <script>
    (function(){try{var t=localStorage.getItem('foubdly-theme');var s=(t==='light'||t==='dark')?t:(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',s);if('style' in document.documentElement&&'colorScheme' in document.documentElement.style)document.documentElement.style.colorScheme=s;}catch(e){}})();
  </script>
  <link rel="icon" type="image/svg+xml" href="assets/images/icons/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/icons/favicon-32x32.png">
  <link rel="apple-touch-icon" href="assets/images/icons/apple-touch-icon.png">
</head>
<body>

  <?php $activeNav = 'home'; require __DIR__ . '/includes/partials/site_header.php'; ?>

  <main>

<!-- ============================ HERO ============================ -->
    <section class="hero" aria-labelledby="hero-title">
      <div class="container">
        <div class="hero-grid">

          <div class="hero-copy">
            <span class="badge badge-primary">
              <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">verified_user</span>
              Campus Custody Desk &middot; Live Registry
            </span>

            <h1 class="font-display-hero" id="hero-title" style="margin-bottom:1.1rem;">
              Lost Something? <span class="text-primary">Found Something?</span>
            </h1>

            <p class="text-lg text-on-surface-variant" style="max-width:34rem; line-height:1.6; margin-bottom:1.5rem;">
              Whether you lost something or found something, report it here. We match both sides and help campus reunite with what's theirs.
            </p>

            <form class="search-bar" id="searchForm" role="search" method="get" action="browse.php">
          <div class="search-input-wrap">
            <span class="material-symbols-outlined" style="font-size:20px; color: var(--on-surface-variant);">search</span>
            <input type="text" id="searchInput" name="q" placeholder="Search for an item, keyword, or tag&hellip;" aria-label="Search campus lost and found items" />
          </div>

          <select id="typeSelect" name="type" class="form-select hidden sm:block" aria-label="Filter by type" style="width:auto; min-width:150px; flex:0 0 auto; cursor:pointer; background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2716%27 height=%2716%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23767886%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27m6 9 6 6 6-6%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 12px center;">
            <option value="all">All Types</option>
            <option value="lost">Lost Items</option>
            <option value="found">Found Items</option>
          </select>

          <select id="categorySelect" name="category" class="form-select hidden sm:block" aria-label="Filter by category" style="width:auto; min-width:150px; flex:0 0 auto; cursor:pointer; background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2716%27 height=%2716%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23767886%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27m6 9 6 6 6-6%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 12px center;">
            <option value="">All Categories</option>
            <option value="electronics">Electronics</option>
            <option value="accessories">Accessories</option>
            <option value="id-cards">Documents &amp; IDs</option>
            <option value="keys">Keys &amp; Keychains</option>
            <option value="keys-fobs">Keys &amp; FOBs</option>
            <option value="backpacks">Backpacks</option>
            <option value="bags-packs">Bags &amp; Cases</option>
            <option value="books">Books &amp; Notebooks</option>
            <option value="book-pens">Books &amp; Pens</option>
            <option value="clothing">Clothing</option>
            <option value="wallets">Wallets</option>
            <option value="other">Other</option>
          </select>

          <select id="zoneSelect" name="zone" class="form-select hidden sm:block" aria-label="Filter by location" style="width:auto; min-width:150px; flex:0 0 auto; cursor:pointer; background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2716%27 height=%2716%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23767886%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27m6 9 6 6 6-6%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 12px center;">
            <option value="">All Zones</option>
            <option value="library">Library</option>
            <option value="engineering">Engineering</option>
            <option value="student-union">Student Union</option>
            <option value="science-hall">Science Hall</option>
            <option value="sports-complex">Sports Complex</option>
            <option value="dorms">Dorms</option>
            <option value="dining">Dining</option>
            <option value="campus-transit">Campus Transit</option>
            <option value="admin-building">Admin Building</option>
            <option value="central-library">Central Library</option>
            <option value="parking-area-b">Parking Area B</option>
            <option value="campus-green">Campus Green</option>
            <option value="arts-center">Arts Center</option>
<option value="athletic-center">Athletic Center</option>
            <option value="Cosunselling Room">Cosunselling Room</option>
            <option value="Music Room">Music Room</option>
            <option value="Badminton Court">Badminton Court</option>
            <option value="M S Dhoni Cricket Wicket">M S Dhoni Cricket Wicket</option>
            <option value="Janardhan Singh Traditional Sports Arena - A">Janardhan Singh Traditional Sports Arena - A</option>
            <option value="Janardhan Singh Traditional Sports Arena - B">Janardhan Singh Traditional Sports Arena - B</option>
            <option value="Jimmy George Volleyball Court">Jimmy George Volleyball Court</option>
            <option value="Jimmy George Beach Volleyball Court">Jimmy George Beach Volleyball Court</option>
            <option value="K D Jadhav Sports Arena">K D Jadhav Sports Arena</option>
            <option value="Kapildev Cricket Ground">Kapildev Cricket Ground</option>
            <option value="Major Dhyanchand Sports Arena - Basketball">Major Dhyanchand Sports Arena - Basketball</option>
          </select>

          <select id="dateSelect" name="date" class="form-select hidden sm:block" aria-label="Filter by date" style="width:auto; min-width:150px; flex:0 0 auto; cursor:pointer; background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2716%27 height=%2716%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23767886%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27m6 9 6 6 6-6%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 12px center;">
            <option value="">Date Range</option>
            <option value="today">Today</option>
            <option value="3-days">Last 3 Days</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
          </select>

          <button type="submit" class="btn btn-primary btn-pill">
            <span class="material-symbols-outlined" style="font-size:18px;">search</span>
            Search
          </button>
        </form>

<div class="hero-popular flex flex-wrap items-center gap-sm">
          <span class="font-label-sm flex items-center gap-xs text-on-surface-variant">
            <span class="material-symbols-outlined" style="font-size:14px;">bolt</span>
            Popular
          </span>
          <button type="button" class="btn btn-outline btn-sm btn-pill" data-keyword="Student Card" data-category="id-cards">
            <span class="material-symbols-outlined" style="font-size:14px;">sell</span> Student Card
          </button>
          <button type="button" class="btn btn-outline btn-sm btn-pill" data-keyword="AirPods">
            <span class="material-symbols-outlined" style="font-size:14px;">headphones</span> AirPods
          </button>
          <button type="button" class="btn btn-outline btn-sm btn-pill" data-keyword="Dorm Keys" data-category="keys">
            <span class="material-symbols-outlined" style="font-size:14px;">key</span> Dorm Keys
          </button>
          <button type="button" class="btn btn-outline btn-sm btn-pill" data-keyword="Laptop Charger" data-category="electronics">
            <span class="material-symbols-outlined" style="font-size:14px;">power</span> Laptop Charger
          </button>
          <button type="button" class="btn btn-outline btn-sm btn-pill" data-keyword="Water Bottle">
            <span class="material-symbols-outlined" style="font-size:14px;">water_drop</span> Water Bottle
          </button>
        </div>
        </div>

        <?php $heroItem = !empty($recentItems) ? $recentItems[0] : null; ?>

        <div class="hero-visual" aria-hidden="true">
          <div class="hv-scene">
            <div class="hv-orb hv-orb--a"></div>
            <div class="hv-orb hv-orb--b"></div>

            <div class="hv-card">
              <div class="hv-card__scan"><span class="material-symbols-outlined">qr_code_scanner</span></div>
              <div class="hv-card__media">
                <?php if ($heroItem && !empty($heroItem['image_1'])): ?>
                  <img src="uploads/items/<?php echo sanitize($heroItem['image_1']); ?>" alt="<?php echo sanitize($heroItem['title']); ?>" loading="lazy">
                <?php else: ?>
                  <span class="material-symbols-outlined"><?php echo ($heroItem && $heroItem['item_type'] === 'lost') ? 'search' : 'inventory_2'; ?></span>
                <?php endif; ?>
              </div>
              <div class="hv-card__body">
                <span class="hv-card__title"><?php echo $heroItem ? sanitize($heroItem['title']) : 'Found item on campus'; ?></span>
                <span class="hv-card__meta">
                  <span class="material-symbols-outlined">place</span>
                  <?php echo $heroItem ? sanitize(zoneName($heroItem['campus_zone'])) : 'Central Library'; ?>
                  &middot; <?php echo $heroItem ? sanitize(timeAgo($heroItem['created_at'])) : 'Recently'; ?>
                </span>
              </div>
              <span class="hv-chip hv-chip--match"><span class="material-symbols-outlined">autorenew</span> Matching</span>
              <span class="hv-chip hv-chip--points"><span class="material-symbols-outlined">star</span> +25 points</span>
              <span class="hv-chip hv-chip--tag"><span class="material-symbols-outlined">sell</span>
                <?php echo $heroItem ? sanitize(categoryName($heroItem['category'])) : 'Personal Belongings'; ?>
              </span>
            </div>
          </div>
        </div>

      </div>
    </div>
    </section>

    <!-- ============================ QUICK ACTIONS ============================ -->
    <section class="container py-2xl">
      <div class="grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-4">
        <a href="report-lost.php" class="card p-lg flex items-center gap-md transition-all hover:shadow-md hover:-translate-y-1 no-underline">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl shrink-0" style="background: var(--status-lost-bg); color: var(--status-lost);">
            <span class="material-symbols-outlined">edit_note</span>
          </div>
          <div class="flex flex-col gap-xs">
            <span class="font-headline-sm">Report Lost</span>
            <span class="font-body-sm text-on-surface-variant">Tell us what went missing</span>
          </div>
          <span class="material-symbols-outlined ml-auto text-on-surface-variant">arrow_forward</span>
        </a>
        <a href="report-found.php" class="card p-lg flex items-center gap-md transition-all hover:shadow-md hover:-translate-y-1 no-underline">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl shrink-0" style="background: var(--status-found-bg); color: var(--status-found);">
            <span class="material-symbols-outlined">inventory_2</span>
          </div>
          <div class="flex flex-col gap-xs">
            <span class="font-headline-sm">Report Found</span>
            <span class="font-body-sm text-on-surface-variant">Log something you found</span>
          </div>
          <span class="material-symbols-outlined ml-auto text-on-surface-variant">arrow_forward</span>
        </a>
        <a href="browse.php" class="card p-lg flex items-center gap-md transition-all hover:shadow-md hover:-translate-y-1 no-underline">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl shrink-0" style="background: var(--surface-container-high); color: var(--primary);">
            <span class="material-symbols-outlined">manage_search</span>
          </div>
          <div class="flex flex-col gap-xs">
            <span class="font-headline-sm">Browse Items</span>
            <span class="font-body-sm text-on-surface-variant">Search the campus registry</span>
          </div>
          <span class="material-symbols-outlined ml-auto text-on-surface-variant">arrow_forward</span>
        </a>
        <a href="points.php" class="card p-lg flex items-center gap-md transition-all hover:shadow-md hover:-translate-y-1 no-underline">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl shrink-0" style="background: var(--status-verified-bg); color: var(--status-verified);">
            <span class="material-symbols-outlined">redeem</span>
          </div>
          <div class="flex flex-col gap-xs">
            <span class="font-headline-sm">Earn Points</span>
            <span class="font-body-sm text-on-surface-variant">Track rewards &amp; balance</span>
          </div>
          <span class="material-symbols-outlined ml-auto text-on-surface-variant">arrow_forward</span>
        </a>
      </div>
    </section>

    <!-- ============================ STATS ============================ -->
    <section class="container py-2xl">
      <div class="mb-xl flex flex-col items-center gap-md text-center">
        <span class="font-label-sm uppercase tracking-widest text-primary">Campus Recovery Stats</span>
        <h2 class="font-headline-lg">The desk, by the numbers</h2>
      </div>

      <div class="grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-lg flex flex-col items-center gap-xs text-center">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-violet-subtle text-brand-violet">
            <span class="material-symbols-outlined">dynamic_feed</span>
          </div>
          <p class="font-headline-lg font-extrabold mt-2 leading-tight"><?php echo number_format($stats['total_items']); ?></p>
          <p class="font-label-md font-semibold text-on-surface">Items Reported</p>
          <p class="font-body-sm text-on-surface-variant">Reports logged across every campus zone since launch.</p>
        </div>

        <div class="card p-lg flex flex-col items-center gap-xs text-center">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-status-found-bg text-status-found">
            <span class="material-symbols-outlined">verified</span>
          </div>
          <p class="font-headline-lg font-extrabold mt-2 leading-tight"><?php echo number_format($stats['returned']); ?></p>
          <p class="font-label-md font-semibold text-on-surface">Items Recovered</p>
          <p class="font-body-sm text-on-surface-variant">Confirmed reunions coordinated through the custody desk.</p>
        </div>

        <div class="card p-lg flex flex-col items-center gap-xs text-center">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-surface-container-high text-primary">
            <span class="material-symbols-outlined">inventory_2</span>
          </div>
          <p class="font-headline-lg font-extrabold mt-2 leading-tight"><?php echo number_format($stats['found_items']); ?></p>
          <p class="font-label-md font-semibold text-on-surface">Items Found</p>
          <p class="font-body-sm text-on-surface-variant">Found reports submitted by the community.</p>
        </div>

        <div class="card p-lg flex flex-col items-center gap-xs text-center">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-status-verified-bg text-status-verified">
            <span class="material-symbols-outlined">group</span>
          </div>
          <p class="font-headline-lg font-extrabold mt-2 leading-tight"><?php echo number_format($stats['total_users']); ?></p>
          <p class="font-label-md font-semibold text-on-surface">Campus Users</p>
          <p class="font-body-sm text-on-surface-variant">Students and staff helping reunite owners with their gear.</p>
        </div>
      </div>
    </section>

    <!-- ============================ RECENT REPORTS ============================ -->
    <section class="container py-2xl" id="recent-reports">
      <div class="flex flex-wrap items-end justify-between gap-lg">
        <div>
          <div class="flex items-center gap-sm">
            <span class="relative flex h-4 w-4 items-center justify-center">
              <span class="absolute h-full w-full animate-ping rounded-full bg-status-found opacity-75"></span>
              <span class="relative h-2 w-2 rounded-full bg-status-found"></span>
            </span>
            <span class="font-label-sm uppercase tracking-widest text-on-surface-variant">Live Campus Feed</span>
          </div>
          <h2 class="font-headline-lg mt-2">Recent Items</h2>
        </div>

        <div class="flex flex-wrap items-center gap-md">
          <div class="flex items-center gap-xs rounded-full bg-surface-ground p-xs" role="group" aria-label="Filter reports by status">
            <button type="button" class="btn btn-sm btn-pill filter-pill bg-primary text-on-primary shadow-sm active" data-filter="all">All</button>
            <button type="button" class="btn btn-sm btn-pill filter-pill bg-surface-ground text-on-surface-variant" data-filter="lost">Lost</button>
            <button type="button" class="btn btn-sm btn-pill filter-pill bg-surface-ground text-on-surface-variant" data-filter="found">Found</button>
          </div>
          <a href="browse.php" class="btn btn-ghost btn-sm btn-pill">
            View All Items
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
          </a>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-4 mt-4" id="feedGrid">

        <?php if (empty($recentItems)): ?>
          <div class="card p-xl flex flex-col items-center gap-md text-center col-span-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-surface-container text-primary">
              <span class="material-symbols-outlined">search_off</span>
            </div>
            <h3 class="font-headline-md">No reports yet</h3>
            <p class="font-body-sm text-on-surface-variant max-w-sm">Be the first to report a lost or found item on campus.</p>
            <a href="report-lost.php" class="btn btn-primary btn-sm btn-pill">Report an Item</a>
          </div>
        <?php else: foreach ($recentItems as $item): ?>
          <article class="card group flex flex-col transition-all hover:shadow-md hover:-translate-y-1" data-status="<?php echo $item['item_type']; ?>" data-keywords="<?php echo sanitize(strtolower($item['title'] . ' ' . $item['description'])); ?>">
            <div class="relative aspect-[16/10] w-full overflow-hidden bg-surface-subtle">
              <?php if ($item['image_1']): ?>
                <img src="uploads/items/<?php echo sanitize($item['image_1']); ?>" alt="<?php echo sanitize($item['title']); ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" />
              <?php else: ?>
                <div class="absolute inset-0 flex items-center justify-center" style="background:var(--surface-container-high);">
                  <span class="material-symbols-outlined" style="font-size:44px; color:var(--primary);"><?php echo $item['item_type'] === 'lost' ? 'search' : 'inventory_2'; ?></span>
                </div>
              <?php endif; ?>
              <div class="absolute left-2 top-2 z-10">
                <span class="badge <?php echo $item['item_type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($item['item_type']); ?></span>
              </div>
              <div class="absolute right-2 top-2 z-10">
                <span class="badge badge-verified" style="background:var(--photo-badge-bg);">
                  <span class="status-dot" style="background:<?php echo $item['status'] === 'returned' ? 'var(--status-found)' : ($item['status'] === 'matched' ? 'var(--brand-violet)' : 'var(--primary)'); ?>;"></span>
                  <?php echo ucfirst($item['status']); ?>
                </span>
              </div>
            </div>
            <div class="flex flex-1 flex-col p-lg">
              <h3 class="font-headline-sm line-clamp-1"><?php echo sanitize($item['title']); ?></h3>
              <p class="font-body-sm text-on-surface-variant mt-1 line-clamp-2"><?php echo sanitize($item['description']); ?></p>
              <div class="mt-3 flex flex-wrap gap-xs">
                <span class="badge badge-primary"><span class="material-symbols-outlined" style="font-size:12px;">sell</span><?php echo sanitize(categoryName($item['category'])); ?></span>
                <span class="badge badge-verified"><span class="material-symbols-outlined" style="font-size:12px;">place</span><?php echo sanitize(zoneName($item['campus_zone'])); ?></span>
              </div>
              <div class="mt-auto flex items-center justify-between gap-sm pt-4">
                <span class="font-label-sm text-on-surface-variant"><?php echo sanitize(timeAgo($item['created_at'])); ?></span>
                <a href="item-detail.php?id=<?php echo (int)$item['id']; ?>" class="btn btn-primary btn-sm btn-pill">View Details</a>
              </div>
            </div>
          </article>
        <?php endforeach; endif; ?>

      </div>

      <div id="emptyState" class="card hidden mt-4 flex flex-col items-center gap-md p-xl text-center">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-surface-container text-primary">
          <span class="material-symbols-outlined">manage_search</span>
        </div>
        <h3 class="font-headline-md">No matching reports right now</h3>
        <p class="font-body-sm text-on-surface-variant max-w-sm">Try a different keyword. New reports land on the live feed every few minutes.</p>
        <div class="flex flex-wrap items-center justify-center gap-sm">
          <button type="button" id="clearFiltersBtn" class="btn btn-primary btn-sm btn-pill">Clear Filters</button>
          <a href="browse.php" class="btn btn-outline btn-sm btn-pill">View All Items</a>
        </div>
      </div>
    </section>

    <!-- ============================ HOW IT WORKS ============================ -->
    <section class="container py-2xl" id="how-it-works">
      <div class="mb-xl flex flex-col items-center gap-md text-center">
        <span class="font-label-sm uppercase tracking-widest text-brand-violet">Standard Operating Procedure</span>
        <h2 class="font-headline-lg">How It Works</h2>
        <p class="font-body-md text-on-surface-variant mt-1 max-w-2xl">Four simple steps take you from &ldquo;where did I put it?&rdquo; to &ldquo;got it back&rdquo; &mdash; with the Campus Custody Desk handling every handover.</p>
      </div>

      <div class="grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-4">
        <div class="card flex flex-col items-start gap-sm p-lg">
          <span class="badge bg-brand-violet" style="color: #fff;">Step 1</span>
          <div class="mt-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-violet-subtle text-brand-violet">
            <span class="material-symbols-outlined">edit_note</span>
          </div>
          <h3 class="font-headline-sm mt-1">Report Item</h3>
          <p class="font-body-sm text-on-surface-variant">Submit a detailed report with photos, location, and time in under two minutes.</p>
        </div>

        <div class="card flex flex-col items-start gap-sm p-lg">
          <span class="badge bg-primary text-on-primary">Step 2</span>
          <div class="mt-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-surface-container-high text-primary">
            <span class="material-symbols-outlined">manage_search</span>
          </div>
          <h3 class="font-headline-sm mt-1">Search &amp; Match</h3>
          <p class="font-body-sm text-on-surface-variant">Search and filter the registry of lost and found entries across campus zones in real time.</p>
        </div>

        <div class="card flex flex-col items-start gap-sm p-lg">
          <span class="badge" style="background: var(--secondary); color: var(--on-secondary);">Step 3</span>
          <div class="mt-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-secondary-container" style="color: var(--on-secondary-container);">
            <span class="material-symbols-outlined">chat_bubble</span>
          </div>
          <h3 class="font-headline-sm mt-1">Connect Safely</h3>
          <p class="font-body-sm text-on-surface-variant">Claim a match or meet at the desk with your proof for a secure, private handover.</p>
        </div>

        <div class="card flex flex-col items-start gap-sm p-lg">
          <span class="badge bg-status-found text-on-secondary">Step 4</span>
          <div class="mt-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-status-found-bg text-status-found">
            <span class="material-symbols-outlined">task_alt</span>
          </div>
          <h3 class="font-headline-sm mt-1">Recover Item</h3>
          <p class="font-body-sm text-on-surface-variant">Confirm ownership at the custody desk, complete the release, and take your belongings home.</p>
        </div>
      </div>
    </section>

    <!-- ============================ COMMUNITY ============================ -->
    <section class="container py-2xl">
      <div class="overflow-hidden rounded-3xl" style="background: linear-gradient(120deg, var(--primary) 0%, var(--brand-violet) 100%); box-shadow: var(--shadow-lg);">
        <div class="flex flex-col items-center gap-xl p-xl text-center lg:flex-row lg:items-center lg:justify-between lg:text-left">
          <div class="flex max-w-2xl flex-col items-center gap-md lg:items-start">
            <span class="badge" style="background: rgba(255,255,255,0.18); color: #fff;">
              <span class="material-symbols-outlined" style="font-size:14px;">groups</span>
              Campus Community
            </span>
            <h2 class="font-headline-lg text-white">Small Actions, Big Difference</h2>
            <p class="font-body-md text-white opacity-80">Every found report you file is a chance for someone else to recover what matters. Reporting a left-behind wallet or a dropped ID card is a small act that makes campus a safer place for everyone.</p>
          </div>
          <div class="flex flex-col items-center gap-md lg:items-end">
            <a href="report-found.php" class="btn btn-pill hover:opacity-95" style="background: var(--surface-card); color: var(--primary); box-shadow: var(--shadow-md);">
              <span class="material-symbols-outlined" style="font-size:18px;">inventory_2</span>
              Report a Found Item
            </a>
            <a href="browse.php" class="btn btn-pill" style="background: rgba(255,255,255,0.18); color: #fff;">
              <span class="material-symbols-outlined" style="font-size:18px;">manage_search</span>
              Browse the Registry
            </a>
          </div>
        </div>
      </div>
    </section>

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
  <script>
  (function() {
    'use strict';

    var searchForm = document.getElementById('searchForm');
    var feedGrid = document.getElementById('feedGrid');
    var emptyState = document.getElementById('emptyState');
    var clearFiltersBtn = document.getElementById('clearFiltersBtn');
    var cards = Array.prototype.slice.call(document.querySelectorAll('#feedGrid [data-status]'));
    var filterPills = Array.prototype.slice.call(document.querySelectorAll('.filter-pill'));

    function getActiveFilter() {
      var active = filterPills.filter(function(p) { return p.classList.contains('active'); })[0];
      return active ? active.dataset.filter : 'all';
    }

    function setPills(filter) {
      filterPills.forEach(function(p) {
        var on = p.dataset.filter === filter;
        p.classList.toggle('active', on);
        p.classList.toggle('bg-primary', on);
        p.classList.toggle('text-on-primary', on);
        p.classList.toggle('shadow-sm', on);
        p.classList.toggle('bg-surface-ground', !on);
        p.classList.toggle('text-on-surface-variant', !on);
      });
    }

    function applyFilters() {
      var status = getActiveFilter();
      var visible = 0;
      cards.forEach(function(card) {
        var matchesStatus = status === 'all' || card.dataset.status === status;
        card.classList.toggle('hidden', !matchesStatus);
        if (matchesStatus) visible++;
      });
      if (emptyState) emptyState.classList.toggle('hidden', visible > 0);
    }

    filterPills.forEach(function(pill) {
      pill.addEventListener('click', function() {
        setPills(pill.dataset.filter);
        applyFilters();
      });
    });

    document.querySelectorAll('[data-keyword]').forEach(function(chip) {
      chip.addEventListener('click', function() {
        window.location.href = 'browse.php?q=' + encodeURIComponent(chip.dataset.keyword) + '&category=' + (chip.dataset.category || '');
      });
    });

    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', function() {
        setPills('all');
        applyFilters();
      });
    }
  })();
  </script>
</body>
</html>