<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$currentUser = isLoggedIn() ? getCurrentUser() : null;

// --- Build filters from GET ---
$q         = trim($_GET['q'] ?? '');
$type      = trim($_GET['type'] ?? 'all');
$category  = trim($_GET['category'] ?? '');
$zone      = trim($_GET['zone'] ?? '');
$dateRange = trim($_GET['date'] ?? '');
$sort      = trim($_GET['sort'] ?? 'most-recent');
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 9;

// Use the items table alias consistently
$where  = ['i.status = "active"'];
$params = [];

if ($type === 'lost' || $type === 'found') {
    $where[] = 'i.item_type = ?';
    $params[] = $type;
}

if (!empty($category)) {
    $where[] = 'i.category = ?';
    $params[] = $category;
}

if (!empty($zone)) {
    $where[] = 'i.campus_zone = ?';
    $params[] = $zone;
}

if (!empty($q)) {
    $where[] = '(
        i.title LIKE ?
        OR i.description LIKE ?
        OR i.brand_model LIKE ?
        OR i.specific_location LIKE ?
    )';

    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}

if (!empty($dateRange)) {
    switch ($dateRange) {
        case 'today':
            $where[] = 'i.date_lost_found = CURDATE()';
            break;

        case '3-days':
            $where[] = 'i.date_lost_found >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)';
            break;

        case 'week':
            $where[] = 'i.date_lost_found >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
            break;

        case 'month':
            $where[] = 'i.date_lost_found >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
            break;
    }
}

$whereSql = implode(' AND ', $where);

// Count total
$countStmt = $db->prepare(
    'SELECT COUNT(*)
     FROM items i
     WHERE ' . $whereSql
);

$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();

// Build sort
$orderBy = 'i.created_at DESC';

switch ($sort) {
    case 'oldest':
        $orderBy = 'i.created_at ASC';
        break;

    case 'views':
        $orderBy = 'i.views DESC, i.created_at DESC';
        break;
}

$offset = ($page - 1) * $perPage;

// Fetch items
$stmt = $db->prepare(
    'SELECT i.*, u.full_name AS reporter_name
     FROM items i
     LEFT JOIN users u ON u.id = i.user_id
     WHERE ' . $whereSql . '
     ORDER BY ' . $orderBy . '
     LIMIT ' . $perPage . '
     OFFSET ' . $offset
);

$stmt->execute($params);
$items = $stmt->fetchAll();

$totalPages = max(1, (int)ceil($totalItems / $perPage));

// Total active items for header badge
$activeCount = (int)$db->query(
    'SELECT COUNT(*) FROM items WHERE status = "active"'
)->fetchColumn();

// Build search URLs preserving filters
function buildUrl(array $overrides = []): string {
    $params = $_GET;

    foreach ($overrides as $k => $v) {
        if ($v === '' || $v === null) {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }

    return 'browse.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Items — Campus Lost &amp; Found Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260929">
  <link rel="stylesheet" href="css/responsive.css?v=20260929">
  <style>
    .browse-headline { padding-top: var(--space-xl); padding-bottom: var(--space-sm); }
    .filter-segment { display: inline-flex; border-radius: var(--radius-full); overflow: hidden; border: 1px solid var(--border-subtle); background: var(--surface-container-low); }
    .filter-segment button {
      padding: 0.375rem 0.875rem; font-size: 0.75rem; font-weight: 600;
      color: var(--on-surface-variant); background: transparent; border: none;
      transition: all 0.2s ease; cursor: pointer; white-space: nowrap;
    }
    .filter-segment button.active { background: var(--primary); color: var(--on-primary); border-radius: var(--radius-full); }
    .filter-segment button:hover:not(.active) { background: var(--surface-container); }
    .filter-chip {
      display: inline-flex; align-items: center; gap: 0.25rem;
      padding: 0.25rem 0.625rem; border-radius: var(--radius-full);
      background: var(--surface-container-high); color: var(--primary);
      font-size: 0.6875rem; font-weight: 600;
    }
    .item-card { position: relative; overflow: hidden; }
    .item-card .card-image { position: relative; overflow: hidden; }
    .item-card .card-image img { transition: transform 0.3s ease; }
    .item-card:hover .card-image img { transform: scale(1.05); }
    .item-card .status-badge { position: absolute; top: 10px; left: 10px; z-index: 2; }
    .pulse-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: var(--status-found); display: inline-block;
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .status-pill {
      display: inline-flex; align-items: center; gap: 0.375rem;
      padding: 0.375rem 0.75rem; border-radius: var(--radius-full);
      background: var(--status-found-bg); color: var(--status-found);
      font-size: 0.75rem; font-weight: 600;
    }
    .sort-select, .filter-dropdown {
      appearance: none; -webkit-appearance: none; cursor: pointer;
      padding: 0.5rem 2rem 0.5rem 0.75rem; border-radius: 10px;
      background: var(--surface-container-low); color: var(--on-surface);
      font-size: 0.75rem; font-weight: 600; border: 1px solid var(--border-subtle);
      transition: all 0.2s ease; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23464554' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 0.5rem center;
    }
    .sort-select:focus, .filter-dropdown:focus { border-color: var(--primary); outline: none; }
    .alert-banner { position: relative; overflow: hidden; border-radius: 1rem; }
    .search-clear { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: var(--on-surface-variant); cursor: pointer; transition: all 0.2s ease; }
    .search-clear:hover { background: var(--surface-container-high); }
    .search-clear .material-symbols-outlined { font-size: 18px; }
    .kbd { display: inline-flex; align-items: center; gap: 0.125rem; padding: 0.125rem 0.375rem; border-radius: 6px; background: var(--surface-container); border: 1px solid var(--border-subtle); font-size: 0.625rem; font-weight: 600; color: var(--on-surface-variant); font-family: 'Inter', monospace; line-height: 1.4; }
  </style>
<script>
    (function(){try{var t=localStorage.getItem('foubdly-theme');var s=(t==='light'||t==='dark')?t:(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',s);if('style' in document.documentElement&&'colorScheme' in document.documentElement.style)document.documentElement.style.colorScheme=s;}catch(e){}})();
  </script>
  <link rel="icon" type="image/svg+xml" href="assets/images/icons/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/icons/favicon-32x32.png">
  <link rel="apple-touch-icon" href="assets/images/icons/apple-touch-icon.png">
</head>
<body>

  <?php $activeNav = 'browse'; require __DIR__ . '/includes/partials/site_header.php'; ?>

  <!-- ===== PAGE CONTENT ===== -->
  <main>
    <div class="container">

      <!-- Breadcrumb & Live Status -->
      <section class="pt-lg pb-sm" style="padding-top: var(--space-lg); padding-bottom: var(--space-sm);">
        <div class="flex items-center justify-between flex-wrap gap-sm">
          <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="material-symbols-outlined" style="font-size:14px;color:var(--outline-variant)">chevron_right</span>
            <span class="current">Browse Items</span>
          </nav>
          <div class="status-pill">
            <span class="pulse-dot"></span>
            <span><?php echo $activeCount; ?> Active Items Listed</span>
          </div>
        </div>
      </section>

      <!-- Headline Row -->
      <section class="browse-headline">
        <div class="flex items-center justify-between flex-wrap gap-lg">
          <div>
            <h1 class="font-display-hero">Browse Campus Lost &amp; Found</h1>
            <p class="font-body-lg text-on-surface-variant mt-1" style="margin-top: 0.375rem; max-width: 520px;">Search, filter, and track claimed and unclaimed items across all campus zones in real time.</p>
          </div>
          <div class="flex gap-sm flex-wrap">
            <a href="report-lost.php" class="btn btn-outline">
              <span class="material-symbols-outlined" style="font-size:18px">add</span>
              Report Lost Item
            </a>
            <a href="report-found.php" class="btn btn-primary">
              <span class="material-symbols-outlined" style="font-size:18px">add</span>
              Report Found Item
            </a>
          </div>
        </div>
      </section>

      <!-- Search & Filter Bar -->
      <section class="card-static p-md mt-lg" style="margin-top: var(--space-lg);">
        <form class="flex flex-col gap-md" method="get" action="browse.php">
          <!-- Search Input -->
          <div class="search-input-wrap" style="border-radius: 14px; padding: 0.625rem 1rem;">
            <span class="material-symbols-outlined text-outline" style="font-size:20px">search</span>
            <input type="text" id="browseSearch" name="q" value="<?php echo sanitize($q); ?>" placeholder="Search by keywords, serial code, model, color, brand, or building..." style="font-size: 0.875rem; flex: 1;">
            <?php if (!empty($q)): ?>
              <a href="<?php echo sanitize(buildUrl(['q' => '', 'page' => 1])); ?>" class="search-clear" title="Clear search">
                <span class="material-symbols-outlined">close</span>
              </a>
            <?php endif; ?>
          </div>

          <!-- Filter Row -->
          <div class="flex items-center flex-wrap gap-sm">
            <!-- Type Segmented Toggle -->
            <div class="filter-segment" id="typeFilter">
              <button type="button" data-type="all" class="<?php echo $type === 'all' ? 'active' : ''; ?>">All</button>
              <button type="button" data-type="lost" class="<?php echo $type === 'lost' ? 'active' : ''; ?>">Lost</button>
              <button type="button" data-type="found" class="<?php echo $type === 'found' ? 'active' : ''; ?>">Found</button>
            </div>
            <input type="hidden" name="type" id="typeInput" value="<?php echo sanitize($type); ?>">

            <!-- Category Dropdown -->
            <select class="filter-dropdown" id="categoryFilter" name="category" onchange="this.form.submit()">
              <option value="" <?php echo $category === '' ? 'selected' : ''; ?>>Category</option>
              <?php
              $cats = ['electronics'=>'Electronics', 'accessories'=>'Accessories', 'id-cards'=>'Documents & IDs', 'keys'=>'Keys & Keychains', 'keys-fobs'=>'Keys & FOBs', 'backpacks'=>'Backpacks', 'bags-packs'=>'Bags & Cases', 'books'=>'Books & Notebooks', 'book-pens'=>'Books & Pens', 'clothing'=>'Clothing', 'wallets'=>'Wallets', 'other'=>'Other'];
              foreach ($cats as $val => $label): ?>
                <option value="<?php echo $val; ?>" <?php echo $category === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Campus Zone Dropdown -->
            <select class="filter-dropdown" id="zoneFilter" name="zone" onchange="this.form.submit()">
              <option value="" <?php echo $zone === '' ? 'selected' : ''; ?>>Campus Zone</option>
              <?php
              $zones = ['library'=>'Library', 'engineering'=>'Engineering', 'student-union'=>'Student Union', 'science-hall'=>'Science Hall', 'sports-complex'=>'Sports Complex', 'dorms'=>'Dorms', 'dining'=>'Dining', 'campus-transit'=>'Campus Transit', 'admin-building'=>'Admin Building', 'arts-center'=>'Arts Center', 'athletic-center'=>'Athletic Center', 'campus-green'=>'Campus Green', 'central-library'=>'Central Library', 'parking-area-b'=>'Parking Area B', 'Cosunselling Room'=>'Cosunselling Room', 'Music Room'=>'Music Room', 'Badminton Court'=>'Badminton Court', 'M S Dhoni Cricket Wicket'=>'M S Dhoni Cricket Wicket', 'Janardhan Singh Traditional Sports Arena - A'=>'Janardhan Singh Traditional Sports Arena - A', 'Janardhan Singh Traditional Sports Arena - B'=>'Janardhan Singh Traditional Sports Arena - B', 'Jimmy George Volleyball Court'=>'Jimmy George Volleyball Court', 'Jimmy George Beach Volleyball Court'=>'Jimmy George Beach Volleyball Court', 'K D Jadhav Sports Arena'=>'K D Jadhav Sports Arena', 'Kapildev Cricket Ground'=>'Kapildev Cricket Ground', 'Major Dhyanchand Sports Arena - Basketball'=>'Major Dhyanchand Sports Arena - Basketball'];
              foreach ($zones as $val => $label): ?>
                <option value="<?php echo $val; ?>" <?php echo $zone === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Date Range Dropdown -->
            <select class="filter-dropdown" id="dateFilter" name="date" onchange="this.form.submit()">
              <option value="" <?php echo $dateRange === '' ? 'selected' : ''; ?>>Date Range</option>
              <option value="today" <?php echo $dateRange === 'today' ? 'selected' : ''; ?>>Today</option>
              <option value="3-days" <?php echo $dateRange === '3-days' ? 'selected' : ''; ?>>Last 3 Days</option>
              <option value="week" <?php echo $dateRange === 'week' ? 'selected' : ''; ?>>This Week</option>
              <option value="month" <?php echo $dateRange === 'month' ? 'selected' : ''; ?>>This Month</option>
            </select>

            <!-- Sort Dropdown -->
            <select class="sort-select" id="sortSelect" name="sort" onchange="this.form.submit()">
              <option value="most-recent" <?php echo $sort === 'most-recent' ? 'selected' : ''; ?>>Most Recent</option>
              <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
              <option value="views" <?php echo $sort === 'views' ? 'selected' : ''; ?>>Most Viewed</option>
            </select>

            <?php if (!empty($q) || !empty($category) || !empty($zone) || !empty($dateRange) || $type !== 'all'): ?>
              <a href="browse.php" class="btn btn-ghost btn-sm">
                <span class="material-symbols-outlined" style="font-size:16px">close</span>
                Clear Filters
              </a>
            <?php endif; ?>
          </div>
        </form>
      </section>

      <!-- Results Summary -->
      <section class="pt-lg" style="padding-top: var(--space-lg);">
        <div class="flex items-center justify-between flex-wrap gap-sm mb-md">
          <div class="flex items-center gap-md flex-wrap">
            <span class="font-label-lg">Showing <?php echo ($totalItems === 0) ? 0 : (($page - 1) * $perPage + 1); ?>&ndash;<?php echo min($page * $perPage, $totalItems); ?> of <?php echo $totalItems; ?> items</span>
          </div>
        </div>
      </section>

      <!-- ===== ITEM GRID ===== -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-lg pb-2xl" style="gap: var(--space-lg); padding-bottom: var(--space-2xl);">

        <?php if (empty($items)): ?>
          <div class="card p-xl flex flex-col items-center gap-md text-center" style="grid-column: 1 / -1;">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-surface-container text-primary">
              <span class="material-symbols-outlined">manage_search</span>
            </div>
            <h3 class="font-headline-md">No matching reports found</h3>
            <p class="font-body-sm text-on-surface-variant max-w-sm">Try adjusting your search keywords or clearing some filters.</p>
            <a href="browse.php" class="btn btn-primary btn-sm btn-pill">View All Items</a>
          </div>
        <?php endif; ?>

        <?php foreach ($items as $item): ?>
          <article class="item-card card">
            <div class="card-image aspect-[16/10]">
              <?php if ($item['image_1']): ?>
                <img src="uploads/items/<?php echo sanitize($item['image_1']); ?>" alt="<?php echo sanitize($item['title']); ?>" class="w-full h-full object-cover">
              <?php else: ?>
                <div class="w-full h-full flex items-center justify-center" style="background:var(--surface-container-high);">
                  <span class="material-symbols-outlined" style="font-size:48px; color:var(--primary);"><?php echo $item['item_type'] === 'lost' ? 'search' : 'inventory_2'; ?></span>
                </div>
              <?php endif; ?>
              <span class="status-badge badge <?php echo $item['item_type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($item['item_type']); ?></span>
              <?php if ($item['status'] === 'matched' || $item['status'] === 'returned'): ?>
                <span class="badge badge-verified" style="position:absolute; top:10px; right:10px; z-index:2;">
                  <span class="material-symbols-outlined" style="font-size:12px">verified</span>
                  <?php echo ucfirst($item['status']); ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="p-md flex flex-col gap-sm">
              <div class="flex items-center justify-between">
                <span class="badge badge-found-soft font-label-sm" style="background:var(--surface-container-high); color:var(--primary);"><?php echo sanitize(categoryName($item['category'])); ?></span>
                <span class="font-body-sm text-outline" style="font-size:0.6875rem"><?php echo sanitize(timeAgo($item['created_at'])); ?></span>
              </div>
              <h3 class="font-headline-sm line-clamp-1"><?php echo sanitize($item['title']); ?></h3>
              <p class="font-body-sm text-on-surface-variant line-clamp-2"><?php echo sanitize($item['description']); ?></p>
              <div class="flex items-center gap-xs text-outline" style="font-size:0.75rem">
                <span class="material-symbols-outlined" style="font-size:14px">location_on</span>
                <?php echo sanitize(zoneName($item['campus_zone'])); ?><?php echo $item['specific_location'] ? ' &mdash; ' . sanitize($item['specific_location']) : ''; ?>
              </div>
              <a href="item-detail.php?id=<?php echo (int)$item['id']; ?>" class="btn <?php echo $item['item_type'] === 'lost' ? 'btn-lost' : 'btn-primary'; ?> btn-sm mt-2" style="margin-top: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size:16px"><?php echo $item['item_type'] === 'lost' ? 'visibility' : 'inventory_2'; ?></span>
                <?php echo $item['item_type'] === 'lost' ? 'View Details' : 'View & Claim'; ?>
              </a>
            </div>
          </article>
        <?php endforeach; ?>

      </section>

      <!-- ===== PAGINATION ===== -->
      <?php if ($totalPages > 1): ?>
      <section class="flex items-center justify-center flex-col gap-md pb-2xl" style="padding-bottom: var(--space-2xl);">
        <span class="font-body-sm text-on-surface-variant">Showing page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="<?php echo sanitize(buildUrl(['page' => $page - 1])); ?>" class="page-btn" aria-label="Previous page">
              <span class="material-symbols-outlined" style="font-size:18px">chevron_left</span>
            </a>
          <?php else: ?>
            <button class="page-btn" disabled aria-label="Previous page"><span class="material-symbols-outlined" style="font-size:18px">chevron_left</span></button>
          <?php endif; ?>

          <?php
          $start = max(1, $page - 2);
          $end = min($totalPages, $page + 2);
          for ($p = $start; $p <= $end; $p++): ?>
            <a href="<?php echo sanitize(buildUrl(['page' => $p])); ?>" class="page-btn <?php echo $p === $page ? 'active' : ''; ?>"><?php echo $p; ?></a>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="<?php echo sanitize(buildUrl(['page' => $page + 1])); ?>" class="page-btn" aria-label="Next page">
              <span class="material-symbols-outlined" style="font-size:18px">chevron_right</span>
            </a>
          <?php else: ?>
            <button class="page-btn" disabled aria-label="Next page"><span class="material-symbols-outlined" style="font-size:18px">chevron_right</span></button>
          <?php endif; ?>
        </div>
      </section>
      <?php endif; ?>

    </div>
  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
  <script>
    (function() {
      'use strict';

      // Type filter segmented toggle - updates hidden input and submits
      var typeFilter = document.getElementById('typeFilter');
      if (typeFilter) {
        typeFilter.querySelectorAll('button').forEach(function(btn) {
          btn.addEventListener('click', function() {
            document.getElementById('typeInput').value = btn.dataset.type;
            typeFilter.closest('form').submit();
          });
        });
      }
    })();
  </script>
</body>
</html>