<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Applications (Modern Design)
 */

$pageTitle = 'Applications';
require_once __DIR__ . '/includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        $id = (int)$_POST['id'];
        $status = $_POST['status'];
        query("UPDATE career_applications SET status = ?, is_read = 1 WHERE id = ?", [$status, $id]);
        setFlash('success', 'Status updated');
        redirect('/admin/applications.php');
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("DELETE FROM career_applications WHERE id = ?", [$id]);
        setFlash('success', 'Application deleted');
        redirect('/admin/applications.php');
    }
}

// Filters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where = ["1=1"];
$params = [];

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}
if ($search) {
    $where[] = "(full_name LIKE ? OR email LIKE ? OR position LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

$whereClause = implode(' AND ', $where);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$total = fetchOne("SELECT COUNT(*) as count FROM career_applications WHERE $whereClause", $params)['count'];
$pagination = paginate($total, $perPage, $page);

$applications = fetchAll("
    SELECT * FROM career_applications 
    WHERE $whereClause 
    ORDER BY is_read ASC, created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

// Status counts
$statusCounts = fetchAll("SELECT status, COUNT(*) as count FROM career_applications GROUP BY status");
$statusMap = array_column($statusCounts, 'count', 'status');
$allCount = array_sum($statusMap);
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Career Applications</h1>
        <p class="text-slate-500 text-sm mt-0.5">Review and manage job applications</p>
    </div>
</div>

<!-- Pipeline Stats -->
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    <a href="/admin/applications.php" class="stat-card text-center <?= !$status ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-xl font-bold text-slate-900"><?= $allCount ?></p>
        <p class="text-[10px] text-slate-500 uppercase font-medium">All</p>
    </a>
    <a href="?status=new" class="stat-card text-center <?= $status === 'new' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-xl font-bold text-blue-600"><?= $statusMap['new'] ?? 0 ?></p>
        <p class="text-[10px] text-slate-500 uppercase font-medium">New</p>
    </a>
    <a href="?status=reviewing" class="stat-card text-center <?= $status === 'reviewing' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-xl font-bold text-amber-600"><?= $statusMap['reviewing'] ?? 0 ?></p>
        <p class="text-[10px] text-slate-500 uppercase font-medium">Reviewing</p>
    </a>
    <a href="?status=shortlisted" class="stat-card text-center <?= $status === 'shortlisted' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-xl font-bold text-indigo-600"><?= $statusMap['shortlisted'] ?? 0 ?></p>
        <p class="text-[10px] text-slate-500 uppercase font-medium">Shortlisted</p>
    </a>
    <a href="?status=interviewed" class="stat-card text-center <?= $status === 'interviewed' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-xl font-bold text-violet-600"><?= $statusMap['interviewed'] ?? 0 ?></p>
        <p class="text-[10px] text-slate-500 uppercase font-medium">Interviewed</p>
    </a>
    <a href="?status=hired" class="stat-card text-center <?= $status === 'hired' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-xl font-bold text-emerald-600"><?= $statusMap['hired'] ?? 0 ?></p>
        <p class="text-[10px] text-slate-500 uppercase font-medium">Hired</p>
    </a>
    <a href="?status=rejected" class="stat-card text-center <?= $status === 'rejected' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-xl font-bold text-red-500"><?= $statusMap['rejected'] ?? 0 ?></p>
        <p class="text-[10px] text-slate-500 uppercase font-medium">Rejected</p>
    </a>
</div>

<!-- Search -->
<div class="card mb-6">
    <div class="p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, or position..." class="form-input pl-10">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?>
            <a href="/admin/applications.php<?= $status ? "?status=$status" : '' ?>" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Applications Table -->
<div class="card overflow-hidden">
    <?php if (empty($applications)): ?>
    <div class="empty-state py-16">
        <svg class="w-16 h-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <p class="mt-3 font-medium text-slate-600">No applications found</p>
        <p class="text-sm">Applications will appear here when candidates apply</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Position</th>
                    <th>Experience</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr class="<?= !$app['is_read'] ? 'bg-blue-50/50' : '' ?>">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm flex-shrink-0
                                        <?= !$app['is_read'] ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-600' ?>">
                                <?= strtoupper(substr($app['full_name'], 0, 2)) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <?php if (!$app['is_read']): ?>
                                    <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span>
                                    <?php endif; ?>
                                    <p class="font-medium text-slate-900 truncate"><?= htmlspecialchars($app['full_name']) ?></p>
                                </div>
                                <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($app['email']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="font-medium text-slate-900"><?= htmlspecialchars($app['position']) ?></p>
                        <?php if ($app['current_company']): ?>
                        <p class="text-xs text-slate-500">Currently at <?= htmlspecialchars($app['current_company']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="text-slate-600"><?= $app['experience_years'] ? $app['experience_years'] . ' years' : '—' ?></span>
                    </td>
                    <td>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?= $app['id'] ?>">
                            <select name="status" onchange="this.form.submit()"
                                    class="text-xs px-2.5 py-1 rounded-full border-0 font-semibold cursor-pointer
                                    <?php
                                    $colors = [
                                        'new' => 'bg-blue-100 text-blue-700',
                                        'reviewing' => 'bg-amber-100 text-amber-700',
                                        'shortlisted' => 'bg-indigo-100 text-indigo-700',
                                        'interviewed' => 'bg-violet-100 text-violet-700',
                                        'offered' => 'bg-cyan-100 text-cyan-700',
                                        'hired' => 'bg-emerald-100 text-emerald-700',
                                        'rejected' => 'bg-red-100 text-red-700'
                                    ];
                                    echo $colors[$app['status']] ?? 'bg-slate-100 text-slate-600';
                                    ?>">
                                <option value="new" <?= $app['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                <option value="reviewing" <?= $app['status'] === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                                <option value="shortlisted" <?= $app['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                                <option value="interviewed" <?= $app['status'] === 'interviewed' ? 'selected' : '' ?>>Interviewed</option>
                                <option value="offered" <?= $app['status'] === 'offered' ? 'selected' : '' ?>>Offered</option>
                                <option value="hired" <?= $app['status'] === 'hired' ? 'selected' : '' ?>>Hired</option>
                                <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <span class="text-sm text-slate-500"><?= timeAgo($app['created_at']) ?></span>
                    </td>
                    <td>
                        <div class="action-btns justify-end">
                            <a href="mailto:<?= htmlspecialchars($app['email']) ?>" class="action-btn edit" title="Send Email">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </a>
                            <?php if ($app['phone']): ?>
                            <a href="tel:<?= htmlspecialchars($app['phone']) ?>" class="action-btn" title="Call">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if ($app['resume_path']): ?>
                            <a href="<?= htmlspecialchars($app['resume_path']) ?>" target="_blank" class="action-btn download" title="Download Resume">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if ($app['linkedin_url']): ?>
                            <a href="<?= htmlspecialchars($app['linkedin_url']) ?>" target="_blank" class="action-btn view" title="LinkedIn Profile">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                            <?php endif; ?>
                            <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this application?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $app['id'] ?>">
                                <button type="submit" class="action-btn delete" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="flex items-center justify-between px-4 py-3 border-t border-slate-100 bg-slate-50/50">
        <p class="text-sm text-slate-500">Showing <?= $pagination['offset'] + 1 ?>-<?= min($pagination['offset'] + $perPage, $total) ?> of <?= $total ?></p>
        <div class="pagination">
            <?php if ($pagination['has_prev']): ?>
            <a href="?page=<?= $page - 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($pagination['has_next']): ?>
            <a href="?page=<?= $page + 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
