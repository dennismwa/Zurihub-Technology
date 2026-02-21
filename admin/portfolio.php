<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Portfolio (Modern Design)
 */

$pageTitle = 'Portfolio';
require_once __DIR__ . '/includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("DELETE FROM portfolio_projects WHERE id = ?", [$id]);
        logActivity('delete', 'portfolio_projects', $id, 'Deleted portfolio project');
        setFlash('success', 'Project deleted successfully');
        redirect('/admin/portfolio.php');
    }
    
    if ($action === 'toggle_featured' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("UPDATE portfolio_projects SET is_featured = NOT is_featured WHERE id = ?", [$id]);
        logActivity('update', 'portfolio_projects', $id, 'Toggled featured status');
        if (isAjax()) jsonSuccess('Updated successfully');
    }
    
    if ($action === 'toggle_active' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("UPDATE portfolio_projects SET is_active = NOT is_active WHERE id = ?", [$id]);
        logActivity('update', 'portfolio_projects', $id, 'Toggled active status');
        if (isAjax()) jsonSuccess('Updated successfully');
    }
}

// Filters
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$where = ["1=1"];
$params = [];

if ($category) {
    $where[] = "p.category_id = ?";
    $params[] = $category;
}
if ($search) {
    $where[] = "(p.title LIKE ? OR p.client_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%"]);
}
if ($status === 'active') {
    $where[] = "p.is_active = 1";
} elseif ($status === 'inactive') {
    $where[] = "p.is_active = 0";
} elseif ($status === 'featured') {
    $where[] = "p.is_featured = 1";
}

$whereClause = implode(' AND ', $where);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$total = fetchOne("SELECT COUNT(*) as count FROM portfolio_projects p WHERE $whereClause", $params)['count'];
$pagination = paginate($total, $perPage, $page);

$projects = fetchAll("
    SELECT p.*, sc.name as category_name
    FROM portfolio_projects p
    LEFT JOIN service_categories sc ON p.category_id = sc.id
    WHERE $whereClause
    ORDER BY p.is_featured DESC, p.sort_order ASC, p.created_at DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

$categories = fetchAll("SELECT id, name FROM service_categories ORDER BY name");

// Get stats
$totalViews = fetchOne("SELECT SUM(views) as total FROM portfolio_projects")['total'] ?? 0;
$featuredCount = fetchOne("SELECT COUNT(*) as count FROM portfolio_projects WHERE is_featured = 1")['count'];
$activeCount = fetchOne("SELECT COUNT(*) as count FROM portfolio_projects WHERE is_active = 1")['count'];
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Portfolio</h1>
        <p class="text-slate-500 text-sm mt-0.5">Manage your project showcase</p>
    </div>
    <a href="/admin/portfolio-edit.php" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Project
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase">Total Projects</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($total) ?></p>
            </div>
            <div class="stat-icon bg-blue-100 text-blue-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase">Total Views</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($totalViews) ?></p>
            </div>
            <div class="stat-icon bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase">Featured</p>
                <p class="text-2xl font-bold text-amber-500 mt-1"><?= number_format($featuredCount) ?></p>
            </div>
            <div class="stat-icon bg-amber-100 text-amber-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase">Active</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1"><?= number_format($activeCount) ?></p>
            </div>
            <div class="stat-icon bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="p-4">
        <form method="GET" class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search projects..." class="form-input pl-10">
            </div>
            <select name="category" class="form-input w-auto min-w-[160px]">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-input w-auto min-w-[140px]">
                <option value="">All Status</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="featured" <?= $status === 'featured' ? 'selected' : '' ?>>Featured</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $category || $status): ?>
            <a href="/admin/portfolio.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Projects Grid -->
<?php if (empty($projects)): ?>
<div class="card">
    <div class="empty-state py-16">
        <svg class="w-20 h-20 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <p class="mt-4 font-semibold text-slate-600">No projects found</p>
        <p class="text-sm text-slate-500 mb-4">Start building your portfolio</p>
        <a href="/admin/portfolio-edit.php" class="btn btn-primary">Add Your First Project</a>
    </div>
</div>
<?php else: ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
    <?php foreach ($projects as $project): ?>
    <div class="card group overflow-hidden" x-data="{ showMenu: false }">
        <!-- Image -->
        <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden">
            <?php if ($project['thumbnail']): ?>
            <img src="<?= htmlspecialchars($project['thumbnail']) ?>" alt="<?= htmlspecialchars($project['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-slate-300">
                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <?php endif; ?>
            
            <!-- Badges -->
            <div class="absolute top-2 left-2 flex gap-1">
                <?php if ($project['is_featured']): ?>
                <span class="badge bg-amber-500 text-white text-[10px]">
                    <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Featured
                </span>
                <?php endif; ?>
                <?php if (!$project['is_active']): ?>
                <span class="badge badge-gray text-[10px]">Draft</span>
                <?php endif; ?>
            </div>
            
            <!-- Quick actions overlay -->
            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                <a href="/admin/portfolio-edit.php?id=<?= $project['id'] ?>" class="btn btn-sm bg-white/90 text-slate-900 hover:bg-white">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <a href="/portfolio#<?= $project['slug'] ?>" target="_blank" class="btn btn-sm bg-white/90 text-slate-900 hover:bg-white">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View
                </a>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-4">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="min-w-0">
                    <h3 class="font-semibold text-slate-900 truncate"><?= htmlspecialchars($project['title']) ?></h3>
                    <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($project['client_name'] ?? 'No client') ?></p>
                </div>
                <div class="relative flex-shrink-0">
                    <button @click="showMenu = !showMenu" class="p-1 rounded hover:bg-slate-100">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                    </button>
                    <div x-show="showMenu" @click.away="showMenu = false" x-transition class="dropdown-menu right-0 min-w-[140px]" x-cloak>
                        <a href="/admin/portfolio-edit.php?id=<?= $project['id'] ?>" class="dropdown-item">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        <button type="button" onclick="toggleFeatured(<?= $project['id'] ?>, this)" class="dropdown-item w-full text-left">
                            <svg class="w-4 h-4" fill="<?= $project['is_featured'] ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <?= $project['is_featured'] ? 'Unfeature' : 'Feature' ?>
                        </button>
                        <button type="button" onclick="toggleActive(<?= $project['id'] ?>, this)" class="dropdown-item w-full text-left">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $project['is_active'] ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' ?>"/></svg>
                            <?= $project['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </button>
                        <div class="border-t border-slate-100 my-1"></div>
                        <form method="POST" onsubmit="return confirmDelete('Delete this project?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $project['id'] ?>">
                            <button type="submit" class="dropdown-item danger w-full text-left">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-between text-xs">
                <span class="badge badge-gray"><?= htmlspecialchars($project['category_name'] ?? 'Uncategorized') ?></span>
                <span class="text-slate-400 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <?= number_format($project['views']) ?>
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
<div class="flex items-center justify-between">
    <p class="text-sm text-slate-500">Showing <?= $pagination['offset'] + 1 ?>-<?= min($pagination['offset'] + $perPage, $total) ?> of <?= $total ?></p>
    <div class="pagination">
        <?php if ($pagination['has_prev']): ?>
        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <?php endif; ?>
        <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($pagination['has_next']): ?>
        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
async function toggleFeatured(id, btn) {
    const formData = new FormData();
    formData.append('action', 'toggle_featured');
    formData.append('id', id);
    
    const response = await fetch('/admin/portfolio.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    
    if (response.ok) location.reload();
}

async function toggleActive(id, btn) {
    const formData = new FormData();
    formData.append('action', 'toggle_active');
    formData.append('id', id);
    
    const response = await fetch('/admin/portfolio.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    
    if (response.ok) location.reload();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
