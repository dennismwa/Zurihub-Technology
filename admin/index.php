<?php
/**
 * Admin Panel - Index Redirect
 * Redirects to login or dashboard based on auth status
 */

session_start();

// If user is logged in, redirect to dashboard
if (isset($_SESSION['admin_user_id']) && !empty($_SESSION['admin_user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Otherwise, redirect to login
header('Location: login.php');
exit;
