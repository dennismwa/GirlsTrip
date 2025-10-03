<?php
// admin/index.php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/login');
}

// Get statistics
$total_tours = $conn->query("SELECT COUNT(*) as count FROM tours WHERE status = 'published'")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(paid_amount) as total FROM bookings WHERE status IN ('confirmed', 'completed')")->fetch_assoc()['total'] ?? 0;

// Recent bookings
$recent_bookings = $conn->query("SELECT b.*, t.title as tour_title, u.full_name, u.email
                                 FROM bookings b
                                 JOIN tours t ON b.tour_id = t.id
                                 JOIN users u ON b.user_id = u.id
                                 ORDER BY b.created_at DESC LIMIT 10");

// Pending verifications
$pending_payments = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'")->fetch_assoc()['count'];

$page_title = 'Admin Dashboard - Girls Trip';

include 'includes/admin-header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Tours</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_tours; ?></p>
            </div>
            <div class="bg-pink-100 p-3 rounded-full">
                <i class="fas fa-map-marked-alt text-2xl text-pink-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Bookings</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_bookings; ?></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-ticket-alt text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Users</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_users; ?></p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-users text-2xl text-green-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo formatPrice($total_revenue); ?></p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fas fa-dollar-sign text-2xl text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Pending Verifications Alert -->
<?php if ($pending_payments > 0): ?>
<div class="bg-orange-100 border border-orange-300 rounded-lg p-4 mb-8">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle text-orange-600 text-xl mr-3"></i>
        <div>
            <p class="font-semibold text-orange-800">
                <?php echo $pending_payments; ?> payment(s) pending verification
            </p>
            <a href="/admin/payments" class="text-sm text-orange-700 hover:underline">
                View pending payments →
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Bookings -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Bookings</h2>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Reference</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Customer</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tour</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                    <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm font-medium text-gray-900">
                                <?php echo $booking['booking_reference']; ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($booking['full_name']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($booking['tour_title']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">
                                <?php echo formatPrice($booking['total_amount']); ?>
                            </td>
                            <td class="py-3 px-4">
                                <?php
                                $status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'completed' => 'bg-blue-100 text-blue-800'
                                ];
                                $color = $status_colors[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                            </td>
                            <td class="py-3 px-4">
                                <a href="/admin/booking-details?id=<?php echo $booking['id']; ?>" 
                                   class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500">
                            No bookings yet
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
<!-- END admin/index.php -->

<?php
// admin/includes/admin-header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin - Girls Trip'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-width: 250px; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="sidebar bg-gray-900 text-white flex-shrink-0">
            <div class="p-6 border-b border-gray-800">
                <img src="/images/logo.png" alt="Girls Trip" class="h-8">
                <p class="text-xs text-gray-400 mt-2">Admin Panel</p>
            </div>
            
            <nav class="p-4">
                <a href="/admin" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-home mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/tours" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-map-marked-alt mr-3"></i>
                    <span>Tours</span>
                </a>
                <a href="/admin/bookings" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-ticket-alt mr-3"></i>
                    <span>Bookings</span>
                </a>
                <a href="/admin/payments" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-credit-card mr-3"></i>
                    <span>Payments</span>
                </a>
                <a href="/admin/users" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-users mr-3"></i>
                    <span>Users</span>
                </a>
                <a href="/admin/categories" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-th-large mr-3"></i>
                    <span>Categories</span>
                </a>
                <a href="/admin/locations" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-map-pin mr-3"></i>
                    <span>Locations</span>
                </a>
                <a href="/admin/activities" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-hiking mr-3"></i>
                    <span>Activities</span>
                </a>
                <a href="/admin/sliders" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-images mr-3"></i>
                    <span>Sliders</span>
                </a>
                <a href="/admin/promo-codes" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-tags mr-3"></i>
                    <span>Promo Codes</span>
                </a>
                <a href="/admin/blog" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-blog mr-3"></i>
                    <span>Blog</span>
                </a>
                <a href="/admin/settings" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-cog mr-3"></i>
                    <span>Settings</span>
                </a>
                
                <div class="border-t border-gray-800 my-4"></div>
                
                <a href="/" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-globe mr-3"></i>
                    <span>View Website</span>
                </a>
                <a href="/logout" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">
                        <?php echo $page_heading ?? 'Dashboard'; ?>
                    </h1>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">
                            Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong>
                        </span>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <?php 
                $msg = getMessage();
                if ($msg): 
                    $bg_color = $msg['type'] === 'success' ? 'bg-green-500' : 'bg-red-500';
                ?>
                <div class="<?php echo $bg_color; ?> text-white px-4 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($msg['message']); ?>
                </div>
                <?php endif; ?>
<!-- END admin/includes/admin-header.php -->

<?php
// admin/includes/admin-footer.php
?>
            </main>
        </div>
    </div>
</body>
</html>
<!-- END admin/includes/admin-footer.php -->

<?php
// admin/tours.php - Tour Management
require_once '../config.php';

if (!isLoggedIn() || (!isAdmin() && !isVendor())) {
    redirect('/login');
}

// Handle delete
if (isset($_GET['delete']) && isAdmin()) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tours WHERE id = $id");
    setMessage('Tour deleted successfully', 'success');
    redirect('/admin/tours');
}

// Fetch tours
$vendor_filter = isVendor() ? "WHERE vendor_id = " . getUserId() : "";
$tours = $conn->query("SELECT t.*, l.name as location_name, c.name as category_name, u.full_name as vendor_name
                       FROM tours t
                       LEFT JOIN locations l ON t.location_id = l.id
                       LEFT JOIN categories c ON t.category_id = c.id
                       LEFT JOIN users u ON t.vendor_id = u.id
                       $vendor_filter
                       ORDER BY t.created_at DESC");

$page_title = 'Manage Tours - Admin';
$page_heading = 'Manage Tours';

include 'includes/admin-header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">All Tours</h2>
    </div>
    <a href="/admin/tour-add" class="bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-pink-700 transition">
        <i class="fas fa-plus mr-2"></i>Add New Tour
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Image</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Title</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Category</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Location</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Price</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Views</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tours && $tours->num_rows > 0): ?>
                    <?php while($tour = $tours->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <?php if ($tour['featured_image']): ?>
                                    <img src="/<?php echo $tour['featured_image']; ?>" 
                                         class="w-16 h-16 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($tour['title']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo ucfirst($tour['type']); ?></p>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($tour['category_name']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($tour['location_name']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">
                                <?php 
                                if ($tour['price']) {
                                    echo formatPrice($tour['price']);
                                } elseif ($tour['price_min'] && $tour['price_max']) {
                                    echo formatPrice($tour['price_min']) . ' - ' . formatPrice($tour['price_max']);
                                }
                                ?>
                            </td>
                            <td class="py-3 px-4">
                                <?php
                                $status_colors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'published' => 'bg-green-100 text-green-800',
                                    'archived' => 'bg-red-100 text-red-800'
                                ];
                                $color = $status_colors[$tour['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                    <?php echo ucfirst($tour['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                <?php echo $tour['views']; ?>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex gap-2">
                                    <a href="/admin/tour-edit?id=<?php echo $tour['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/tours/<?php echo $tour['slug']; ?>" target="_blank"
                                       class="text-green-600 hover:text-green-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <a href="/admin/tours?delete=<?php echo $tour['id']; ?>" 
                                           onclick="return confirm('Are you sure?')"
                                           class="text-red-600 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-500">
                            No tours found. <a href="/admin/tour-add" class="text-pink-600 hover:underline">Add your first tour</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
<!-- END admin/tours.php -->

<?php
// admin/settings.php - Settings Management
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/login');
}

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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- General Settings -->
            <div class="md:col-span-2">
                <h3 class="text-xl font-bold text-gray-900 mb-4">General Settings</h3>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                <input type="text" name="site_name" value="<?php echo getSetting('site_name'); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site Email</label>
                <input type="email" name="site_email" value="<?php echo getSetting('site_email'); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site Phone</label>
                <input type="text" name="site_phone" value="<?php echo getSetting('site_phone'); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                <input type="text" name="currency" value="<?php echo getSetting('currency'); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <!-- Payment Settings -->
            <div class="md:col-span-2 mt-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Payment Settings</h3>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Shortcode</label>
                <input type="text" name="mpesa_shortcode" value="<?php echo getSetting('mpesa_shortcode'); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Consumer Key</label>
                <input type="text" name="mpesa_consumer_key" value="<?php echo getSetting('mpesa_consumer_key'); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Consumer Secret</label>
                <input type="password" name="mpesa_consumer_secret" value="<?php echo getSetting('mpesa_consumer_secret'); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Passkey</label>
                <input type="password" name="mpesa_passkey" value="<?php echo getSetting('mpesa_passkey'); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deposit Percentage (%)</label>
                <input type="number" name="deposit_percentage" value="<?php echo getSetting('deposit_percentage'); ?>" 
                       min="0" max="100"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Affiliate Commission Rate (%)</label>
                <input type="number" name="affiliate_commission_rate" value="<?php echo getSetting('affiliate_commission_rate'); ?>" 
                       min="0" max="100" step="0.1"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            </div>
        </div>
        
        <div class="mt-8">
            <button type="submit" name="submit" 
                    class="bg-pink-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-pink-700 transition">
                <i class="fas fa-save mr-2"></i>Save Settings
            </button>
        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>