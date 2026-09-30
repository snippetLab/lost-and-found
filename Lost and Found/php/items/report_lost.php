<?php
/**
 * Lost Item Report Handler
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('report-lost.php'));
    exit;
}

requireLogin();
validateCSRF();

$db = getDB();

// Gather and validate all fields
$title          = trim($_POST['title'] ?? '');
$category       = trim($_POST['category'] ?? '');
$brandModel     = trim($_POST['brand_model'] ?? '');
$color          = trim($_POST['color'] ?? '');
$serialNumber   = trim($_POST['serial_number'] ?? '');
$description    = trim($_POST['description'] ?? '');
$dateLost       = trim($_POST['date_lost'] ?? '');
$timePeriod     = trim($_POST['time_period'] ?? '');
$campusZone     = trim($_POST['campus_zone'] ?? '');
$specificLoc    = trim($_POST['specific_location'] ?? '');

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

if (empty($dateLost)) {
    $errors[] = 'Date lost is required.';
} else {
    $d = DateTime::createFromFormat('Y-m-d', $dateLost);
    if (!$d || $d->format('Y-m-d') !== $dateLost) {
        $errors[] = 'Please enter a valid date.';
    } elseif ($d > new DateTime()) {
        $errors[] = 'Date lost cannot be in the future.';
    }
}

if (empty($campusZone) || !in_array($campusZone, allowedZones(), true)) {
    $errors[] = 'Please select a valid campus zone.';
}

if (!empty($timePeriod) && !in_array($timePeriod, allowedTimePeriods(), true)) {
    $errors[] = 'Invalid time period selected.';
}

$images = [];
if (!empty($errors) === false) {
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

// If validation failed, redirect back
if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ' . appUrl('report-lost.php'));
    exit;
}

// Insert the lost item report
$stmt = $db->prepare(
    'INSERT INTO items (user_id, item_type, title, category, description, brand_model, color, serial_number, campus_zone, specific_location, date_lost_found, time_period, image_1, image_2, image_3, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    getCurrentUserId(),
    'lost',
    $title,
    $category,
    $description,
    $brandModel ?: null,
    $color ?: null,
    $serialNumber ?: null,
    $campusZone,
    $specificLoc ?: null,
    $dateLost,
    $timePeriod ?: null,
    $images[0] ?? null,
    $images[1] ?? null,
    $images[2] ?? null,
    'active'
]);

setFlash('success', 'Lost item report submitted successfully! The campus matching network is now looking for matches.');
header('Location: ' . appUrl('my-items.php'));
exit;