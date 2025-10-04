<?php
// pay-balance.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('/login');
}

$booking_id = isset($_GET['booking']) ? intval($_GET['booking']) : 0;

if ($booking_id === 0) {
    redirect('/account/bookings');
}

// Fetch booking details
$query = "SELECT b.*, t.title as tour_title, t.slug as tour_slug, t.featured_image,
          l.name as location_name, u.full_name, u.email, u.phone
          FROM bookings b
          JOIN tours t ON b.tour_id = t.id
          LEFT JOIN locations l ON t.location_id = l.id
          JOIN users u ON b.user_id = u.id
          WHERE b.id = $booking_id AND b.user_id = " . getUserId();
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    redirect('/account/bookings');
}

$booking = $result->fetch_assoc();

if ($booking['balance'] <= 0) {
    setMessage('This booking has been fully paid', 'success');
    redirect('/account/bookings');
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitize($_POST['payment_method']);
    $phone_number = sanitize($_POST['phone_number'] ?? '');
    $mpesa_code = sanitize($_POST['mpesa_code'] ?? '');
    $amount = floatval($_POST['amount']);
    
    // Validate amount
    if ($amount <= 0 || $amount > $booking['balance']) {
        setMessage('Invalid payment amount', 'error');
    } else {
        $transaction_id = null;
        $payment_status = 'pending';
        $verification_code = generateVerificationCode();
        
        if ($payment_method === 'mpesa') {
            if (!empty($phone_number)) {
                // Initiate M-Pesa STK Push
                $mpesa_response = initiateMpesaPayment($phone_number, $amount, $booking['booking_reference']);
                
                if ($mpesa_response['success']) {
                    $transaction_id = $mpesa_response['transaction_id'];
                    $payment_status = 'pending';
                } else {
                    setMessage('M-Pesa payment initiation failed. Please try again.', 'error');
                }
            } elseif (!empty($mpesa_code)) {
                // Manual M-Pesa code entry
                $transaction_id = $mpesa_code;
                $payment_status = 'pending';
            }
        } elseif ($payment_method === 'cash') {
            $payment_status = 'pending';
        }
        
        // Create payment record
        $payment_query = "INSERT INTO payments (booking_id, transaction_id, payment_method, amount, 
                          payment_type, mpesa_code, phone_number, status, verification_code)
                          VALUES ($booking_id, " . ($transaction_id ? "'$transaction_id'" : "NULL") . ", 
                          '$payment_method', $amount, 'balance', 
                          " . ($mpesa_code ? "'$mpesa_code'" : "NULL") . ", 
                          " . ($phone_number ? "'$phone_number'" : "NULL") . ", 
                          '$payment_status', '$verification_code')";
        
        if ($conn->query($payment_query)) {
            // Send notification email
            $email_subject = "Balance Payment Received - " . $booking['booking_reference'];
            $email_message = "
                <h2>Balance Payment Received</h2>
                <p>Dear " . $booking['full_name'] . ",</p>
                <p>We have received your balance payment of " . formatPrice($amount) . "</p>
                <p><strong>Booking Reference:</strong> " . $booking['booking_reference'] . "</p>
                <p>We will verify your payment and update your booking status shortly.</p>
            ";
            sendEmail($booking['email'], $email_subject, $email_message);
            
            setMessage('Payment submitted successfully! We will verify and confirm shortly.', 'success');
            redirect('/account/bookings');
        } else {
            setMessage('Payment processing failed. Please try again.', 'error');
        }
    }
}

$page_title = 'Pay Balance - Girls Trip';

include 'includes/header.php';
?>

<section class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <i class="fas fa-credit-card text-5xl text-pink-600 mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Pay Balance</h1>
                <p class="text-gray-600">Complete your booking payment</p>
            </div>
            
            <!-- Booking Summary -->
            <div class="bg-pink-50 border border-pink-200 rounded-lg p-6 mb-8">
                <h3 class="font-bold text-gray-900 mb-4">Booking Summary</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Booking Reference</p>
                        <p class="font-bold text-gray-900"><?php echo $booking['booking_reference']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tour</p>
                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($booking['tour_title']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Amount</p>
                        <p class="font-semibold text-gray-900"><?php echo formatPrice($booking['total_amount']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Amount Paid</p>
                        <p class="font-semibold text-green-600"><?php echo formatPrice($booking['paid_amount']); ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600 mb-1">Balance Due</p>
                        <p class="font-bold text-2xl text-orange-600"><?php echo formatPrice($booking['balance']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Payment Form -->
            <form method="POST" id="payment-form">
                <!-- Payment Amount -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Amount
                    </label>
                    <input type="number" name="amount" step="0.01" 
                           value="<?php echo $booking['balance']; ?>" 
                           max="<?php echo $booking['balance']; ?>"
                           min="1" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <p class="text-xs text-gray-600 mt-2">
                        You can pay the full balance or a partial amount
                    </p>
                </div>
                
                <!-- Payment Methods -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Select Payment Method</h3>
                    
                    <div class="space-y-4">
                        <!-- M-Pesa STK Push -->
                        <label class="flex items-center p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-500 transition">
                            <input type="radio" name="payment_method" value="mpesa" checked 
                                   onchange="updatePaymentMethod()"
                                   class="mr-4 text-pink-600 focus:ring-pink-500">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-mobile-alt text-2xl text-green-600 mr-3"></i>
                                    <p class="font-bold text-gray-900">M-Pesa Express</p>
                                </div>
                                <p class="text-sm text-gray-600">Fast and secure payment via M-Pesa</p>
                            </div>
                        </label>
                        
                        <!-- Manual M-Pesa -->
                        <label class="flex items-center p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-500 transition">
                            <input type="radio" name="payment_method" value="mpesa_manual" 
                                   onchange="updatePaymentMethod()"
                                   class="mr-4 text-pink-600 focus:ring-pink-500">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-money-check-alt text-2xl text-green-600 mr-3"></i>
                                    <p class="font-bold text-gray-900">Manual M-Pesa Payment</p>
                                </div>
                                <p class="text-sm text-gray-600">Pay via Paybill and enter confirmation code</p>
                            </div>
                        </label>
                        
                        <!-- Cash Payment -->
                        <label class="flex items-center p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-500 transition">
                            <input type="radio" name="payment_method" value="cash" 
                                   onchange="updatePaymentMethod()"
                                   class="mr-4 text-pink-600 focus:ring-pink-500">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-money-bill-wave text-2xl text-blue-600 mr-3"></i>
                                    <p class="font-bold text-gray-900">Cash Payment</p>
                                </div>
                                <p class="text-sm text-gray-600">Pay cash at our office</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- M-Pesa STK Push Form -->
                <div id="mpesa-form" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        M-Pesa Phone Number
                    </label>
                    <input type="tel" name="phone_number" placeholder="254XXXXXXXXX" 
                           value="<?php echo htmlspecialchars($booking['phone']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <p class="text-xs text-gray-600 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        You will receive an M-Pesa prompt on your phone
                    </p>
                </div>
                
                <!-- Manual M-Pesa Form -->
                <div id="mpesa-manual-form" class="mb-6" style="display: none;">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Payment Instructions:</h4>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                            <li>Go to M-Pesa menu</li>
                            <li>Select Lipa Na M-Pesa</li>
                            <li>Select Pay Bill</li>
                            <li>Enter Business Number: <strong>123456</strong></li>
                            <li>Enter Account Number: <strong><?php echo $booking['booking_reference']; ?></strong></li>
                            <li>Enter the amount you want to pay</li>
                            <li>Enter your M-Pesa PIN and confirm</li>
                            <li>Enter the M-Pesa confirmation code below</li>
                        </ol>
                    </div>
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        M-Pesa Confirmation Code
                    </label>
                    <input type="text" name="mpesa_code" placeholder="e.g., QA12BC3DEF" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>
                
                <!-- Cash Payment Info -->
                <div id="cash-form" class="mb-6" style="display: none;">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            Cash Payment Information
                        </h4>
                        <p class="text-sm text-gray-700 mb-3">
                            To complete your cash payment, please visit our office at:
                        </p>
                        <address class="text-sm text-gray-700 mb-3 not-italic">
                            <strong>Girls Trip Office</strong><br>
                            Nairobi, Kenya<br>
                            Mon - Sat: 8am - 6pm
                        </address>
                        <p class="text-sm text-gray-700">
                            Please bring your booking reference: <strong><?php echo $booking['booking_reference']; ?></strong>
                        </p>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-pink-600 text-white px-6 py-4 rounded-lg font-semibold hover:bg-pink-700 transition">
                    <i class="fas fa-lock mr-2"></i>
                    <span id="submit-text">Complete Payment</span>
                </button>
            </form>
        </div>
    </div>
</section>

<script>
function updatePaymentMethod() {
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    
    document.getElementById('mpesa-form').style.display = 'none';
    document.getElementById('mpesa-manual-form').style.display = 'none';
    document.getElementById('cash-form').style.display = 'none';
    
    if (method === 'mpesa') {
        document.getElementById('mpesa-form').style.display = 'block';
        document.getElementById('submit-text').textContent = 'Send M-Pesa Prompt';
    } else if (method === 'mpesa_manual') {
        document.getElementById('mpesa-manual-form').style.display = 'block';
        document.getElementById('submit-text').textContent = 'Verify Payment';
    } else if (method === 'cash') {
        document.getElementById('cash-form').style.display = 'block';
        document.getElementById('submit-text').textContent = 'Confirm Payment';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
