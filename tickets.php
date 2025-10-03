<?php
// tickets.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('/login');
}

$user_id = getUserId();

$tickets_query = "SELECT tk.*, b.booking_reference, b.travel_date, b.adults, b.children,
                  t.title as tour_title, t.slug as tour_slug, t.featured_image, t.start_date,
                  l.name as location_name
                  FROM tickets tk
                  JOIN bookings b ON tk.booking_id = b.id
                  JOIN tours t ON b.tour_id = t.id
                  LEFT JOIN locations l ON t.location_id = l.id
                  WHERE b.user_id = $user_id
                  ORDER BY tk.created_at DESC";
$tickets = $conn->query($tickets_query);

$page_title = 'My Tickets - Girls Trip';

include 'includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">My Tickets</h1>
        <p class="text-pink-100">View and manage your travel tickets</p>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($tickets && $tickets->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($ticket = $tickets->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-pink-600 text-white p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-xs opacity-90">Ticket Number</p>
                                    <p class="font-bold"><?php echo $ticket['ticket_number']; ?></p>
                                </div>
                                <?php
                                $status_class = [
                                    'active' => 'bg-green-500',
                                    'used' => 'bg-gray-500',
                                    'cancelled' => 'bg-red-500',
                                    'expired' => 'bg-orange-500'
                                ];
                                $class = $status_class[$ticket['status']] ?? 'bg-gray-500';
                                ?>
                                <span class="<?php echo $class; ?> text-white text-xs px-2 py-1 rounded-full">
                                    <?php echo ucfirst($ticket['status']); ?>
                                </span>
                            </div>
                            <p class="text-sm opacity-90"><?php echo ucfirst($ticket['passenger_type']); ?></p>
                        </div>
                        
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 mb-3">
                                <?php echo htmlspecialchars($ticket['tour_title']); ?>
                            </h3>
                            
                            <div class="space-y-2 text-sm text-gray-700 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt text-pink-600 w-5"></i>
                                    <span><?php echo htmlspecialchars($ticket['location_name']); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-pink-600 w-5"></i>
                                    <span><?php echo date('F d, Y', strtotime($ticket['travel_date'])); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-user text-pink-600 w-5"></i>
                                    <span><?php echo htmlspecialchars($ticket['passenger_name']); ?></span>
                                </div>
                            </div>
                            
                            <a href="/ticket-view?ticket=<?php echo $ticket['ticket_number']; ?>" 
                               class="w-full bg-pink-600 text-white text-center px-4 py-2 rounded-lg font-medium hover:bg-pink-700 transition block">
                                <i class="fas fa-qrcode mr-2"></i>View Ticket
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-lg shadow-md">
                <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Tickets Yet</h3>
                <p class="text-gray-600 mb-6">Book a tour to get your tickets</p>
                <a href="/tours" class="btn-primary inline-block">
                    Browse Tours
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<!-- END tickets.php -->
