<?php
// login.php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('/account');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = '$email' AND status = 'active'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Redirect to intended page or account
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                redirect($redirect_url);
            } else {
                redirect('/account');
            }
        } else {
            setMessage('Invalid email or password', 'error');
        }
    } else {
        setMessage('Invalid email or password', 'error');
    }
}

$page_title = 'Login - Girls Trip';

include 'includes/header.php';
?>

<section class="py-12 bg-gray-50 min-h-screen flex items-center">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
                <p class="text-gray-600">Login to your account</p>
            </div>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>
                
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 text-pink-600 focus:ring-pink-500">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="/forgot-password" class="text-sm text-pink-600 hover:underline">
                        Forgot Password?
                    </a>
                </div>
                
                <button type="submit" 
                        class="w-full bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-pink-700 transition">
                    Login
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="/register" class="text-pink-600 font-medium hover:underline">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
