        </main>
        
        <!-- Footer -->
        <footer class="mt-auto border-t border-slate-200 bg-white px-6 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <p>&copy; <?= date('Y') ?> Zurihub Technology. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="/admin/settings.php" class="hover:text-slate-700 transition">Settings</a>
                    <a href="/" target="_blank" class="hover:text-slate-700 transition">View Site</a>
                    <span class="text-slate-300">|</span>
                    <span>v2.0</span>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Toast notifications -->
    <?php if ($flash = getFlash()): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 max-w-sm px-4 py-3 rounded-xl shadow-lg border backdrop-blur-sm
                <?= $flash['type'] === 'success' ? 'bg-emerald-50/90 border-emerald-200 text-emerald-800' : 
                   ($flash['type'] === 'error' ? 'bg-red-50/90 border-red-200 text-red-800' : 'bg-blue-50/90 border-blue-200 text-blue-800') ?>">
        <div class="flex items-start gap-3">
            <?php if ($flash['type'] === 'success'): ?>
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <?php elseif ($flash['type'] === 'error'): ?>
            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <?php else: ?>
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <?php endif; ?>
            <div class="flex-1">
                <p class="font-medium text-sm"><?= $flash['type'] === 'success' ? 'Success!' : ($flash['type'] === 'error' ? 'Error' : 'Info') ?></p>
                <p class="text-sm opacity-90"><?= htmlspecialchars($flash['message']) ?></p>
            </div>
            <button @click="show = false" class="text-current opacity-50 hover:opacity-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Utility scripts -->
    <script>
        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }
        
        function formatCurrency(amount, currency = 'KES') {
            const symbols = { KES: 'KSh', USD: '$', GBP: '£', EUR: '€' };
            return (symbols[currency] || currency) + ' ' + formatNumber(amount);
        }
        
        function confirmDelete(message = 'Are you sure you want to delete this item? This action cannot be undone.') {
            return confirm(message);
        }
        
        function showLoading(element) {
            const originalContent = element.innerHTML;
            element.disabled = true;
            element.innerHTML = '<svg class="animate-spin w-4 h-4 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            return () => {
                element.disabled = false;
                element.innerHTML = originalContent;
            };
        }
        
        async function fetchAPI(url, options = {}) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                return await response.json();
            } catch (error) {
                console.error('API Error:', error);
                return { success: false, error: 'Network error' };
            }
        }
        
        // Table row checkbox selection
        document.querySelectorAll('.select-all').forEach(el => {
            el.addEventListener('change', function() {
                const table = this.closest('table');
                table.querySelectorAll('.select-row').forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        });
    </script>
</body>
</html>
