<?php
/**
 * Logout Handler - Destroy session and redirect
 */
require_once __DIR__ . '/../../includes/auth.php';

logoutUser();
header('Location: ' . appUrl('sign-in.php'));
exit;