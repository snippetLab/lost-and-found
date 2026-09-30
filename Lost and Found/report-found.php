<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    setFlash('error', 'Please sign in to report a found item.');
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
    <title>Report Found Item — Campus Lost &amp; Found Portal</title>
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

    <?php $activeNav = 'report-found'; require __DIR__ . '/includes/partials/site_header.php'; ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-bar">
        <div class="container breadcrumb-inner">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="index.php" class="breadcrumb-link">Home</a>
                <span class="breadcrumb-sep material-symbols-outlined">chevron_right</span>
                <span class="breadcrumb-link">Campus Desk</span>
                <span class="breadcrumb-sep material-symbols-outlined">chevron_right</span>
                <span class="breadcrumb-current">Report Found Item</span>
            </nav>
            <div class="custody-intake-badge">
                <span class="material-symbols-outlined badge-icon">verified</span>
                <span class="badge-text">Campus Custody Intake Registry</span>
                <span class="pulse-dot"></span>
            </div>
        </div>
    </section>

    <!-- Headline Section -->
    <section class="headline-section">
        <div class="container headline-grid">
            <div class="headline-content">
                <span class="overline-label">Custodial Intake Registry</span>
                <h1 class="display-hero">Report a Found Campus Item</h1>
                <p class="headline-desc">Register items discovered on campus grounds to initiate the custody hand-off process and reunite belongings with their rightful owners through our secure escrow system.</p>
            </div>
            <div class="vault-info-card">
                <div class="vault-card-header">
                    <span class="material-symbols-outlined vault-icon">lock_vault</span>
                    <span class="vault-label">Custody Desk Vault</span>
                </div>
                <div class="vault-card-body">
                    <div class="vault-status-row">
                        <span class="vault-status-dot active"></span>
                        <span class="vault-status-text">Vault Operational</span>
                    </div>
                    <div class="vault-slots">
                        <span class="vault-slot free">12-A</span>
                        <span class="vault-slot free">12-B</span>
                        <span class="vault-slot occupied">12-C</span>
                        <span class="vault-slot free">12-D</span>
                    </div>
                    <span class="vault-uptime">Uptime: 99.7% — Last sync: 2 min ago</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Good Samaritan Protection Banner -->
    <section class="samaritan-banner">
        <div class="container samaritan-inner">
            <div class="samaritan-left">
                <span class="material-symbols-outlined samaritan-icon">volunteer_activism</span>
                <div class="samaritan-text">
                    <h2 class="samaritan-heading">Good Samaritan Protection &amp; Escrow Guarantee</h2>
                    <p class="samaritan-desc">All findings registered through the Campus Custody Desk are protected under University Policy 14.C — Good Samaritan Clause. Finders are shielded from liability and may qualify for points rewards upon verified reunion.</p>
                </div>
            </div>
            <div class="samaritan-badge-wrap">
                <span class="vault-12b-badge">
                    <span class="material-symbols-outlined">lock_open</span>
                    Vault 12-B Open
                </span>
            </div>
        </div>
    </section>

    <!-- Progress Tracker -->
    <section class="progress-tracker-section">
        <div class="container">
            <div class="progress-tracker">
                <div class="progress-track">
                    <div class="progress-fill" style="width: 12.5%"></div>
                </div>
                <div class="progress-step active">
                    <div class="step-circle">
                        <span class="step-num">1</span>
                        <span class="material-symbols-outlined step-check" style="display:none">check</span>
                    </div>
                    <span class="step-label">Item Info</span>
                </div>
                <div class="progress-step">
                    <div class="step-circle">
                        <span class="step-num">2</span>
                        <span class="material-symbols-outlined step-check" style="display:none">check</span>
                    </div>
                    <span class="step-label">Location &amp; Time</span>
                </div>
                <div class="progress-step">
                    <div class="step-circle">
                        <span class="step-num">3</span>
                        <span class="material-symbols-outlined step-check" style="display:none">check</span>
                    </div>
                    <span class="step-label">Photos &amp; Privacy</span>
                </div>
                <div class="progress-step">
                    <div class="step-circle">
                        <span class="step-num">4</span>
                        <span class="material-symbols-outlined step-check" style="display:none">check</span>
                    </div>
                    <span class="step-label">Custody Hand-off</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container main-grid">

            <!-- Form Column (8 cols) -->
            <div class="form-column">

                <div style="margin-bottom:1rem;"><?php displayFlash(); ?></div>

                <form action="php/items/report_found.php" method="post" enctype="multipart/form-data" novalidate>
                <?php echo csrfField(); ?>

                <!-- Section 1: Item Classification -->
                <div class="form-section" id="section-item-info">
                    <div class="section-header">
                        <span class="section-number">1</span>
                        <div class="section-title-group">
                            <h2 class="section-title">Item Classification</h2>
                            <p class="section-subtitle">Categorize the found item for proper escrow handling</p>
                        </div>
                    </div>

                    <div class="category-radio-grid">
                        <label class="category-option selected">
                            <input type="radio" name="category" value="electronics" class="sr-only" checked>
                            <div class="category-card">
                                <span class="material-symbols-outlined category-icon">devices</span>
                                <span class="category-name">Electronics</span>
                                <span class="category-sub">Phones, tablets, chargers</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="id-cards" class="sr-only">
                            <div class="category-card">
                                <span class="material-symbols-outlined category-icon">badge</span>
                                <span class="category-name">ID &amp; Cards</span>
                                <span class="category-sub">Student IDs, credit cards</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="keys-fobs" class="sr-only">
                            <div class="category-card">
                                <span class="material-symbols-outlined category-icon">key</span>
                                <span class="category-name">Keys &amp; FOBs</span>
                                <span class="category-sub">Keys, access fobs, keychains</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="bags-packs" class="sr-only">
                            <div class="category-card">
                                <span class="material-symbols-outlined category-icon">backpack</span>
                                <span class="category-name">Bags &amp; Packs</span>
                                <span class="category-sub">Backpacks, purses, totes</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="books-pens" class="sr-only">
                            <div class="category-card">
                                <span class="material-symbols-outlined category-icon">menu_book</span>
                                <span class="category-name">Books &amp; Pens</span>
                                <span class="category-sub">Textbooks, notebooks, pens</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="clothing" class="sr-only">
                            <div class="category-card">
                                <span class="material-symbols-outlined category-icon">checkroom</span>
                                <span class="category-name">Clothing</span>
                                <span class="category-sub">Jackets, scarves, hats</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="wallets" class="sr-only">
                            <div class="category-card">
                                <span class="material-symbols-outlined category-icon">wallet</span>
                                <span class="category-name">Wallets</span>
                                <span class="category-sub">Wallets, cardholders, pouches</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="other" class="sr-only">
                            <div class="category-card">
                                <span class="material-symbols-outlined category-icon">category</span>
                                <span class="category-name">Other</span>
                                <span class="category-sub">Miscellaneous items</span>
                            </div>
                        </label>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="item-title">Title / Name</label>
                            <input type="text" id="item-title" name="title" class="form-input" placeholder="e.g., Black USB-C Charger" maxlength="150">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="item-brand">Brand</label>
                            <input type="text" id="item-brand" name="brand_model" class="form-input" placeholder="e.g., Apple, Samsung, Anker">
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label">Color</label>
                            <div class="color-swatch-picker" id="colorPicker">
                                <label class="color-swatch selected" title="Black">
                                    <input type="radio" name="color" value="black" class="sr-only" checked>
                                    <span class="swatch-dot" style="background:#1a1a1a"></span>
                                </label>
                                <label class="color-swatch" title="Silver">
                                    <input type="radio" name="color" value="silver" class="sr-only">
                                    <span class="swatch-dot" style="background:#b0b0b0"></span>
                                </label>
                                <label class="color-swatch" title="Blue">
                                    <input type="radio" name="color" value="blue" class="sr-only">
                                    <span class="swatch-dot" style="background:#3b82f6"></span>
                                </label>
                                <label class="color-swatch" title="Red">
                                    <input type="radio" name="color" value="red" class="sr-only">
                                    <span class="swatch-dot" style="background:#ef4444"></span>
                                </label>
                                <label class="color-swatch" title="Yellow">
                                    <input type="radio" name="color" value="yellow" class="sr-only">
                                    <span class="swatch-dot" style="background:#eab308"></span>
                                </label>
                                <label class="color-swatch" title="Green">
                                    <input type="radio" name="color" value="green" class="sr-only">
                                    <span class="swatch-dot" style="background:#22c55e"></span>
                                </label>
                                <label class="color-swatch" title="Purple">
                                    <input type="radio" name="color" value="purple" class="sr-only">
                                    <span class="swatch-dot" style="background:#a855f7"></span>
                                </label>
                                <label class="color-swatch" title="White">
                                    <input type="radio" name="color" value="white" class="sr-only">
                                    <span class="swatch-dot" style="background:#f5f5f5; border:1px solid #ccc"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="item-condition">Condition</label>
                            <select id="item-condition" class="form-select" disabled>
                                <option value="" selected>Visual condition noted at intake</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="item-marks">Distinctive Marks &amp; Description</label>
                        <textarea id="item-marks" name="description" class="form-textarea" rows="3" placeholder="Scratches, engravings, stickers, dents, or any identifying features... (min 20 characters)" minlength="20" maxlength="5000"></textarea>
                    </div>
                </div>

                <!-- Section 2: Location & Timing of Discovery -->
                <div class="form-section" id="section-location-time">
                    <div class="section-header">
                        <span class="section-number">2</span>
                        <div class="section-title-group">
                            <h2 class="section-title">Location &amp; Timing of Discovery</h2>
                            <p class="section-subtitle">Specify where and when the item was found</p>
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="building-zone">Building / Zone</label>
                            <select id="building-zone" name="campus_zone" class="form-select">
                                <option value="" disabled selected>Select building or zone</option>
                                <option value="studevant-hall">Studevant Hall</option>
                                <option value="hamilton-library">Hamilton Library</option>
                                <option value="student-union">Student Union</option>
                                <option value="engineering-center">Engineering Center</option>
                                <option value="science-complex">Science Complex</option>
                                <option value="recreation-center">Recreation Center</option>
                                <option value="arts-building">Arts Building</option>
                                <option value="admin-tower">Administration Tower</option>
                                <option value="parking-structure">Parking Structure</option>
<option value="quad">Central Quad</option>
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
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="exact-spot">Exact Spot</label>
                            <input type="text" id="exact-spot" name="specific_location" class="form-input" placeholder="e.g., 2nd floor lounge, near room 214">
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="discovery-date">Date Found</label>
                            <input type="date" id="discovery-date" name="date_found" class="form-input" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time Period</label>
                            <div class="time-period-btns" id="timePeriodBtns">
                                <button type="button" class="time-btn" data-period="morning">
                                    <span class="material-symbols-outlined">wb_sunny</span> Morning
                                </button>
                                <button type="button" class="time-btn selected" data-period="afternoon">
                                    <span class="material-symbols-outlined">light_mode</span> Afternoon
                                </button>
                                <button type="button" class="time-btn" data-period="evening">
                                    <span class="material-symbols-outlined">wb_twilight</span> Evening
                                </button>
                                <button type="button" class="time-btn" data-period="night">
                                    <span class="material-symbols-outlined">dark_mode</span> Night
                                </button>
                            </div>
                            <input type="hidden" name="time_period" id="timePeriodInput" value="afternoon">
                        </div>
                    </div>

                    <div class="campus-mini-map">
                        <div class="map-placeholder">
                            <span class="material-symbols-outlined map-pin">location_on</span>
                            <span class="map-text">Pinpoint the exact discovery spot on campus so the matching engine can cross-reference nearby reports.</span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Visual Proof & Photos -->
                <div class="form-section" id="section-photos">
                    <div class="section-header">
                        <span class="section-number">3</span>
                        <div class="section-title-group">
                            <h2 class="section-title">Visual Proof &amp; Photos</h2>
                            <p class="section-subtitle">Upload clear photos for identification and verification</p>
                        </div>
                    </div>

                    <div class="privacy-notice">
                        <span class="material-symbols-outlined">info</span>
                        <p>Photos help owners verify their item. Up to 3 images (JPG, PNG, GIF, WebP), max 10MB each.</p>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="upload-zone" for="file-input-1" id="upload-zone">
                                <span class="material-symbols-outlined upload-icon">cloud_upload</span>
                                <p class="upload-title">Photo 1</p>
                                <p class="upload-sub" id="upload-sub-1">click to browse — JPG, PNG up to 10MB</p>
                                <input type="file" id="file-input-1" name="image_1" class="sr-only" accept="image/*">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="upload-zone" for="file-input-2" id="upload-zone-2">
                                <span class="material-symbols-outlined upload-icon">cloud_upload</span>
                                <p class="upload-title">Photo 2</p>
                                <p class="upload-sub" id="upload-sub-2">click to browse — JPG, PNG up to 10MB</p>
                                <input type="file" id="file-input-2" name="image_2" class="sr-only" accept="image/*">
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="upload-zone" for="file-input-3" id="upload-zone-3">
                            <span class="material-symbols-outlined upload-icon">cloud_upload</span>
                            <p class="upload-title">Photo 3</p>
                            <p class="upload-sub" id="upload-sub-3">click to browse — JPG, PNG up to 10MB</p>
                            <input type="file" id="file-input-3" name="image_3" class="sr-only" accept="image/*">
                        </label>
                    </div>
                </div>

                <!-- Section 4: Physical Custody & Hand-Off Status -->
                <div class="form-section" id="section-custody">
                    <div class="section-header">
                        <span class="section-number">4</span>
                        <div class="section-title-group">
                            <h2 class="section-title">Physical Custody &amp; Hand-Off Status</h2>
                            <p class="section-subtitle">Designate custody arrangement for the found item</p>
                        </div>
                    </div>

                    <div class="custody-options">
                        <label class="custody-option selected">
                            <input type="radio" name="custody_status" value="escrow-vault" class="sr-only" checked>
                            <div class="custody-card">
                                <span class="custody-radio-dot">
                                    <span class="radio-inner"></span>
                                </span>
                                <div class="custody-card-content">
                                    <span class="custody-card-title">Surrendered to Room 12 Escrow Vault</span>
                                    <span class="custody-card-desc">Item secured in the automated vault system for safekeeping</span>
                                </div>
                                <span class="recommended-badge">Recommended</span>
                            </div>
                        </label>
                        <label class="custody-option">
                            <input type="radio" name="custody_status" value="i-hold" class="sr-only">
                            <div class="custody-card">
                                <span class="custody-radio-dot">
                                    <span class="radio-inner"></span>
                                </span>
                                <div class="custody-card-content">
                                    <span class="custody-card-title">I Currently Hold The Item</span>
                                    <span class="custody-card-desc">I am temporarily keeping the item until a match is found</span>
                                </div>
                            </div>
                        </label>
                        <label class="custody-option">
                            <input type="radio" name="custody_status" value="front-desk" class="sr-only">
                            <div class="custody-card">
                                <span class="custody-radio-dot">
                                    <span class="radio-inner"></span>
                                </span>
                                <div class="custody-card-content">
                                    <span class="custody-card-title">Left at Building Front Desk / Security Kiosk</span>
                                    <span class="custody-card-desc">Item deposited at a building's front desk or security station</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Section 5: Finder Verification -->
                <div class="form-section" id="section-finder">
                    <div class="section-header">
                        <span class="section-number">5</span>
                        <div class="section-title-group">
                            <h2 class="section-title">Finder Verification &amp; Privacy</h2>
                            <p class="section-subtitle">Your report is linked to your signed-in account</p>
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="finder-name">Full Name</label>
                            <input type="text" id="finder-name" class="form-input" value="<?php echo sanitize($currentUser['full_name']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="finder-email">Email Address</label>
                            <input type="email" id="finder-email" class="form-input" value="<?php echo sanitize($currentUser['email']); ?>" disabled>
                        </div>
                    </div>

                    <div class="finder-checkboxes">
                        <label class="checkbox-label">
                            <input type="checkbox" class="checkbox-input" checked>
                            <span class="checkbox-custom">
                                <span class="material-symbols-outlined">check</span>
                            </span>
                            <span class="checkbox-text">Keep my identity confidential during the claim process</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" class="checkbox-input" checked>
                            <span class="checkbox-custom">
                                <span class="material-symbols-outlined">check</span>
                            </span>
                            <span class="checkbox-text">Notify me when a claim is submitted for this item</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" class="checkbox-input" id="honorCheck" required>
                            <span class="checkbox-custom">
                                <span class="material-symbols-outlined">check</span>
                            </span>
                            <span class="checkbox-text">I agree to the Campus Property Act &amp; Finder's Honor Code (required)</span>
                        </label>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="action-bar">
                    <button type="submit" class="btn btn-primary submit-found-btn" id="submit-found-btn">
                        <span class="material-symbols-outlined">send</span> Submit Found Report
                    </button>
                </div>

                <!-- Legal Footnote -->
                <div class="legal-footnote">
                    <span class="material-symbols-outlined">gavel</span>
                    <p>Submitted under Campus Property Act §14.C — Good Samaritan Protection. All fields are verified against university records. False reports may result in disciplinary action per the Student Code of Conduct.</p>
                </div>

                </form>
            </div>

            <!-- Sidebar (4 cols) -->
            <aside class="sidebar-column">

                <!-- Campus Escrow Hub Card -->
                <div class="sidebar-card escrow-hub-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined">account_balance</span>
                        <h3 class="card-title">Campus Escrow Hub</h3>
                    </div>
                    <div class="card-body">
                        <div class="escrow-info-row">
                            <span class="material-symbols-outlined">location_on</span>
                            <span>Room 12, Admin Tower — Ground Floor</span>
                        </div>
                        <div class="escrow-info-row">
                            <span class="material-symbols-outlined">schedule</span>
                            <span>Mon–Fri: 8:00 AM – 8:00 PM</span>
                        </div>
                        <div class="escrow-info-row">
                            <span class="material-symbols-outlined">call</span>
                            <span>(555) 234-5678 ext. 12</span>
                        </div>
                    </div>
                </div>

                <!-- Custody Golden Rules Card -->
                <div class="sidebar-card golden-rules-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined">emoji_events</span>
                        <h3 class="card-title">Custody Golden Rules</h3>
                    </div>
                    <div class="card-body">
                        <ol class="golden-rules-list">
                            <li>
                                <span class="rule-num">1</span>
                                <div class="rule-text">
                                    <strong>Never leave items unattended</strong>
                                    <span>Always hand off to a verified officer or vault slot.</span>
                                </div>
                            </li>
                            <li>
                                <span class="rule-num">2</span>
                                <div class="rule-text">
                                    <strong>Document everything</strong>
                                    <span>Take 2+ photos before surrender to protect yourself.</span>
                                </div>
                            </li>
                            <li>
                                <span class="rule-num">3</span>
                                <div class="rule-text">
                                    <strong>Report within 24 hours</strong>
                                    <span>Timely reports increase reunion rates by 68%.</span>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Desk Reunion Rate Card -->
                <div class="sidebar-card reunion-rate-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined">analytics</span>
                        <h3 class="card-title">Desk Reunion Rate</h3>
                    </div>
                    <div class="card-body">
                        <div class="donut-chart-wrap">
                            <svg class="donut-chart" viewBox="0 0 120 120" width="120" height="120">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="var(--color-surface-variant, #e0e0e0)" stroke-width="12"/>
                                <circle cx="60" cy="60" r="52" fill="none" stroke="var(--color-primary, #3f51b5)" stroke-width="12" stroke-dasharray="326.7 326.7" stroke-dashoffset="91.5" stroke-linecap="round" transform="rotate(-90 60 60)"/>
                            </svg>
                            <div class="donut-label">
                                <span class="donut-percent">72%</span>
                                <span class="donut-sub">reunited</span>
                            </div>
                        </div>
                    </div>
                </div>

            </aside>

        </div>
    </main>

    <?php require __DIR__ . '/includes/partials/site_footer.php'; ?>

    <script src="js/main.js?v=20260929"></script>
    <script>
        (function() {
            'use strict';

            var form = document.querySelector('.form-column form');

            // ─── Color Swatch Selection ──────────────────────────────
            var colorSwatches = document.querySelectorAll('.color-swatch');
            colorSwatches.forEach(function (swatch) {
                swatch.addEventListener('click', function () {
                    colorSwatches.forEach(function (s) {
                        s.classList.remove('selected');
                    });
                    swatch.classList.add('selected');
                });
            });

            // ─── Time Period Button Toggle ───────────────────────────
            var timeBtns = document.querySelectorAll('#timePeriodBtns .time-btn');
            timeBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    timeBtns.forEach(function (b) {
                        b.classList.remove('selected');
                    });
                    btn.classList.add('selected');
                    document.getElementById('timePeriodInput').value = btn.dataset.period;
                });
            });

            // ─── Category Radio Selection Visual Update ──────────────
            var categoryOptions = document.querySelectorAll('.category-option');
            categoryOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    categoryOptions.forEach(function (opt) {
                        opt.classList.remove('selected');
                    });
                    option.classList.add('selected');
                    var innerRadio = option.querySelector('input[type="radio"]');
                    if (innerRadio) {
                        innerRadio.checked = true;
                    }
                });
            });

            // ─── Custody Option Selection ────────────────────────────
            var custodyOptions = document.querySelectorAll('.custody-option');
            custodyOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    custodyOptions.forEach(function (opt) {
                        opt.classList.remove('selected');
                    });
                    option.classList.add('selected');
                    var innerRadio = option.querySelector('input[type="radio"]');
                    if (innerRadio) {
                        innerRadio.checked = true;
                    }
                });
            });

            // ─── File name feedback on selection ─────────────────────
            [['file-input-1', 'upload-sub-1'], ['file-input-2', 'upload-sub-2'], ['file-input-3', 'upload-sub-3']].forEach(function (pair) {
                var input = document.getElementById(pair[0]);
                var sub = document.getElementById(pair[1]);
                if (input && sub) {
                    input.addEventListener('change', function () {
                        if (input.files && input.files[0]) {
                            sub.textContent = input.files[0].name;
                        }
                    });
                }
            });

        })();
    </script>
</body>
</html>