<?php
/**
 * Found Item Report Handler
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('report-found.php'));
    exit;
}

requireLogin();
validateCSRF();

$db = getDB();

// Gather fields
$title        = trim($_POST['title'] ?? '');
$category     = trim($_POST['category'] ?? '');
$brandModel   = trim($_POST['brand_model'] ?? '');
$color        = trim($_POST['color'] ?? '');
$description  = trim($_POST['description'] ?? '');
$dateFound    = trim($_POST['date_found'] ?? '');
$timePeriod   = trim($_POST['time_period'] ?? '');
$campusZone   = trim($_POST['campus_zone'] ?? '');
$specificLoc  = trim($_POST['specific_location'] ?? '');
$custodyStatus = trim($_POST['custody_status'] ?? 'escrow-vault');

$errors = [];

if (empty($title)) {
    $errors[] = 'Item name is required.';
} elseif (strlen($title) > 150) {
    $errors[] = 'Item name must be under 150 characters.';
}

if (empty($category) || !in_array($category, allowedCategories(), true)) {
    $errors[] = 'Please select a valid category.';
}

if (empty($description) || strlen($description) < 20) {
    $errors[] = 'Description must be at least 20 characters long.';
} elseif (strlen($description) > 5000) {
    $errors[] = 'Description is too long (max 5000 characters).';
}

if (empty($dateFound)) {
    $errors[] = 'Date found is required.';
} else {
    $d = DateTime::createFromFormat('Y-m-d', $dateFound);
    if (!$d || $d->format('Y-m-d') !== $dateFound) {
        $errors[] = 'Please enter a valid date.';
    } elseif ($d > new DateTime()) {
        $errors[] = 'Date found cannot be in the future.';
    }
}

if (empty($campusZone) || !in_array($campusZone, allowedZones(), true)) {
    $errors[] = 'Please select a valid campus zone.';
}

$allowedCustody = ['escrow-vault', 'i-hold', 'front-desk'];
if (empty($custodyStatus) || !in_array($custodyStatus, $allowedCustody, true)) {
    $custodyStatus = 'escrow-vault';
}

$images = [];
if (empty($errors)) {
    for ($i = 1; $i <= 3; $i++) {
        $inputName = 'image_' . $i;
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($inputName, __DIR__ . '/../../uploads/items');
            if ($uploaded !== null) {
                $images[] = $uploaded;
            } else {
                $errors[] = 'Image ' . $i . ' is invalid. Please upload JPG, PNG, GIF or WebP under 10MB.';
                break;
            }
        }
    }
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ' . appUrl('report-found.php'));
    exit;
}

$stmt = $db->prepare(
    'INSERT INTO items (user_id, item_type, title, category, description, brand_model, color, campus_zone, specific_location, date_lost_found, time_period, image_1, image_2, image_3, custody_status, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    getCurrentUserId(),
    'found',
    $title,
    $category,
    $description,
    $brandModel ?: null,
    $color ?: null,
    $campusZone,
    $specificLoc ?: null,
    $dateFound,
    $timePeriod ?: null,
    $images[0] ?? null,
    $images[1] ?? null,
    $images[2] ?? null,
    $custodyStatus,
    'active'
]);

setFlash('success', 'Found item report submitted successfully! Thank you for helping your campus community.');
header('Location: ' . appUrl('my-items.php'));
exit;