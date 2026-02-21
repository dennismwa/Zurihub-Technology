<?php
/**
 * ZURIHUB TECHNOLOGY - Support Ticket view & reply
 */

$pageTitle = 'Support Ticket';
require_once __DIR__ . '/includes/header.php';

$currentUser = currentUser();
$ticketId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$ticketId) {
    setFlash('error', 'Ticket not found');
    redirect('/admin/tickets.php');
}

$ticket = fetchOne("SELECT * FROM support_tickets WHERE id = ?", [$ticketId]);
if (!$ticket) {
    setFlash('error', 'Ticket not found');
    redirect('/admin/tickets.php');
}

// Handle reply POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply') {
        $message = trim($_POST['message'] ?? '');
        if ($message !== '') {
            query(
                "INSERT INTO support_ticket_replies (ticket_id, reply_by, staff_id, message) VALUES (?, 'staff', ?, ?)",
                [$ticketId, $currentUser['id'], $message]
            );
            query(
                "UPDATE support_tickets SET last_reply_at = NOW(), last_reply_by = 'staff', status = 'in_progress', is_read = 1 WHERE id = ?",
                [$ticketId]
            );
            setFlash('success', 'Reply sent');
            redirect('/admin/ticket-view.php?id=' . $ticketId);
        }
    }
    if ($_POST['action'] === 'update_status') {
        $newStatus = $_POST['status'] ?? '';
        $allowed = ['open', 'in_progress', 'waiting_reply', 'resolved', 'closed'];
        if (in_array($newStatus, $allowed)) {
            query("UPDATE support_tickets SET status = ?, is_read = 1 WHERE id = ?", [$newStatus, $ticketId]);
            setFlash('success', 'Status updated');
            redirect('/admin/ticket-view.php?id=' . $ticketId);
        }
    }
}

query("UPDATE support_tickets SET is_read = 1 WHERE id = ?", [$ticketId]);

$replies = fetchAll("
    SELECT r.*, u.full_name as staff_name 
    FROM support_ticket_replies r 
    LEFT JOIN admin_users u ON r.staff_id = u.id 
    WHERE r.ticket_id = ? 
    ORDER BY r.created_at ASC
", [$ticketId]);

$statusColors = [
    'open' => 'badge-info',
    'in_progress' => 'badge-warning',
    'waiting_reply' => 'badge-warning',
    'resolved' => 'badge-success',
    'closed' => 'badge-gray'
];
?>

<div class="mb-6">
    <a href="/admin/tickets.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 text-sm font-medium">← Back to tickets</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="table-container">
            <div class="p-4 border-b border-gray-100">
                <h1 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($ticket['subject']) ?></h1>
                <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($ticket['ticket_ref']) ?> · Created <?= date('M j, Y g:i A', strtotime($ticket['created_at'])) ?></p>
            </div>
            <div class="p-4 space-y-4">
                <?php
                $first = true;
                foreach ($replies as $r):
                    $isStaff = $r['reply_by'] === 'staff';
                    $label = $isStaff ? ($r['staff_name'] ?: 'Staff') : $ticket['visitor_name'];
                ?>
                <div class="flex <?= $isStaff ? 'justify-end' : '' ?>">
                    <div class="max-w-[85%] rounded-lg px-4 py-3 <?= $isStaff ? 'bg-primary-100 text-primary-900' : 'bg-gray-50 border border-gray-100' ?>">
                        <p class="text-xs font-semibold text-gray-500 mb-1"><?= htmlspecialchars($label) ?> · <?= date('M j, g:i A', strtotime($r['created_at'])) ?></p>
                        <div class="text-sm"><?= nl2br(htmlspecialchars($r['message'])) ?></div>
                    </div>
                </div>
                <?php $first = false; endforeach; ?>
            </div>
            <form method="post" class="p-4 border-t border-gray-100 bg-gray-50">
                <input type="hidden" name="action" value="reply">
                <textarea name="message" rows="3" class="form-input mb-3" placeholder="Type your reply..." required></textarea>
                <button type="submit" class="btn-primary">Send reply</button>
            </form>
        </div>
    </div>

    <div class="space-y-4">
        <div class="table-container p-4">
            <h3 class="font-semibold text-gray-900 mb-3">Ticket details</h3>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-gray-500">Status</dt><dd class="mt-0.5"><span class="badge <?= $statusColors[$ticket['status']] ?? 'badge-gray' ?>"><?= str_replace('_', ' ', $ticket['status']) ?></span></dd></div>
                <div><dt class="text-gray-500">Priority</dt><dd class="mt-0.5"><?= ucfirst($ticket['priority']) ?></dd></div>
                <div><dt class="text-gray-500">Visitor</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars($ticket['visitor_name']) ?></dd></div>
                <div><dt class="text-gray-500">Email</dt><dd class="mt-0.5"><a href="mailto:<?= htmlspecialchars($ticket['visitor_email']) ?>" class="text-primary-600 hover:underline"><?= htmlspecialchars($ticket['visitor_email']) ?></a></dd></div>
                <?php if ($ticket['visitor_phone']): ?>
                <div><dt class="text-gray-500">Phone</dt><dd class="mt-0.5"><?= htmlspecialchars($ticket['visitor_phone']) ?></dd></div>
                <?php endif; ?>
            </dl>
        </div>
        <div class="table-container p-4">
            <h3 class="font-semibold text-gray-900 mb-3">Update status</h3>
            <form method="post">
                <input type="hidden" name="action" value="update_status">
                <select name="status" class="form-input mb-3">
                    <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option>
                    <option value="waiting_reply" <?= $ticket['status'] === 'waiting_reply' ? 'selected' : '' ?>>Waiting reply</option>
                    <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
                <button type="submit" class="btn-secondary w-full">Save status</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
