<?php
/**
 * Profile Update Handler
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('profile.php'));
    exit;
}

requireLogin();
validateCSRF();

$db = getDB();
$userId = getCurrentUserId();

$updateType = $_POST['update_type'] ?? 'profile';
$errors = [];

$fullName = trim($_POST['full_name'] ?? '');
$mobile   = trim($_POST['mobile'] ?? '');
$newPassword = $_POST['new_password'] ?? '';
$confirmNew  = $_POST['confirm_password'] ?? '';
$currentPass = $_POST['current_password'] ?? '';

if ($updateType === 'password') {
    // Password-only update
    $fullName = null;
    $mobile = null;

    if (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }
    if ($newPassword !== $confirmNew) {
        $errors[] = 'New passwords do not match.';
    }
    $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $storedHash = $stmt->fetchColumn();
    if (!password_verify($currentPass, $storedHash ?: '')) {
        $errors[] = 'Current password is incorrect.';
    }
} else {
    // Profile update (with optional password change)
    if (empty($fullName)) {
        $errors[] = 'Full name is required.';
    } elseif (strlen($fullName) < 2 || strlen($fullName) > 100) {
        $errors[] = 'Full name must be between 2 and 100 characters.';
    }
    if (!empty($mobile) && !isValidMobile($mobile)) {
        $errors[] = 'Please enter a valid mobile number.';
    }
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
        if ($newPassword !== $confirmNew) {
            $errors[] = 'New passwords do not match.';
        }
        $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $storedHash = $stmt->fetchColumn();
        if (!password_verify($currentPass, $storedHash ?: '')) {
            $errors[] = 'Current password is incorrect.';
        }
    }
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ' . appUrl('profile.php'));
    exit;
}

// Apply changes
if ($updateType === 'password') {
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hashed, $userId]);
    setFlash('success', 'Password updated successfully!');
} else {
    if (!empty($newPassword)) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE users SET full_name = ?, mobile = ?, password = ? WHERE id = ?');
        $stmt->execute([$fullName, $mobile, $hashed, $userId]);
    } else {
        $stmt = $db->prepare('UPDATE users SET full_name = ?, mobile = ? WHERE id = ?');
        $stmt->execute([$fullName, $mobile, $userId]);
    }

    // Update session name
    $_SESSION['user_name'] = $fullName;

    // Optional profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage('profile_image', __DIR__ . '/../../uploads/profiles');
        if ($uploaded !== null) {
            $db->prepare('UPDATE users SET profile_image = ? WHERE id = ?')->execute([$uploaded, $userId]);
        } else {
            setFlash('error', 'Profile image is invalid. Please upload JPG, PNG, GIF or WebP under 10MB.');
            header('Location: ' . appUrl('profile.php'));
            exit;
        }
    }

    setFlash('success', 'Profile updated successfully!');
}

header('Location: ' . appUrl('profile.php'));
exit;