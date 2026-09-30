<?php
/**
 * Admin - Users
 */
$activePage = 'users';
$pageTitle  = 'Users';
require_once __DIR__ . '/partials/header.php';

$db = getDB();

$perPage = 12;
$page = max(1, (int)($_GET['p'] ?? 1));
$totalUsers = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$pager = paginate($totalUsers, $perPage, $page);

$stmt = $db->prepare(
    'SELECT id, full_name, email, mobile, role, points_balance, status, created_at
     FROM users
     ORDER BY created_at DESC
     LIMIT ' . (int)$pager['per_page'] . ' OFFSET ' . (int)$pager['offset']
);
$stmt->execute();
$users = $stmt->fetchAll();

$currentUserId = (int)getCurrentUserId();
?>

<div class="admin-head">
  <div>
    <h1 class="font-headline-lg">Users</h1>
    <p class="font-body-sm" style="color:var(--on-surface-variant);">Student &amp; admin accounts. Deactivated users cannot sign in.</p>
  </div>
</div>

<section class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Mobile</th>
          <th>Role</th>
          <th>Points</th>
          <th>Status</th>
          <th>Joined</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--on-surface-variant);padding:2.5rem;">No users found.</td></tr>
        <?php else: ?>
          <?php foreach ($users as $user): ?>
            <tr>
              <td>
                <div class="cell-strong">
                  <div class="avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                  <div style="min-width:0;">
                    <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">
                      <?php echo sanitize($user['full_name']); ?>
                      <?php if ($user['id'] == $currentUserId): ?><span class="font-body-sm" style="color:var(--primary);">(you)</span><?php endif; ?>
                    </div>
                    <div class="font-body-sm" style="color:var(--on-surface-variant); font-size:.6875rem;"><?php echo sanitize($user['email']); ?></div>
                  </div>
                </div>
              </td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize($user['mobile'] ?? '—'); ?></td>
              <td>
                <span class="status-tag status-tag--<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
              </td>
              <td class="font-label-md" style="font-weight:700;">
                <?php echo number_format((int)$user['points_balance']); ?>
              </td>
              <td>
                <span class="status-tag status-tag--<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
              </td>
              <td class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo timeAgo($user['created_at']); ?></td>
              <td>
                <div class="admin-actions" style="justify-content:flex-end;">
                  <a class="btn btn-outline btn-sm" href="<?php echo appUrl('admin/user-view.php?id=' . $user['id']); ?>">View</a>
                  <?php if ($user['status'] === 'active'): ?>
                    <form action="<?php echo appUrl('php/admin/user_action.php'); ?>" method="post" style="display:inline;"
                          onsubmit="return confirm('Deactivate account for <?php echo sanitize($user['full_name']); ?>? They will be unable to sign in.');">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                      <input type="hidden" name="action" value="deactivate">
                      <button type="submit" class="btn btn--danger btn-sm">Deactivate</button>
                    </form>
                  <?php else: ?>
                    <form action="<?php echo appUrl('php/admin/user_action.php'); ?>" method="post" style="display:inline;">
                      <?php echo csrfField(); ?>
                      <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                      <input type="hidden" name="action" value="activate">
                      <button type="submit" class="btn btn-primary btn-sm">Activate</button>
                    </form>
                  <?php endif; ?>
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
        <a href="?p=<?php echo $pager['current_page'] - 1; ?>">&larr; Prev</a>
      <?php endif; ?>
      <?php if ($pager['current_page'] < $pager['total_pages']): ?>
        <a href="?p=<?php echo $pager['current_page'] + 1; ?>">Next &rarr;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>