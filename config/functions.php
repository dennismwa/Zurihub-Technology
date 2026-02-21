<?php
/**
 * ZURIHUB TECHNOLOGY - Helper Functions
 * Common utility functions used throughout the application
 */

define('ZURIHUB', true);
require_once __DIR__ . '/database.php';

/**
 * Sanitize user input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a unique reference number
 */
function generateRefNo($prefix = 'ZRH') {
    return $prefix . date('Ymd') . strtoupper(substr(uniqid(), -5));
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'KES') {
    $symbols = [
        'KES' => 'KSh',
        'USD' => '$',
        'GBP' => '£',
        'EUR' => '€'
    ];
    $symbol = $symbols[$currency] ?? $currency;
    return $symbol . ' ' . number_format($amount, 0);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Format relative time
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Get client IP address
 */
function getClientIP() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return 'UNKNOWN';
}

/**
 * Send email using PHPMailer or mail()
 */
function sendEmail($to, $subject, $body, $cc = null, $attachments = []) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
    
    if ($cc) {
        $headers .= "Cc: " . $cc . "\r\n";
    }
    
    $emailBody = getEmailTemplate($subject, $body);
    
    $sent = @mail($to, $subject, $emailBody, $headers);
    
    // Log the email
    logEmail($to, $cc, $subject, $body, $sent ? 'sent' : 'failed');
    
    return $sent;
}

/**
 * Get HTML email template
 */
function getEmailTemplate($subject, $content) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
    </head>
    <body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;">
            <tr>
                <td style="padding:30px;background:#101F4C;text-align:center;">
                    <img src="' . SITE_URL . '/assets/zurihub-logo.png" alt="' . SITE_NAME . '" style="height:50px;">
                </td>
            </tr>
            <tr>
                <td style="padding:40px 30px;">
                    ' . $content . '
                </td>
            </tr>
            <tr>
                <td style="padding:30px;background:#f8f9fa;text-align:center;font-size:12px;color:#666;">
                    <p style="margin:0 0 10px;">© ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>
                    <p style="margin:0;">
                        <a href="' . SITE_URL . '" style="color:#3B82F6;">Website</a> | 
                        <a href="mailto:' . SITE_EMAIL . '" style="color:#3B82F6;">Contact Us</a>
                    </p>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

/**
 * Log email to database
 */
function logEmail($to, $cc, $subject, $body, $status, $error = null) {
    try {
        query("INSERT INTO email_logs (to_email, cc_email, subject, body, status, error_message) VALUES (?, ?, ?, ?, ?, ?)", 
            [$to, $cc, $subject, $body, $status, $error]);
    } catch (Exception $e) {
        error_log("Failed to log email: " . $e->getMessage());
    }
}

/**
 * Log activity
 */
function logActivity($action, $entityType = null, $entityId = null, $description = null, $oldValues = null, $newValues = null) {
    try {
        $userId = $_SESSION['admin_id'] ?? null;
        query("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, old_values, new_values, ip_address, user_agent) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $userId,
            $action,
            $entityType,
            $entityId,
            $description,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Get site setting
 */
function getSetting($key, $default = null) {
    static $settings = null;
    
    if ($settings === null) {
        try {
            $rows = fetchAll("SELECT setting_key, setting_value FROM site_settings");
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            return $default;
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Update site setting
 */
function updateSetting($key, $value) {
    query("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
}

/**
 * Generate slug from string
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Handle file upload
 */
function uploadFile($file, $directory = 'uploads', $allowedTypes = null) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed'];
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File too large'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = $allowedTypes ?? array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOC_TYPES);
    
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    $uploadDir = dirname(__DIR__) . '/uploads/' . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => '/uploads/' . $directory . '/' . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to move file'];
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Success JSON response
 */
function jsonSuccess($message, $data = []) {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
}

/**
 * Error JSON response
 */
function jsonError($message, $statusCode = 400) {
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

/**
 * CSRF token generation
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token verification
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Flash message setter
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash message getter
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get pagination data
 */
function paginate($total, $perPage, $currentPage) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}
