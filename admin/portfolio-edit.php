<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Portfolio Edit/Create
 */

$pageTitle = 'Edit Portfolio';
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project = null;
$errors = [];

if ($id) {
    $project = fetchOne("SELECT * FROM portfolio_projects WHERE id = ?", [$id]);
    if (!$project) {
        setFlash('error', 'Project not found');
        redirect('/admin/portfolio.php');
    }
    $pageTitle = 'Edit: ' . $project['title'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'category_id' => $_POST['category_id'] ?: null,
        'client_name' => trim($_POST['client_name'] ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'full_description' => trim($_POST['full_description'] ?? ''),
        'challenge' => trim($_POST['challenge'] ?? ''),
        'solution' => trim($_POST['solution'] ?? ''),
        'results' => trim($_POST['results'] ?? ''),
        'thumbnail' => trim($_POST['thumbnail'] ?? ''),
        'project_url' => trim($_POST['project_url'] ?? ''),
        'completion_date' => $_POST['completion_date'] ?: null,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'sort_order' => (int)($_POST['sort_order'] ?? 0)
    ];
    
    // Parse JSON fields
    $data['technologies'] = json_encode(array_filter(array_map('trim', explode(',', $_POST['technologies'] ?? ''))));
    $data['features'] = json_encode(array_filter(array_map('trim', explode("\n", $_POST['features'] ?? ''))));
    $data['images'] = json_encode(array_filter(array_map('trim', explode("\n", $_POST['images'] ?? ''))));
    
    // Validation
    if (empty($data['title'])) {
        $errors[] = 'Title is required';
    }
    
    // Generate slug if empty
    if (empty($data['slug'])) {
        $data['slug'] = slugify($data['title']);
    }
    
    // Check for duplicate slug
    $existingSlug = fetchOne(
        "SELECT id FROM portfolio_projects WHERE slug = ? AND id != ?", 
        [$data['slug'], $id]
    );
    if ($existingSlug) {
        $data['slug'] .= '-' . time();
    }
    
    // Handle image upload
    if (!empty($_FILES['thumbnail_file']['name'])) {
        $upload = uploadFile($_FILES['thumbnail_file'], 'portfolio', ALLOWED_IMAGE_TYPES);
        if ($upload['success']) {
            $data['thumbnail'] = $upload['path'];
        } else {
            $errors[] = 'Image upload failed: ' . $upload['error'];
        }
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
            query("UPDATE portfolio_projects SET " . implode(', ', $fields) . " WHERE id = ?", $params);
            logActivity('update', 'portfolio_projects', $id, 'Updated portfolio project: ' . $data['title']);
            setFlash('success', 'Project updated successfully');
        } else {
            // Insert
            $fields = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            query("INSERT INTO portfolio_projects ($fields) VALUES ($placeholders)", array_values($data));
            $newId = lastInsertId();
            logActivity('create', 'portfolio_projects', $newId, 'Created portfolio project: ' . $data['title']);
            setFlash('success', 'Project created successfully');
        }
        redirect('/admin/portfolio.php');
    }
}

// Get categories
$categories = fetchAll("SELECT id, name FROM service_categories ORDER BY name");

// Parse JSON fields for display
$technologies = $project ? implode(', ', json_decode($project['technologies'] ?? '[]', true) ?: []) : '';
$features = $project ? implode("\n", json_decode($project['features'] ?? '[]', true) ?: []) : '';
$images = $project ? implode("\n", json_decode($project['images'] ?? '[]', true) ?: []) : '';
?>

<!-- Page Header -->
<div class="flex items-center gap-4 mb-6">
    <a href="/admin/portfolio.php" class="p-2 hover:bg-gray-100 rounded-lg">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900"><?= $id ? 'Edit Project' : 'Add New Project' ?></h1>
        <p class="text-gray-500"><?= $id ? 'Update project details' : 'Create a new portfolio project' ?></p>
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

<form method="POST" enctype="multipart/form-data" class="space-y-6">
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Basic Info -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Project Title *</label>
                            <input type="text" name="title" required
                                   value="<?= htmlspecialchars($project['title'] ?? $_POST['title'] ?? '') ?>"
                                   class="form-input" placeholder="e.g., E-commerce Platform">
                        </div>
                        <div>
                            <label class="form-label">URL Slug</label>
                            <input type="text" name="slug"
                                   value="<?= htmlspecialchars($project['slug'] ?? $_POST['slug'] ?? '') ?>"
                                   class="form-input" placeholder="Auto-generated if empty">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Client Name</label>
                            <input type="text" name="client_name"
                                   value="<?= htmlspecialchars($project['client_name'] ?? $_POST['client_name'] ?? '') ?>"
                                   class="form-input" placeholder="e.g., Acme Corporation">
                        </div>
                        <div>
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-input">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                    <?= ($project['category_id'] ?? $_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" rows="2" class="form-input"
                                  placeholder="Brief overview of the project"><?= htmlspecialchars($project['short_description'] ?? $_POST['short_description'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="form-label">Full Description</label>
                        <textarea name="full_description" rows="4" class="form-input"
                                  placeholder="Detailed description of the project"><?= htmlspecialchars($project['full_description'] ?? $_POST['full_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Case Study -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Case Study Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Challenge</label>
                        <textarea name="challenge" rows="3" class="form-input"
                                  placeholder="What problem did the client face?"><?= htmlspecialchars($project['challenge'] ?? $_POST['challenge'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="form-label">Solution</label>
                        <textarea name="solution" rows="3" class="form-input"
                                  placeholder="How did you solve the problem?"><?= htmlspecialchars($project['solution'] ?? $_POST['solution'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="form-label">Results</label>
                        <textarea name="results" rows="3" class="form-input"
                                  placeholder="What were the outcomes?"><?= htmlspecialchars($project['results'] ?? $_POST['results'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Technical Details -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Technical Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Technologies (comma-separated)</label>
                        <input type="text" name="technologies"
                               value="<?= htmlspecialchars($technologies) ?>"
                               class="form-input" placeholder="e.g., PHP, MySQL, JavaScript, Tailwind CSS">
                    </div>
                    
                    <div>
                        <label class="form-label">Key Features (one per line)</label>
                        <textarea name="features" rows="4" class="form-input"
                                  placeholder="User Authentication&#10;Payment Integration&#10;Real-time Notifications"><?= htmlspecialchars($features) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Publish Settings -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Publish</h3>
                
                <div class="space-y-4">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" 
                               <?= ($project['is_active'] ?? 1) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">Active (visible on site)</span>
                    </label>
                    
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_featured" value="1"
                               <?= ($project['is_featured'] ?? 0) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">Featured project</span>
                    </label>
                    
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" min="0"
                               value="<?= $project['sort_order'] ?? 0 ?>"
                               class="form-input">
                    </div>
                    
                    <div>
                        <label class="form-label">Completion Date</label>
                        <input type="date" name="completion_date"
                               value="<?= $project['completion_date'] ?? '' ?>"
                               class="form-input">
                    </div>
                    
                    <div>
                        <label class="form-label">Project URL</label>
                        <input type="url" name="project_url"
                               value="<?= htmlspecialchars($project['project_url'] ?? '') ?>"
                               class="form-input" placeholder="https://example.com">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn-primary flex-1">
                        <?= $id ? 'Update Project' : 'Create Project' ?>
                    </button>
                    <a href="/admin/portfolio.php" class="btn-secondary">Cancel</a>
                </div>
            </div>
            
            <!-- Thumbnail -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Thumbnail Image</h3>
                
                <div class="space-y-4">
                    <!-- Current Image Preview -->
                    <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden" id="thumbnail-preview">
                        <?php if (!empty($project['thumbnail'])): ?>
                        <img src="<?= htmlspecialchars($project['thumbnail']) ?>" 
                             alt="Current thumbnail" class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="form-label">Upload New Image</label>
                        <input type="file" name="thumbnail_file" accept="image/*"
                               onchange="previewImage(this)"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>
                    
                    <p class="text-xs text-gray-500">Or use image URL:</p>
                    <input type="text" name="thumbnail"
                           value="<?= htmlspecialchars($project['thumbnail'] ?? '') ?>"
                           class="form-input text-sm" placeholder="/assets/project-image.jpg">
                </div>
            </div>
            
            <!-- Additional Images -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Gallery Images</h3>
                <div>
                    <label class="form-label">Image URLs (one per line)</label>
                    <textarea name="images" rows="4" class="form-input text-sm"
                              placeholder="/assets/image1.jpg&#10;/assets/image2.jpg"><?= htmlspecialchars($images) ?></textarea>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function previewImage(input) {
    const preview = document.getElementById('thumbnail-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
