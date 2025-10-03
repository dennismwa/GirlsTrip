<?php
// admin/bookings.php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/login');
}

$bookings_query = "SELECT b.*, t.title as tour_title, u.full_name, u.email,
                   (SELECT SUM(amount) FROM payments WHERE booking_id = b.id AND status IN ('completed', 'verified')) as total_paid
                   FROM bookings b
                   JOIN tours t ON b.tour_id = t.id
                   JOIN users u ON b.user_id = u.id
                   ORDER BY b.created_at DESC";
$bookings = $conn->query($bookings_query);

$page_title = 'Manage Bookings - Admin';
$page_heading = 'Manage Bookings';

include 'includes/admin-header.php';
?>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Reference</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Customer</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tour</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Travel Date</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Guests</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Paid</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings && $bookings->num_rows > 0): ?>
                    <?php while($booking = $bookings->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm font-medium text-gray-900">
                                <?php echo $booking['booking_reference']; ?>
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($booking['full_name']); ?></p>
                                <p class="text-xs text-gray-600"><?php echo htmlspecialchars($booking['email']); ?></p>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($booking['tour_title']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?php echo date('M d, Y', strtotime($booking['travel_date'])); ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?php echo $booking['adults'] + $booking['children']; ?>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">
                                <?php echo formatPrice($booking['total_amount']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-green-600">
                                <?php echo formatPrice($booking['total_paid'] ?? 0); ?>
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
                        <td colspan="9" class="py-12 text-center text-gray-500">
                            No bookings yet
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
<!-- END admin/bookings.php -->
