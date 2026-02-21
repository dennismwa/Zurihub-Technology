<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Quotations Management (Modern Design)
 */

$pageTitle = 'Quotations';
require_once __DIR__ . '/includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        $id = (int)$_POST['id'];
        $status = $_POST['status'];
        $validStatuses = ['new', 'contacted', 'in_progress', 'quoted', 'converted', 'closed', 'spam'];
        
        if (in_array($status, $validStatuses)) {
            query("UPDATE quotation_requests SET status = ?, is_read = 1 WHERE id = ?", [$status, $id]);
            logActivity('update', 'quotation_requests', $id, "Updated status to: $status");
            if (isAjax()) jsonSuccess('Status updated');
            setFlash('success', 'Status updated successfully');
        }
        redirect('/admin/quotations.php');
    }
    
    if ($action === 'bulk_action' && isset($_POST['ids'], $_POST['bulk_status'])) {
        $ids = array_map('intval', $_POST['ids']);
        $status = $_POST['bulk_status'];
        if ($ids && in_array($status, ['contacted', 'in_progress', 'quoted', 'converted', 'closed', 'spam', 'delete'])) {
            if ($status === 'delete') {
                query("DELETE FROM quotation_requests WHERE id IN (" . implode(',', $ids) . ")");
                setFlash('success', count($ids) . ' quotations deleted');
            } else {
                query("UPDATE quotation_requests SET status = ?, is_read = 1 WHERE id IN (" . implode(',', $ids) . ")", [$status]);
                setFlash('success', count($ids) . ' quotations updated');
            }
        }
        redirect('/admin/quotations.php');
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("DELETE FROM quotation_requests WHERE id = ?", [$id]);
        logActivity('delete', 'quotation_requests', $id, 'Deleted quotation request');
        setFlash('success', 'Quotation deleted');
        redirect('/admin/quotations.php');
    }
}

// Filters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$where = ["1=1"];
$params = [];

if ($status) {
    $where[] = "q.status = ?";
    $params[] = $status;
}
if ($search) {
    $where[] = "(q.full_name LIKE ? OR q.email LIKE ? OR q.company_name LIKE ? OR q.reference_no LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($dateFrom) {
    $where[] = "DATE(q.created_at) >= ?";
    $params[] = $dateFrom;
}
if ($dateTo) {
    $where[] = "DATE(q.created_at) <= ?";
    $params[] = $dateTo;
}

$whereClause = implode(' AND ', $where);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$total = fetchOne("SELECT COUNT(*) as count FROM quotation_requests q WHERE $whereClause", $params)['count'];
$pagination = paginate($total, $perPage, $page);

$quotations = fetchAll("
    SELECT q.*, sc.name as category_name, pp.name as package_name
    FROM quotation_requests q
    LEFT JOIN service_categories sc ON q.category_id = sc.id
    LEFT JOIN pricing_packages pp ON q.package_id = pp.id
    WHERE $whereClause
    ORDER BY q.created_at DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

$statusCounts = fetchAll("SELECT status, COUNT(*) as count FROM quotation_requests GROUP BY status");
$statusMap = array_column($statusCounts, 'count', 'status');
$allCount = array_sum($statusMap);
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Quotation Requests</h1>
        <p class="text-slate-500 text-sm mt-0.5">Manage incoming project inquiries and leads</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="exportData()" class="btn btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    <a href="/admin/quotations.php" class="stat-card text-center <?= !$status ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-2xl font-bold text-slate-900"><?= $allCount ?></p>
        <p class="text-xs text-slate-500">All</p>
    </a>
    <a href="?status=new" class="stat-card text-center <?= $status === 'new' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-2xl font-bold text-blue-600"><?= $statusMap['new'] ?? 0 ?></p>
        <p class="text-xs text-slate-500">New</p>
    </a>
    <a href="?status=contacted" class="stat-card text-center <?= $status === 'contacted' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-2xl font-bold text-amber-600"><?= $statusMap['contacted'] ?? 0 ?></p>
        <p class="text-xs text-slate-500">Contacted</p>
    </a>
    <a href="?status=in_progress" class="stat-card text-center <?= $status === 'in_progress' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-2xl font-bold text-violet-600"><?= $statusMap['in_progress'] ?? 0 ?></p>
        <p class="text-xs text-slate-500">In Progress</p>
    </a>
    <a href="?status=quoted" class="stat-card text-center <?= $status === 'quoted' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-2xl font-bold text-indigo-600"><?= $statusMap['quoted'] ?? 0 ?></p>
        <p class="text-xs text-slate-500">Quoted</p>
    </a>
    <a href="?status=converted" class="stat-card text-center <?= $status === 'converted' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-2xl font-bold text-emerald-600"><?= $statusMap['converted'] ?? 0 ?></p>
        <p class="text-xs text-slate-500">Converted</p>
    </a>
    <a href="?status=closed" class="stat-card text-center <?= $status === 'closed' ? 'ring-2 ring-blue-500' : '' ?>">
        <p class="text-2xl font-bold text-slate-400"><?= $statusMap['closed'] ?? 0 ?></p>
        <p class="text-xs text-slate-500">Closed</p>
    </a>
</div>

<!-- Filters & Search -->
<div class="card mb-6">
    <div class="p-4">
        <form method="GET" class="flex flex-col lg:flex-row gap-3">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by name, email, company, or reference..."
                           class="form-input pl-10">
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <input type="date" name="date_from" value="<?= $dateFrom ?>" class="form-input w-36" placeholder="From">
                <input type="date" name="date_to" value="<?= $dateTo ?>" class="form-input w-36" placeholder="To">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                <?php if ($search || $dateFrom || $dateTo): ?>
                <a href="/admin/quotations.php<?= $status ? "?status=$status" : '' ?>" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<form method="POST" id="bulkForm">
    <input type="hidden" name="action" value="bulk_action">
    
    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-10">
                            <input type="checkbox" class="select-all rounded border-slate-300 text-blue-600 focus:ring-blue-500" onchange="toggleAll(this)">
                        </th>
                        <th>Client</th>
                        <th>Project</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quotations)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state py-12">
                                <svg class="w-16 h-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="mt-2 font-medium text-slate-600">No quotations found</p>
                                <p class="text-sm">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($quotations as $quote): ?>
                    <tr class="<?= !$quote['is_read'] ? 'bg-blue-50/50' : '' ?>">
                        <td>
                            <input type="checkbox" name="ids[]" value="<?= $quote['id'] ?>" class="select-row rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-600 font-semibold text-sm flex-shrink-0">
                                    <?= strtoupper(substr($quote['full_name'], 0, 2)) ?>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <?php if (!$quote['is_read']): ?>
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        <?php endif; ?>
                                        <p class="font-medium text-slate-900 truncate"><?= htmlspecialchars($quote['full_name']) ?></p>
                                    </div>
                                    <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($quote['email']) ?></p>
                                    <?php if ($quote['company_name']): ?>
                                    <p class="text-xs text-slate-400 truncate"><?= htmlspecialchars($quote['company_name']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="font-medium text-slate-900"><?= htmlspecialchars($quote['category_name'] ?? $quote['project_type'] ?? 'General') ?></p>
                            <?php if ($quote['package_name']): ?>
                            <span class="badge badge-purple text-xs"><?= htmlspecialchars($quote['package_name']) ?></span>
                            <?php endif; ?>
                            <p class="text-xs text-slate-400 mt-0.5"><?= $quote['reference_no'] ?></p>
                        </td>
                        <td>
                            <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($quote['budget_range'] ?? 'Not specified') ?></span>
                        </td>
                        <td>
                            <select onchange="updateStatus(<?= $quote['id'] ?>, this.value)"
                                    class="text-xs px-2.5 py-1 rounded-full border-0 font-semibold cursor-pointer
                                    <?php
                                    $colors = [
                                        'new' => 'bg-blue-100 text-blue-700',
                                        'contacted' => 'bg-amber-100 text-amber-700',
                                        'in_progress' => 'bg-violet-100 text-violet-700',
                                        'quoted' => 'bg-indigo-100 text-indigo-700',
                                        'converted' => 'bg-emerald-100 text-emerald-700',
                                        'closed' => 'bg-slate-100 text-slate-600',
                                        'spam' => 'bg-red-100 text-red-700'
                                    ];
                                    echo $colors[$quote['status']] ?? 'bg-slate-100 text-slate-600';
                                    ?>">
                                <option value="new" <?= $quote['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                <option value="contacted" <?= $quote['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                                <option value="in_progress" <?= $quote['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="quoted" <?= $quote['status'] === 'quoted' ? 'selected' : '' ?>>Quoted</option>
                                <option value="converted" <?= $quote['status'] === 'converted' ? 'selected' : '' ?>>Converted</option>
                                <option value="closed" <?= $quote['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                <option value="spam" <?= $quote['status'] === 'spam' ? 'selected' : '' ?>>Spam</option>
                            </select>
                        </td>
                        <td>
                            <p class="text-sm text-slate-600"><?= formatDate($quote['created_at'], 'M d, Y') ?></p>
                            <p class="text-xs text-slate-400"><?= formatDate($quote['created_at'], 'h:i A') ?></p>
                        </td>
                        <td>
                            <div class="action-btns justify-end">
                                <a href="/admin/quotation-view.php?id=<?= $quote['id'] ?>" class="action-btn view" title="View Details">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="mailto:<?= $quote['email'] ?>" class="action-btn edit" title="Send Email">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </a>
                                <?php if ($quote['phone']): ?>
                                <a href="tel:<?= $quote['phone'] ?>" class="action-btn" title="Call">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </a>
                                <?php endif; ?>
                                <button type="button" onclick="deleteQuote(<?= $quote['id'] ?>)" class="action-btn delete" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Table Footer -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50">
            <!-- Bulk actions -->
            <div class="flex items-center gap-2" id="bulkActions" style="display: none;">
                <span class="text-sm text-slate-600"><span id="selectedCount">0</span> selected</span>
                <select name="bulk_status" class="form-input text-sm py-1.5 w-auto">
                    <option value="">Bulk action...</option>
                    <option value="contacted">Mark Contacted</option>
                    <option value="in_progress">Mark In Progress</option>
                    <option value="quoted">Mark Quoted</option>
                    <option value="converted">Mark Converted</option>
                    <option value="closed">Mark Closed</option>
                    <option value="spam">Mark Spam</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <div class="flex items-center gap-4 ml-auto">
                <p class="text-sm text-slate-500">
                    Showing <?= $pagination['offset'] + 1 ?>-<?= min($pagination['offset'] + $perPage, $total) ?> of <?= number_format($total) ?>
                </p>
                <div class="pagination">
                    <?php if ($pagination['has_prev']): ?>
                    <a href="?page=<?= $page - 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($pagination['total_pages'], $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>" 
                       class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['has_next']): ?>
                    <a href="?page=<?= $page + 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
function toggleAll(checkbox) {
    document.querySelectorAll('.select-row').forEach(cb => cb.checked = checkbox.checked);
    updateBulkUI();
}

document.querySelectorAll('.select-row').forEach(cb => {
    cb.addEventListener('change', updateBulkUI);
});

function updateBulkUI() {
    const checked = document.querySelectorAll('.select-row:checked').length;
    document.getElementById('bulkActions').style.display = checked > 0 ? 'flex' : 'none';
    document.getElementById('selectedCount').textContent = checked;
}

function updateStatus(id, status) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" value="${id}">
        <input type="hidden" name="status" value="${status}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function deleteQuote(id) {
    if (confirmDelete('Are you sure you want to delete this quotation? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function exportData() {
    window.location.href = '/admin/quotations.php?export=csv&status=<?= $status ?>&search=<?= urlencode($search) ?>';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
