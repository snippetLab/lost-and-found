<?php
/**
 * Google Sign-In — OAuth 2.0 callback.
 * Receives the authorization code, exchanges it for tokens,
 * loads the Google profile, then logs the user in or creates a
 * normal student account. Must match the redirect URI in Google Cloud.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/google_oauth.php';

function googleFail(string $logMessage, string $flashMessage): void {
    error_log('google_oauth: ' . $logMessage);
    setFlash('error', $flashMessage);
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

// 1) Verify CSRF state.
$state = $_GET['state'] ?? '';
$expected = $_SESSION['google_oauth_state'] ?? null;
unset($_SESSION['google_oauth_state']);
if ($expected === null || !is_string($expected) || !is_string($state) || !hash_equals($expected, $state)) {
    googleFail('state mismatch', 'Google Sign-In could not be completed. Please try again.');
}

// 2) User cancelled / Google returned an error.
if (isset($_GET['error'])) {
    setFlash('error', 'Google Sign-In was cancelled or not authorized. Please try again or use email and password.');
    header('Location: ' . appUrl('sign-in.php'));
    exit;
}

// 3) Require an authorization code.
$code = $_GET['code'] ?? '';
if ($code === '' || !googleConfigured()) {
    googleFail('missing code or credentials', 'Google Sign-In could not be completed. Please try again.');
}

$redirectUri = googleRedirectUri();

// 4) Exchange the authorization code for an access token (server-side only).
$tokenCh = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($tokenCh, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $code,
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => $redirectUri,
        'grant_type'    => 'authorization_code',
    ]),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT        => 15,
]);
$tokenBody  = curl_exec($tokenCh);
$tokenHttp  = (int)curl_getinfo($tokenCh, CURLINFO_RESPONSE_CODE);
$tokenErrno = curl_errno($tokenCh);
curl_close($tokenCh);

if ($tokenErrno !== 0 || $tokenBody === false) {
    googleFail('token exchange network error (' . $tokenErrno . ')', 'Could not reach Google. Please try again later.');
}

$tokens = json_decode((string)$tokenBody, true);
if ($tokenHttp !== 200 || !is_array($tokens) || empty($tokens['access_token'])) {
    googleFail('token exchange failed HTTP ' . $tokenHttp . ': ' . substr((string)$tokenBody, 0, 300), 'Google Sign-In could not be completed. Please try again.');
}

// 5) Fetch the Google profile with the access token.
$infoCh = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($infoCh, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $tokens['access_token']],
    CURLOPT_TIMEOUT        => 15,
]);
$infoBody  = curl_exec($infoCh);
$infoHttp  = (int)curl_getinfo($infoCh, CURLINFO_RESPONSE_CODE);
$infoErrno = curl_errno($infoCh);
curl_close($infoCh);

if ($infoErrno !== 0 || $infoBody === false) {
    googleFail('userinfo network error (' . $infoErrno . ')', 'Could not retrieve your Google profile. Please try again later.');
}

$profile = json_decode((string)$infoBody, true);
if ($infoHttp !== 200 || !is_array($profile) || empty($profile['email'])) {
    googleFail('userinfo failed HTTP ' . $infoHttp . ': ' . substr((string)$infoBody, 0, 300), 'Google Sign-In could not be completed. Please try again.');
}

// 6) Verify the account is verified and belongs to this client.
if (empty($profile['email_verified'])) {
    googleFail('email not verified for ' . $profile['email'], 'Please verify your email address with Google and try again.');
}
if (!isValidEmail($profile['email'])) {
    googleFail('invalid email from Google: ' . (string)$profile['email'], 'The Google account does not have a valid email address.');
}

$email = strtolower(trim($profile['email']));
$name  = trim((string)($profile['name'] ?? ''));
if ($name === '') {
    $name = explode('@', $email, 2)[0];
}
$name = mb_substr($name, 0, 100);

// 7) Find or create the account (Google accounts are students unless the
//    account is already legitimately configured as an admin).
$db = getDB();

$stmt = $db->prepare('SELECT id, full_name, email, role, status FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    if ($user['status'] !== 'active') {
        setFlash('error', 'Your account has been suspended. Please contact support.');
        header('Location: ' . appUrl('sign-in.php'));
        exit;
    }
    loginUser($user);
    setFlash('success', 'Welcome back, ' . sanitize($user['full_name']) . '!');
    header('Location: ' . appUrl('index.php'));
    exit;
}

// New Google account → normal student account. A random unusable password
// keeps the NOT NULL password column satisfied without a schema change.
$placeholderPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

$stmt = $db->prepare('INSERT INTO users (full_name, email, password, role, points_balance) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$name, $email, $placeholderPassword, 'student', 0]);

$newUserId = $db->lastInsertId();
$stmt = $db->prepare('SELECT id, full_name, email, role, status FROM users WHERE id = ?');
$stmt->execute([$newUserId]);
$newUser = $stmt->fetch();

loginUser($newUser);

// Match the existing registration behaviour (welcome bonus).
$db->prepare('UPDATE users SET points_balance = points_balance + 10 WHERE id = ?')->execute([$newUserId]);
$db->prepare('INSERT INTO points_transactions (user_id, points, transaction_type, description) VALUES (?, ?, ?, ?)')
   ->execute([$newUserId, 10, 'bonus', 'Welcome bonus for joining the Campus Lost & Found Portal']);

setFlash('success', 'Account created successfully! Welcome to Campus Lost & Found. You earned 10 bonus points!');
header('Location: ' . appUrl('index.php'));
exit;