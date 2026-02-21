<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Authentication
 * Handles login, logout, and session management
 */

define('ZURIHUB', true);
require_once dirname(__DIR__, 2) . '/config/functions.php';

class Auth {
    
    /**
     * Attempt to login user
     */
    public static function login($username, $password) {
        $user = fetchOne(
            "SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1",
            [$username, $username]
        );
        
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        if (!password_verify($password, $user['password'])) {
            logActivity('login_failed', 'admin_users', $user['id'], "Failed login attempt for: $username");
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Update last login
        query("UPDATE admin_users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        // Set session
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $user['full_name'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_avatar'] = $user['avatar'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        logActivity('login', 'admin_users', $user['id'], "Admin logged in: {$user['username']}");
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        $userId = $_SESSION['admin_id'] ?? null;
        
        if ($userId) {
            logActivity('logout', 'admin_users', $userId, "Admin logged out");
        }
        
        // Clear session
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     */
    public static function check() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    /**
     * Get current user
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email'],
            'name' => $_SESSION['admin_name'],
            'role' => $_SESSION['admin_role'],
            'avatar' => $_SESSION['admin_avatar']
        ];
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($roles) {
        if (!self::check()) return false;
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array($_SESSION['admin_role'], $roles);
    }
    
    /**
     * Require authentication
     */
    public static function requireLogin() {
        if (!self::check()) {
            if (isAjax()) {
                jsonError('Session expired. Please login again.', 401);
            }
            redirect('/admin/login.php');
        }
        
        // Check session timeout
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_LIFETIME)) {
            self::logout();
            if (isAjax()) {
                jsonError('Session expired. Please login again.', 401);
            }
            redirect('/admin/login.php?expired=1');
        }
        
        // Refresh login time on activity
        $_SESSION['login_time'] = time();
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($roles) {
        self::requireLogin();
        
        if (!self::hasRole($roles)) {
            if (isAjax()) {
                jsonError('Access denied', 403);
            }
            redirect('/admin/dashboard.php?error=access_denied');
        }
    }
    
    /**
     * Update password
     */
    public static function updatePassword($userId, $currentPassword, $newPassword) {
        $user = fetchOne("SELECT password FROM admin_users WHERE id = ?", [$userId]);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        query("UPDATE admin_users SET password = ? WHERE id = ?", [$hashedPassword, $userId]);
        
        logActivity('password_change', 'admin_users', $userId, 'Password changed');
        
        return ['success' => true, 'message' => 'Password updated successfully'];
    }
    
    /**
     * Update profile
     */
    public static function updateProfile($userId, $data) {
        $allowed = ['full_name', 'email', 'avatar'];
        $updates = [];
        $params = [];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'No data to update'];
        }
        
        $params[] = $userId;
        query("UPDATE admin_users SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        
        // Update session
        if (isset($data['full_name'])) $_SESSION['admin_name'] = $data['full_name'];
        if (isset($data['email'])) $_SESSION['admin_email'] = $data['email'];
        if (isset($data['avatar'])) $_SESSION['admin_avatar'] = $data['avatar'];
        
        logActivity('profile_update', 'admin_users', $userId, 'Profile updated');
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    }
}

// Helper functions for templates
function isLoggedIn() {
    return Auth::check();
}

function currentUser() {
    return Auth::user();
}

function requireLogin() {
    Auth::requireLogin();
}

function hasRole($roles) {
    return Auth::hasRole($roles);
}
