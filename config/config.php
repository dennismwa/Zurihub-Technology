<?php
/**
 * ZURIHUB TECHNOLOGY - Configuration File
 * Contains database credentials and global settings
 */

// Prevent direct access
if (!defined('ZURIHUB')) {
    die('Direct access not permitted');
}

// Environment (development, production)
define('ENVIRONMENT', 'development');

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'zurihubc_Technology');
define('DB_USER', 'zurihubc_Technology');
define('DB_PASS', 'Ye]PwVZwV[-5X[FL');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'Zurihub Technology');
define('SITE_URL', 'https://zurihub.co.ke');
define('SITE_EMAIL', 'info@zurihub.co.ke');
define('CC_EMAIL', 'mwangidennis546@gmail.com');

// Admin Configuration
define('ADMIN_PATH', '/admin');
define('ADMIN_EMAIL', 'info@zurihub.co.ke');

// Security
define('SECURE_AUTH_KEY', 'zurihub_' . md5('zurihub_secure_key_2024'));
define('SESSION_LIFETIME', 3600); // 1 hour

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx']);
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');

// Timezone
date_default_timezone_set('Africa/Nairobi');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
