<?php
// admin/payments.php - Payment Verification System
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/login');
}

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    $status = sanitize($_POST['status']);
    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
    
    $update_query = "UPDATE payments SET status = '$status', verified_by = " . getUserId() . ", 
                     verified_at = NOW(), notes = '$notes' WHERE id = $payment_id";
    
    if ($conn->query($update_query)) {
        if ($status === 'verified' || $status === 'completed') {
            // Get payment details
            $payment = $conn->query("SELECT * FROM payments WHERE id = $payment_id")->fetch_assoc();
            $booking_id = $payment['booking_id'];
            
            // Update booking paid amount and balance
            $conn->query("UPDATE bookings SET 
                         paid_amount = paid_amount + " . $payment['amount'] . ",
                         balance = total_amount - (paid_amount + " . $payment['amount'] . "),
                         status = 'confirmed' 
                         WHERE id = $booking_id");
            
            // Get booking and user details for notification
            $booking_query = "SELECT b.*, u.email, u.full_name, t.title as tour_title
                             FROM bookings b
                             JOIN users u ON b.user_id = u.id
                             JOIN tours t ON b.tour_id = t.id
                             WHERE b.id = $booking_id";
            $booking = $conn->query($booking_query)->fetch_assoc();
            
            // Send confirmation email
            $email_subject = "Payment Verified - " . $booking['booking_reference'];
            $email_message = "
                <h2>Payment Verified</h2>
                <p>Dear " . $booking['full_name'] . ",</p>
                <p>Your payment of " . formatPrice($payment['amount']) . " has been verified and confirmed.</p>
                <p><strong>Booking Reference:</strong> " . $booking['booking_reference'] . "</p>
                <p><strong>Tour:</strong> " . $booking['tour_title'] . "</p>
                <p><strong>Remaining Balance:</strong> " . formatPrice($booking['balance']) . "</p>
                <p>Thank you for choosing Girls Trip!</p>
            ";
            sendEmail($booking['email'], $email_subject, $email_message);
            
            setMessage('Payment verified successfully and customer notified', 'success');
        } elseif ($status === 'failed') {
            setMessage('Payment marked as failed', 'success');
        }
    } else {
        setMessage('Failed to update payment status', 'error');
    }
    
    redirect('/admin/payments');
}

// Fetch payments with filters
$filter = isset($_GET['filter']) ? sanitize($_GET['filter']) : 'pending';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$where_conditions = [];
if ($filter !== 'all') {
    $where_conditions[] = "p.status = '$filter'";
}
if ($search) {
    $where_conditions[] = "(b.booking_reference LIKE '%$search%' OR u.full_name LIKE '%$search%' OR p.mpesa_code LIKE '%$search%')";
}

$where = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$payments_query = "SELECT p.*, b.booking_reference, b.total_amount, b.paid_amount, b.balance,
                   t.title as tour_title, t.slug as tour_slug,
                   u.full_name, u.email, u.phone,
                   verifier.full_name as verified_by_name
                   FROM payments p
                   JOIN bookings b ON p.booking_id = b.id
                   JOIN tours t ON b.tour_id = t.id
                   JOIN users u ON b.user_id = u.id
                   LEFT JOIN users verifier ON p.verified_by = verifier.id
                   $where
                   ORDER BY p.created_at DESC";
$payments = $conn->query($payments_query);

// Get payment statistics
$stats_query = "SELECT 
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'verified' THEN 1 END) as verified_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
                SUM(CASE WHEN status IN ('verified', 'completed') THEN amount ELSE 0 END) as total_verified
                FROM payments";
$stats = $conn->query($stats_query)->fetch_assoc();

$page_title = 'Manage Payments - Admin';
$page_heading = 'Payment Verification';

include 'includes/admin-header.php';
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-md p-4">
        <p class="text-sm text-gray-600 mb-1">Pending</p>
        <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending_count']; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <p class="text-sm text-gray-600 mb-1">Verified</p>
        <p class="text-2xl font-bold text-blue-600"><?php echo $stats['verified_count']; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <p class="text-sm text-gray-600 mb-1">Completed</p>
        <p class="text-2xl font-bold text-green-600"><?php echo $stats['completed_count']; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <p class="text-sm text-gray-600 mb-1">Failed</p>
        <p class="text-2xl font-bold text-red-600"><?php echo $stats['failed_count']; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <p class="text-sm text-gray-600 mb-1">Total Verified</p>
        <p class="text-xl font-bold text-gray-900"><?php echo formatPrice($stats['total_verified']); ?></p>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Filter Buttons -->
        <div class="flex gap-2 flex-wrap">
            <a href="/admin/payments?filter=pending" 
               class="px-4 py-2 rounded-lg transition <?php echo $filter === 'pending' ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <i class="fas fa-clock mr-1"></i>Pending (<?php echo $stats['pending_count']; ?>)
            </a>
            <a href="/admin/payments?filter=verified" 
               class="px-4 py-2 rounded-lg transition <?php echo $filter === 'verified' ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <i class="fas fa-check mr-1"></i>Verified
            </a>
            <a href="/admin/payments?filter=completed" 
               class="px-4 py-2 rounded-lg transition <?php echo $filter === 'completed' ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <i class="fas fa-check-double mr-1"></i>Completed
            </a>
            <a href="/admin/payments?filter=failed" 
               class="px-4 py-2 rounded-lg transition <?php echo $filter === 'failed' ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <i class="fas fa-times mr-1"></i>Failed
            </a>
            <a href="/admin/payments?filter=all" 
               class="px-4 py-2 rounded-lg transition <?php echo $filter === 'all' ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <i class="fas fa-list mr-1"></i>All
            </a>
        </div>
        
        <!-- Search -->
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search by reference, name, M-Pesa code..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Booking Ref</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Customer</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tour</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Method</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">M-Pesa Code</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Type</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments && $payments->num_rows > 0): ?>
                    <?php while($payment = $payments->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?php echo date('M d, Y', strtotime($payment['created_at'])); ?>
                                <br>
                                <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($payment['created_at'])); ?></span>
                            </td>
                            <td class="py-3 px-4 text-sm font-medium text-gray-900">
                                <a href="/admin/booking-details?id=<?php echo $payment['booking_id']; ?>" 
                                   class="text-pink-600 hover:underline">
                                    <?php echo $payment['booking_reference']; ?>
                                </a>
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($payment['full_name']); ?></p>
                                <p class="text-xs text-gray-600"><?php echo htmlspecialchars($payment['phone']); ?></p>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <div class="max-w-xs truncate" title="<?php echo htmlspecialchars($payment['tour_title']); ?>">
                                    <?php echo htmlspecialchars($payment['tour_title']); ?>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <?php
                                $method_config = [
                                    'mpesa' => ['icon' => 'fa-mobile-alt', 'color' => 'text-green-600', 'label' => 'M-Pesa'],
                                    'card' => ['icon' => 'fa-credit-card', 'color' => 'text-blue-600', 'label' => 'Card'],
                                    'cash' => ['icon' => 'fa-money-bill-wave', 'color' => 'text-yellow-600', 'label' => 'Cash']
                                ];
                                $method = $method_config[$payment['payment_method']] ?? ['icon' => 'fa-question', 'color' => 'text-gray-600', 'label' => 'Other'];
                                ?>
                                <div class="flex items-center">
                                    <i class="fas <?php echo $method['icon']; ?> <?php echo $method['color']; ?> mr-2"></i>
                                    <span class="text-sm"><?php echo $method['label']; ?></span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">
                                <?php echo formatPrice($payment['amount']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <?php if ($payment['mpesa_code']): ?>
                                    <span class="font-mono text-gray-900"><?php echo $payment['mpesa_code']; ?></span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                    <?php echo ucfirst($payment['payment_type']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <?php
                                $status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'verified' => 'bg-blue-100 text-blue-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800'
                                ];
                                $color = $status_colors[$payment['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                                <?php if ($payment['verified_by_name']): ?>
                                    <br>
                                    <span class="text-xs text-gray-500">by <?php echo htmlspecialchars($payment['verified_by_name']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($payment['status'] === 'pending'): ?>
                                    <div class="flex gap-2">
                                        <button onclick="openVerifyModal(<?php echo $payment['id']; ?>, '<?php echo addslashes($payment['booking_reference']); ?>', <?php echo $payment['amount']; ?>, 'verified')" 
                                                class="text-green-600 hover:text-green-700 text-sm font-medium" title="Verify Payment">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button onclick="openVerifyModal(<?php echo $payment['id']; ?>, '<?php echo addslashes($payment['booking_reference']); ?>', <?php echo $payment['amount']; ?>, 'failed')" 
                                                class="text-red-600 hover:text-red-700 text-sm font-medium" title="Reject Payment">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <button onclick="viewPaymentDetails(<?php echo htmlspecialchars(json_encode($payment)); ?>)" 
                                            class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                            <p>No payments found</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Verification Modal -->
<div id="verify-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4" id="modal-title">Verify Payment</h3>
        
        <form id="verify-form" method="POST">
            <input type="hidden" name="verify_payment" value="1">
            <input type="hidden" name="payment_id" id="verify-payment-id">
            <input type="hidden" name="status" id="verify-status">
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Booking Reference:</p>
                <p class="font-semibold text-gray-900" id="modal-booking-ref"></p>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Amount:</p>
                <p class="font-semibold text-gray-900" id="modal-amount"></p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                <textarea name="notes" rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                          placeholder="Add any notes about this payment..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" 
                        class="flex-1 bg-pink-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-pink-700 transition">
                    <i class="fas fa-check mr-2"></i>Confirm
                </button>
                <button type="button" onclick="closeVerifyModal()" 
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openVerifyModal(paymentId, bookingRef, amount, status) {
    document.getElementById('verify-payment-id').value = paymentId;
    document.getElementById('verify-status').value = status;
    document.getElementById('modal-booking-ref').textContent = bookingRef;
    document.getElementById('modal-amount').textContent = 'KES ' + amount.toFixed(2);
    
    const title = status === 'verified' ? 'Verify Payment' : 'Reject Payment';
    document.getElementById('modal-title').textContent = title;
    
    document.getElementById('verify-modal').classList.remove('hidden');
    document.getElementById('verify-modal').classList.add('flex');
}

function closeVerifyModal() {
    document.getElementById('verify-modal').classList.add('hidden');
    document.getElementById('verify-modal').classList.remove('flex');
    document.getElementById('verify-form').reset();
}

function viewPaymentDetails(payment) {
    alert('Payment Details:\n\n' +
          'Booking: ' + payment.booking_reference + '\n' +
          'Amount: KES ' + payment.amount + '\n' +
          'Status: ' + payment.status + '\n' +
          'Method: ' + payment.payment_method + '\n' +
          (payment.mpesa_code ? 'M-Pesa Code: ' + payment.mpesa_code + '\n' : '') +
          (payment.notes ? 'Notes: ' + payment.notes : '')
    );
}

// Close modal on background click
document.getElementById('verify-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVerifyModal();
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>
