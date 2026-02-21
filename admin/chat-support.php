<?php
/**
 * ZURIHUB TECHNOLOGY - Chat Support (Modern Design)
 */

$pageTitle = 'Live Chat';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$currentUser = currentUser();
$conversationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle reply POST — must run before any output so redirect() can send headers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $convId = (int)($_POST['conversation_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    if ($convId && $message !== '') {
        query(
            "INSERT INTO chat_messages (conversation_id, sender_type, staff_id, message, is_read) VALUES (?, 'staff', ?, ?, 0)",
            [$convId, $currentUser['id'], $message]
        );
        $preview = strlen($message) > 160 ? substr($message, 0, 157) . '...' : $message;
        query("UPDATE chat_conversations SET last_message_at = NOW(), last_message_preview = ?, assigned_to = ? WHERE id = ?", [$preview, $currentUser['id'], $convId]);
        setFlash('success', 'Reply sent');
        redirect('/admin/chat-support.php?id=' . $convId);
    }
    setFlash('error', 'Message is required');
    redirect('/admin/chat-support.php?id=' . $convId);
}

// Handle status change — must run before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_status') {
    $convId = (int)($_POST['conversation_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if ($convId && in_array($newStatus, ['open', 'closed'])) {
        query("UPDATE chat_conversations SET status = ? WHERE id = ?", [$newStatus, $convId]);
        setFlash('success', 'Status updated');
    }
    redirect('/admin/chat-support.php?id=' . $convId);
}

require_once __DIR__ . '/includes/header.php';

// Single conversation view
if ($conversationId) {
    $conv = fetchOne("SELECT * FROM chat_conversations WHERE id = ?", [$conversationId]);
    if (!$conv) {
        setFlash('error', 'Conversation not found');
        redirect('/admin/chat-support.php');
    }
    query("UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND sender_type = 'visitor'", [$conversationId]);
    $messages = fetchAll("SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC", [$conversationId]);
    $pageTitle = 'Chat: ' . $conv['visitor_name'];
}

// List conversations
$conversations = fetchAll("
    SELECT c.*, 
           (SELECT COUNT(*) FROM chat_messages m WHERE m.conversation_id = c.id AND m.sender_type = 'visitor' AND m.is_read = 0) AS unread_count
    FROM chat_conversations c 
    ORDER BY 
        CASE WHEN c.status = 'open' THEN 0 ELSE 1 END,
        c.last_message_at DESC, 
        c.created_at DESC 
    LIMIT 100
");

$openCount = count(array_filter($conversations, fn($c) => $c['status'] === 'open'));
$closedCount = count($conversations) - $openCount;
?>

<?php if ($conversationId && $conv): ?>
<!-- Single conversation view -->
<div class="mb-4">
    <a href="/admin/chat-support.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm font-medium transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to conversations
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Chat area -->
    <div class="lg:col-span-3">
        <div class="card overflow-hidden flex flex-col" style="height: calc(100vh - 200px); min-height: 500px;">
            <!-- Chat header -->
            <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-600 font-semibold">
                        <?= strtoupper(substr($conv['visitor_name'], 0, 2)) ?>
                    </div>
                    <div>
                        <h2 class="font-semibold text-slate-900"><?= htmlspecialchars($conv['visitor_name']) ?></h2>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($conv['visitor_email']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge <?= $conv['status'] === 'open' ? 'badge-success' : 'badge-gray' ?>"><?= ucfirst($conv['status']) ?></span>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="change_status">
                        <input type="hidden" name="conversation_id" value="<?= $conversationId ?>">
                        <input type="hidden" name="status" value="<?= $conv['status'] === 'open' ? 'closed' : 'open' ?>">
                        <button type="submit" class="btn btn-sm <?= $conv['status'] === 'open' ? 'btn-secondary' : 'btn-success' ?>">
                            <?= $conv['status'] === 'open' ? 'Close Chat' : 'Reopen' ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 bg-slate-50 space-y-3" id="chatMessages">
                <?php foreach ($messages as $m): ?>
                <div class="flex <?= $m['sender_type'] === 'staff' ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-[75%] <?= $m['sender_type'] === 'staff' ? 'order-2' : '' ?>">
                        <div class="rounded-2xl px-4 py-2.5 <?= $m['sender_type'] === 'staff' 
                            ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-br-md' 
                            : 'bg-white border border-slate-200 text-slate-800 rounded-bl-md shadow-sm' ?>">
                            <p class="text-sm whitespace-pre-wrap"><?= htmlspecialchars($m['message']) ?></p>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 <?= $m['sender_type'] === 'staff' ? 'text-right' : '' ?>">
                            <?= $m['sender_type'] === 'visitor' ? 'Visitor' : 'You' ?> · <?= date('M j, g:i A', strtotime($m['created_at'])) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Reply form -->
            <?php if ($conv['status'] === 'open'): ?>
            <form method="POST" class="p-4 border-t border-slate-100 bg-white flex-shrink-0">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="conversation_id" value="<?= $conversationId ?>">
                <div class="flex gap-3">
                    <textarea name="message" rows="2" class="form-input flex-1 resize-none" placeholder="Type your reply..." required></textarea>
                    <button type="submit" class="btn btn-primary self-end">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Send
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="p-4 border-t border-slate-100 bg-slate-50 text-center text-sm text-slate-500 flex-shrink-0">
                This conversation is closed. <button type="submit" form="reopenForm" class="text-blue-600 hover:underline">Reopen</button> to reply.
                <form method="POST" id="reopenForm" class="hidden">
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="conversation_id" value="<?= $conversationId ?>">
                    <input type="hidden" name="status" value="open">
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <div class="card p-4">
            <h3 class="font-semibold text-slate-900 mb-3">Visitor Info</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs">Name</dt>
                    <dd class="font-medium text-slate-900"><?= htmlspecialchars($conv['visitor_name']) ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Email</dt>
                    <dd class="font-medium text-slate-900"><?= htmlspecialchars($conv['visitor_email']) ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Started</dt>
                    <dd class="text-slate-700"><?= date('M j, Y g:i A', strtotime($conv['created_at'])) ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">IP Address</dt>
                    <dd class="text-slate-700 font-mono text-xs"><?= htmlspecialchars($conv['visitor_ip'] ?? '—') ?></dd>
                </div>
            </dl>
            
            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="mailto:<?= htmlspecialchars($conv['visitor_email']) ?>" class="btn btn-secondary btn-sm w-full">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email Visitor
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.getElementById('chatMessages');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
});
</script>

<?php else: ?>
<!-- Conversations list -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Live Chat</h1>
        <p class="text-slate-500 text-sm mt-0.5">Website chat conversations</p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-medium text-slate-500 uppercase">Total Chats</p>
        <p class="text-2xl font-bold text-slate-900 mt-1"><?= count($conversations) ?></p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-medium text-slate-500 uppercase">Open</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $openCount ?></p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-medium text-slate-500 uppercase">Closed</p>
        <p class="text-2xl font-bold text-slate-400 mt-1"><?= $closedCount ?></p>
    </div>
</div>

<!-- Conversations -->
<div class="card overflow-hidden">
    <?php if (empty($conversations)): ?>
    <div class="empty-state py-16">
        <svg class="w-16 h-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <p class="mt-3 font-medium text-slate-600">No conversations yet</p>
        <p class="text-sm">Chat conversations from the website widget will appear here</p>
    </div>
    <?php else: ?>
    <div class="divide-y divide-slate-100">
        <?php foreach ($conversations as $c): ?>
        <a href="/admin/chat-support.php?id=<?= $c['id'] ?>" class="flex items-center gap-4 p-4 hover:bg-slate-50 transition">
            <div class="relative flex-shrink-0">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-semibold
                            <?= $c['status'] === 'open' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500' ?>">
                    <?= strtoupper(substr($c['visitor_name'], 0, 2)) ?>
                </div>
                <?php if (($c['unread_count'] ?? 0) > 0): ?>
                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                    <?= $c['unread_count'] > 9 ? '9+' : $c['unread_count'] ?>
                </span>
                <?php endif; ?>
            </div>
            
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="font-semibold text-slate-900"><?= htmlspecialchars($c['visitor_name']) ?></p>
                    <span class="badge text-[9px] <?= $c['status'] === 'open' ? 'badge-success' : 'badge-gray' ?>"><?= ucfirst($c['status']) ?></span>
                </div>
                <p class="text-xs text-slate-500 mb-1"><?= htmlspecialchars($c['visitor_email']) ?></p>
                <p class="text-sm text-slate-600 truncate"><?= $c['last_message_preview'] ? htmlspecialchars($c['last_message_preview']) : '—' ?></p>
            </div>
            
            <div class="flex-shrink-0 text-right">
                <p class="text-xs text-slate-400"><?= $c['last_message_at'] ? timeAgo($c['last_message_at']) : date('M j', strtotime($c['created_at'])) ?></p>
            </div>
            
            <svg class="w-5 h-5 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
