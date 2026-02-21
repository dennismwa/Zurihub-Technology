<?php
/**
 * ZURIHUB TECHNOLOGY - Quotation Details (Modern Design)
 */

$pageTitle = 'Quotation Details';
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    redirect('/admin/quotations.php');
}

$quote = fetchOne("
    SELECT q.*, sc.name as category_name, pp.name as package_name, pp.price as package_price
    FROM quotation_requests q
    LEFT JOIN service_categories sc ON q.category_id = sc.id
    LEFT JOIN pricing_packages pp ON q.package_id = pp.id
    WHERE q.id = ?
", [$id]);

if (!$quote) {
    setFlash('error', 'Quotation not found');
    redirect('/admin/quotations.php');
}

if (!$quote['is_read']) {
    query("UPDATE quotation_requests SET is_read = 1 WHERE id = ?", [$id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $status = $_POST['status'];
        $notes = trim($_POST['admin_notes'] ?? '');
        $quotedAmount = $_POST['quoted_amount'] ?: null;
        
        query("UPDATE quotation_requests SET status = ?, admin_notes = ?, quoted_amount = ? WHERE id = ?", 
              [$status, $notes, $quotedAmount, $id]);
        logActivity('update', 'quotation_requests', $id, "Updated quotation: status=$status");
        setFlash('success', 'Quotation updated successfully');
        redirect('/admin/quotation-view.php?id=' . $id);
    }
}

$statusColors = [
    'new' => 'bg-blue-100 text-blue-700',
    'contacted' => 'bg-amber-100 text-amber-700',
    'in_progress' => 'bg-violet-100 text-violet-700',
    'quoted' => 'bg-indigo-100 text-indigo-700',
    'converted' => 'bg-emerald-100 text-emerald-700',
    'closed' => 'bg-slate-100 text-slate-600',
    'spam' => 'bg-red-100 text-red-700'
];
?>

<!-- Back link -->
<div class="mb-4">
    <a href="/admin/quotations.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm font-medium transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Quotations
    </a>
</div>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($quote['full_name']) ?></h1>
            <span class="badge <?= $statusColors[$quote['status']] ?>"><?= ucfirst(str_replace('_', ' ', $quote['status'])) ?></span>
        </div>
        <p class="text-slate-500 text-sm mt-0.5">
            <span class="font-mono"><?= $quote['reference_no'] ?></span> · Received <?= timeAgo($quote['created_at']) ?>
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="mailto:<?= $quote['email'] ?>?subject=Re: Your Quotation Request (<?= $quote['reference_no'] ?>)" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Send Email
        </a>
        <?php if ($quote['phone']): ?>
        <a href="tel:<?= $quote['phone'] ?>" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Call
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Client Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Client Information
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">Full Name</p>
                        <p class="text-slate-900 font-medium"><?= htmlspecialchars($quote['full_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">Email</p>
                        <a href="mailto:<?= $quote['email'] ?>" class="text-blue-600 hover:text-blue-700 font-medium"><?= htmlspecialchars($quote['email']) ?></a>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">Phone</p>
                        <p class="text-slate-900"><?= htmlspecialchars($quote['phone'] ?? '—') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">Company</p>
                        <p class="text-slate-900"><?= htmlspecialchars($quote['company_name'] ?? '—') ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Project Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Project Details
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">Service Category</p>
                        <p class="text-slate-900 font-medium"><?= htmlspecialchars($quote['category_name'] ?? $quote['project_type'] ?? 'General Inquiry') ?></p>
                    </div>
                    <?php if ($quote['package_name']): ?>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">Selected Package</p>
                        <p class="text-slate-900 font-medium">
                            <?= htmlspecialchars($quote['package_name']) ?>
                            <?php if ($quote['package_price']): ?>
                            <span class="text-emerald-600">(<?= formatCurrency($quote['package_price']) ?>)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">Budget Range</p>
                        <p class="text-slate-900"><?= htmlspecialchars($quote['budget_range'] ?? '—') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">Timeline</p>
                        <p class="text-slate-900"><?= htmlspecialchars($quote['timeline'] ?? '—') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase font-medium mb-1">How They Found Us</p>
                        <p class="text-slate-900"><?= htmlspecialchars($quote['how_found_us'] ?? '—') ?></p>
                    </div>
                </div>
                
                <?php if ($quote['project_description']): ?>
                <div class="pt-5 border-t border-slate-100">
                    <p class="text-xs text-slate-500 uppercase font-medium mb-2">Project Description</p>
                    <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($quote['project_description']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($quote['requirements']): ?>
                <div class="pt-5 border-t border-slate-100">
                    <p class="text-xs text-slate-500 uppercase font-medium mb-2">Specific Requirements</p>
                    <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($quote['requirements']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Technical Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    Technical Information
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-slate-500">IP Address</p>
                        <p class="font-mono text-slate-700"><?= htmlspecialchars($quote['ip_address'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-slate-500">Submitted</p>
                        <p class="text-slate-700"><?= formatDate($quote['created_at'], 'Y-m-d H:i:s') ?></p>
                    </div>
                    <?php if ($quote['user_agent']): ?>
                    <div class="sm:col-span-2">
                        <p class="text-slate-500 mb-1">Browser/Device</p>
                        <p class="text-slate-700 text-xs break-all bg-slate-50 rounded p-2 font-mono"><?= htmlspecialchars($quote['user_agent']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        
        <!-- Update Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-900">Update Status</h3>
            </div>
            <form method="POST" class="card-body">
                <input type="hidden" name="action" value="update_status">
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-input">
                            <option value="new" <?= $quote['status'] === 'new' ? 'selected' : '' ?>>New</option>
                            <option value="contacted" <?= $quote['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                            <option value="in_progress" <?= $quote['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="quoted" <?= $quote['status'] === 'quoted' ? 'selected' : '' ?>>Quoted</option>
                            <option value="converted" <?= $quote['status'] === 'converted' ? 'selected' : '' ?>>Converted</option>
                            <option value="closed" <?= $quote['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                            <option value="spam" <?= $quote['status'] === 'spam' ? 'selected' : '' ?>>Spam</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label">Quoted Amount (KES)</label>
                        <input type="number" name="quoted_amount" step="0.01" value="<?= $quote['quoted_amount'] ?? '' ?>" class="form-input" placeholder="50000">
                    </div>
                    
                    <div>
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" rows="4" class="form-input" placeholder="Internal notes..."><?= htmlspecialchars($quote['admin_notes'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full">Save Changes</button>
                </div>
            </form>
        </div>
        
        <!-- Quick Actions -->
        <div class="card p-4">
            <h3 class="font-semibold text-slate-900 mb-3">Quick Actions</h3>
            <div class="space-y-2">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $quote['phone'] ?? '') ?>?text=Hi <?= urlencode($quote['full_name']) ?>, regarding your quotation request (<?= $quote['reference_no'] ?>)..." 
                   target="_blank" class="btn btn-sm w-full justify-center" style="background: #dcfce7; color: #166534;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
            </div>
        </div>
        
        <!-- Timeline -->
        <div class="card p-4">
            <h3 class="font-semibold text-slate-900 mb-3">Timeline</h3>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-medium text-slate-900 text-sm">Request Received</p>
                        <p class="text-xs text-slate-500"><?= formatDate($quote['created_at'], 'M d, Y \a\t h:i A') ?></p>
                    </div>
                </div>
                <?php if ($quote['updated_at'] !== $quote['created_at']): ?>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <p class="font-medium text-slate-900 text-sm">Last Updated</p>
                        <p class="text-xs text-slate-500"><?= formatDate($quote['updated_at'], 'M d, Y \a\t h:i A') ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
