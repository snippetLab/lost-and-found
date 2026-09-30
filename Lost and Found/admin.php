<?php
/**
 * Admin Panel - Entry Point Redirect
 * The real dashboard now lives in admin/index.php (folder-based structure).
 * This file stays at the web root so existing "Admin" buttons across the
 * student pages keep working without changes.
 */
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

header('Location: ' . appUrl('admin/index.php'));
exit;