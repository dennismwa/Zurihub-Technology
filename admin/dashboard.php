<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Dashboard (Modern Design)
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Fetch dashboard stats
$stats = fetchOne("SELECT * FROM dashboard_stats") ?? [];

// Get recent quotations
$recentQuotations = fetchAll("
    SELECT q.*, sc.name as category_name 
    FROM quotation_requests q 
    LEFT JOIN service_categories sc ON q.category_id = sc.id 
    ORDER BY q.created_at DESC 
    LIMIT 5
");

// Get recent messages
$recentMessages = fetchAll("
    SELECT * FROM contact_messages 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Monthly quotations for chart
$monthlyQuotations = fetchAll("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        DATE_FORMAT(created_at, '%b') as month_name,
        COUNT(*) as count
    FROM quotation_requests 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
");

// Category distribution
$categoryStats = fetchAll("
    SELECT sc.name, COUNT(q.id) as count
    FROM service_categories sc
    LEFT JOIN quotation_requests q ON q.category_id = sc.id
    GROUP BY sc.id
    ORDER BY count DESC
    LIMIT 6
");

$chartLabels = array_column($monthlyQuotations, 'month_name');
$chartData = array_column($monthlyQuotations, 'count');
$categoryLabels = array_column($categoryStats, 'name');
$categoryData = array_column($categoryStats, 'count');
?>

<!-- Page header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
            <p class="text-slate-500 text-sm mt-0.5">Welcome back, <?= htmlspecialchars($currentUser['name']) ?>! Here's what's happening.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-500"><?= date('l, F j, Y') ?></span>
            <a href="/admin/quotations.php" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                View Quotations
            </a>
        </div>
    </div>
</div>

<!-- Stats cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Quotations -->
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Quotes</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($stats['total_quotations'] ?? 0) ?></p>
                <p class="text-xs text-emerald-600 font-medium mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +<?= $stats['quotations_this_month'] ?? 0 ?> this month
                </p>
            </div>
            <div class="stat-icon bg-blue-100 text-blue-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>
    
    <!-- New/Pending -->
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Pending</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($stats['new_quotations'] ?? 0) ?></p>
                <p class="text-xs text-amber-600 font-medium mt-2">Awaiting response</p>
            </div>
            <div class="stat-icon bg-amber-100 text-amber-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    
    <!-- Messages -->
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Messages</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($stats['total_messages'] ?? 0) ?></p>
                <p class="text-xs text-rose-600 font-medium mt-2"><?= $stats['new_messages'] ?? 0 ?> unread</p>
            </div>
            <div class="stat-icon bg-rose-100 text-rose-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>
    
    <!-- Portfolio -->
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Projects</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($stats['total_projects'] ?? 0) ?></p>
                <p class="text-xs text-violet-600 font-medium mt-2"><?= number_format($stats['total_portfolio_views'] ?? 0) ?> views</p>
            </div>
            <div class="stat-icon bg-violet-100 text-violet-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>
</div>

<?php if (isset($stats['open_chat_conversations']) || isset($stats['open_tickets'])): ?>
<!-- Support stats -->
<div class="grid grid-cols-2 lg:grid-cols-2 gap-4 mb-6">
    <a href="/admin/chat-support.php" class="stat-card block">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Live Chats</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($stats['open_chat_conversations'] ?? 0) ?></p>
                <p class="text-xs text-indigo-600 font-medium mt-2"><?= number_format($stats['unread_chat_messages'] ?? 0) ?> unread messages</p>
            </div>
            <div class="stat-icon bg-indigo-100 text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
        </div>
    </a>
    <a href="/admin/tickets.php" class="stat-card block">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Support Tickets</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($stats['open_tickets'] ?? 0) ?></p>
                <p class="text-xs text-cyan-600 font-medium mt-2"><?= number_format($stats['unread_tickets'] ?? 0) ?> need attention</p>
            </div>
            <div class="stat-icon bg-cyan-100 text-cyan-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
        </div>
    </a>
</div>
<?php endif; ?>

<!-- Charts row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Line chart -->
    <div class="lg:col-span-2 card">
        <div class="card-header">
            <div>
                <h3 class="font-semibold text-slate-900">Quotation Trends</h3>
                <p class="text-xs text-slate-500 mt-0.5">Last 6 months performance</p>
            </div>
            <span class="badge badge-success">
                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                <?= $stats['quotations_this_month'] ?? 0 ?>%
            </span>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="quotationsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Doughnut chart -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="font-semibold text-slate-900">Categories</h3>
                <p class="text-xs text-slate-500 mt-0.5">Service distribution</p>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-container" style="height: 200px !important;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent quotations -->
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-slate-900">Recent Quotations</h3>
            <a href="/admin/quotations.php" class="text-xs font-medium text-blue-600 hover:text-blue-700">View all →</a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if (empty($recentQuotations)): ?>
            <div class="empty-state">
                <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm">No quotations yet</p>
            </div>
            <?php else: ?>
            <?php foreach ($recentQuotations as $quote): ?>
            <a href="/admin/quotation-view.php?id=<?= $quote['id'] ?>" class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-600 font-semibold text-sm flex-shrink-0">
                    <?= strtoupper(substr($quote['full_name'], 0, 2)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 truncate"><?= htmlspecialchars($quote['full_name']) ?></p>
                    <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($quote['category_name'] ?? $quote['project_type'] ?? 'General inquiry') ?></p>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="badge <?= $quote['status'] === 'new' ? 'badge-info' : ($quote['status'] === 'converted' ? 'badge-success' : 'badge-gray') ?>">
                        <?= ucfirst($quote['status']) ?>
                    </span>
                    <p class="text-xs text-slate-400 mt-1"><?= timeAgo($quote['created_at']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent messages -->
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-slate-900">Recent Messages</h3>
            <a href="/admin/messages.php" class="text-xs font-medium text-blue-600 hover:text-blue-700">View all →</a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if (empty($recentMessages)): ?>
            <div class="empty-state">
                <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <p class="text-sm">No messages yet</p>
            </div>
            <?php else: ?>
            <?php foreach ($recentMessages as $msg): ?>
            <div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition cursor-pointer" onclick="window.location='/admin/messages.php'">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center text-emerald-600 font-semibold text-sm flex-shrink-0">
                    <?= strtoupper(substr($msg['full_name'], 0, 2)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 truncate"><?= htmlspecialchars($msg['full_name']) ?></p>
                    <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($msg['subject'] ?: substr($msg['message'], 0, 50)) ?></p>
                </div>
                <div class="text-right flex-shrink-0">
                    <?php if (!$msg['is_read']): ?>
                    <span class="w-2 h-2 bg-blue-500 rounded-full inline-block"></span>
                    <?php endif; ?>
                    <p class="text-xs text-slate-400 mt-1"><?= timeAgo($msg['created_at']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Charts initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Default chart options
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';
    
    // Line chart
    const quotationsCtx = document.getElementById('quotationsChart');
    if (quotationsCtx) {
        new Chart(quotationsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Quotations',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { padding: 10 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { padding: 10 }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
    // Doughnut chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($categoryLabels) ?>,
                datasets: [{
                    data: <?= json_encode($categoryData) ?>,
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#06b6d4'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
