<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Logout
 */

define('ZURIHUB', true);
require_once __DIR__ . '/includes/auth.php';

Auth::logout();
redirect('/admin/login.php');
