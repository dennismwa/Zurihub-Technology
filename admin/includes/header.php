<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Header (Modern Design)
 */

require_once __DIR__ . '/auth.php';
requireLogin();

$currentUser = currentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get unread counts for notifications
$unreadQuotations = fetchOne("SELECT COUNT(*) as count FROM quotation_requests WHERE is_read = 0")['count'] ?? 0;
$unreadMessages = fetchOne("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0")['count'] ?? 0;
$unreadApplications = fetchOne("SELECT COUNT(*) as count FROM career_applications WHERE is_read = 0")['count'] ?? 0;
$unreadChatMessages = 0;
$unreadTickets = 0;
try {
    $unreadChatMessages = fetchOne("SELECT COUNT(*) as count FROM chat_messages WHERE sender_type = 'visitor' AND is_read = 0")['count'] ?? 0;
    $unreadTickets = fetchOne("SELECT COUNT(*) as count FROM support_tickets WHERE status IN ('open','in_progress','waiting_reply') AND is_read = 0")['count'] ?? 0;
} catch (Exception $e) {}
$totalUnread = $unreadQuotations + $unreadMessages + $unreadApplications + $unreadChatMessages + $unreadTickets;
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - Zurihub Admin</title>
    <link rel="icon" type="image/png" href="/assets/zurihub-logo.png">
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                        sidebar: {
                            bg: '#0f172a',
                            hover: '#1e293b',
                            active: '#3b82f6',
                            border: '#1e293b',
                        }
                    },
                    boxShadow: {
                        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                        'card': '0 0 0 1px rgba(0,0,0,.05), 0 1px 3px rgba(0,0,0,.1)',
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        * { font-family: 'Inter', system-ui, sans-serif; }
        
        /* Modern Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Sidebar styling */
        .sidebar-section { margin-bottom: 1.5rem; }
        .sidebar-label { 
            font-size: 0.65rem; 
            font-weight: 700; 
            letter-spacing: 0.1em; 
            text-transform: uppercase;
            color: #64748b;
            padding: 0 1rem;
            margin-bottom: 0.5rem;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            margin: 0.125rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #94a3b8;
            transition: all 0.15s ease;
        }
        .sidebar-link:hover {
            background: #1e293b;
            color: #f1f5f9;
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .sidebar-link .badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            min-width: 1.25rem;
            text-align: center;
        }
        
        /* Button styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.15s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 1px 3px rgba(59, 130, 246, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
        }
        .btn-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .btn-success:hover { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
        .btn-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; }
        .btn-danger:hover { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }
        .btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
        .btn-warning:hover { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
        .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.7rem; }
        .btn-icon { padding: 0.5rem; }
        .btn-icon.btn-sm { padding: 0.375rem; }
        
        /* Badge styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: capitalize;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        
        /* Card styles */
        .card {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-body { padding: 1.25rem; }
        
        /* Stat card */
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        .stat-card .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Form inputs */
        .form-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: white;
            transition: all 0.15s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .form-input::placeholder { color: #94a3b8; }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.375rem;
        }
        
        /* Table styles */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .data-table thead th {
            background: #f8fafc;
            padding: 0.75rem 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .data-table tbody td {
            padding: 0.875rem 1rem;
            font-size: 0.875rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        
        /* Action buttons in tables */
        .action-btns {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        .action-btn {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            transition: all 0.15s ease;
            color: #64748b;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        .action-btn:hover { background: #f1f5f9; color: #334155; }
        .action-btn.view:hover { background: #dbeafe; color: #2563eb; }
        .action-btn.edit:hover { background: #d1fae5; color: #059669; }
        .action-btn.delete:hover { background: #fee2e2; color: #dc2626; }
        .action-btn.download:hover { background: #f3e8ff; color: #7c3aed; }
        .action-btn.assign:hover { background: #fef3c7; color: #d97706; }
        
        /* Dropdown menu */
        .dropdown-menu {
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            min-width: 10rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            z-index: 50;
            overflow: hidden;
        }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            font-size: 0.8rem;
            color: #475569;
            transition: all 0.1s;
        }
        .dropdown-item:hover { background: #f8fafc; color: #1e293b; }
        .dropdown-item.danger { color: #dc2626; }
        .dropdown-item.danger:hover { background: #fef2f2; }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }
        .empty-state svg { margin: 0 auto 1rem; opacity: 0.5; }
        
        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .pagination button, .pagination a {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 500;
            border-radius: 0.375rem;
            color: #64748b;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.15s;
        }
        .pagination button:hover, .pagination a:hover { background: #f8fafc; color: #334155; }
        .pagination .active { background: #3b82f6; color: white; border-color: #3b82f6; }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.2s ease-out; }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        .animate-slideIn { animation: slideIn 0.25s ease-out; }
        
        /* Chart container fix */
        .chart-container {
            position: relative;
            width: 100%;
            height: 250px !important;
            max-height: 250px;
        }
        .chart-container canvas {
            max-height: 250px !important;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased" x-data="{ sidebarOpen: false }">
    
    <!-- Mobile overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden"
         x-cloak></div>
    
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 transition-transform duration-200 ease-out lg:translate-x-0">
        
        <!-- Logo -->
        <div class="h-16 px-4 flex items-center justify-between border-b border-slate-800">
            <a href="/admin/dashboard.php" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-white font-bold text-lg">Zurihub</span>
                    <span class="block text-[10px] text-slate-500 font-medium -mt-0.5">ADMIN PANEL</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-2" style="height: calc(100vh - 4rem);">
            
            <!-- Main -->
            <div class="sidebar-section">
                <p class="sidebar-label">Main</p>
                <a href="/admin/dashboard.php" class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/statistics.php" class="sidebar-link <?= $currentPage === 'statistics' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Analytics</span>
                </a>
            </div>
            
            <!-- Inbox -->
            <div class="sidebar-section">
                <p class="sidebar-label">Inbox</p>
                <a href="/admin/quotations.php" class="sidebar-link <?= $currentPage === 'quotations' || $currentPage === 'quotation-view' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Quotations</span>
                    <?php if ($unreadQuotations > 0): ?><span class="badge"><?= $unreadQuotations ?></span><?php endif; ?>
                </a>
                <a href="/admin/messages.php" class="sidebar-link <?= $currentPage === 'messages' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Messages</span>
                    <?php if ($unreadMessages > 0): ?><span class="badge"><?= $unreadMessages ?></span><?php endif; ?>
                </a>
                <a href="/admin/applications.php" class="sidebar-link <?= $currentPage === 'applications' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Applications</span>
                    <?php if ($unreadApplications > 0): ?><span class="badge"><?= $unreadApplications ?></span><?php endif; ?>
                </a>
            </div>
            
            <!-- Support -->
            <div class="sidebar-section">
                <p class="sidebar-label">Support</p>
                <a href="/admin/chat-support.php" class="sidebar-link <?= $currentPage === 'chat-support' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>Live Chat</span>
                    <?php if ($unreadChatMessages > 0): ?><span class="badge"><?= $unreadChatMessages ?></span><?php endif; ?>
                </a>
                <a href="/admin/tickets.php" class="sidebar-link <?= $currentPage === 'tickets' || $currentPage === 'ticket-view' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    <span>Tickets</span>
                    <?php if ($unreadTickets > 0): ?><span class="badge"><?= $unreadTickets ?></span><?php endif; ?>
                </a>
            </div>
            
            <!-- Content -->
            <div class="sidebar-section">
                <p class="sidebar-label">Content</p>
                <a href="/admin/portfolio.php" class="sidebar-link <?= $currentPage === 'portfolio' || $currentPage === 'portfolio-edit' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Portfolio</span>
                </a>
                <a href="/admin/pricing.php" class="sidebar-link <?= $currentPage === 'pricing' || $currentPage === 'pricing-edit' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Pricing</span>
                </a>
                <a href="/admin/testimonials.php" class="sidebar-link <?= $currentPage === 'testimonials' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span>Testimonials</span>
                </a>
            </div>
            
            <!-- Settings -->
            <div class="sidebar-section">
                <p class="sidebar-label">Settings</p>
                <a href="/admin/settings.php" class="sidebar-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Settings</span>
                </a>
                <a href="/admin/profile.php" class="sidebar-link <?= $currentPage === 'profile' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>My Profile</span>
                </a>
                <a href="/" target="_blank" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>View Website</span>
                </a>
            </div>
        </nav>
    </aside>
    
    <!-- Main content -->
    <div class="lg:pl-64 min-h-screen flex flex-col">
        
        <!-- Top header -->
        <header class="sticky top-0 z-30 h-16 bg-white/80 backdrop-blur-md border-b border-slate-200">
            <div class="h-full px-4 sm:px-6 flex items-center justify-between gap-4">
                
                <!-- Left: hamburger + search -->
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="hidden md:flex items-center bg-slate-100 rounded-lg pl-3 pr-4 py-2 w-72">
                        <svg class="w-4 h-4 text-slate-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" placeholder="Search..." class="bg-transparent border-0 text-sm text-slate-600 placeholder-slate-400 w-full focus:outline-none">
                    </div>
                </div>
                
                <!-- Right: notifications + profile -->
                <div class="flex items-center gap-2">
                    
                    <!-- Quick add -->
                    <a href="/admin/portfolio-edit.php" class="hidden sm:flex btn btn-primary btn-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add Project</span>
                    </a>
                    
                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <?php if ($totalUnread > 0): ?>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                            <?php endif; ?>
                        </button>
                        
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="dropdown-menu w-80"
                             x-cloak>
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="font-semibold text-slate-900">Notifications</p>
                                <p class="text-xs text-slate-500"><?= $totalUnread ?> unread</p>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <?php if ($unreadQuotations > 0): ?>
                                <a href="/admin/quotations.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition">
                                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900"><?= $unreadQuotations ?> new quotation<?= $unreadQuotations > 1 ? 's' : '' ?></p>
                                        <p class="text-xs text-slate-500 truncate">Requires attention</p>
                                    </div>
                                </a>
                                <?php endif; ?>
                                <?php if ($unreadMessages > 0): ?>
                                <a href="/admin/messages.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900"><?= $unreadMessages ?> new message<?= $unreadMessages > 1 ? 's' : '' ?></p>
                                        <p class="text-xs text-slate-500 truncate">Contact form submissions</p>
                                    </div>
                                </a>
                                <?php endif; ?>
                                <?php if ($unreadChatMessages > 0): ?>
                                <a href="/admin/chat-support.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition">
                                    <div class="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900"><?= $unreadChatMessages ?> chat message<?= $unreadChatMessages > 1 ? 's' : '' ?></p>
                                        <p class="text-xs text-slate-500 truncate">Live chat awaiting reply</p>
                                    </div>
                                </a>
                                <?php endif; ?>
                                <?php if ($unreadTickets > 0): ?>
                                <a href="/admin/tickets.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition">
                                    <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900"><?= $unreadTickets ?> open ticket<?= $unreadTickets > 1 ? 's' : '' ?></p>
                                        <p class="text-xs text-slate-500 truncate">Support tickets pending</p>
                                    </div>
                                </a>
                                <?php endif; ?>
                                <?php if ($totalUnread === 0): ?>
                                <div class="px-4 py-8 text-center">
                                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    <p class="text-sm text-slate-500">All caught up!</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-semibold shadow">
                                <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium text-slate-900 leading-tight"><?= htmlspecialchars($currentUser['name']) ?></p>
                                <p class="text-xs text-slate-500"><?= ucfirst(str_replace('_', ' ', $currentUser['role'])) ?></p>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="dropdown-menu"
                             x-cloak>
                            <a href="/admin/profile.php" class="dropdown-item">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                My Profile
                            </a>
                            <a href="/admin/settings.php" class="dropdown-item">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Settings
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="/admin/logout.php" class="dropdown-item danger">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main content area -->
        <main class="flex-1 p-4 sm:p-6">
