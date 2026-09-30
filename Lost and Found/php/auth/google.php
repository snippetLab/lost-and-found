<?php
/**
 * Google Sign-In — Start OAuth 2.0 authorization code flow.
 * Redirects the user to accounts.google.com.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/google_oauth.php';

if (!googleConfigured()) {
    setFlash('error', 'Google Sign-In is not configured yet. Please sign in with your email and password.');
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

// Already authenticated? Send them home.
if (isLoggedIn()) {
    header('Location: ' . appUrl('index.php'));
    exit;
}

// State protects the callback against CSRF / login CSRF.
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => googleRedirectUri(),
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'online',
    'prompt'        => 'select_account',
    'state'         => $state,
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

header('Location: ' . $authUrl);
exit;