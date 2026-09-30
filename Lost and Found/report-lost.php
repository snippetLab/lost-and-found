<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    setFlash('error', 'Please sign in to report a lost item.');
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report Lost Item — Campus Lost &amp; Found</title>
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

  <?php $activeNav = 'report-lost'; require __DIR__ . '/includes/partials/site_header.php'; ?>

  <main class="container py-xl">

    <!-- ==================== BREADCRUMB ==================== -->
    <nav class="breadcrumb mb-lg" aria-label="Breadcrumb">
      <a href="index.php">Home</a>
      <span style="color:var(--outline-variant);">/</span>
      <a href="#">Report an Item</a>
      <span style="color:var(--outline-variant);">/</span>
      <span class="current">Lost Item Report</span>
    </nav>

    <?php displayFlash(); ?>

    <!-- ==================== HEADER BANNER ==================== -->
    <div class="card-static mb-xl" style="border-radius:1.25rem; overflow:hidden;">
      <div style="background:linear-gradient(135deg, #ffe4e6 0%, #ffecf0 40%, #fdf2f8 100%); padding:2rem 2rem 1.5rem;">
        <div class="flex flex-col lg:grid lg:grid-cols-12 gap-xl" style="align-items:start;">
          <!-- Left content -->
          <div class="lg:col-span-8" style="display:flex; flex-direction:column; gap:1rem;">
            <span class="badge badge-lost">
              <span class="material-symbols-outlined" style="font-size:12px;">live_tv</span>
              LIVE RECOVERY LOG &bull; CASE REGISTRATION
            </span>
            <h1 class="font-headline-lg" style="color:var(--status-lost); margin-top:0.25rem;">Report a Lost Item</h1>
            <p class="font-body-md" style="color:var(--on-surface-variant); max-width:520px; line-height:1.7;">
              Register your lost item into the campus recovery network. Our matching engine continuously scans incoming turn-ins and alerts you the moment a match is detected — so your belongings can find their way back.
            </p>
            <!-- Stats -->
            <div class="flex flex-wrap gap-md" style="margin-top:0.5rem;">
              <div class="flex items-center gap-sm" style="background:var(--surface-card); border-radius:12px; padding:0.625rem 1rem; box-shadow:var(--shadow-sm);">
                <span class="material-symbols-outlined text-status-verified" style="font-size:20px;">verified</span>
                <div>
                  <div class="font-label-md" style="color:var(--on-surface-variant);">Auto Match</div>
                  <div class="font-label-lg" style="color:var(--on-surface);">92% Rate</div>
                </div>
              </div>
              <div class="flex items-center gap-sm" style="background:var(--surface-card); border-radius:12px; padding:0.625rem 1rem; box-shadow:var(--shadow-sm);">
                <span class="material-symbols-outlined" style="font-size:20px; color:#F59E0B;">bolt</span>
                <div>
                  <div class="font-label-md" style="color:var(--on-surface-variant);">Avg. Return</div>
                  <div class="font-label-lg" style="color:var(--on-surface);">&lt; 4 Hours</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Stepper -->
        <div class="flex flex-wrap gap-sm mt-lg" style="margin-top:1.5rem;">
          <div class="flex items-center gap-sm" style="background:rgba(225,29,72,0.1); border-radius:12px; padding:0.625rem 1rem; flex:1; min-width:140px;">
            <div style="width:28px; height:28px; border-radius:8px; background:var(--status-lost); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; flex-shrink:0;">1</div>
            <div>
              <div class="font-label-md" style="color:var(--status-lost);">Item Details</div>
            </div>
          </div>
          <div class="flex items-center gap-sm" style="background:var(--surface-container-low); border-radius:12px; padding:0.625rem 1rem; flex:1; min-width:140px;">
            <div style="width:28px; height:28px; border-radius:8px; background:var(--surface-container-highest); color:var(--on-surface-variant); display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; flex-shrink:0;">2</div>
            <div>
              <div class="font-label-md" style="color:var(--on-surface-variant);">Location &amp; Date</div>
            </div>
          </div>
          <div class="flex items-center gap-sm" style="background:var(--surface-container-low); border-radius:12px; padding:0.625rem 1rem; flex:1; min-width:140px;">
            <div style="width:28px; height:28px; border-radius:8px; background:var(--surface-container-highest); color:var(--on-surface-variant); display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; flex-shrink:0;">3</div>
            <div>
              <div class="font-label-md" style="color:var(--on-surface-variant);">Photos &amp; Marks</div>
            </div>
          </div>
          <div class="flex items-center gap-sm" style="background:var(--surface-container-low); border-radius:12px; padding:0.625rem 1rem; flex:1; min-width:140px;">
            <div style="width:28px; height:28px; border-radius:8px; background:var(--surface-container-highest); color:var(--on-surface-variant); display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; flex-shrink:0;">4</div>
            <div>
              <div class="font-label-md" style="color:var(--on-surface-variant);">Verification</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== MAIN 12-COL GRID ==================== -->
    <div class="flex flex-col lg:grid lg:grid-cols-12 gap-xl">

      <!-- ========== FORM COLUMN (8 cols) ========== -->
      <div class="lg:col-span-8">
        <form id="lostItemForm" action="php/items/report_lost.php" method="post" enctype="multipart/form-data" novalidate>
          <?php echo csrfField(); ?>
          <div style="display:flex; flex-direction:column; gap:1.5rem;">

            <!-- ===== SECTION 1: ITEM INFORMATION ===== -->
            <div class="card-static" style="border-radius:1rem; overflow:hidden;">
              <div style="padding:1.25rem 1.5rem 1.5rem; display:flex; flex-direction:column; gap:1.25rem;">
                <div class="flex items-center gap-sm">
                  <div style="width:32px; height:32px; border-radius:8px; background:var(--status-lost); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; flex-shrink:0;">1</div>
                  <h2 class="font-headline-md" style="margin:0;">Item Information</h2>
                </div>

                <!-- Item Name -->
                <div class="form-group">
                  <label class="form-label" for="title">
                    Item Name / Title <span class="form-label-required">*</span>
                  </label>
                  <div class="relative">
                    <span class="material-symbols-outlined" style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); font-size:20px; color:var(--on-surface-variant); pointer-events:none;">devices</span>
                    <input type="text" name="title" id="title" class="form-input" placeholder="e.g., Black Wireless Headphones" style="padding-left:2.75rem;" maxlength="150" required>
                  </div>
                </div>

                <!-- Primary Category -->
                <div class="form-group">
                  <label class="form-label" for="category">
                    Primary Category <span class="form-label-required">*</span>
                  </label>
                  <div class="relative">
                    <select name="category" id="category" class="form-select" required>
                      <option value="" disabled selected>Select a category</option>
                      <option value="electronics">Electronics</option>
                      <option value="id-cards">ID Cards</option>
                      <option value="keys">Keys</option>
                      <option value="backpacks">Backpacks</option>
                      <option value="books">Books</option>
                      <option value="clothing">Clothing</option>
                      <option value="accessories">Accessories</option>
                      <option value="other">Other</option>
                    </select>
                    <span class="material-symbols-outlined" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); font-size:20px; color:var(--on-surface-variant); pointer-events:none;">expand_more</span>
                  </div>
                </div>

                <!-- Brand & Model -->
                <div class="form-group">
                  <label class="form-label" for="brand_model">
                    Brand &amp; Model <span class="form-label-hint">(Optional)</span>
                  </label>
                  <input type="text" name="brand_model" id="brand_model" class="form-input" placeholder="e.g., Sony WH-1000XM4">
                </div>

                <!-- Primary Color -->
                <div class="form-group">
                  <label class="form-label">Primary Color</label>
                  <div class="color-swatch-group flex flex-wrap items-center gap-sm">
                    <button type="button" class="color-swatch" data-color="black" style="background:#1a1a2e;" title="Black"></button>
                    <button type="button" class="color-swatch" data-color="blue" style="background:#2563eb;" title="Blue"></button>
                    <button type="button" class="color-swatch" data-color="silver" style="background:#94a3b8;" title="Silver / Gray"></button>
                    <button type="button" class="color-swatch" data-color="white" style="background:#f1f5f9; border:1px solid var(--border-subtle);" title="White"></button>
                    <button type="button" class="color-swatch" data-color="red" style="background:#e11d48;" title="Red / Pink"></button>
                    <button type="button" class="color-swatch" data-color="brown" style="background:#8b5e3c;" title="Brown / Leather"></button>
                    <button type="button" class="color-swatch" data-color="other" style="background:linear-gradient(135deg,#e2e8f0,#94a3b8,#2563eb,#e11d48,#1a1a2e); border:2px solid var(--border-subtle);" title="Other"></button>
                    <span class="font-body-sm" style="color:var(--on-surface-variant); margin-left:0.25rem;">Other</span>
                  </div>
                  <input type="hidden" name="color" id="color" value="">
                </div>

                <!-- Serial Number -->
                <div class="form-group">
                  <label class="form-label" for="serial_number">
                    Serial Number / Student ID / IMEI
                    <span class="form-label-hint">(Optional)</span>
                  </label>
                  <input type="text" name="serial_number" id="serial_number" class="form-input" placeholder="e.g., SN-2847A93X">
                  <div class="flex items-center gap-xs" style="margin-top:0.125rem;">
                    <span class="material-symbols-outlined" style="font-size:14px; color:var(--primary);">lock</span>
                    <span class="font-body-sm" style="color:var(--on-surface-variant);">Your personal identifiers are encrypted and kept confidential.</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- ===== SECTION 2: WHEN & WHERE ===== -->
            <div class="card-static" style="border-radius:1rem; overflow:hidden;">
              <div style="padding:1.25rem 1.5rem 1.5rem; display:flex; flex-direction:column; gap:1.25rem;">
                <div class="flex items-center gap-sm">
                  <div style="width:32px; height:32px; border-radius:8px; background:var(--status-lost); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; flex-shrink:0;">2</div>
                  <h2 class="font-headline-md" style="margin:0;">When &amp; Where Was It Lost?</h2>
                </div>

                <!-- Date Lost -->
                <div class="form-group">
                  <label class="form-label" for="date_lost">
                    Date Lost <span class="form-label-required">*</span>
                  </label>
                  <div class="relative">
                    <span class="material-symbols-outlined" style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); font-size:20px; color:var(--on-surface-variant); pointer-events:none;">calendar_today</span>
                    <input type="date" name="date_lost" id="date_lost" class="form-input" style="padding-left:2.75rem;" max="<?php echo date('Y-m-d'); ?>" required>
                  </div>
                </div>

                <!-- Time Range -->
                <div class="form-group">
                  <label class="form-label" for="time_period">
                    Approximate Time Range <span class="form-label-hint">(Optional)</span>
                  </label>
                  <div class="relative">
                    <select name="time_period" id="time_period" class="form-select">
                      <option value="" disabled selected>Select time period</option>
                      <option value="morning">Morning (6 AM – 12 PM)</option>
                      <option value="afternoon">Afternoon (12 PM – 5 PM)</option>
                      <option value="evening">Evening (5 PM – 9 PM)</option>
                      <option value="late-night">Late Night (9 PM – 6 AM)</option>
                      <option value="unknown">Unknown</option>
                    </select>
                    <span class="material-symbols-outlined" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); font-size:20px; color:var(--on-surface-variant); pointer-events:none;">expand_more</span>
                  </div>
                </div>

                <!-- Campus Zone -->
                <div class="form-group">
                  <label class="form-label" for="campus_zone">
                    Campus Zone / Building <span class="form-label-required">*</span>
                  </label>
                  <div class="relative">
                    <select name="campus_zone" id="campus_zone" class="form-select" required>
                      <option value="" disabled selected>Select location</option>
                      <option value="library">Library</option>
                      <option value="engineering">Engineering</option>
                      <option value="student-union">Student Union</option>
                      <option value="science-hall">Science Hall</option>
                      <option value="sports-complex">Sports Complex</option>
                      <option value="dorms">Dorms</option>
                      <option value="dining">Dining</option>
<option value="campus-transit">Campus Transit</option>
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
                    <span class="material-symbols-outlined" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); font-size:20px; color:var(--on-surface-variant); pointer-events:none;">expand_more</span>
                  </div>
                </div>

                <!-- Specific Location -->
                <div class="form-group">
                  <label class="form-label" for="specific_location">
                    Specific Location / Room <span class="form-label-hint">(Optional)</span>
                  </label>
                  <input type="text" name="specific_location" id="specific_location" class="form-input" placeholder="e.g., 3rd Floor, Study Room B-204">
                </div>
              </div>
            </div>

            <!-- ===== SECTION 3: DISTINCTIVE MARKS ===== -->
            <div class="card-static" style="border-radius:1rem; overflow:hidden;">
              <div style="padding:1.25rem 1.5rem 1.5rem; display:flex; flex-direction:column; gap:1.25rem;">
                <div class="flex items-center gap-sm">
                  <div style="width:32px; height:32px; border-radius:8px; background:var(--status-lost); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; flex-shrink:0;">3</div>
                  <h2 class="font-headline-md" style="margin:0;">Distinctive Marks &amp; Description</h2>
                </div>

                <div class="form-group">
                  <label class="form-label" for="description">
                    Detailed Description <span class="form-label-required">*</span>
                    <span class="form-label-hint">Min. 20 characters</span>
                  </label>
                  <textarea name="description" id="description" class="form-textarea" rows="5" minlength="20" maxlength="5000" placeholder="Describe your item in detail — unique scratches, stickers, engravings, custom cases, distinguishing features that would help someone identify it..." required></textarea>
                </div>
              </div>
            </div>

            <!-- ===== SECTION 4: PHOTO REFERENCE ===== -->
            <div class="card-static" style="border-radius:1rem; overflow:hidden;">
              <div style="padding:1.25rem 1.5rem 1.5rem; display:flex; flex-direction:column; gap:1.25rem;">
                <div class="flex items-center gap-sm">
                  <div style="width:32px; height:32px; border-radius:8px; background:var(--status-lost); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; flex-shrink:0;">4</div>
                  <h2 class="font-headline-md" style="margin:0;">Photo Reference <span class="form-label-hint">(Recommended)</span></h2>
                </div>

                <label for="image_1" style="border:2px dashed var(--border-subtle); border-radius:12px; padding:2rem; display:flex; flex-direction:column; align-items:center; gap:0.75rem; background:var(--surface-container-low); cursor:pointer; transition:all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary)';this.style.background='rgba(70,72,212,0.03)'" onmouseout="this.style.borderColor='var(--border-subtle)';this.style.background='var(--surface-container-low)'">
                  <span class="material-symbols-outlined" style="font-size:40px; color:var(--primary);">cloud_upload</span>
                  <div class="font-label-lg" style="color:var(--on-surface);">Click to upload photos or drag and drop</div>
                  <div class="font-body-sm" style="color:var(--on-surface-variant);">PNG, JPG, GIF, WebP up to 10MB each (max 3)</div>
                  <input type="file" id="image_1" name="image_1" accept="image/*" style="display:none;">
                </label>

                <div class="flex flex-wrap gap-md" id="photoPreviewRow">
                  <label for="image_2" style="width:120px; height:120px; border-radius:12px; border:2px dashed var(--border-subtle); display:flex; flex-direction:column; align-items:center; justify-content:center; background:var(--surface-container-low); cursor:pointer; transition:all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
                    <span class="material-symbols-outlined" style="font-size:24px; color:var(--on-surface-variant);">add</span>
                    <span class="font-body-sm" style="color:var(--on-surface-variant);">Photo 2</span>
                    <input type="file" id="image_2" name="image_2" accept="image/*" style="display:none;">
                  </label>
                  <label for="image_3" style="width:120px; height:120px; border-radius:12px; border:2px dashed var(--border-subtle); display:flex; flex-direction:column; align-items:center; justify-content:center; background:var(--surface-container-low); cursor:pointer; transition:all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
                    <span class="material-symbols-outlined" style="font-size:24px; color:var(--on-surface-variant);">add</span>
                    <span class="font-body-sm" style="color:var(--on-surface-variant);">Photo 3</span>
                    <input type="file" id="image_3" name="image_3" accept="image/*" style="display:none;">
                  </label>
                </div>
              </div>
            </div>

            <!-- ===== SECTION 5: VERIFICATION & NOTIFICATIONS ===== -->
            <div class="card-static" style="border-radius:1rem; overflow:hidden;">
              <div style="padding:1.25rem 1.5rem 1.5rem; display:flex; flex-direction:column; gap:1.25rem;">
                <div class="flex items-center gap-sm">
                  <div style="width:32px; height:32px; border-radius:8px; background:var(--status-lost); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; flex-shrink:0;">5</div>
                  <h2 class="font-headline-md" style="margin:0;">Verification &amp; Notifications</h2>
                </div>

<!-- Email -->
                <div class="form-group">
                  <label class="form-label" for="university_email">
                    Email Address <span class="form-label-required">*</span>
                  </label>
                  <div class="relative">
                    <span class="material-symbols-outlined" style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); font-size:20px; color:var(--on-surface-variant); pointer-events:none;">mail</span>
                    <input type="email" id="university_email" class="form-input" style="padding-left:2.75rem;" value="<?php echo sanitize($currentUser['email']); ?>" disabled>
                  </div>
                  <div class="flex items-center gap-xs" style="margin-top:0.125rem;">
                    <span class="material-symbols-outlined" style="font-size:14px; color:var(--status-verified);">verified_user</span>
                    <span class="font-body-sm" style="color:var(--on-surface-variant);">Reports are linked to your signed-in account so you can track status under My Reports.</span>
                  </div>
                </div>

                <!-- Checkboxes -->
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                  <label class="flex items-start gap-sm cursor-pointer" style="padding:0.75rem 1rem; background:var(--surface-container-low); border-radius:12px; transition:background 0.2s ease;" onmouseover="this.style.background='var(--surface-container)'" onmouseout="this.style.background='var(--surface-container-low)'">
                    <input type="checkbox" class="form-checkbox" checked style="margin-top:2px;">
                    <div>
                      <div class="font-label-lg" style="color:var(--on-surface);">Keep personal contact details confidential</div>
                      <div class="font-body-sm" style="color:var(--on-surface-variant);">Your email and phone will only be shared upon verified match confirmation.</div>
                    </div>
                  </label>
                  <label class="flex items-start gap-sm cursor-pointer" style="padding:0.75rem 1rem; background:var(--surface-container-low); border-radius:12px; transition:background 0.2s ease;" onmouseover="this.style.background='var(--surface-container)'" onmouseout="this.style.background='var(--surface-container-low)'">
                    <input type="checkbox" class="form-checkbox" checked style="margin-top:2px;">
                    <div>
                      <div class="font-label-lg" style="color:var(--on-surface);">Email alert on match</div>
                      <div class="font-body-sm" style="color:var(--on-surface-variant);">Get notified when a found item matches your description.</div>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <!-- ===== ACTION BUTTONS ===== -->
            <div class="flex flex-wrap items-center gap-md">
              <button type="button" class="btn btn-ghost btn-lg" id="saveDraftBtn">
                <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                Save Draft
              </button>
              <button type="submit" class="btn btn-lost btn-lg btn-pill" id="submitBtn">
                <span class="material-symbols-outlined" style="font-size:18px;">send</span>
                Submit Lost Item Report
              </button>
            </div>
            <div class="flex items-center gap-xs" style="margin-top:-0.5rem;">
              <span class="material-symbols-outlined" style="font-size:16px; color:var(--status-lost);">info</span>
              <span class="font-body-sm" style="color:var(--on-surface-variant);">Reports are indexed instantly into the live campus feed</span>
            </div>

          </div>
        </form>
      </div>

      <!-- ========== SIDEBAR (4 cols) ========== -->
      <div class="lg:col-span-4">
        <div style="display:flex; flex-direction:column; gap:1.5rem;">

          <!-- Campus Matching Protocol -->
          <div class="card-static" style="border-radius:1rem; padding:1.25rem;">
            <div class="flex items-center gap-sm mb-md">
              <span class="material-symbols-outlined text-primary" style="font-size:22px;">psychology</span>
              <h3 class="font-headline-sm">Campus Matching Protocol</h3>
            </div>
            <div style="display:flex; flex-direction:column; gap:0.75rem;">
              <div class="flex items-start gap-sm">
                <div style="width:28px; height:28px; border-radius:8px; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0;">1</div>
                <div>
                  <div class="font-label-lg" style="color:var(--on-surface);">Real-time Similarity Score</div>
                  <div class="font-body-sm" style="color:var(--on-surface-variant);">The engine compares your report against all active turn-ins within seconds.</div>
                </div>
              </div>
              <div class="flex items-start gap-sm">
                <div style="width:28px; height:28px; border-radius:8px; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0;">2</div>
                <div>
                  <div class="font-label-lg" style="color:var(--on-surface);">Escrow Intake Vault</div>
                  <div class="font-body-sm" style="color:var(--on-surface-variant);">Matched items are secured at the campus custody desk until released.</div>
                </div>
              </div>
              <div class="flex items-start gap-sm">
                <div style="width:28px; height:28px; border-radius:8px; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0;">3</div>
                <div>
                  <div class="font-label-lg" style="color:var(--on-surface);">Frictionless ID Release</div>
                  <div class="font-body-sm" style="color:var(--on-surface-variant);">Verify your identity and collect your item at the custody desk.</div>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-xs mt-md" style="padding:0.625rem 0.75rem; background:var(--status-verified-bg); border-radius:8px;">
              <span class="material-symbols-outlined" style="font-size:16px; color:var(--status-verified);">notifications_active</span>
              <span class="font-body-sm" style="color:var(--status-verified); font-weight:600;">You'll receive real-time updates at every step.</span>
            </div>
          </div>

          <!-- Physical Custody Desk (Dark Card) -->
          <div style="border-radius:1rem; padding:1.25rem; background:var(--inverse-surface); color:var(--inverse-on-surface); display:flex; flex-direction:column; gap:0.75rem;">
            <div class="flex items-center gap-sm">
              <span class="material-symbols-outlined" style="font-size:22px;">shield</span>
              <h3 class="font-headline-sm" style="color:#fff;">Physical Custody Desk</h3>
            </div>
            <div style="display:flex; flex-direction:column; gap:0.5rem;">
              <div class="flex items-center gap-sm">
                <span class="material-symbols-outlined" style="font-size:18px; color:var(--inverse-primary);">location_on</span>
                <span class="font-body-md" style="color:var(--inverse-on-surface);">IT Admin Building Room 12</span>
              </div>
              <div class="flex items-center gap-sm">
                <span class="material-symbols-outlined" style="font-size:18px; color:var(--inverse-primary);">call</span>
                <span class="font-body-md" style="color:var(--inverse-on-surface);">03000000000</span>
              </div>
              <div class="flex items-center gap-sm">
                <span class="material-symbols-outlined" style="font-size:18px; color:var(--inverse-primary);">schedule</span>
                <span class="font-body-md" style="color:var(--inverse-on-surface);">Mon&ndash;Fri, 8 AM &ndash; 7 PM</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div><!-- end 12-col grid -->

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
  <script>
    (function() {
      'use strict';

      var form = document.getElementById('lostItemForm');

      // --- Color Swatch Selection (stores value in hidden input) ---
      var swatchGroups = document.querySelectorAll('.color-swatch-group');
      swatchGroups.forEach(function(group) {
        group.querySelectorAll('.color-swatch').forEach(function(swatch) {
          swatch.addEventListener('click', function() {
            group.querySelectorAll('.color-swatch').forEach(function(s) {
              s.classList.remove('active');
            });
            swatch.classList.add('active');
            document.getElementById('color').value = swatch.dataset.color;
          });
        });
      });

      // --- Client-side visual validation (server remains source of truth) ---
      if (form) {
        form.addEventListener('submit', function(e) {
          var requiredFields = form.querySelectorAll('[required]');
          var allValid = true;
          requiredFields.forEach(function(field) {
            if (!field.value || !field.value.trim()) {
              allValid = false;
              field.style.borderColor = 'var(--status-lost)';
            } else {
              field.style.borderColor = '';
            }
          });
          var descField = form.querySelector('textarea[minlength]');
          if (descField && descField.value.trim().length < 20) {
            allValid = false;
            descField.style.borderColor = 'var(--status-lost)';
          }
          if (allValid) {
            form.submit();
          }
        });
      }

      // --- Save Draft (visual affordance only) ---
      var saveDraftBtn = document.getElementById('saveDraftBtn');
      if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function() {
          var originalText = saveDraftBtn.innerHTML;
          saveDraftBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px;">check</span> Draft Saved';
          saveDraftBtn.disabled = true;
          setTimeout(function() {
            saveDraftBtn.innerHTML = originalText;
            saveDraftBtn.disabled = false;
          }, 2000);
        });
      }

      // --- Photo filename preview ---
      function attachPreview(inputId) {
        var input = document.getElementById(inputId);
        if (input) {
          input.addEventListener('change', function() {
            if (input.files && input.files[0]) {
              var label = input.closest('label');
              var sub = label.querySelector('div.font-body-sm');
              if (sub) sub.textContent = input.files[0].name;
            }
          });
        }
      }
      attachPreview('image_1');
      attachPreview('image_2');
      attachPreview('image_3');
    })();
  </script>

</body>
</html>