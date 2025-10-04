<?php
// admin/settings.php - Site Settings Management
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/login');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            updateSetting($key, sanitize($value));
        }
    }
    setMessage('Settings updated successfully', 'success');
    redirect('/admin/settings');
}

$page_title = 'Settings - Admin';
$page_heading = 'Site Settings';

include 'includes/admin-header.php';
?>

<div class="bg-white rounded-lg shadow-md p-8">
    <form method="POST">
        <!-- General Settings -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b-2 border-pink-600">
                <i class="fas fa-cog text-pink-600 mr-2"></i>General Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                    <input type="text" name="site_name" value="<?php echo getSetting('site_name', 'Girls Trip'); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Email</label>
                    <input type="email" name="site_email" value="<?php echo getSetting('site_email', 'info@girlstrip.co.ke'); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Phone</label>
                    <input type="text" name="site_phone" value="<?php echo getSetting('site_phone', '+254 700 000 000'); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                    <input type="text" name="currency" value="<?php echo getSetting('currency', 'KES'); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                    <select name="timezone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="Africa/Nairobi" <?php echo getSetting('timezone') === 'Africa/Nairobi' ? 'selected' : ''; ?>>Africa/Nairobi (EAT)</option>
                        <option value="Africa/Kampala" <?php echo getSetting('timezone') === 'Africa/Kampala' ? 'selected' : ''; ?>>Africa/Kampala</option>
                        <option value="Africa/Dar_es_Salaam" <?php echo getSetting('timezone') === 'Africa/Dar_es_Salaam' ? 'selected' : ''; ?>>Africa/Dar es Salaam</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Language</label>
                    <select name="language" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="en" <?php echo getSetting('language') === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="sw" <?php echo getSetting('language') === 'sw' ? 'selected' : ''; ?>>Swahili</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Payment Settings -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b-2 border-pink-600">
                <i class="fas fa-credit-card text-pink-600 mr-2"></i>Payment Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Paybill/Till Number</label>
                    <input type="text" name="mpesa_shortcode" value="<?php echo getSetting('mpesa_shortcode'); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Your M-Pesa business number</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Consumer Key</label>
                    <input type="text" name="mpesa_consumer_key" value="<?php echo getSetting('mpesa_consumer_key'); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">From Safaricom Daraja</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Consumer Secret</label>
                    <input type="password" name="mpesa_consumer_secret" value="<?php echo getSetting('mpesa_consumer_secret'); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Keep this secure</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Passkey</label>
                    <input type="password" name="mpesa_passkey" value="<?php echo getSetting('mpesa_passkey'); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">From Safaricom Daraja</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deposit Percentage (%)</label>
                    <input type="number" name="deposit_percentage" value="<?php echo getSetting('deposit_percentage', '30'); ?>" 
                           min="0" max="100" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Default deposit amount for installment plans</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Environment</label>
                    <select name="mpesa_environment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="sandbox" <?php echo getSetting('mpesa_environment') === 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testing)</option>
                        <option value="production" <?php echo getSetting('mpesa_environment') === 'production' ? 'selected' : ''; ?>>Production (Live)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Booking Settings -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b-2 border-pink-600">
                <i class="fas fa-ticket-alt text-pink-600 mr-2"></i>Booking Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Allow Installment Payments</label>
                    <select name="allow_installments" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="1" <?php echo getSetting('allow_installments', '1') === '1' ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo getSetting('allow_installments') === '0' ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Booking Days in Advance</label>
                    <input type="number" name="min_booking_days" value="<?php echo getSetting('min_booking_days', '3'); ?>" 
                           min="0" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Days before travel date to allow bookings</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cancellation Allowed (Hours Before)</label>
                    <input type="number" name="cancellation_hours" value="<?php echo getSetting('cancellation_hours', '48'); ?>" 
                           min="0" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Free cancellation window</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Participants Per Tour</label>
                    <input type="number" name="max_participants" value="<?php echo getSetting('max_participants', '50'); ?>" 
                           min="1" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>
        
        <!-- Affiliate Settings -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b-2 border-pink-600">
                <i class="fas fa-user-friends text-pink-600 mr-2"></i>Affiliate Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Affiliate Commission Rate (%)</label>
                    <input type="number" name="affiliate_commission_rate" value="<?php echo getSetting('affiliate_commission_rate', '10'); ?>" 
                           min="0" max="100" step="0.1" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Payout Amount</label>
                    <input type="number" name="min_payout_amount" value="<?php echo getSetting('min_payout_amount', '1000'); ?>" 
                           min="0" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>
        
        <!-- Email Settings -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b-2 border-pink-600">
                <i class="fas fa-envelope text-pink-600 mr-2"></i>Email Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                    <input type="text" name="smtp_host" value="<?php echo getSetting('smtp_host'); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                    <input type="number" name="smtp_port" value="<?php echo getSetting('smtp_port', '587'); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                    <input type="text" name="smtp_username" value="<?php echo getSetting('smtp_username'); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                    <input type="password" name="smtp_password" value="<?php echo getSetting('smtp_password'); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>
        
        <!-- Social Media Settings -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b-2 border-pink-600">
                <i class="fas fa-share-alt text-pink-600 mr-2"></i>Social Media Links
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-facebook text-blue-600 mr-2"></i>Facebook URL
                    </label>
                    <input type="url" name="facebook_url" value="<?php echo getSetting('facebook_url'); ?>" 
                           placeholder="https://facebook.com/yourpage"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-instagram text-pink-600 mr-2"></i>Instagram URL
                    </label>
                    <input type="url" name="instagram_url" value="<?php echo getSetting('instagram_url'); ?>" 
                           placeholder="https://instagram.com/yourprofile"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter URL
                    </label>
                    <input type="url" name="twitter_url" value="<?php echo getSetting('twitter_url'); ?>" 
                           placeholder="https://twitter.com/yourhandle"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-whatsapp text-green-600 mr-2"></i>WhatsApp Number
                    </label>
                    <input type="text" name="whatsapp_number" value="<?php echo getSetting('whatsapp_number'); ?>" 
                           placeholder="254700000000"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="flex gap-4">
            <button type="submit" name="submit" 
                    class="bg-pink-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-pink-700 transition shadow-lg">
                <i class="fas fa-save mr-2"></i>Save All Settings
            </button>
            <button type="reset" 
                    class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                <i class="fas fa-undo mr-2"></i>Reset
            </button>
        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>
