<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Pricing Management
 */

$pageTitle = 'Pricing Management';
require_once __DIR__ . '/includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("DELETE FROM pricing_packages WHERE id = ?", [$id]);
        logActivity('delete', 'pricing_packages', $id, 'Deleted pricing package');
        setFlash('success', 'Package deleted successfully');
        redirect('/admin/pricing.php');
    }
    
    if ($action === 'toggle_popular' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("UPDATE pricing_packages SET is_popular = NOT is_popular WHERE id = ?", [$id]);
        if (isAjax()) {
            jsonSuccess('Updated');
        }
    }
    
    if ($action === 'toggle_active' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("UPDATE pricing_packages SET is_active = NOT is_active WHERE id = ?", [$id]);
        if (isAjax()) {
            jsonSuccess('Updated');
        }
    }
}

// Get categories
$categories = fetchAll("SELECT id, name, slug FROM service_categories ORDER BY sort_order, name");

// Get pricing packages grouped by category
$packages = fetchAll("
    SELECT pp.*, sc.name as category_name
    FROM pricing_packages pp
    LEFT JOIN service_categories sc ON pp.category_id = sc.id
    ORDER BY pp.category_id, pp.sort_order, pp.price
");

// Group packages by category
$packagesByCategory = [];
foreach ($packages as $pkg) {
    $catId = $pkg['category_id'] ?? 0;
    if (!isset($packagesByCategory[$catId])) {
        $packagesByCategory[$catId] = [
            'name' => $pkg['category_name'] ?? 'Uncategorized',
            'packages' => []
        ];
    }
    $packagesByCategory[$catId]['packages'][] = $pkg;
}

$selectedCategory = $_GET['category'] ?? '';
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Pricing Packages</h1>
        <p class="text-gray-500 mt-1">Manage your service pricing</p>
    </div>
    <a href="/admin/pricing-edit.php" class="btn-primary inline-flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Package
    </a>
</div>

<!-- Category Filter Tabs -->
<div class="flex flex-wrap gap-2 mb-6 p-1 bg-gray-100 rounded-xl overflow-x-auto">
    <a href="/admin/pricing.php" 
       class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap <?= !$selectedCategory ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600 hover:text-gray-900' ?>">
        All Categories
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="?category=<?= $cat['id'] ?>" 
       class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap <?= $selectedCategory == $cat['id'] ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600 hover:text-gray-900' ?>">
        <?= htmlspecialchars($cat['name']) ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Packages Grid -->
<?php foreach ($packagesByCategory as $catId => $category): ?>
<?php if ($selectedCategory && $selectedCategory != $catId) continue; ?>
<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <span class="w-2 h-2 bg-primary-500 rounded-full"></span>
        <?= htmlspecialchars($category['name']) ?>
        <span class="text-sm font-normal text-gray-500">(<?= count($category['packages']) ?> packages)</span>
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($category['packages'] as $pkg): ?>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 relative <?= $pkg['is_popular'] ? 'ring-2 ring-primary-500' : '' ?>" 
             x-data="{ showMenu: false }">
            
            <?php if ($pkg['is_popular']): ?>
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                <span class="bg-primary-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                    Most Popular
                </span>
            </div>
            <?php endif; ?>
            
            <?php if (!$pkg['is_active']): ?>
            <div class="absolute top-4 right-4">
                <span class="badge badge-gray">Inactive</span>
            </div>
            <?php endif; ?>
            
            <!-- Package Header -->
            <div class="mb-4 pt-2">
                <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($pkg['name']) ?></h3>
                <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($pkg['description'] ?? '') ?></p>
            </div>
            
            <!-- Pricing -->
            <div class="mb-6">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-gray-900"><?= formatCurrency($pkg['price'], $pkg['currency']) ?></span>
                    <?php if ($pkg['original_price'] && $pkg['original_price'] > $pkg['price']): ?>
                    <span class="text-lg text-gray-400 line-through"><?= formatCurrency($pkg['original_price'], $pkg['currency']) ?></span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    <?= $pkg['billing_type'] === 'one_time' ? 'One-time payment' : ucfirst($pkg['billing_type']) ?>
                </p>
            </div>
            
            <!-- Features -->
            <?php 
            $features = json_decode($pkg['features'], true) ?: [];
            if (!empty($features)): 
            ?>
            <ul class="space-y-2 mb-6">
                <?php foreach (array_slice($features, 0, 5) as $feature): ?>
                <li class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= htmlspecialchars($feature) ?>
                </li>
                <?php endforeach; ?>
                <?php if (count($features) > 5): ?>
                <li class="text-sm text-gray-400">+<?= count($features) - 5 ?> more features</li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>
            
            <!-- Actions -->
            <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                <a href="/admin/pricing-edit.php?id=<?= $pkg['id'] ?>" 
                   class="flex-1 btn-secondary text-center text-sm py-2">
                    Edit
                </a>
                
                <div class="relative">
                    <button @click="showMenu = !showMenu" class="p-2 hover:bg-gray-100 rounded-lg">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>
                    
                    <div x-show="showMenu" @click.away="showMenu = false" x-transition
                         class="absolute right-0 bottom-full mb-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-10"
                         x-cloak>
                        <button onclick="togglePopular(<?= $pkg['id'] ?>)" 
                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left">
                            <svg class="w-4 h-4 <?= $pkg['is_popular'] ? 'text-yellow-500' : '' ?>" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <?= $pkg['is_popular'] ? 'Remove Popular' : 'Mark as Popular' ?>
                        </button>
                        <button onclick="toggleActive(<?= $pkg['id'] ?>)"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <?= $pkg['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </button>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" onsubmit="return confirmDelete('Delete this package?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $pkg['id'] ?>">
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 w-full text-left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($packagesByCategory)): ?>
<div class="text-center py-12">
    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-gray-500 mb-4">No pricing packages found</p>
    <a href="/admin/pricing-edit.php" class="btn-primary">Create your first package</a>
</div>
<?php endif; ?>

<script>
async function togglePopular(id) {
    await fetchAPI('/admin/pricing.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'toggle_popular', id: id })
    });
    location.reload();
}

async function toggleActive(id) {
    await fetchAPI('/admin/pricing.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'toggle_active', id: id })
    });
    location.reload();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
