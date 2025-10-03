<?php
// ticket-view.php
require_once 'config.php';

$ticket_number = isset($_GET['ticket']) ? sanitize($_GET['ticket']) : '';

if (empty($ticket_number)) {
    redirect('/tickets');
}

$ticket_query = "SELECT tk.*, b.booking_reference, b.travel_date, b.adults, b.children,
                 t.title as tour_title, t.slug as tour_slug, t.featured_image, t.start_date, t.duration,
                 l.name as location_name, l.county,
                 u.full_name, u.email, u.phone
                 FROM tickets tk
                 JOIN bookings b ON tk.booking_id = b.id
                 JOIN tours t ON b.tour_id = t.id
                 LEFT JOIN locations l ON t.location_id = l.id
                 JOIN users u ON b.user_id = u.id
                 WHERE tk.ticket_number = '$ticket_number'";
$result = $conn->query($ticket_query);

if (!$result || $result->num_rows === 0) {
    redirect('/tickets');
}

$ticket = $result->fetch_assoc();

// Check if user owns this ticket
if (isLoggedIn() && getUserId() != $ticket['user_id'] && !isAdmin()) {
    redirect('/tickets');
}

$page_title = 'Ticket - ' . $ticket_number;

include 'includes/header.php';
?>

<section class="py-12 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Ticket Header -->
            <div class="bg-pink-600 text-white p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">Girls Trip Ticket</h1>
                        <p class="text-pink-100">Booking Reference: <?php echo $ticket['booking_reference']; ?></p>
                    </div>
                    <div class="text-right">
                        <?php
                        $status_class = [
                            'active' => 'bg-green-500',
                            'used' => 'bg-gray-500',
                            'cancelled' => 'bg-red-500',
                            'expired' => 'bg-orange-500'
                        ];
                        $class = $status_class[$ticket['status']] ?? 'bg-gray-500';
                        ?>
                        <span class="<?php echo $class; ?> text-white px-4 py-2 rounded-full font-semibold">
                            <?php echo ucfirst($ticket['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- QR Code Section -->
            <div class="text-center py-8 border-b border-gray-200">
                <div class="w-48 h-48 bg-white border-4 border-gray-300 mx-auto mb-4 flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-qrcode text-6xl text-gray-400 mb-2"></i>
                        <p class="text-xs text-gray-600">QR Code</p>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 mb-1"><?php echo $ticket['ticket_number']; ?></p>
                <p class="text-sm text-gray-600">Verification Code: <?php echo $ticket['verification_code']; ?></p>
            </div>
            
            <!-- Ticket Details -->
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Tour Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tour Name</p>
                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['tour_title']); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Location</p>
                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['location_name']); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Travel Date</p>
                        <p class="font-semibold text-gray-900"><?php echo date('F d, Y', strtotime($ticket['travel_date'])); ?></p>
                    </div>
                    
                    <?php if ($ticket['duration']): ?>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Duration</p>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['duration']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Passenger Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Name</p>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['passenger_name']); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Type</p>
                            <p class="font-semibold text-gray-900"><?php echo ucfirst($ticket['passenger_type']); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Email</p>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['email']); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Phone</p>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['phone']); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Important Information -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                    <h4 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>Important Information
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Please arrive 30 minutes before departure</li>
                        <li>• Present this ticket and a valid ID</li>
                        <li>• Keep your verification code secure</li>
                        <li>• Contact us if you need to make changes</li>
                    </ul>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
                <button onclick="window.print()" class="text-pink-600 font-medium hover:underline">
                    <i class="fas fa-print mr-2"></i>Print Ticket
                </button>
                <a href="/contact" class="text-gray-600 font-medium hover:underline">
                    <i class="fas fa-phone mr-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
</section>

<style>
@media print {
    header, footer, .bottom-nav, .no-print {
        display: none !important;
    }
    
    body {
        padding-bottom: 0 !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
