<?php
/**
 * Google OAuth 2.0 Configuration (server-side only).
 *
 * 1. Create OAuth 2.0 Client credentials in Google Cloud Console
 *    (APIs & Services > Credentials > Create Credentials > OAuth client ID,
 *     application type "Web application").
 * 2. Add the redirect URI below (must match EXACTLY, including http/https,
 *    host, port, path and filename — no trailing slash):
 *        http://localhost/foubdly/php/auth/google_callback.php
 * 3. Set these three values. The secret is never sent to the browser.
 *
 * Values can optionally be supplied via environment variables
 * (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI).
 */

if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') !== false && getenv('GOOGLE_CLIENT_ID') !== '' ? getenv('GOOGLE_CLIENT_ID') : '');
}

if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') !== false && getenv('GOOGLE_CLIENT_SECRET') !== '' ? getenv('GOOGLE_CLIENT_SECRET') : '');
}

/**
 * Optional explicit redirect URI. When empty, the app derives it from the
 * current request so it always matches the URL the user is actually on.
 * Set it explicitly if you ever call google.php from more than one URL/path.
 */
if (!defined('GOOGLE_REDIRECT_URI')) {
    define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') !== false && getenv('GOOGLE_REDIRECT_URI') !== '' ? getenv('GOOGLE_REDIRECT_URI') : '');
}

/**
 * Determine the OAuth redirect URI for this app.
 * e.g. http://localhost/foubdly/php/auth/google_callback.php
 */
function googleRedirectUri(): string {
    if (defined('GOOGLE_REDIRECT_URI') && GOOGLE_REDIRECT_URI !== '') {
        return GOOGLE_REDIRECT_URI;
    }

    $https = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', (string)dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    return $scheme . '://' . $host . $dir . '/google_callback.php';
}

/**
 * Return true when real Google credentials have been configured.
 */
function googleConfigured(): bool {
    return defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== ''
        && defined('GOOGLE_CLIENT_SECRET') && GOOGLE_CLIENT_SECRET !== '';
}