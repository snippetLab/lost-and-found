<?php
/**
 * CSRF Protection
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF token and store it in the session
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get a hidden input field with the CSRF token
 */
function csrfField(): string {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify a submitted CSRF token against the session token
 */
function verifyCSRFToken(?string $submittedToken): bool {
    if (empty($submittedToken) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submittedToken);
}

/**
 * Validate CSRF token from POST request, send 403 on failure
 */
function validateCSRF(): void {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Invalid security token. Please go back and try again.');
    }
    // Regenerate after use
    unset($_SESSION['csrf_token']);
}
