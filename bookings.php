<?php
require_once 'config.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('/login');
}

$tour_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($tour_id === 0) {
    redirect('/tours');
}

// Fetch tour details
$query = "SELECT t.*, l.name as location_name, c.name as category_name
          FROM tours t 
          LEFT JOIN locations l ON t.location_id = l.id
          LEFT JOIN categories c ON t.category_id = c.id
          WHERE t.id = $tour_id AND t.status = 'published'";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    redirect('/tours');
}

$tour = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adults = max(1, intval($_POST['adults']));
    $children = max(0, intval($_POST['children']));
    $travel_date = sanitize($_POST['travel_date']);
    $payment_plan = sanitize($_POST['payment_plan']);
    $custom_amount = isset($_POST['custom_amount']) ? floatval($_POST['custom_amount']) : 0;
    $promo_code = isset($_POST['promo_code']) ? sanitize($_POST['promo_code']) : '';
    
    // Calculate total
    $adult_price = $tour['price'] ?? $tour['price_min'] ?? 0;
    $child_price = $tour['child_price'] ?? ($adult_price * 0.5);
    
    $total_amount = ($adults * $adult_price) + ($children * $child_price);
    
    // Apply promo code if exists
    $discount_amount = 0;
    $promo_id = null;
    
    if (!empty($promo_code)) {
        $promo_query = "SELECT * FROM promo_codes 
                        WHERE code = '$promo_code' 
                        AND status = 'active' 
                        AND (valid_from IS NULL OR valid_from <= CURDATE())
                        AND (valid_until IS NULL OR valid_until >= CURDATE())
                        AND (max_uses IS NULL OR used_count < max_uses)
                        AND (min_amount IS NULL OR min_amount <= $total_amount)";
        $promo_result = $conn->query($promo_query);
        
        if ($promo_result && $promo_result->num_rows > 0) {
            $promo = $promo_result->fetch_assoc();
            $promo_id = $promo['id'];
            
            if ($promo['discount_type'] === 'percentage') {
                $discount_amount = ($total_amount * $promo['discount_value']) / 100;
            } else {
                $discount_amount = $promo['discount_value'];
            }
            
            $total_amount -= $discount_amount;
        }
    }
    
    // Determine payment amount
    $paid_amount = 0;
    if ($payment_plan === 'full') {
        $paid_amount = $total_amount;
    } elseif ($payment_plan === 'deposit') {
        $deposit_percentage = floatval(getSetting('deposit_percentage', 30));
        $paid_amount = ($total_amount * $deposit_percentage) / 100;
    } elseif ($payment_plan === 'installment' && $custom_amount > 0) {
        $paid_amount = min($custom_amount, $total_amount);
    }
    
    $balance = $total_amount - $paid_amount;
    
    // Create booking
    $booking_reference = generateReference('GT');
    $user_id = getUserId();
    
    $booking_query = "INSERT INTO bookings (booking_reference, user_id, tour_id, adults, children, 
                      total_amount, paid_amount, balance, payment_plan, booking_date, travel_date, status)
                      VALUES ('$booking_reference', $user_id, $tour_id, $adults, $children, 
                      $total_amount, 0, $total_amount, '$payment_plan', CURDATE(), '$travel_date', 'pending')";
    
    if ($conn->query($booking_query)) {
        $booking_id = $conn->insert_id;
        
        // Apply promo code
        if ($promo_id && $discount_amount > 0) {
            $conn->query("INSERT INTO booking_promo_codes (booking_id, promo_code_id, discount_amount) 
                         VALUES ($booking_id, $promo_id, $discount_amount)");
            $conn->query("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = $promo_id");
        }
        
        // Store booking info in session for payment
        $_SESSION['pending_booking'] = [
            'booking_id' => $booking_id,
            'booking_reference' => $booking_reference,
            'amount' => $paid_amount,
            'payment_plan' => $payment_plan
        ];
        
        redirect('/payment');
    } else {
        setMessage('Booking failed. Please try again.', 'error');
    }
}

$page_title = 'Book ' . $tour['title'] . ' - Girls Trip';

include 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm">
            <a href="/" class="text-gray-600 hover:text-pink-600">Home</a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="/tours" class="text-gray-600 hover:text-pink-600">Tours</a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="/tours/<?php echo $tour['slug']; ?>" class="text-gray-600 hover:text-pink-600">
                <?php echo htmlspecialchars($tour['title']); ?>
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-900">Booking</span>
        </nav>
    </div>
</div>

<!-- Booking Form -->
<section class="py-12 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Booking Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Complete Your Booking</h1>
                    
                    <form method="POST" id="booking-form">
                        <!-- Number of People -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Number of People</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Adults (18+ years)
                                    </label>
                                    <div class="flex items-center">
                                        <button type="button" onclick="decrementAdults()" 
                                                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-l-lg hover:bg-gray-300">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" name="adults" id="adults" value="1" min="1" 
                                               class="w-20 text-center border-y border-gray-300 py-2 focus:outline-none" readonly>
                                        <button type="button" onclick="incrementAdults()" 
                                                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-r-lg hover:bg-gray-300">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <?php if ($tour['child_price']): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Children (Below 18 years)
                                        </label>
                                        <div class="flex items-center">
                                            <button type="button" onclick="decrementChildren()" 
                                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-l-lg hover:bg-gray-300">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="children" id="children" value="0" min="0" 
                                                   class="w-20 text-center border-y border-gray-300 py-2 focus:outline-none" readonly>
                                            <button type="button" onclick="incrementChildren()" 
                                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-r-lg hover:bg-gray-300">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Travel Date -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Travel Date
                            </label>
                            <input type="date" name="travel_date" required 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   <?php if ($tour['start_date']): ?>
                                       value="<?php echo $tour['start_date']; ?>"
                                   <?php endif; ?>
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        </div>
                        
                        <!-- Payment Plan -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Plan</h3>
                            
                            <div class="space-y-3">
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-500 transition">
                                    <input type="radio" name="payment_plan" value="full" checked 
                                           onchange="updatePaymentPlan()"
                                           class="mt-1 mr-3 text-pink-600 focus:ring-pink-500">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">Pay Full Amount</p>
                                        <p class="text-sm text-gray-600">Pay the complete amount now</p>
                                    </div>
                                </label>
                                
                                <?php if ($tour['allow_installments']): ?>
                                    <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-500 transition">
                                        <input type="radio" name="payment_plan" value="deposit" 
                                               onchange="updatePaymentPlan()"
                                               class="mt-1 mr-3 text-pink-600 focus:ring-pink-500">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">Pay Deposit</p>
                                            <p class="text-sm text-gray-600">
                                                Pay <?php echo getSetting('deposit_percentage', 30); ?>% now, 
                                                rest later
                                            </p>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-500 transition">
                                        <input type="radio" name="payment_plan" value="installment" 
                                               onchange="updatePaymentPlan()"
                                               class="mt-1 mr-3 text-pink-600 focus:ring-pink-500">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">Lipa Mdogo Mdogo</p>
                                            <p class="text-sm text-gray-600 mb-2">Choose your own payment amount</p>
                                            <input type="number" name="custom_amount" id="custom_amount" 
                                                   placeholder="Enter amount (KES)" min="0" step="100"
                                                   onchange="updateCustomAmount()"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                        </div>
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Promo Code -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Promo Code (Optional)
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="promo_code" id="promo_code" 
                                       placeholder="Enter promo code"
                                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                <button type="button" onclick="applyPromo()" 
                                        class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-300 transition">
                                    Apply
                                </button>
                            </div>
                            <p id="promo-message" class="text-sm mt-2"></p>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" required 
                                       class="mt-1 mr-3 text-pink-600 focus:ring-pink-500">
                                <span class="text-sm text-gray-700">
                                    I agree to the <a href="/terms" class="text-pink-600 hover:underline">Terms and Conditions</a> 
                                    and <a href="/cancellation" class="text-pink-600 hover:underline">Cancellation Policy</a>
                                </span>
                            </label>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full bg-pink-600 text-white px-6 py-4 rounded-lg font-semibold hover:bg-pink-700 transition">
                                <i class="fas fa-lock mr-2"></i>Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Booking Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Booking Summary</h3>
                    
                    <!-- Tour Info -->
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <?php if ($tour['featured_image']): ?>
                            <img src="/<?php echo htmlspecialchars($tour['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($tour['title']); ?>" 
                                 class="w-full h-32 object-cover rounded-lg mb-3">
                        <?php endif; ?>
                        
                        <h4 class="font-semibold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($tour['title']); ?>
                        </h4>
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-map-marker-alt text-pink-600 mr-1"></i>
                            <?php echo htmlspecialchars($tour['location_name']); ?>
                        </p>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div class="space-y-3 mb-4 pb-4 border-b border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Adults (<span id="summary-adults">1</span>)</span>
                            <span class="font-medium" id="adult-total">
                                <?php echo formatPrice($tour['price'] ?? $tour['price_min'] ?? 0); ?>
                            </span>
                        </div>
                        
                        <?php if ($tour['child_price']): ?>
                            <div class="flex justify-between text-sm" id="children-row" style="display: none;">
                                <span class="text-gray-600">Children (<span id="summary-children">0</span>)</span>
                                <span class="font-medium" id="child-total">KES 0.00</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between text-sm" id="discount-row" style="display: none;">
                            <span class="text-green-600">Discount</span>
                            <span class="font-medium text-green-600" id="discount-amount">- KES 0.00</span>
                        </div>
                    </div>
                    
                    <!-- Total -->
                    <div class="space-y-3">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total Amount</span>
                            <span id="total-amount" class="text-pink-600">
                                <?php echo formatPrice($tour['price'] ?? $tour['price_min'] ?? 0); ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Amount to Pay Now</span>
                            <span class="font-semibold" id="pay-now-amount">
                                <?php echo formatPrice($tour['price'] ?? $tour['price_min'] ?? 0); ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between" id="balance-row" style="display: none;">
                            <span class="text-sm text-gray-600">Balance Due</span>
                            <span class="font-medium text-orange-600" id="balance-amount">KES 0.00</span>
                        </div>
                    </div>
                    
                    <!-- Security Info -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center text-xs text-gray-600 mb-2">
                            <i class="fas fa-lock text-green-600 mr-2"></i>
                            <span>Secure Payment</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-600">
                            <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                            <span>Your data is protected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const adultPrice = <?php echo $tour['price'] ?? $tour['price_min'] ?? 0; ?>;
const childPrice = <?php echo $tour['child_price'] ?? 0; ?>;
const depositPercentage = <?php echo getSetting('deposit_percentage', 30); ?>;

let adults = 1;
let children = 0;
let discountAmount = 0;

function incrementAdults() {
    adults++;
    document.getElementById('adults').value = adults;
    updateSummary();
}

function decrementAdults() {
    if (adults > 1) {
        adults--;
        document.getElementById('adults').value = adults;
        updateSummary();
    }
}

function incrementChildren() {
    children++;
    document.getElementById('children').value = children;
    updateSummary();
}

function decrementChildren() {
    if (children > 0) {
        children--;
        document.getElementById('children').value = children;
        updateSummary();
    }
}

function updateSummary() {
    const adultTotal = adults * adultPrice;
    const childTotal = children * childPrice;
    const subtotal = adultTotal + childTotal;
    const total = subtotal - discountAmount;
    
    document.getElementById('summary-adults').textContent = adults;
    document.getElementById('adult-total').textContent = formatPrice(adultTotal);
    
    if (children > 0) {
        document.getElementById('summary-children').textContent = children;
        document.getElementById('child-total').textContent = formatPrice(childTotal);
        document.getElementById('children-row').style.display = 'flex';
    } else {
        document.getElementById('children-row').style.display = 'none';
    }
    
    document.getElementById('total-amount').textContent = formatPrice(total);
    updatePaymentPlan();
}

function updatePaymentPlan() {
    const paymentPlan = document.querySelector('input[name="payment_plan"]:checked').value;
    const adultTotal = adults * adultPrice;
    const childTotal = children * childPrice;
    const subtotal = adultTotal + childTotal;
    const total = subtotal - discountAmount;
    
    let payNow = total;
    let balance = 0;
    
    if (paymentPlan === 'deposit') {
        payNow = (total * depositPercentage) / 100;
        balance = total - payNow;
    } else if (paymentPlan === 'installment') {
        const customAmount = parseFloat(document.getElementById('custom_amount').value) || 0;
        payNow = Math.min(customAmount, total);
        balance = total - payNow;
    }
    
    document.getElementById('pay-now-amount').textContent = formatPrice(payNow);
    
    if (balance > 0) {
        document.getElementById('balance-amount').textContent = formatPrice(balance);
        document.getElementById('balance-row').style.display = 'flex';
    } else {
        document.getElementById('balance-row').style.display = 'none';
    }
}

function updateCustomAmount() {
    updatePaymentPlan();
}

function formatPrice(amount) {
    return 'KES ' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '        $deposit_percentage = floatval(getSetting('deposit_percentage', 30));,');
}

function applyPromo() {
    const promoCode = document.getElementById('promo_code').value;
    const promoMessage = document.getElementById('promo-message');
    
    if (!promoCode) {
        promoMessage.textContent = '';
        return;
    }
    
    // This is a simple client-side validation
    // Real validation happens on server side
    promoMessage.textContent = 'Promo code will be validated on submission';
    promoMessage.className = 'text-sm mt-2 text-blue-600';
}
</script>

<?php include 'includes/footer.php'; ?>