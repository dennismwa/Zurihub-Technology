<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Pricing Edit/Create
 */

$pageTitle = 'Edit Pricing Package';
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$package = null;
$errors = [];

if ($id) {
    $package = fetchOne("SELECT * FROM pricing_packages WHERE id = ?", [$id]);
    if (!$package) {
        setFlash('error', 'Package not found');
        redirect('/admin/pricing.php');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'category_id' => $_POST['category_id'] ?: null,
        'description' => trim($_POST['description'] ?? ''),
        'price' => (float)($_POST['price'] ?? 0),
        'original_price' => $_POST['original_price'] ? (float)$_POST['original_price'] : null,
        'currency' => $_POST['currency'] ?? 'KES',
        'billing_type' => $_POST['billing_type'] ?? 'one_time',
        'is_popular' => isset($_POST['is_popular']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'sort_order' => (int)($_POST['sort_order'] ?? 0)
    ];
    
    // Parse features
    $features = array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')));
    $data['features'] = json_encode(array_values($features));
    
    // Validation
    if (empty($data['name'])) {
        $errors[] = 'Package name is required';
    }
    if ($data['price'] <= 0) {
        $errors[] = 'Price must be greater than 0';
    }
    if (empty($data['category_id'])) {
        $errors[] = 'Category is required';
    }
    
    // Generate slug
    if (empty($data['slug'])) {
        $data['slug'] = slugify($data['name']);
    }
    
    if (empty($errors)) {
        if ($id) {
            // Update
            $fields = [];
            $params = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
            $params[] = $id;
            query("UPDATE pricing_packages SET " . implode(', ', $fields) . " WHERE id = ?", $params);
            logActivity('update', 'pricing_packages', $id, 'Updated pricing package: ' . $data['name']);
            setFlash('success', 'Package updated successfully');
        } else {
            // Insert
            $fields = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            query("INSERT INTO pricing_packages ($fields) VALUES ($placeholders)", array_values($data));
            $newId = lastInsertId();
            logActivity('create', 'pricing_packages', $newId, 'Created pricing package: ' . $data['name']);
            setFlash('success', 'Package created successfully');
        }
        redirect('/admin/pricing.php');
    }
}

// Get categories
$categories = fetchAll("SELECT id, name FROM service_categories ORDER BY name");

// Parse features for display
$features = $package ? implode("\n", json_decode($package['features'] ?? '[]', true) ?: []) : '';
?>

<!-- Page Header -->
<div class="flex items-center gap-4 mb-6">
    <a href="/admin/pricing.php" class="p-2 hover:bg-gray-100 rounded-lg">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900"><?= $id ? 'Edit Package' : 'Add New Package' ?></h1>
        <p class="text-gray-500"><?= $id ? 'Update pricing package details' : 'Create a new pricing package' ?></p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Basic Info -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Package Information</h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Package Name *</label>
                            <input type="text" name="name" required
                                   value="<?= htmlspecialchars($package['name'] ?? $_POST['name'] ?? '') ?>"
                                   class="form-input" placeholder="e.g., Business Website">
                        </div>
                        <div>
                            <label class="form-label">URL Slug</label>
                            <input type="text" name="slug"
                                   value="<?= htmlspecialchars($package['slug'] ?? $_POST['slug'] ?? '') ?>"
                                   class="form-input" placeholder="Auto-generated if empty">
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">Service Category *</label>
                        <select name="category_id" required class="form-input">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" 
                                <?= ($package['category_id'] ?? $_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label">Short Description</label>
                        <textarea name="description" rows="2" class="form-input"
                                  placeholder="Brief description of this package"><?= htmlspecialchars($package['description'] ?? $_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Pricing -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" step="0.01" min="0" required
                                   value="<?= $package['price'] ?? $_POST['price'] ?? '' ?>"
                                   class="form-input" placeholder="50000">
                        </div>
                        <div>
                            <label class="form-label">Original Price <span class="text-gray-400">(for strikethrough)</span></label>
                            <input type="number" name="original_price" step="0.01" min="0"
                                   value="<?= $package['original_price'] ?? $_POST['original_price'] ?? '' ?>"
                                   class="form-input" placeholder="75000">
                        </div>
                        <div>
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-input">
                                <option value="KES" <?= ($package['currency'] ?? 'KES') === 'KES' ? 'selected' : '' ?>>KES (Kenyan Shilling)</option>
                                <option value="USD" <?= ($package['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD (US Dollar)</option>
                                <option value="GBP" <?= ($package['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP (British Pound)</option>
                                <option value="EUR" <?= ($package['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">Billing Type</label>
                        <div class="flex flex-wrap gap-4">
                            <?php foreach (['one_time' => 'One-time', 'monthly' => 'Monthly', 'yearly' => 'Yearly', 'custom' => 'Custom'] as $value => $label): ?>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="billing_type" value="<?= $value ?>"
                                       <?= ($package['billing_type'] ?? 'one_time') === $value ? 'checked' : '' ?>
                                       class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                <span class="text-sm text-gray-700"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Features -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Features</h3>
                
                <div>
                    <label class="form-label">Package Features (one per line)</label>
                    <textarea name="features" rows="8" class="form-input font-mono text-sm"
                              placeholder="5 Pages&#10;Mobile Responsive&#10;Contact Form&#10;Basic SEO&#10;1 Month Support"><?= htmlspecialchars($features) ?></textarea>
                    <p class="text-xs text-gray-500 mt-2">Enter each feature on a new line. These will be displayed as bullet points.</p>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Publish Settings -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings</h3>
                
                <div class="space-y-4">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" 
                               <?= ($package['is_active'] ?? 1) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">Active (visible on site)</span>
                    </label>
                    
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_popular" value="1"
                               <?= ($package['is_popular'] ?? 0) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">Mark as Popular</span>
                    </label>
                    
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" min="0"
                               value="<?= $package['sort_order'] ?? 0 ?>"
                               class="form-input">
                        <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn-primary flex-1">
                        <?= $id ? 'Update Package' : 'Create Package' ?>
                    </button>
                    <a href="/admin/pricing.php" class="btn-secondary">Cancel</a>
                </div>
            </div>
            
            <!-- Preview Card -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview</h3>
                <p class="text-sm text-gray-500 mb-4">This is how the package will appear on the pricing page.</p>
                
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="text-center">
                        <h4 class="font-bold text-gray-900" id="preview-name"><?= htmlspecialchars($package['name'] ?? 'Package Name') ?></h4>
                        <p class="text-sm text-gray-500 mt-1" id="preview-desc"><?= htmlspecialchars($package['description'] ?? 'Package description') ?></p>
                        <div class="mt-4">
                            <span class="text-2xl font-bold text-gray-900" id="preview-price"><?= formatCurrency($package['price'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Live preview updates
document.querySelector('input[name="name"]').addEventListener('input', function() {
    document.getElementById('preview-name').textContent = this.value || 'Package Name';
});

document.querySelector('textarea[name="description"]').addEventListener('input', function() {
    document.getElementById('preview-desc').textContent = this.value || 'Package description';
});

document.querySelector('input[name="price"]').addEventListener('input', function() {
    const price = parseFloat(this.value) || 0;
    document.getElementById('preview-price').textContent = 'KSh ' + price.toLocaleString();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
