<?php
/**
 * ZURIHUB TECHNOLOGY - Admin Settings (Modern Design)
 */

$pageTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    
    if ($section === 'general') {
        updateSetting('site_name', sanitize($_POST['site_name']));
        updateSetting('site_tagline', sanitize($_POST['site_tagline']));
        setFlash('success', 'General settings updated');
    }
    
    if ($section === 'contact') {
        updateSetting('contact_email', sanitize($_POST['contact_email']));
        updateSetting('contact_phone', sanitize($_POST['contact_phone']));
        updateSetting('contact_address', sanitize($_POST['contact_address']));
        setFlash('success', 'Contact settings updated');
    }
    
    if ($section === 'notifications') {
        updateSetting('notification_email', sanitize($_POST['notification_email']));
        updateSetting('cc_email', sanitize($_POST['cc_email']));
        setFlash('success', 'Notification settings updated');
    }
    
    if ($section === 'social') {
        updateSetting('social_facebook', sanitize($_POST['social_facebook']));
        updateSetting('social_twitter', sanitize($_POST['social_twitter']));
        updateSetting('social_linkedin', sanitize($_POST['social_linkedin']));
        updateSetting('social_instagram', sanitize($_POST['social_instagram']));
        setFlash('success', 'Social media settings updated');
    }
    
    logActivity('update', 'site_settings', null, "Updated $section settings");
    redirect('/admin/settings.php#' . $section);
}

// Get current settings
$settings = [];
$rows = fetchAll("SELECT setting_key, setting_value FROM site_settings");
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Settings</h1>
        <p class="text-slate-500 text-sm mt-0.5">Manage site configuration and preferences</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6" x-data="{ activeTab: 'general' }">
    
    <!-- Navigation -->
    <div class="lg:col-span-1">
        <div class="card p-2 sticky top-24">
            <nav class="space-y-0.5">
                <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition border">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    General
                </button>
                <button @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition border">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Contact Info
                </button>
                <button @click="activeTab = 'notifications'" :class="activeTab === 'notifications' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition border">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifications
                </button>
                <button @click="activeTab = 'social'" :class="activeTab === 'social' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition border">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                    Social Media
                </button>
            </nav>
        </div>
    </div>
    
    <!-- Settings Forms -->
    <div class="lg:col-span-3">
        
        <!-- General Settings -->
        <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="font-semibold text-slate-900">General Settings</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Basic site configuration</p>
                    </div>
                </div>
                <form method="POST" class="card-body">
                    <input type="hidden" name="section" value="general">
                    <div class="space-y-5">
                        <div>
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" class="form-input" placeholder="Zurihub Technology">
                        </div>
                        <div>
                            <label class="form-label">Tagline / Slogan</label>
                            <input type="text" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>" class="form-input" placeholder="Transforming Ideas Into Digital Solutions">
                            <p class="text-xs text-slate-400 mt-1.5">Appears in browser tabs and search results</p>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Contact Settings -->
        <div x-show="activeTab === 'contact'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="font-semibold text-slate-900">Contact Information</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Business contact details displayed on the website</p>
                    </div>
                </div>
                <form method="POST" class="card-body">
                    <input type="hidden" name="section" value="contact">
                    <div class="space-y-5">
                        <div>
                            <label class="form-label">Email Address</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="form-input pl-11" placeholder="info@zurihub.co.ke">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Phone Number</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <input type="text" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>" class="form-input pl-11" placeholder="+254 700 000 000">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Physical Address</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-3 w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <textarea name="contact_address" rows="2" class="form-input pl-11" placeholder="123 Business Park, Nairobi"><?= htmlspecialchars($settings['contact_address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Notification Settings -->
        <div x-show="activeTab === 'notifications'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="font-semibold text-slate-900">Email Notifications</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Configure where form submissions are sent</p>
                    </div>
                </div>
                <form method="POST" class="card-body">
                    <input type="hidden" name="section" value="notifications">
                    <div class="space-y-5">
                        <div>
                            <label class="form-label">Primary Notification Email</label>
                            <input type="email" name="notification_email" value="<?= htmlspecialchars($settings['notification_email'] ?? '') ?>" class="form-input" placeholder="notifications@zurihub.co.ke">
                            <p class="text-xs text-slate-400 mt-1.5">All form submissions will be sent to this email</p>
                        </div>
                        <div>
                            <label class="form-label">CC Email (Optional)</label>
                            <input type="email" name="cc_email" value="<?= htmlspecialchars($settings['cc_email'] ?? '') ?>" class="form-input" placeholder="backup@zurihub.co.ke">
                            <p class="text-xs text-slate-400 mt-1.5">Copy of notifications will also be sent here</p>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Social Media Settings -->
        <div x-show="activeTab === 'social'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="font-semibold text-slate-900">Social Media Links</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Your social media profile URLs</p>
                    </div>
                </div>
                <form method="POST" class="card-body">
                    <input type="hidden" name="section" value="social">
                    <div class="space-y-5">
                        <div>
                            <label class="form-label flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </label>
                            <input type="url" name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>" class="form-input" placeholder="https://facebook.com/zurihub">
                        </div>
                        <div>
                            <label class="form-label flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#1DA1F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                Twitter / X
                            </label>
                            <input type="url" name="social_twitter" value="<?= htmlspecialchars($settings['social_twitter'] ?? '') ?>" class="form-input" placeholder="https://twitter.com/zurihub">
                        </div>
                        <div>
                            <label class="form-label flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#0A66C2]" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                LinkedIn
                            </label>
                            <input type="url" name="social_linkedin" value="<?= htmlspecialchars($settings['social_linkedin'] ?? '') ?>" class="form-input" placeholder="https://linkedin.com/company/zurihub">
                        </div>
                        <div>
                            <label class="form-label flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#E4405F]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                                Instagram
                            </label>
                            <input type="url" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'] ?? '') ?>" class="form-input" placeholder="https://instagram.com/zurihub">
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
