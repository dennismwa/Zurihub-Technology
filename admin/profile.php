<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Profile (Modern Design)
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';

$user = fetchOne("SELECT * FROM admin_users WHERE id = ?", [$currentUser['id']]);
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($fullName)) $errors[] = 'Full name is required';
        if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required';
        
        $existing = fetchOne("SELECT id FROM admin_users WHERE email = ? AND id != ?", [$email, $user['id']]);
        if ($existing) $errors[] = 'Email already in use';
        
        if (empty($errors)) {
            Auth::updateProfile($user['id'], ['full_name' => $fullName, 'email' => $email]);
            $success = 'Profile updated successfully';
            $user['full_name'] = $fullName;
            $user['email'] = $email;
        }
    }
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword)) $errors[] = 'Current password is required';
        if (empty($newPassword) || strlen($newPassword) < 8) $errors[] = 'New password must be at least 8 characters';
        if ($newPassword !== $confirmPassword) $errors[] = 'Passwords do not match';
        
        if (empty($errors)) {
            $result = Auth::updatePassword($user['id'], $currentPassword, $newPassword);
            if ($result['success']) {
                $success = 'Password changed successfully';
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

$recentActivity = fetchAll("
    SELECT * FROM activity_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
", [$user['id']]);
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">My Profile</h1>
        <p class="text-slate-500 text-sm mt-0.5">Manage your account settings and preferences</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <ul class="text-red-700 text-sm space-y-1">
            <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-xl">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <p class="text-emerald-700 text-sm font-medium"><?= htmlspecialchars($success) ?></p>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    <!-- Profile Card -->
    <div class="lg:col-span-1">
        <div class="card p-6 text-center sticky top-24">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/25">
                <span class="text-2xl font-bold text-white"><?= strtoupper(substr($user['full_name'], 0, 2)) ?></span>
            </div>
            <h3 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($user['full_name']) ?></h3>
            <p class="text-sm text-slate-500"><?= htmlspecialchars($user['email']) ?></p>
            <span class="inline-block mt-3 badge badge-success"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></span>
            
            <div class="mt-6 pt-6 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <p>Member since <?= formatDate($user['created_at'], 'F Y') ?></p>
                <?php if ($user['last_login']): ?>
                <p>Last login: <?= timeAgo($user['last_login']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Forms -->
    <div class="lg:col-span-3 space-y-6">
        
        <!-- Profile Info -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="font-semibold text-slate-900">Profile Information</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Update your personal details</p>
                </div>
            </div>
            <form method="POST" class="card-body">
                <input type="hidden" name="action" value="update_profile">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Username</label>
                        <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled class="form-input bg-slate-50 text-slate-500 cursor-not-allowed">
                        <p class="text-xs text-slate-400 mt-1">Username cannot be changed</p>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <input type="text" value="<?= ucfirst(str_replace('_', ' ', $user['role'])) ?>" disabled class="form-input bg-slate-50 text-slate-500 cursor-not-allowed">
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="font-semibold text-slate-900">Change Password</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ensure your account stays secure</p>
                </div>
            </div>
            <form method="POST" class="card-body">
                <input type="hidden" name="action" value="change_password">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" required minlength="8" class="form-input">
                        <p class="text-xs text-slate-400 mt-1">Minimum 8 characters</p>
                    </div>
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" required class="form-input">
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-900">Recent Activity</h3>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivity)): ?>
                <p class="text-slate-500 text-sm text-center py-4">No recent activity</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentActivity as $activity): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 truncate"><?= htmlspecialchars($activity['action']) ?></p>
                            <p class="text-xs text-slate-400"><?= timeAgo($activity['created_at']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
