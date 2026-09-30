<?php
/**
 * Database Configuration
 * 
 * Update the values below to match your MySQL setup.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'lost_found_db');
define('DB_USER', 'root');         // Change to your MySQL username
define('DB_PASS', '');             // Change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

/**
 * Application base URL path (e.g. '/foubdly' when running from
 * http://localhost/foubdly). Auto-detected once from the document root,
 * so the app also works when installed directly at the web root.
 * Use appUrl() (includes/functions.php) to build app-absolute URLs.
 */
if (!defined('BASE_PATH')) {
    $appRootDir  = realpath(__DIR__ . '/..');
    $documentRootDir = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    if ($appRootDir !== false && $documentRootDir !== false) {
        $appRoot = str_replace('\\', '/', $appRootDir);
        $docRoot = str_replace('\\', '/', rtrim($documentRootDir, '/\\'));
        if ($appRoot === $docRoot) {
            define('BASE_PATH', '');
        } elseif (strpos($appRoot, $docRoot . '/') === 0) {
            define('BASE_PATH', rtrim(substr($appRoot, strlen($docRoot)), '/'));
        } else {
            define('BASE_PATH', '');
        }
    } else {
        define('BASE_PATH', '');
    }
}

/**
 * Get PDO database connection
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please check your configuration.');
        }
    }
    return $pdo;
}
