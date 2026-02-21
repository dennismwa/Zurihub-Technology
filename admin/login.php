<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Login (Modern Design)
 */

define('ZURIHUB', true);
require_once __DIR__ . '/includes/auth.php';

if (Auth::check()) {
    redirect('/admin/dashboard.php');
}

$error = '';
$expired = isset($_GET['expired']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $result = Auth::login($username, $password);
        if ($result['success']) {
            redirect('/admin/dashboard.php');
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Zurihub Admin</title>
    <link rel="icon" type="image/png" href="/assets/zurihub-logo.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        
        .login-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }
        
        .glow {
            box-shadow: 0 0 60px rgba(59, 130, 246, 0.15);
        }
        
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30 mb-5">
                <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-1">Welcome back</h1>
            <p class="text-slate-400 text-sm">Sign in to your Zurihub admin account</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl glow p-8">
            
            <?php if ($expired): ?>
            <div class="mb-6 p-4 bg-amber-50 border border-amber-100 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800">Session expired</p>
                    <p class="text-xs text-amber-600 mt-0.5">Please sign in again to continue</p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800">Sign in failed</p>
                    <p class="text-xs text-red-600 mt-0.5"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5" x-data="{ showPwd: false }">
                
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-2">Username or Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input type="text" id="username" name="username" required
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 input-focus focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                               placeholder="Enter username or email">
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input :type="showPwd ? 'text' : 'password'" id="password" name="password" required
                               class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 input-focus focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                               placeholder="Enter password">
                        <button type="button" @click="showPwd = !showPwd" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600 transition">
                            <svg x-show="!showPwd" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPwd" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Remember -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 focus:ring-offset-0">
                        <span class="ml-2 text-sm text-slate-600">Remember me</span>
                    </label>
                </div>
                
                <!-- Submit -->
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3.5 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transform hover:-translate-y-0.5">
                    <span>Sign In</span>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
        </div>
        
        <!-- Back link -->
        <div class="text-center mt-8">
            <a href="/" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Website
            </a>
        </div>
        
        <!-- Footer -->
        <p class="text-center text-slate-500 text-xs mt-6">&copy; <?= date('Y') ?> Zurihub Technology. All rights reserved.</p>
    </div>
    
    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
