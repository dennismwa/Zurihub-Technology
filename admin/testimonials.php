<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Testimonials Management
 */

$pageTitle = 'Testimonials';
require_once __DIR__ . '/includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("DELETE FROM testimonials WHERE id = ?", [$id]);
        setFlash('success', 'Testimonial deleted');
        redirect('/admin/testimonials.php');
    }
    
    if ($action === 'toggle_approved' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("UPDATE testimonials SET is_approved = NOT is_approved WHERE id = ?", [$id]);
        if (isAjax()) jsonSuccess('Updated');
        redirect('/admin/testimonials.php');
    }
    
    if ($action === 'toggle_featured' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        query("UPDATE testimonials SET is_featured = NOT is_featured WHERE id = ?", [$id]);
        if (isAjax()) jsonSuccess('Updated');
        redirect('/admin/testimonials.php');
    }
    
    if ($action === 'save') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $data = [
            'client_name' => sanitize($_POST['client_name']),
            'client_title' => sanitize($_POST['client_title']),
            'company_name' => sanitize($_POST['company_name']),
            'testimonial' => sanitize($_POST['testimonial']),
            'rating' => (int)$_POST['rating'],
            'service_type' => sanitize($_POST['service_type']),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_approved' => isset($_POST['is_approved']) ? 1 : 0
        ];
        
        if ($id) {
            $fields = [];
            $params = [];
            foreach ($data as $k => $v) {
                $fields[] = "$k = ?";
                $params[] = $v;
            }
            $params[] = $id;
            query("UPDATE testimonials SET " . implode(', ', $fields) . " WHERE id = ?", $params);
            setFlash('success', 'Testimonial updated');
        } else {
            $fields = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            query("INSERT INTO testimonials ($fields) VALUES ($placeholders)", array_values($data));
            setFlash('success', 'Testimonial added');
        }
        redirect('/admin/testimonials.php');
    }
}

// Fetch testimonials
$testimonials = fetchAll("SELECT * FROM testimonials ORDER BY is_featured DESC, created_at DESC");
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Testimonials</h1>
        <p class="text-gray-500 mt-1"><?= count($testimonials) ?> testimonials</p>
    </div>
    <button onclick="openModal()" class="btn-primary inline-flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Testimonial
    </button>
</div>

<!-- Testimonials Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($testimonials as $t): ?>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 relative">
        <?php if ($t['is_featured']): ?>
        <span class="absolute top-4 right-4 text-yellow-500">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
        </span>
        <?php endif; ?>
        
        <!-- Rating -->
        <div class="flex gap-1 mb-3">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <svg class="w-4 h-4 <?= $i <= $t['rating'] ? 'text-yellow-400' : 'text-gray-200' ?>" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            <?php endfor; ?>
        </div>
        
        <!-- Testimonial Text -->
        <p class="text-gray-600 text-sm mb-4 line-clamp-4">"<?= htmlspecialchars($t['testimonial']) ?>"</p>
        
        <!-- Client Info -->
        <div class="pt-4 border-t border-gray-100">
            <p class="font-semibold text-gray-900"><?= htmlspecialchars($t['client_name']) ?></p>
            <p class="text-sm text-gray-500">
                <?= htmlspecialchars($t['client_title']) ?>
                <?php if ($t['company_name']): ?>, <?= htmlspecialchars($t['company_name']) ?><?php endif; ?>
            </p>
            <?php if ($t['service_type']): ?>
            <span class="inline-block mt-2 text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded"><?= htmlspecialchars($t['service_type']) ?></span>
            <?php endif; ?>
        </div>
        
        <!-- Status & Actions -->
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
            <span class="badge <?= $t['is_approved'] ? 'badge-success' : 'badge-warning' ?>">
                <?= $t['is_approved'] ? 'Approved' : 'Pending' ?>
            </span>
            <div class="flex items-center gap-1">
                <button onclick="editTestimonial(<?= htmlspecialchars(json_encode($t)) ?>)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
                <form method="POST" class="inline" onsubmit="return confirmDelete()">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div id="testimonialModal" class="fixed inset-0 z-50 hidden" x-data>
    <div class="fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg relative max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add Testimonial</h3>
            </div>
            <form method="POST" class="p-6">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="testimonialId" value="">
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Client Name *</label>
                        <input type="text" name="client_name" id="client_name" required class="form-input">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Title/Position</label>
                            <input type="text" name="client_title" id="client_title" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Company</label>
                            <input type="text" name="company_name" id="company_name" class="form-input">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Testimonial *</label>
                        <textarea name="testimonial" id="testimonial" rows="4" required class="form-input"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Rating</label>
                            <select name="rating" id="rating" class="form-input">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Service Type</label>
                            <input type="text" name="service_type" id="service_type" class="form-input" placeholder="e.g., Web Development">
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_approved" id="is_approved" value="1" class="w-4 h-4 text-primary-600 rounded">
                            <span class="text-sm text-gray-700">Approved</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" class="w-4 h-4 text-primary-600 rounded">
                            <span class="text-sm text-gray-700">Featured</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('testimonialModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add Testimonial';
    document.getElementById('testimonialId').value = '';
    document.querySelector('#testimonialModal form').reset();
}

function closeModal() {
    document.getElementById('testimonialModal').classList.add('hidden');
}

function editTestimonial(data) {
    document.getElementById('testimonialModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Testimonial';
    document.getElementById('testimonialId').value = data.id;
    document.getElementById('client_name').value = data.client_name || '';
    document.getElementById('client_title').value = data.client_title || '';
    document.getElementById('company_name').value = data.company_name || '';
    document.getElementById('testimonial').value = data.testimonial || '';
    document.getElementById('rating').value = data.rating || 5;
    document.getElementById('service_type').value = data.service_type || '';
    document.getElementById('is_approved').checked = data.is_approved == 1;
    document.getElementById('is_featured').checked = data.is_featured == 1;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
