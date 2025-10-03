<?php
// account/bookings.php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('/login');
}

$user_id = getUserId();

$bookings_query = "SELECT b.*, t.title as tour_title, t.slug as tour_slug, t.featured_image,
                   l.name as location_name,
                   (SELECT SUM(amount) FROM payments WHERE booking_id = b.id AND status IN ('completed', 'verified')) as total_paid
                   FROM bookings b
                   JOIN tours t ON b.tour_id = t.id
                   LEFT JOIN locations l ON t.location_id = l.id
                   WHERE b.user_id = $user_id
                   ORDER BY b.created_at DESC";
$bookings = $conn->query($bookings_query);

$page_title = 'My Bookings - Girls Trip';

include '../includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">My Bookings</h1>
        <p class="text-pink-100">View and manage all your bookings</p>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($bookings && $bookings->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while($booking = $bookings->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="md:flex">
                            <div class="md:w-48">
                                <?php if ($booking['featured_image']): ?>
                                    <img src="/<?php echo htmlspecialchars($booking['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($booking['tour_title']); ?>" 
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-image text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-1 p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                                            <?php echo htmlspecialchars($booking['tour_title']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Reference: <span class="font-semibold"><?php echo $booking['booking_reference']; ?></span>
                                        </p>
                                    </div>
                                    <?php
                                    $status_colors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'completed' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $color = $status_colors[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full <?php echo $color; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-gray-600">Travel Date</p>
                                        <p class="font-semibold text-gray-900">
                                            <?php echo date('M d, Y', strtotime($booking['travel_date'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600">Guests</p>
                                        <p class="font-semibold text-gray-900">
                                            <?php echo $booking['adults']; ?> Adults
                                            <?php if ($booking['children'] > 0): ?>
                                                , <?php echo $booking['children']; ?> Children
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600">Total Amount</p>
                                        <p class="font-semibold text-pink-600">
                                            <?php echo formatPrice($booking['total_amount']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600">Amount Paid</p>
                                        <p class="font-semibold text-green-600">
                                            <?php echo formatPrice($booking['total_paid'] ?? 0); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if ($booking['balance'] > 0): ?>
                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-4">
                                        <p class="text-sm text-orange-800">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            Balance Due: <span class="font-semibold"><?php echo formatPrice($booking['balance']); ?></span>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex gap-3">
                                    <a href="/tours/<?php echo $booking['tour_slug']; ?>" 
                                       class="text-pink-600 font-medium hover:underline text-sm">
                                        <i class="fas fa-eye mr-1"></i>View Tour
                                    </a>
                                    <?php if ($booking['balance'] > 0 && $booking['status'] === 'confirmed'): ?>
                                        <a href="/pay-balance?booking=<?php echo $booking['id']; ?>" 
                                           class="text-blue-600 font-medium hover:underline text-sm">
                                            <i class="fas fa-credit-card mr-1"></i>Pay Balance
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-lg shadow-md">
                <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Bookings Yet</h3>
                <p class="text-gray-600 mb-6">Start your adventure by booking a tour</p>
                <a href="/tours" class="btn-primary inline-block">
                    Browse Tours
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
<!-- END account/bookings.php -->
