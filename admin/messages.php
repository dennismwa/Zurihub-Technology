<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Messages (Modern Design)
 */

$pageTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        $id = (int)$_POST['id'];
        $status = $_POST['status'];
        query("UPDATE contact_messages SET status = ?, is_read = 1 WHERE id = ?", [$status, $id]);
        setFlash('success', 'Status updated');
        redirect('/admin/messages.php');
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("DELETE FROM contact_messages WHERE id = ?", [$id]);
        setFlash('success', 'Message deleted');
        redirect('/admin/messages.php');
    }
    
    if ($action === 'bulk_delete' && isset($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);
        if ($ids) {
            query("DELETE FROM contact_messages WHERE id IN (" . implode(',', $ids) . ")");
            setFlash('success', count($ids) . ' messages deleted');
        }
        redirect('/admin/messages.php');
    }
    
    if ($action === 'mark_all_read') {
        query("UPDATE contact_messages SET is_read = 1 WHERE is_read = 0");
        setFlash('success', 'All messages marked as read');
        redirect('/admin/messages.php');
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
    $where[] = "(full_name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

$whereClause = implode(' AND ', $where);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$total = fetchOne("SELECT COUNT(*) as count FROM contact_messages WHERE $whereClause", $params)['count'];
$pagination = paginate($total, $perPage, $page);

$messages = fetchAll("
    SELECT * FROM contact_messages 
    WHERE $whereClause 
    ORDER BY is_read ASC, created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

// Get unread count
$unreadCount = fetchOne("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0")['count'];
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Contact Messages</h1>
        <p class="text-slate-500 text-sm mt-0.5"><?= number_format($total) ?> total messages<?= $unreadCount > 0 ? ", <span class=\"text-blue-600 font-medium\">$unreadCount unread</span>" : '' ?></p>
    </div>
    <?php if ($unreadCount > 0): ?>
    <form method="POST" class="inline">
        <input type="hidden" name="action" value="mark_all_read">
        <button type="submit" class="btn btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Mark all as read
        </button>
    </form>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Search messages..." class="form-input pl-10">
            </div>
            <select name="status" class="form-input w-auto min-w-[140px]">
                <option value="">All Status</option>
                <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>New</option>
                <option value="read" <?= $status === 'read' ? 'selected' : '' ?>>Read</option>
                <option value="replied" <?= $status === 'replied' ? 'selected' : '' ?>>Replied</option>
                <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
                <option value="spam" <?= $status === 'spam' ? 'selected' : '' ?>>Spam</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $status): ?>
            <a href="/admin/messages.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Messages List -->
<form method="POST" id="bulkForm">
    <input type="hidden" name="action" value="bulk_delete">
    
    <div class="card overflow-hidden">
        <?php if (empty($messages)): ?>
        <div class="empty-state py-16">
            <svg class="w-16 h-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="mt-3 font-medium text-slate-600">No messages found</p>
            <p class="text-sm">Messages from your contact form will appear here</p>
        </div>
        <?php else: ?>
        <div class="divide-y divide-slate-100">
            <?php foreach ($messages as $msg): ?>
            <div class="group hover:bg-slate-50/50 transition" x-data="{ expanded: false, showActions: false }">
                <div class="flex items-start gap-4 p-4 cursor-pointer" @click="expanded = !expanded; <?= !$msg['is_read'] ? "markAsRead({$msg['id']})" : '' ?>">
                    <!-- Checkbox -->
                    <div class="pt-1" @click.stop>
                        <input type="checkbox" name="ids[]" value="<?= $msg['id'] ?>" class="select-row rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </div>
                    
                    <!-- Avatar -->
                    <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-semibold
                                <?= !$msg['is_read'] ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-600' ?>">
                        <?= strtoupper(substr($msg['full_name'], 0, 2)) ?>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <?php if (!$msg['is_read']): ?>
                            <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                            <?php endif; ?>
                            <p class="font-semibold text-slate-900 truncate <?= !$msg['is_read'] ? 'text-slate-900' : 'text-slate-700' ?>">
                                <?= htmlspecialchars($msg['full_name']) ?>
                            </p>
                            <span class="text-slate-400">·</span>
                            <p class="text-sm text-slate-500 truncate"><?= htmlspecialchars($msg['email']) ?></p>
                        </div>
                        <p class="font-medium text-sm text-slate-800 truncate mb-1"><?= htmlspecialchars($msg['subject'] ?: 'No Subject') ?></p>
                        <p class="text-sm text-slate-500 truncate"><?= htmlspecialchars(substr($msg['message'], 0, 120)) ?>...</p>
                    </div>
                    
                    <!-- Meta -->
                    <div class="flex-shrink-0 text-right">
                        <span class="badge <?php
                            $colors = ['new' => 'badge-info', 'read' => 'badge-gray', 'replied' => 'badge-success', 'closed' => 'badge-gray', 'spam' => 'badge-danger'];
                            echo $colors[$msg['status']] ?? 'badge-gray';
                        ?>"><?= ucfirst($msg['status']) ?></span>
                        <p class="text-xs text-slate-400 mt-2"><?= timeAgo($msg['created_at']) ?></p>
                    </div>
                    
                    <!-- Expand icon -->
                    <div class="flex-shrink-0 pt-1">
                        <svg class="w-5 h-5 text-slate-400 transition-transform" :class="expanded && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                
                <!-- Expanded content -->
                <div x-show="expanded" x-collapse x-cloak class="px-4 pb-4 ml-[4.5rem]">
                    <div class="bg-slate-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-slate-700 whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($msg['message']) ?></p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject'] ?: 'Your message') ?>" class="btn btn-primary btn-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            Reply
                        </a>
                        
                        <form method="POST" class="inline-flex">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                            <select name="status" onchange="this.form.submit()" class="form-input text-xs py-1.5 pr-8">
                                <option value="new" <?= $msg['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                <option value="read" <?= $msg['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                                <option value="replied" <?= $msg['status'] === 'replied' ? 'selected' : '' ?>>Replied</option>
                                <option value="closed" <?= $msg['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                <option value="spam" <?= $msg['status'] === 'spam' ? 'selected' : '' ?>>Spam</option>
                            </select>
                        </form>
                        
                        <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this message?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #dc2626;">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </form>
                        
                        <?php if ($msg['phone']): ?>
                        <a href="tel:<?= $msg['phone'] ?>" class="btn btn-secondary btn-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <?= htmlspecialchars($msg['phone']) ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <?php if (!empty($messages)): ?>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-3" id="bulkActions" style="display: none;">
                <span class="text-sm text-slate-600"><span id="selectedCount">0</span> selected</span>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirmDelete('Delete selected messages?')">Delete Selected</button>
            </div>
            
            <?php if ($pagination['total_pages'] > 1): ?>
            <div class="flex items-center gap-4 ml-auto">
                <p class="text-sm text-slate-500">Page <?= $page ?> of <?= $pagination['total_pages'] ?></p>
                <div class="pagination">
                    <?php if ($pagination['has_prev']): ?>
                    <a href="?page=<?= $page - 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ($pagination['has_next']): ?>
                    <a href="?page=<?= $page + 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</form>

<script>
document.querySelectorAll('.select-row').forEach(cb => {
    cb.addEventListener('change', updateBulkUI);
});

function updateBulkUI() {
    const checked = document.querySelectorAll('.select-row:checked').length;
    document.getElementById('bulkActions').style.display = checked > 0 ? 'flex' : 'none';
    document.getElementById('selectedCount').textContent = checked;
}

function markAsRead(id) {
    fetch('/admin/messages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: `action=mark_read&id=${id}`
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
