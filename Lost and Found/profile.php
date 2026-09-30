<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = getDB();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Profile update handler is php/profile/update.php
$avatarUrl = $currentUser['profile_image'] ? 'uploads/profiles/' . $currentUser['profile_image'] : null;
$initial = strtoupper(substr($currentUser['full_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile — Campus Lost &amp; Found Portal</title>
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

  <?php $activeNav = 'profile'; require __DIR__ . '/includes/partials/site_header.php'; ?>

  <main class="container py-xl">

    <nav class="breadcrumb mb-lg" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <span style="color:var(--outline-variant);">/</span>
      <span class="current">My Profile</span>
    </nav>

    <?php displayFlash(); ?>

    <div class="flex flex-col lg:grid lg:grid-cols-12 gap-xl">

      <!-- Profile Card -->
      <div class="lg:col-span-4">
        <div class="card-static" style="border-radius:1rem; padding:1.5rem; display:flex; flex-direction:column; align-items:center; gap:0.75rem; text-align:center;">
          <div style="width:96px; height:96px; border-radius:50%; overflow:hidden; background:var(--surface-container-high); display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:800; color:var(--primary);">
            <?php if ($avatarUrl): ?>
              <img src="<?php echo sanitize($avatarUrl); ?>" alt="Profile" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <?php echo $initial; ?>
            <?php endif; ?>
          </div>
          <h1 class="font-headline-md"><?php echo sanitize($currentUser['full_name']); ?></h1>
          <p class="font-body-sm" style="color:var(--on-surface-variant);"><?php echo sanitize($currentUser['email']); ?></p>
          <span class="badge <?php echo $currentUser['role'] === 'admin' ? 'badge-lost' : 'badge-found'; ?>">
            <span class="material-symbols-outlined" style="font-size:12px"><?php echo $currentUser['role'] === 'admin' ? 'shield' : 'school'; ?></span>
            <?php echo ucfirst($currentUser['role']); ?>
          </span>
          <div style="width:100%; border-top:1px solid var(--border-subtle); margin-top:0.5rem; padding-top:0.75rem; display:flex; flex-direction:column; gap:0.5rem;">
            <div class="flex items-center justify-between">
              <span class="font-body-sm" style="color:var(--on-surface-variant);">Points Balance</span>
              <span class="font-label-lg" style="color:var(--primary);"><?php echo number_format($currentUser['points_balance']); ?></span>
            </div>
            <?php if ($currentUser['mobile']): ?>
            <div class="flex items-center justify-between">
              <span class="font-body-sm" style="color:var(--on-surface-variant);">Mobile</span>
              <span class="font-body-sm"><?php echo sanitize($currentUser['mobile']); ?></span>
            </div>
            <?php endif; ?>
            <div class="flex items-center justify-between">
              <span class="font-body-sm" style="color:var(--on-surface-variant);">Member Since</span>
              <span class="font-body-sm"><?php echo date('M Y', strtotime($currentUser['created_at'])); ?></span>
            </div>
          </div>
          <a href="points.php" class="btn btn-primary btn-sm btn-pill" style="width:100%; margin-top:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:16px">redeem</span>
            View Points &amp; Rewards
          </a>
          <a href="my-items.php" class="btn btn-outline btn-sm btn-pill" style="width:100%;">
            <span class="material-symbols-outlined" style="font-size:16px">inventory_2</span>
            My Reports
          </a>
        </div>
      </div>

      <!-- Edit Forms -->
      <div class="lg:col-span-8" style="display:flex; flex-direction:column; gap:1.5rem;">

        <!-- Personal Info -->
        <div class="card-static" style="border-radius:1rem; padding:1.5rem;">
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:22px; color:var(--primary);">person</span>
            <h2 class="font-headline-md">Personal Information</h2>
          </div>
          <form action="php/profile/update.php" method="post" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:1rem;">
            <?php echo csrfField(); ?>
            <input type="hidden" name="update_type" value="profile">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
              <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="form-input" value="<?php echo sanitize($currentUser['full_name']); ?>" maxlength="100" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="mobile">Mobile Number</label>
                <input type="text" name="mobile" id="mobile" class="form-input" value="<?php echo sanitize($currentUser['mobile'] ?? ''); ?>" placeholder="e.g., +923001234567">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="profile_image">Profile Photo (JPG, PNG, up to 10MB)</label>
              <input type="file" name="profile_image" id="profile_image" class="form-input" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary btn-pill" style="align-self:flex-start;">
              <span class="material-symbols-outlined" style="font-size:18px">save</span>
              Save Changes
            </button>
          </form>
        </div>

        <!-- Change Password -->
        <div class="card-static" style="border-radius:1rem; padding:1.5rem;">
          <div class="flex items-center gap-sm mb-md">
            <span class="material-symbols-outlined" style="font-size:22px; color:var(--status-lost);">lock</span>
            <h2 class="font-headline-md">Change Password</h2>
          </div>
          <form action="php/profile/update.php" method="post" style="display:flex; flex-direction:column; gap:1rem;">
            <?php echo csrfField(); ?>
            <input type="hidden" name="update_type" value="password">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-md">
              <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" class="form-input" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-input" minlength="6" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-input" minlength="6" required>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-pill" style="align-self:flex-start;">
              <span class="material-symbols-outlined" style="font-size:18px">password</span>
              Update Password
            </button>
          </form>
        </div>

      </div>

    </div>

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
</body>
</html>