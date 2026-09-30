<?php
/**
 * Admin - Item Reports
 */
$activePage = 'items';
$pageTitle  = 'Item Reports';
require_once __DIR__ . '/partials/header.php';

$db = getDB();

// --- Filters ---
// Real DB statuses are: active | matched | returned | closed
// There is no "pending" item state (reports go live instantly).
$filters = [
    'all'      => ['label' => 'All'],
    'lost'     => ['label' => 'Lost'],
    'found'    => ['label' => 'Found'],
    'active'   => ['label' => 'Active'],
    'resolved' => ['label' => 'Resolved'],
    'pending'  => ['label' => 'Pending'],
];
$currentFilter = $_GET['s'] ?? 'all';
if (!array_key_exists($currentFilter, $filters)) {
    $currentFilter = 'all';
}

// --- Counts per filter (built from one grouped query) ---
$counts = ['all' => 0, 'lost' => 0, 'found' => 0, 'active' => 0, 'resolved' => 0, 'pending' => 0];
try {
    foreach ($db->query('SELECT item_type, status, COUNT(*) AS c FROM items GROUP BY item_type, status')->fetchAll() as $row) {
        $counts['all'] += (int)$row['c'];
        if ($row['item_type'] === 'lost')       $counts['lost'] += (int)$row['c'];
        if ($row['item_type'] === 'found')      $counts['found'] += (int)$row['c'];
        if ($row['status'] === 'active')        $counts['active'] += (int)$row['c'];
        if (in_array($row['status'], ['returned', 'closed'], true)) $counts['resolved'] += (int)$row['c'];
    }
} catch (Exception $e) {
    error_log('Admin item counts failed: ' . $e->getMessage());
}

// --- Search & user filter ---
$q = trim($_GET['q'] ?? '');
$u = max(0, (int)($_GET['u'] ?? 0));

// --- Build WHERE clause based on the selected filter ---
$where  = '1 = 1';
$params = [];
if ($currentFilter === 'lost') {
    $where .= ' AND i.item_type = ?';
    $params[] = 'lost';
} elseif ($currentFilter === 'found') {
    $where .= ' AND i.item_type = ?';
    $params[] = 'found';
} elseif ($currentFilter === 'active') {
    $where .= ' AND i.status = ?';
    $params[] = 'active';
} elseif ($currentFilter === 'resolved') {
    $where .= ' AND i.status IN (?, ?)';
    $params[] = 'returned';
    $params[] = 'closed';
} elseif ($currentFilter === 'pending') {
    $where .= ' AND i.status = ?';
    $params[] = 'pending';
}

if ($q !== '') {
    $where .= ' AND (i.title LIKE ? OR i.description LIKE ? OR i.brand_model LIKE ? OR i.specific_location LIKE ? OR i.serial_number LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($u > 0) {
    $where .= ' AND i.user_id = ?';
    $params[] = $u;
}

// Build a query-string suffix that preserves filters for links
$qsParts = ['s=' . $currentFilter];
if ($q !== '')   { $qsParts[] = 'q=' . urlencode($q); }
if ($u > 0)      { $qsParts[] = 'u=' . $u; }
$qs = implode('&', $qsParts);

// --- Pagination ---
$perPage = 12;
$page = max(1, (int)($_GET['p'] ?? 1));
$stmt = $db->prepare('SELECT COUNT(*) FROM items i WHERE ' . $where);
$stmt->execute($params);
$totalItems = (int)$stmt->fetchColumn();
$pager = paginate($totalItems, $perPage, $page);

$stmt = $db->prepare(
    'SELECT i.*, u.full_name AS submitter, u.email AS submitter_email
     FROM items i
     LEFT JOIN users u ON u.id = i.user_id
     WHERE ' . $where . '
     ORDER BY i.created_at DESC
     LIMIT ' . (int)$pager['per_page'] . ' OFFSET ' . (int)$pager['offset']
);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<div class="admin-head">
  <div>
    <h1 class="font-headline-lg">Item Reports</h1>
    <p class="font-body-sm" style="color:var(--on-surface-variant);">All lost &amp; found reports across campus.</p>
  </div>
  <form class="admin-page-search" action="<?php echo appUrl('admin/items.php'); ?>" method="get" role="search">
    <span class="material-symbols-outlined" style="font-size:18px;color:var(--on-surface-variant);">search</span>
    <input type="text" name="q" placeholder="Search title, description, serial&hellip;" aria-label="Search item reports"
           value="<?php echo sanitize($_GET['q'] ?? ''); ?>">
    <input type="hidden" name="s" value="<?php echo $currentFilter; ?>">
    <?php if ($u > 0): ?><input type="hidden" name="u" value="<?php echo $u; ?>"><?php endif; ?>
    <button type="submit" class="btn btn-primary btn-sm btn-pill">Search</button>
    <?php if ($q !== '' || $u > 0): ?>
      <a class="btn btn-ghost btn-sm" href="<?php echo appUrl('admin/items.php?s=' . $currentFilter); ?>">Clear</a>
    <?php endif; ?>
  </form>
</div>

<?php if (($q !== '' || $u > 0) && $totalItems === 0): ?>
  <div class="admin-empty" style="margin-bottom:1rem;">
    <span class="material-symbols-outlined">search_off</span>
    <p>No item reports match your search.</p>
  </div>
<?php endif; ?>

<div class="filter-tabs" style="margin-bottom:1rem;">
  <?php foreach ($filters as $key => $f): ?>
    <a class="<?php echo $currentFilter === $key ? 'is-active' : ''; ?>" href="?<?php echo $key === $currentFilter ? $qs : 's=' . $key . ($q !== '' ? '&q=' . urlencode($q) : '') . ($u > 0 ? '&u=' . $u : ''); ?>">
      <?php echo $f['label']; ?> <span class="filter-tabs__count"><?php echo $counts[$key]; ?></span>
    </a>
  <?php endforeach; ?>
</div>

<section class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Item</th>
          <th>Type</th>
          <th>Status</th>
          <th>Zone</th>
          <th>Reporter</th>
          <th>Submitted</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($items)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--on-surface-variant);padding:2.5rem;">No item reports match this filter.</td></tr>
        <?php else: ?>
          <?php foreach ($items as $item): ?>
            <tr>
              <td>
                <div class="cell-strong">
                  <div class="thumb">
                    <?php if ($item['image_1']): ?>
                      <img src="<?php echo appUrl('uploads/items/' . $item['image_1']); ?>" alt="">
                    <?php else: ?>
                      <span class="material-symbols-outlined" style="font-size:20px;color:var(--primary);"><?php echo $item['item_type'] === 'lost' ? 'search' : 'inventory_2'; ?></span>
                    <?php endif; ?>
                  </div>
                  <div style="min-width:0;">
                    <div style="font-weight:600;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo sanitize($item['title']); ?></div>
                    <div class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize(categoryName($item['category'])); ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge <?php echo $item['item_type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?php echo strtoupper($item['item_type']); ?></span>
              </td>
              <td>
                <span class="status-tag status-tag--<?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span>
              </td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize(zoneName($item['campus_zone'])); ?></td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize($item['submitter'] ?? '—'); ?></td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($item['created_at']); ?></td>
              <td>
                <div class="admin-actions" style="justify-content:flex-end;">
                  <a class="btn btn-outline btn-sm" href="<?php echo appUrl('admin/item-view.php?id=' . $item['id']); ?>">View</a>
                  <?php if ($item['status'] === 'active' || $item['status'] === 'matched'): ?>
                    <form action="<?php echo appUrl('php/admin/item_action.php'); ?>" method="post">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                      <input type="hidden" name="action" value="resolve">
                      <button type="submit" class="btn btn-primary btn-sm">Resolve</button>
                    </form>
                  <?php else: ?>
                    <form action="<?php echo appUrl('php/admin/item_action.php'); ?>" method="post">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                      <input type="hidden" name="action" value="reopen">
                      <button type="submit" class="btn btn-ghost btn-sm">Reopen</button>
                    </form>
                  <?php endif; ?>
                  <form action="<?php echo appUrl('php/admin/item_action.php'); ?>" method="post" style="display:inline;"
                        onsubmit="return confirm('Delete this item report? This cannot be undone.');">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn--danger btn--icon btn-sm" title="Delete">
                      <span class="material-symbols-outlined" style="font-size:16px">delete</span>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pager['total_pages'] > 1): ?>
    <div class="admin-pagination">
      <span style="color:var(--on-surface-variant);">Page <?php echo $pager['current_page']; ?> of <?php echo $pager['total_pages']; ?></span>
      <?php if ($pager['current_page'] > 1): ?>
        <a href="?<?php echo $qs; ?>&p=<?php echo $pager['current_page'] - 1; ?>">&larr; Prev</a>
      <?php endif; ?>
      <?php if ($pager['current_page'] < $pager['total_pages']): ?>
        <a href="?<?php echo $qs; ?>&p=<?php echo $pager['current_page'] + 1; ?>">Next &rarr;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>