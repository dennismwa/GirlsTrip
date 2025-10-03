<?php
// Girls Trip - Database Configuration
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'vxjtgclw_trip');
define('DB_PASS', 'gAkMbv5!nr$(*pF%');
define('DB_NAME', 'vxjtgclw_trip');

// Site Configuration
define('SITE_NAME', 'Girls Trip');
define('SITE_URL', 'https://yourdomain.com');
define('SITE_EMAIL', 'info@girlstrip.co.ke');
define('TIMEZONE', 'Africa/Nairobi');

// Set Timezone
date_default_timezone_set(TIMEZONE);

// Connect to Database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Helper Functions
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isVendor() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'vendor';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function formatPrice($amount) {
    return 'KES ' . number_format($amount, 2);
}

function generateReference($prefix = 'GT') {
    return $prefix . date('Ymd') . rand(1000, 9999);
}

function generateTicketNumber() {
    return 'TKT' . date('Ymd') . rand(10000, 99999);
}

function generateVerificationCode($length = 8) {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, $length));
}

function createSlug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($string));
    return trim($slug, '-');
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) return 'just now';
    if ($difference < 3600) return floor($difference / 60) . ' minutes ago';
    if ($difference < 86400) return floor($difference / 3600) . ' hours ago';
    if ($difference < 604800) return floor($difference / 86400) . ' days ago';
    
    return date('M d, Y', $timestamp);
}

function uploadImage($file, $folder = 'uploads') {
    $target_dir = "images/" . $folder . "/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $newFileName;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image'];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'File is too large'];
    }
    
    // Allow certain file formats
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowed)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'path' => $target_file];
    }
    
    return ['success' => false, 'message' => 'Error uploading file'];
}

// Get site settings
function getSetting($key, $default = '') {
    global $conn;
    $key = sanitize($key);
    $query = "SELECT setting_value FROM settings WHERE setting_key = '$key'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['setting_value'];
    }
    
    return $default;
}

// Update site setting
function updateSetting($key, $value) {
    global $conn;
    $key = sanitize($key);
    $value = sanitize($value);
    
    $query = "INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')
              ON DUPLICATE KEY UPDATE setting_value = '$value'";
    
    return $conn->query($query);
}

// Error and Success Messages
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Send Email Function (basic)
function sendEmail($to, $subject, $message) {
    $headers = "From: " . SITE_EMAIL . "\r\n";
    $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// M-Pesa STK Push Function (placeholder)
function initiateMpesaPayment($phone, $amount, $reference) {
    // Implement M-Pesa API integration here
    // This is a placeholder - you'll need to integrate with Safaricom's Daraja API
    
    $mpesa_shortcode = getSetting('mpesa_shortcode');
    $consumer_key = getSetting('mpesa_consumer_key');
    $consumer_secret = getSetting('mpesa_consumer_secret');
    $passkey = getSetting('mpesa_passkey');
    
    // Return dummy response for now
    return [
        'success' => true,
        'message' => 'Payment initiated',
        'transaction_id' => 'MPX' . time()
    ];
}

// Pagination Helper
function paginate($query, $page = 1, $per_page = 12) {
    global $conn;
    
    $page = max(1, intval($page));
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM ($query) as count_table";
    $count_result = $conn->query($count_query);
    $total = $count_result->fetch_assoc()['total'];
    
    // Get paginated data
    $paginated_query = $query . " LIMIT $per_page OFFSET $offset";
    $result = $conn->query($paginated_query);
    
    return [
        'data' => $result,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total / $per_page)
    ];
}
?>