<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect to home
if (isLoggedIn()) {
    header('Location: ' . appUrl('index.php'));
    exit;
}

$mode = $_GET['mode'] ?? ($_POST['mode'] ?? 'login');
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — Campus Lost & Found Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
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

  <?php require __DIR__ . '/includes/partials/site_header.php'; ?>

  <main class="container py-xl" style="display:flex;flex-direction:column;gap:var(--space-lg);">

    <!-- ===== BREADCRUMB & SECURITY BAR ===== -->
    <div class="flex items-center justify-between flex-wrap gap-sm">
      <nav class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
        <span class="current">Campus Verification &amp; Single Sign-On</span>
      </nav>
      <span class="badge badge-verified">
        <span class="material-symbols-outlined" style="font-size:14px;">verified_user</span>
        SSO &bull; 256-Bit SSL Campus Gate
      </span>
    </div>

    <!-- ===== HEADLINE ===== -->
    <div class="flex flex-col gap-xs" style="max-width:640px;">
      <span class="font-label-sm uppercase tracking-wider text-primary">Central Escrow Authentication</span>
      <h1 class="font-headline-lg">Sign In to Campus Lost &amp; Found</h1>
      <p class="font-body-md text-on-surface-variant">Authenticate with your university credentials or SSO to access the campus lost &amp; found escrow system, track item retrievals, and manage reports.</p>
    </div>

    <!-- ===== MAIN LAYOUT ===== -->
    <div class="grid lg:grid-cols-12 gap-lg">

      <!-- AUTH CARD (7 cols) -->
      <div class="lg:col-span-7">
        <div class="card-static" style="padding:var(--space-xl);display:flex;flex-direction:column;gap:var(--space-lg);">

          <?php if ($flash): ?>
            <div style="padding:0.75rem 1rem; border-radius:12px; background:<?php echo $flash['type'] === 'error' ? 'var(--status-lost-bg)' : 'var(--status-found-bg)'; ?>; color:<?php echo $flash['type'] === 'error' ? 'var(--status-lost)' : 'var(--status-found)'; ?>; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.5rem;">
              <span class="material-symbols-outlined" style="font-size:20px;"><?php echo $flash['type'] === 'error' ? 'error' : 'check_circle'; ?></span>
              <?php echo sanitize($flash['message']); ?>
            </div>
          <?php endif; ?>

          <!-- Persona/Role Tab Switcher -->
          <div class="flex rounded-lg overflow-hidden" style="background:var(--surface-subtle);padding:4px;" id="roleTabBar">
            <button class="flex-1 flex items-center justify-center gap-sm py-sm px-md rounded-md font-label-lg transition-all" id="tabStudent" style="background:var(--surface-card);color:var(--on-surface);box-shadow:var(--shadow-sm);">
              <span class="material-symbols-outlined" style="font-size:20px;">school</span>
              Student &amp; Faculty
            </button>
            <button class="flex-1 flex items-center justify-center gap-sm py-sm px-md rounded-md font-label-lg transition-all" id="tabCustodial" style="background:transparent;color:var(--on-surface-variant);">
              <span class="material-symbols-outlined" style="font-size:20px;">admin_panel_settings</span>
              Custodial / Officer
            </button>
          </div>

          <!-- SSO Buttons -->
          <div class="flex flex-col gap-sm">
            <button class="btn btn-primary btn-lg w-full" style="border-radius:12px;" disabled title="SSO integration requires university identity provider">
              <span class="material-symbols-outlined" style="font-size:20px;">account_balance</span>
              Sign in with Single Sign-On (SSO)
</button>
            <a href="php/auth/google.php" class="btn btn-outline btn-lg w-full" style="border-radius:12px;background:var(--surface-container-low);">
              <svg width="20" height="20" viewBox="0 0 48 48" style="flex-shrink:0;">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0124 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 01-4.087 5.571l.001-.001 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
              </svg>
              Continue with Google
            </a>
          </div>

          <!-- Divider -->
          <div class="flex items-center gap-md">
            <div class="flex-1 border-t" style="border-color:var(--border-subtle);"></div>
            <span class="font-label-sm uppercase tracking-wider text-on-surface-variant" style="white-space:nowrap;">Or Sign In With Campus Credentials</span>
            <div class="flex-1 border-t" style="border-color:var(--border-subtle);"></div>
          </div>

          <!-- Form mode toggle -->
          <div class="flex items-center justify-between">
            <h2 class="font-headline-sm" id="formTitle"><?php echo $mode === 'register' ? 'Create Campus Account' : 'Student Portal Log In'; ?></h2>
            <button type="button" class="font-body-sm text-primary font-semibold" id="toggleMode" style="cursor:pointer;"><?php echo $mode === 'register' ? 'Already have an account? Sign In' : 'No account yet? Create ID'; ?></button>
          </div>

          <!-- Form -->
          <form id="authForm" method="post" action="<?php echo $mode === 'register' ? 'php/auth/register.php' : 'php/auth/login.php'; ?>" class="flex flex-col gap-md" autocomplete="off">
            <?php echo csrfField(); ?>
            <input type="hidden" name="mode" value="<?php echo $mode === 'register' ? 'register' : 'login'; ?>" id="modeInput">
            <!-- Full Name (hidden by default, for registration) -->
            <div class="form-group <?php echo $mode === 'register' ? '' : 'hidden'; ?>" id="groupName">
              <label class="form-label" for="fullName">Full Name <span class="form-label-required">*</span></label>
              <div class="relative">
                <span class="material-symbols-outlined" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:20px;color:var(--on-surface-variant);pointer-events:none;">person</span>
                <input type="text" id="fullName" name="fullName" class="form-input" placeholder="Enter your full name" style="padding-left:42px;" autocomplete="name" <?php echo $mode === 'register' ? 'required' : ''; ?>>
              </div>
            </div>

            <!-- Mobile (hidden by default, for registration) -->
            <div class="form-group <?php echo $mode === 'register' ? '' : 'hidden'; ?>" id="groupMobile">
              <label class="form-label" for="mobile">Mobile Number <span class="form-label-required">*</span></label>
              <div class="relative">
                <span class="material-symbols-outlined" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:20px;color:var(--on-surface-variant);pointer-events:none;">phone_iphone</span>
                <input type="tel" id="mobile" name="mobile" class="form-input" placeholder="e.g. 03001234567" style="padding-left:42px;" <?php echo $mode === 'register' ? 'required' : ''; ?>>
              </div>
            </div>

<!-- Email -->
            <div class="form-group" id="groupEmail">
              <label class="form-label" for="email">Email Address <span class="form-label-required">*</span></label>
              <div class="relative">
                <span class="material-symbols-outlined" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:20px;color:var(--on-surface-variant);pointer-events:none;">alternate_email</span>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email address" style="padding-left:42px;" required>
              </div>
            </div>

            <!-- Password -->
            <div class="form-group" id="groupPassword">
              <label class="form-label" for="password">
                Password <span class="form-label-required">*</span>
              </label>
              <div class="relative">
                <span class="material-symbols-outlined" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:20px;color:var(--on-surface-variant);pointer-events:none;">key</span>
                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your campus password" style="padding-left:42px;padding-right:42px;" required>
                <button type="button" class="flex items-center justify-center" data-toggle-password="password" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);width:32px;height:32px;border-radius:8px;color:var(--on-surface-variant);cursor:pointer;" onmouseover="this.style.background='var(--surface-subtle)'" onmouseout="this.style.background='transparent'">
                  <span class="material-symbols-outlined" style="font-size:20px;">visibility</span>
                </button>
              </div>
            </div>

            <!-- Confirm Password (hidden by default, for registration) -->
            <div class="form-group <?php echo $mode === 'register' ? '' : 'hidden'; ?>" id="groupConfirm">
              <label class="form-label" for="confirmPassword">Confirm Password <span class="form-label-required">*</span></label>
              <div class="relative">
                <span class="material-symbols-outlined" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:20px;color:var(--on-surface-variant);pointer-events:none;">key</span>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" placeholder="Re-enter your password" style="padding-left:42px;padding-right:42px;" autocomplete="new-password" <?php echo $mode === 'register' ? 'required' : ''; ?>>
                <button type="button" class="flex items-center justify-center" data-toggle-password="confirmPassword" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);width:32px;height:32px;border-radius:8px;color:var(--on-surface-variant);cursor:pointer;" onmouseover="this.style.background='var(--surface-subtle)'" onmouseout="this.style.background='transparent'">
                  <span class="material-symbols-outlined" style="font-size:20px;">visibility</span>
                </button>
              </div>
            </div>

            <!-- Checkbox row -->
            <div class="flex items-center justify-between flex-wrap gap-sm">
              <label class="flex items-center gap-sm cursor-pointer select-none">
                <input type="checkbox" class="form-checkbox" checked>
                <span class="font-body-sm text-on-surface-variant">Trust this campus terminal (30 days)</span>
              </label>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary btn-lg w-full" style="border-radius:12px;" id="submitBtn">
              <span class="material-symbols-outlined" style="font-size:20px;">arrow_forward</span>
              <span id="submitBtnText"><?php echo $mode === 'register' ? 'Create Account & Sign In' : 'Sign In to Portal'; ?></span>
            </button>
          </form>

          <!-- Footer in card -->
          <div class="flex items-center justify-between flex-wrap gap-sm pt-md" style="border-top:1px solid var(--border-subtle);">
            <span class="font-body-sm text-on-surface-variant flex items-center gap-xs">
              <span class="material-symbols-outlined" style="font-size:16px;color:var(--status-found);">shield</span>
              Campus Officer on Duty: Matcher System (Desk 12)
            </span>
            <a href="index.php" class="font-body-sm font-semibold text-primary flex items-center gap-xs hover:underline">
              <span class="material-symbols-outlined" style="font-size:16px;">terminal</span>
              Security Dispatch Terminal
            </a>
          </div>
        </div>
      </div>

      <!-- RIGHT PANEL (5 cols) -->
      <div class="lg:col-span-5 flex flex-col gap-lg">

        <!-- Photo Card -->
        <div class="card overflow-hidden" style="border-radius:1rem;">
          <div class="relative overflow-hidden" style="height:200px;">
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDmJivoHzugH_FZAdjJ4_woSz5WcOMxrIcet1yHgK05DbPUPwy5aFDBYYbyNIwDVF18m_3F4RfDa6aWPacemIauey3ukQC-YCaVVDl7bUJWePUfASsQeHi_Ota0Rddy-t3j40-zLSpmwXaDMwgn108HrvYft29krgGvW8QGWx19LYTedHeH9EQNY0sGKPnKHkSlU_ZsapUEOTC4so3MZTA4yQYTcopG7DJTZB_i8Lw" alt="Custodial office" class="w-full h-full object-cover">
            <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(40,48,68,0.8) 0%, transparent 60%);"></div>
            <div class="absolute" style="bottom:var(--space-md);left:var(--space-md);right:var(--space-md);">
              <span class="font-label-sm uppercase tracking-wider" style="color:rgba(255,255,255,0.7);">Live Custody Vault</span>
              <h3 class="font-headline-md text-white mt-1">IT Admin Room 12 Intake Hub</h3>
              <div class="mt-1">
                <span class="badge badge-found" style="font-size:0.625rem;">
                  <span class="status-dot" style="width:5px;height:5px;"></span>
                  Open for Retrieval
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Verification Guarantees (3 items) -->
        <div class="flex flex-col gap-sm">
          <div class="flex items-center gap-md p-md rounded-xl" style="background:var(--surface-container-low);">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:var(--status-verified-bg);">
              <span class="material-symbols-outlined text-status-verified" style="font-size:22px;">shield_person</span>
            </div>
            <div>
              <span class="font-label-lg text-on-surface">Why Campus Authentication?</span>
              <p class="font-body-sm text-on-surface-variant mt-1">Identity-verified escrow prevents impersonation and ensures only authorized students retrieve items.</p>
            </div>
          </div>
          <div class="flex items-center gap-md p-md rounded-xl" style="background:var(--surface-container-low);">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:var(--status-found-bg);">
              <span class="material-symbols-outlined text-status-found" style="font-size:22px;">privacy_tip</span>
            </div>
            <div>
              <span class="font-label-lg text-on-surface">Student Privacy Shield</span>
              <p class="font-body-sm text-on-surface-variant mt-1">FERPA-compliant data handling ensures your personal information stays confidential within the campus system.</p>
            </div>
          </div>
          <div class="flex items-center gap-md p-md rounded-xl" style="background:var(--surface-container-low);">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:var(--brand-violet-subtle);">
              <span class="material-symbols-outlined text-brand-violet" style="font-size:22px;">bolt</span>
            </div>
            <div>
              <span class="font-label-lg text-on-surface">Instant AI Match Notification</span>
              <p class="font-body-sm text-on-surface-variant mt-1">Get real-time alerts when a found item matches your lost report using AI-powered image recognition.</p>
            </div>
          </div>
        </div>

        <!-- Activity Bar -->
        <div class="flex items-center gap-sm p-md rounded-xl" style="background:var(--status-found-bg);">
          <span class="material-symbols-outlined text-status-found" style="font-size:20px;">local_activity</span>
          <span class="font-label-lg text-status-found">Points &amp; Rewards Available for Returned Items</span>
        </div>

        <!-- Smart Locker Image Card -->
        <div class="card overflow-hidden" style="border-radius:1rem;">
          <div class="relative overflow-hidden" style="height:160px;">
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCXTy3cM6GxQ4Fwog4gLZ9NtiwcpD1ZZfSai-Wnp8AW8PIDg3UVj7UVmduOU55ulzDgpDXhUWx2maSsqeEVjzPWCb41FHoOVqHP6VkR50O8l5yn9tqotzUBCg6wDIMePjprKErOVTkoCoThcOoeVETWE4r-DS-9p3P5YJuiAVITyikaehws8xFJmMqqFZp5aJYp8V2W0Bupc__yGiRq60k3s3W13PKeGqT3bozLpbo" alt="Smart locker" class="w-full h-full object-cover">
            <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(40,48,68,0.75) 0%, transparent 50%);"></div>
            <div class="absolute" style="bottom:var(--space-md);left:var(--space-md);right:var(--space-md);">
              <h3 class="font-headline-sm text-white">Charging Points on Returns</h3>
              <p class="font-body-sm text-white opacity-80 mt-1">Every verified return earns points redeemable at campus facilities.</p>
            </div>
          </div>
        </div>

        <!-- Compliance Bar -->
        <div class="flex items-center justify-between flex-wrap gap-sm p-md rounded-xl" style="background:var(--surface-subtle);">
          <span class="font-body-sm text-on-surface-variant flex items-center gap-xs">
            <span class="material-symbols-outlined" style="font-size:16px;color:var(--primary);">gavel</span>
            Complies with Campus Custody Protocol &sect;12.4
          </span>
          <span class="badge badge-verified" style="font-size:0.625rem;">
            <span class="material-symbols-outlined" style="font-size:13px;">lock</span>
            FERPA Protected
          </span>
        </div>

      </div>
    </div>

    <!-- ===== FAST ASSISTANCE BAR ===== -->
    <div class="flex items-center gap-md p-md rounded-xl flex-wrap" style="background:var(--surface-container-low);border:1px solid var(--border-subtle);">
      <span class="material-symbols-outlined text-primary" style="font-size:24px;">contact_support</span>
      <span class="font-body-lg text-on-surface flex-1">Need immediate physical escrow pickup?</span>
      <a href="tel:+15551234567" class="btn btn-primary btn-sm">
        <span class="material-symbols-outlined" style="font-size:16px;">phone</span>
        Call Desk
      </a>
    </div>

    <!-- ===== HELP DESK FAQ SECTION ===== -->
    <div class="card-static" style="padding:var(--space-xl);">
      <div class="flex flex-col gap-md" style="max-width:720px;">
        <div class="flex items-start gap-md">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:var(--surface-container-high);">
            <span class="material-symbols-outlined text-primary" style="font-size:22px;">help_center</span>
          </div>
          <div>
            <h3 class="font-headline-sm text-on-surface">Forgot your student password?</h3>
            <p class="font-body-md text-on-surface-variant mt-2">Visit the IT Help Desk in person with a valid student ID, or contact support to reset your campus credentials.</p>
          </div>
        </div>
        <div class="flex items-center gap-sm flex-wrap">
          <a href="index.php" class="btn btn-outline btn-sm">
            <span class="material-symbols-outlined" style="font-size:16px;">menu_book</span>
            How It Works
          </a>
          <a href="browse.php" class="btn btn-outline btn-sm">
            <span class="material-symbols-outlined" style="font-size:16px;">quiz</span>
            Verification FAQs
          </a>
        </div>
      </div>
    </div>

  </main>

  <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

  <script src="js/main.js?v=20260929"></script>
  <script>
    (function() {
      'use strict';

      // --- Role Tab Switching ---
      const tabStudent = document.getElementById('tabStudent');
      const tabCustodial = document.getElementById('tabCustodial');
      const emailInput = document.getElementById('email');
      const activeTabStyle = { background: 'var(--surface-card)', color: 'var(--on-surface)', boxShadow: 'var(--shadow-sm)' };
      const inactiveTabStyle = { background: 'transparent', color: 'var(--on-surface-variant)', boxShadow: 'none' };

      function applyTabStyle(btn, active) {
        const s = active ? activeTabStyle : inactiveTabStyle;
        Object.assign(btn.style, s);
      }

      tabStudent.addEventListener('click', function() {
        applyTabStyle(this, true);
        applyTabStyle(tabCustodial, false);
        emailInput.placeholder = 'Enter your email address';
        document.getElementById('formTitle').textContent = 'Student Portal Log In';
      });

      tabCustodial.addEventListener('click', function() {
        applyTabStyle(this, true);
        applyTabStyle(tabStudent, false);
        emailInput.placeholder = 'Enter your email address';
        document.getElementById('formTitle').textContent = 'Officer Portal Log In';
      });

      // --- Sign In / Register Mode Toggle ---
      const toggleMode = document.getElementById('toggleMode');
      const formTitle = document.getElementById('formTitle');
      const groupName = document.getElementById('groupName');
      const groupMobile = document.getElementById('groupMobile');
      const groupConfirm = document.getElementById('groupConfirm');
      const submitBtnText = document.getElementById('submitBtnText');
      const authForm = document.getElementById('authForm');
      const modeInput = document.getElementById('modeInput');
      let isRegister = document.getElementById('modeInput').value === 'register';

      function setMode(register) {
        isRegister = register;
        groupName.classList.toggle('hidden', !register);
        groupMobile.classList.toggle('hidden', !register);
        groupConfirm.classList.toggle('hidden', !register);
        document.getElementById('fullName').required = register;
        document.getElementById('mobile').required = register;
        document.getElementById('confirmPassword').required = register;
        if (modeInput) modeInput.value = register ? 'register' : 'login';
        if (authForm) authForm.action = register ? 'php/auth/register.php' : 'php/auth/login.php';
        if (register) {
          formTitle.textContent = 'Create Campus Account';
          submitBtnText.textContent = 'Create Account & Sign In';
          toggleMode.textContent = 'Already have an account? Sign In';
        } else {
          formTitle.textContent = 'Student Portal Log In';
          submitBtnText.textContent = 'Sign In to Portal';
          toggleMode.textContent = 'No account yet? Create ID';
        }
      }

      toggleMode.addEventListener('click', function() {
        setMode(!isRegister);
      });

      // Show loading state on submit
      authForm.addEventListener('submit', function(e) {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined animate-pulse" style="font-size:20px;">sync</span> Processing...';
      });

    })();
  </script>
</body>
</html>