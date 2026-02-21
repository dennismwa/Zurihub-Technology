<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Statistics
 */

$pageTitle = 'Statistics';
require_once __DIR__ . '/includes/header.php';

// Get stats
$totalQuotations = fetchOne("SELECT COUNT(*) as c FROM quotation_requests")['c'];
$totalMessages = fetchOne("SELECT COUNT(*) as c FROM contact_messages")['c'];
$totalProjects = fetchOne("SELECT COUNT(*) as c FROM portfolio_projects WHERE is_active = 1")['c'];
$totalViews = fetchOne("SELECT SUM(views) as v FROM portfolio_projects")['v'] ?? 0;

// Monthly quotations (last 12 months)
$monthlyData = fetchAll("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        DATE_FORMAT(created_at, '%b %Y') as label,
        COUNT(*) as count
    FROM quotation_requests 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month ASC
");

// Status distribution
$statusData = fetchAll("
    SELECT status, COUNT(*) as count
    FROM quotation_requests
    GROUP BY status
");

// Top services
$serviceData = fetchAll("
    SELECT project_type, COUNT(*) as count
    FROM quotation_requests
    WHERE project_type IS NOT NULL AND project_type != ''
    GROUP BY project_type
    ORDER BY count DESC
    LIMIT 10
");

// Conversion funnel
$funnel = [
    'new' => fetchOne("SELECT COUNT(*) as c FROM quotation_requests WHERE status = 'new'")['c'],
    'contacted' => fetchOne("SELECT COUNT(*) as c FROM quotation_requests WHERE status = 'contacted'")['c'],
    'quoted' => fetchOne("SELECT COUNT(*) as c FROM quotation_requests WHERE status = 'quoted'")['c'],
    'converted' => fetchOne("SELECT COUNT(*) as c FROM quotation_requests WHERE status = 'converted'")['c']
];
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Statistics & Analytics</h1>
        <p class="text-gray-500 mt-1">Overview of your business metrics</p>
    </div>
    <select class="form-input w-auto" id="dateRange">
        <option value="30">Last 30 Days</option>
        <option value="90">Last 90 Days</option>
        <option value="365" selected>Last 12 Months</option>
    </select>
</div>

<!-- Stats Overview -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="stat-card">
        <p class="text-sm font-medium text-gray-500">Total Quotations</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($totalQuotations) ?></p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-gray-500">Contact Messages</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($totalMessages) ?></p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-gray-500">Portfolio Projects</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($totalProjects) ?></p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-gray-500">Portfolio Views</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($totalViews) ?></p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    
    <!-- Quotations Over Time -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quotations Over Time</h3>
        <canvas id="quotationsLineChart" height="250"></canvas>
    </div>
    
    <!-- Status Distribution -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Distribution</h3>
        <canvas id="statusPieChart" height="250"></canvas>
    </div>
</div>

<!-- Second Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    
    <!-- Conversion Funnel -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversion Funnel</h3>
        <div class="space-y-4">
            <?php 
            $maxFunnel = max($funnel);
            $funnelColors = ['new' => 'bg-blue-500', 'contacted' => 'bg-yellow-500', 'quoted' => 'bg-purple-500', 'converted' => 'bg-green-500'];
            foreach ($funnel as $stage => $count): 
                $width = $maxFunnel > 0 ? ($count / $maxFunnel * 100) : 0;
            ?>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700"><?= ucfirst($stage) ?></span>
                    <span class="text-gray-500"><?= $count ?></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="<?= $funnelColors[$stage] ?> h-3 rounded-full transition-all duration-500" style="width: <?= $width ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($funnel['new'] > 0): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Conversion Rate: <strong class="text-green-600"><?= $funnel['new'] > 0 ? round($funnel['converted'] / $funnel['new'] * 100, 1) : 0 ?>%</strong>
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Top Services Requested -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Services Requested</h3>
        <canvas id="servicesBarChart" height="250"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quotations Line Chart
    new Chart(document.getElementById('quotationsLineChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($monthlyData, 'label')) ?>,
            datasets: [{
                label: 'Quotations',
                data: <?= json_encode(array_map('intval', array_column($monthlyData, 'count'))) ?>,
                borderColor: '#3d9268',
                backgroundColor: 'rgba(61, 146, 104, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                x: { grid: { display: false } }
            }
        }
    });
    
    // Status Pie Chart
    new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_map('ucfirst', array_column($statusData, 'status'))) ?>,
            datasets: [{
                data: <?= json_encode(array_map('intval', array_column($statusData, 'count'))) ?>,
                backgroundColor: ['#3b82f6', '#f59e0b', '#8b5cf6', '#10b981', '#6b7280', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
    
    // Services Bar Chart
    new Chart(document.getElementById('servicesBarChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($serviceData, 'project_type')) ?>,
            datasets: [{
                label: 'Requests',
                data: <?= json_encode(array_map('intval', array_column($serviceData, 'count'))) ?>,
                backgroundColor: '#3d9268',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                y: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
