<?php
/**
 * ZURIHUB TECHNOLOGY - Support Tickets (Modern Design)
 */

$pageTitle = 'Tickets';
require_once __DIR__ . '/includes/header.php';

$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$search = $_GET['search'] ?? '';

$where = ["1=1"];
$params = [];

if ($status) {
    $where[] = "t.status = ?";
    $params[] = $status;
}
if ($priority) {
    $where[] = "t.priority = ?";
    $params[] = $priority;
}
if ($search) {
    $where[] = "(t.ticket_ref LIKE ? OR t.subject LIKE ? OR t.visitor_name LIKE ? OR t.visitor_email LIKE ?)";
    $q = "%{$search}%";
    $params = array_merge($params, [$q, $q, $q, $q]);
}

$whereClause = implode(' AND ', $where);

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$total = fetchOne("SELECT COUNT(*) as count FROM support_tickets t WHERE $whereClause", $params)['count'];
$pagination = paginate($total, $perPage, $page);

$tickets = fetchAll("
    SELECT t.*, 
           (SELECT COUNT(*) FROM support_ticket_replies r WHERE r.ticket_id = t.id) as reply_count
    FROM support_tickets t 
    WHERE $whereClause 
    ORDER BY 
        CASE WHEN t.status IN ('open', 'in_progress', 'waiting_reply') THEN 0 ELSE 1 END,
        t.is_read ASC,
        CASE t.priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
        t.created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

// Stats
$statusCounts = fetchAll("SELECT status, COUNT(*) as count FROM support_tickets GROUP BY status");
$statusMap = array_column($statusCounts, 'count', 'status');
$openCount = ($statusMap['open'] ?? 0) + ($statusMap['in_progress'] ?? 0) + ($statusMap['waiting_reply'] ?? 0);
$resolvedCount = ($statusMap['resolved'] ?? 0) + ($statusMap['closed'] ?? 0);
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Support Tickets</h1>
        <p class="text-slate-500 text-sm mt-0.5">Manage customer support requests</p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <a href="/admin/tickets.php" class="stat-card <?= !$status ? 'ring-2 ring-blue-500' : '' ?>">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase">All Tickets</p>
                <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($total) ?></p>
            </div>
            <div class="stat-icon bg-slate-100 text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
        </div>
    </a>
    <a href="?status=open" class="stat-card <?= $status === 'open' ? 'ring-2 ring-blue-500' : '' ?>">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase">Open</p>
                <p class="text-2xl font-bold text-blue-600 mt-1"><?= number_format($statusMap['open'] ?? 0) ?></p>
            </div>
            <div class="stat-icon bg-blue-100 text-blue-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </a>
    <a href="?status=in_progress" class="stat-card <?= $status === 'in_progress' ? 'ring-2 ring-blue-500' : '' ?>">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase">In Progress</p>
                <p class="text-2xl font-bold text-amber-600 mt-1"><?= number_format($statusMap['in_progress'] ?? 0) ?></p>
            </div>
            <div class="stat-icon bg-amber-100 text-amber-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
        </div>
    </a>
    <a href="?status=resolved" class="stat-card <?= $status === 'resolved' ? 'ring-2 ring-blue-500' : '' ?>">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase">Resolved</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1"><?= number_format($statusMap['resolved'] ?? 0) ?></p>
            </div>
            <div class="stat-icon bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
    </a>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="p-4">
        <form method="GET" class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by ref, subject, name, email..." class="form-input pl-10">
            </div>
            <select name="status" class="form-input w-auto min-w-[150px]">
                <option value="">All Status</option>
                <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="waiting_reply" <?= $status === 'waiting_reply' ? 'selected' : '' ?>>Waiting Reply</option>
                <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
            <select name="priority" class="form-input w-auto min-w-[130px]">
                <option value="">All Priority</option>
                <option value="urgent" <?= $priority === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                <option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>High</option>
                <option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>Medium</option>
                <option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>Low</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $status || $priority): ?>
            <a href="/admin/tickets.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tickets Table -->
<div class="card overflow-hidden">
    <?php if (empty($tickets)): ?>
    <div class="empty-state py-16">
        <svg class="w-16 h-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        <p class="mt-3 font-medium text-slate-600">No tickets found</p>
        <p class="text-sm">Support tickets will appear here</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Requester</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Replies</th>
                    <th>Created</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                <tr class="<?= !$t['is_read'] ? 'bg-blue-50/50' : '' ?>">
                    <td>
                        <div class="flex items-center gap-3">
                            <?php if (!$t['is_read']): ?>
                            <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <p class="font-mono text-xs text-slate-500"><?= htmlspecialchars($t['ticket_ref']) ?></p>
                                <p class="font-medium text-slate-900 truncate max-w-[250px]"><?= htmlspecialchars($t['subject']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="font-medium text-slate-900"><?= htmlspecialchars($t['visitor_name']) ?></p>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($t['visitor_email']) ?></p>
                    </td>
                    <td>
                        <?php
                        $statusColors = [
                            'open' => 'bg-blue-100 text-blue-700',
                            'in_progress' => 'bg-amber-100 text-amber-700',
                            'waiting_reply' => 'bg-violet-100 text-violet-700',
                            'resolved' => 'bg-emerald-100 text-emerald-700',
                            'closed' => 'bg-slate-100 text-slate-600'
                        ];
                        ?>
                        <span class="badge <?= $statusColors[$t['status']] ?? 'badge-gray' ?> text-[10px]">
                            <?= ucwords(str_replace('_', ' ', $t['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $priorityColors = [
                            'urgent' => 'bg-red-100 text-red-700',
                            'high' => 'bg-orange-100 text-orange-700',
                            'medium' => 'bg-blue-100 text-blue-700',
                            'low' => 'bg-slate-100 text-slate-600'
                        ];
                        ?>
                        <span class="badge <?= $priorityColors[$t['priority']] ?? 'badge-gray' ?> text-[10px]">
                            <?= ucfirst($t['priority']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-sm text-slate-600"><?= number_format($t['reply_count']) ?></span>
                    </td>
                    <td>
                        <p class="text-sm text-slate-600"><?= date('M j, Y', strtotime($t['created_at'])) ?></p>
                        <p class="text-xs text-slate-400"><?= date('g:i A', strtotime($t['created_at'])) ?></p>
                    </td>
                    <td>
                        <div class="action-btns justify-end">
                            <a href="/admin/ticket-view.php?id=<?= $t['id'] ?>" class="action-btn view" title="View Ticket">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="mailto:<?= htmlspecialchars($t['visitor_email']) ?>?subject=Re: [<?= $t['ticket_ref'] ?>] <?= htmlspecialchars($t['subject']) ?>" class="action-btn edit" title="Reply via Email">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </a>
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
        <p class="text-sm text-slate-500">Page <?= $page ?> of <?= $pagination['total_pages'] ?></p>
        <div class="pagination">
            <?php if ($pagination['has_prev']): ?>
            <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&priority=<?= urlencode($priority) ?>&search=<?= urlencode($search) ?>">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&priority=<?= urlencode($priority) ?>&search=<?= urlencode($search) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($pagination['has_next']): ?>
            <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&priority=<?= urlencode($priority) ?>&search=<?= urlencode($search) ?>">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
