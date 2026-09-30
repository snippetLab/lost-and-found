<?php
/**
 * Shared Helper Functions
 */

/**
 * Sanitize string input
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Build an application-absolute URL within the app's base path
 * (e.g. appUrl('sign-in.php') => '/foubdly/sign-in.php').
 * BASE_PATH is defined in config/database.php.
 */
function appUrl(string $path = ''): string {
    $base = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
    return $base . '/' . ltrim($path, '/');
}

/**
 * Validate email format
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate mobile number (basic: 10-15 digits, optional + prefix)
 */
function isValidMobile(string $mobile): bool {
    return preg_match('/^\+?[0-9]{10,15}$/', $mobile) === 1;
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlash(): void {
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'] === 'error' ? 'error' : 'success';
        $bg = $type === 'error' ? 'var(--status-lost-bg)' : 'var(--status-found-bg)';
        $color = $type === 'error' ? 'var(--status-lost)' : 'var(--status-found)';
        $icon = $type === 'error' ? 'error' : 'check_circle';
        echo '<div style="padding:0.75rem 1rem; border-radius:12px; background:' . $bg . '; color:' . $color . '; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">';
        echo '<span class="material-symbols-outlined" style="font-size:20px;">' . $icon . '</span>';
        echo sanitize($flash['message']);
        echo '</div>';
    }
}

/**
 * Allowed item categories
 */
function allowedCategories(): array {
    return [
        'electronics', 'id-cards', 'keys', 'keys-fobs',
        'backpacks', 'bags-packs', 'books', 'books-pens',
        'clothing', 'accessories', 'wallets', 'other'
    ];
}

/**
 * Allowed campus zones
 */
function allowedZones(): array {
    return [
        'library', 'engineering', 'student-union', 'science-hall',
        'science-building', 'sports-complex', 'dorms', 'residence-halls',
        'dining', 'campus-transit', 'admin-building', 'arts-center',
        'athletic-center', 'campus-green', 'parking-area-b',
        'central-library', 'academic-hall-4', 'hamilton-library',
        'studevant-hall', 'engineering-center', 'science-complex',
        'recreation-center', 'arts-building', 'admin-tower',
        'parking-structure', 'quad',
        'Cosunselling Room',
        'Music Room',
        'Badminton Court',
        'M S Dhoni Cricket Wicket',
        'Janardhan Singh Traditional Sports Arena - A',
        'Janardhan Singh Traditional Sports Arena - B',
        'Jimmy George Volleyball Court',
        'Jimmy George Beach Volleyball Court',
        'K D Jadhav Sports Arena',
        'Kapildev Cricket Ground',
        'Major Dhyanchand Sports Arena - Basketball'
    ];
}

/**
 * Allowed time periods
 */
function allowedTimePeriods(): array {
    return ['morning', 'afternoon', 'evening', 'late-night', 'night', 'unknown'];
}

/**
 * Allowed item statuses
 */
function allowedItemStatuses(): array {
    return ['active', 'matched', 'returned', 'closed'];
}

/**
 * Upload an image file safely
 * Returns the stored filename or null on failure
 */
function uploadImage(string $inputName, string $uploadDir): ?string {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$inputName];
    $maxSize = 10 * 1024 * 1024; // 10MB

    if ($file['size'] > $maxSize) {
        return null;
    }

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    $safeFilename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = $uploadDir . '/' . $safeFilename;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $safeFilename;
    }

    return null;
}

/**
 * Get relative upload path for display (relative so it resolves
 * correctly inside the app's subdirectory, e.g. /foubdly)
 */
function getUploadPath(string $dir, string $filename): string {
    return $dir . '/' . $filename;
}

/**
 * Pagination helper
 */
function paginate(int $totalItems, int $perPage, int $currentPage): array {
    $totalPages = max(1, (int)ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total_items'  => $totalItems,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
    ];
}

/**
 * Time ago helper
 */
function timeAgo(string $datetime): string {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) {
        if ($diff->d == 1) return 'Yesterday';
        if ($diff->d < 7) return $diff->d . ' days ago';
        return floor($diff->d / 7) . ' week' . (floor($diff->d / 7) > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Category display name
 */
function categoryName(string $slug): string {
    $names = [
        'electronics'  => 'Electronics',
        'id-cards'     => 'ID & Cards',
        'keys'         => 'Keys & Keychains',
        'keys-fobs'    => 'Keys & FOBs',
        'backpacks'    => 'Backpacks',
        'bags-packs'   => 'Bags & Packs',
        'books'        => 'Books & Notebooks',
        'books-pens'   => 'Books & Pens',
        'clothing'     => 'Clothing',
        'accessories'  => 'Accessories',
        'wallets'      => 'Wallets',
        'other'        => 'Other',
    ];
    return $names[$slug] ?? ucfirst(str_replace('-', ' ', $slug));
}

/**
 * Zone display name
 */
function zoneName(string $slug): string {
    $names = [
        'library'           => 'Library',
        'engineering'       => 'Engineering',
        'student-union'     => 'Student Union',
        'science-hall'      => 'Science Hall',
        'science-building'  => 'Science Building (SB)',
        'sports-complex'    => 'Sports Complex',
        'dorms'             => 'Dorms',
        'residence-halls'   => 'Residence Halls',
        'dining'            => 'Dining',
        'campus-transit'    => 'Campus Transit',
        'admin-building'    => 'Admin Building',
        'arts-center'       => 'Arts Center',
        'athletic-center'   => 'Athletic Center',
        'campus-green'      => 'Campus Green',
        'parking-area-b'    => 'Parking Area B',
        'central-library'   => 'Central Library',
        'academic-hall-4'   => 'Academic Hall 4',
        'hamilton-library'  => 'Hamilton Library',
        'studevant-hall'    => 'Studevant Hall',
        'engineering-center'=> 'Engineering Center',
        'science-complex'   => 'Science Complex',
        'recreation-center' => 'Recreation Center',
        'arts-building'     => 'Arts Building',
        'admin-tower'       => 'Administration Tower',
        'parking-structure' => 'Parking Structure',
        'quad'              => 'Central Quad',
        'Cosunselling Room' => 'Cosunselling Room',
        'Music Room'        => 'Music Room',
        'Badminton Court'   => 'Badminton Court',
        'M S Dhoni Cricket Wicket' => 'M S Dhoni Cricket Wicket',
        'Janardhan Singh Traditional Sports Arena - A' => 'Janardhan Singh Traditional Sports Arena - A',
        'Janardhan Singh Traditional Sports Arena - B' => 'Janardhan Singh Traditional Sports Arena - B',
        'Jimmy George Volleyball Court' => 'Jimmy George Volleyball Court',
        'Jimmy George Beach Volleyball Court' => 'Jimmy George Beach Volleyball Court',
        'K D Jadhav Sports Arena' => 'K D Jadhav Sports Arena',
        'Kapildev Cricket Ground' => 'Kapildev Cricket Ground',
        'Major Dhyanchand Sports Arena - Basketball' => 'Major Dhyanchand Sports Arena - Basketball',
    ];
    return $names[$slug] ?? ucfirst(str_replace('-', ' ', $slug));
}
