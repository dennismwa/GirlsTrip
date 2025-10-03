<?php
// account.php - User Dashboard
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('/login');
}

$user_id = getUserId();

// Get user info
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Get user bookings
$bookings_query = "SELECT b.*, t.title as tour_title, t.slug as tour_slug, t.featured_image
                   FROM bookings b
                   JOIN tours t ON b.tour_id = t.id
                   WHERE b.user_id = $user_id
                   ORDER BY b.created_at DESC LIMIT 5";
$bookings = $conn->query($bookings_query);

// Get user tickets
$tickets_query = "SELECT tk.*, b.booking_reference, t.title as tour_title, b.travel_date
                  FROM tickets tk
                  JOIN bookings b ON tk.booking_id = b.id
                  JOIN tours t ON b.tour_id = t.id
                  WHERE b.user_id = $user_id
                  ORDER BY tk.created_at DESC LIMIT 5";
$tickets = $conn->query($tickets_query);

$page_title = 'My Account - Girls Trip';

include 'includes/header.php';
?>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
            <p class="text-gray-600">Manage your bookings and account settings</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-user text-3xl text-pink-600"></i>
                        </div>
                        <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <nav class="space-y-2">
                        <a href="/account" class="flex items-center px-4 py-3 bg-pink-50 text-pink-600 rounded-lg font-medium">
                            <i class="fas fa-home mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="/account/bookings" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg">
                            <i class="fas fa-ticket-alt mr-3"></i>
                            <span>My Bookings</span>
                        </a>
                        <a href="/account/tickets" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg">
                            <i class="fas fa-qrcode mr-3"></i>
                            <span>My Tickets</span>
                        </a>
                        <a href="/account/profile" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg">
                            <i class="fas fa-user-edit mr-3"></i>
                            <span>Edit Profile</span>
                        </a>
                        <?php if ($user['user_type'] === 'vendor'): ?>
                            <a href="/admin" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg">
                                <i class="fas fa-cog mr-3"></i>
                                <span>Vendor Dashboard</span>
                            </a>
                        <?php endif; ?>
                        <a href="/logout" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            <span>Logout</span>
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Recent Bookings -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Recent Bookings</h2>
                        <a href="/account/bookings" class="text-pink-600 text-sm font-medium hover:underline">
                            View All
                        </a>
                    </div>
                    
                    <?php if ($bookings && $bookings->num_rows > 0): ?>
                        <div class="space-y-4">
                            <?php while($booking = $bookings->fetch_assoc()): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-start gap-4">
                                        <?php if ($booking['featured_image']): ?>
                                            <img src="/<?php echo htmlspecialchars($booking['featured_image']); ?>" 
                                                 class="w-20 h-20 object-cover rounded-lg">
                                        <?php endif; ?>
                                        
                                        <div class="flex-1">
                                            <h3 class="font-bold text-gray-900 mb-1">
                                                <?php echo htmlspecialchars($booking['tour_title']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-600 mb-2">
                                                Ref: <?php echo $booking['booking_reference']; ?>
                                            </p>
                                            <div class="flex items-center gap-4 text-sm">
                                                <span class="text-gray-600">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    <?php echo date('M d, Y', strtotime($booking['travel_date'])); ?>
                                                </span>
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
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <p class="font-bold text-pink-600 mb-2">
                                                <?php echo formatPrice($booking['total_amount']); ?>
                                            </p>
                                            <a href="/tours/<?php echo $booking['tour_slug']; ?>" 
                                               class="text-sm text-gray-600 hover:text-pink-600">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-600 mb-4">You haven't made any bookings yet</p>
                            <a href="/tours" class="btn-primary inline-block">
                                Browse Tours
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Tickets -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900">My Tickets</h2>
                        <a href="/account/tickets" class="text-pink-600 text-sm font-medium hover:underline">
                            View All
                        </a>
                    </div>
                    
                    <?php if ($tickets && $tickets->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php while($ticket = $tickets->fetch_assoc()): ?>
                                <div class="border-2 border-pink-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="bg-pink-100 text-pink-700 px-2 py-1 rounded text-xs font-semibold">
                                            <?php echo ucfirst($ticket['passenger_type']); ?>
                                        </span>
                                        <span class="text-xs text-gray-600">
                                            <?php echo $ticket['ticket_number']; ?>
                                        </span>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-2">
                                        <?php echo htmlspecialchars($ticket['tour_title']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-600 mb-3">
                                        <?php echo date('F d, Y', strtotime($ticket['travel_date'])); ?>
                                    </p>
                                    <a href="/ticket/<?php echo $ticket['ticket_number']; ?>" 
                                       class="text-pink-600 text-sm font-medium hover:underline">
                                        <i class="fas fa-qrcode mr-1"></i>View Ticket
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-qrcode text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-600">No tickets yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>