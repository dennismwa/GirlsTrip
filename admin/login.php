<?php
// admin/login.php - Admin & Vendor Login Portal
require_once '../config.php';

// If already logged in as admin/vendor, redirect to dashboard
if (isLoggedIn() && (isAdmin() || isVendor())) {
    redirect('/admin');
}

// If logged in but not admin/vendor, show error and logout
if (isLoggedIn() && !isAdmin() && !isVendor()) {
    session_destroy();
    setMessage('You do not have admin access', 'error');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Query for admin or vendor users only
    $query = "SELECT * FROM users WHERE email = '$email' AND status = 'active' AND user_type IN ('admin', 'vendor')";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Set remember me cookie if checked
            if ($remember) {
                setcookie('admin_remember', $user['id'], time() + (86400 * 30), '/'); // 30 days
            }
            
            // Log login activity
            $login_ip = $_SERVER['REMOTE_ADDR'];
            $conn->query("INSERT INTO login_logs (user_id, ip_address, user_agent, login_time) 
                         VALUES ({$user['id']}, '$login_ip', '{$_SERVER['HTTP_USER_AGENT']}', NOW())");
            
            setMessage('Welcome back, ' . $user['full_name'], 'success');
            redirect('/admin');
        } else {
            setMessage('Invalid email or password', 'error');
        }
    } else {
        setMessage('Invalid email or password', 'error');
    }
}

$page_title = 'Admin Login - Girls Trip';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #fce7f3 0%, #f3e8ff 100%);
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo Section -->
        <div class="text-center mb-8 float-animation">
            <a href="/" class="inline-block">
                <div class="w-24 h-24 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-plane text-5xl text-pink-600"></i>
                </div>
                <h1 class="text-4xl font-bold text-pink-600 mb-2">Girls Trip</h1>
                <p class="text-gray-700 font-medium">Admin Control Panel</p>
            </a>
        </div>
        
        <!-- Login Card -->
        <div class="login-card rounded-2xl shadow-2xl p-8 mb-6">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-user-shield text-3xl text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Admin Login</h2>
                <p class="text-gray-600">Sign in to access the dashboard</p>
            </div>
            
            <!-- Alert Messages -->
            <?php 
            $msg = getMessage();
            if ($msg): 
                $bg_color = $msg['type'] === 'success' ? 'bg-green-500' : 'bg-red-500';
                $icon = $msg['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            ?>
            <div class="<?php echo $bg_color; ?> text-white px-4 py-3 rounded-lg mb-6 flex items-center animate-pulse">
                <i class="fas <?php echo $icon; ?> mr-3"></i>
                <span><?php echo htmlspecialchars($msg['message']); ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" id="loginForm">
                <!-- Email Field -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope text-pink-600 mr-2"></i>Email Address
                    </label>
                    <input type="email" name="email" required autofocus
                           placeholder="admin@girlstrip.co.ke"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition">
                </div>
                
                <!-- Password Field -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock text-pink-600 mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required 
                               placeholder="Enter your password"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition pr-12">
                        <button type="button" onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-pink-600">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" value="1"
                               class="w-4 h-4 text-pink-600 bg-gray-100 border-gray-300 rounded focus:ring-pink-500 focus:ring-2">
                        <span class="ml-2 text-sm text-gray-700">Remember me</span>
                    </label>
                    <a href="/forgot-password" class="text-sm text-pink-600 hover:text-pink-700 font-medium hover:underline">
                        Forgot password?
                    </a>
                </div>
                
                <!-- Login Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-pink-700 hover:to-purple-700 transition shadow-lg transform hover:scale-105 duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In to Dashboard
                </button>
            </form>
            
            <!-- Divider -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-center gap-4 text-sm">
                    <a href="/" class="text-gray-600 hover:text-pink-600 transition flex items-center">
                        <i class="fas fa-home mr-2"></i>Back to Website
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="/contact" class="text-gray-600 hover:text-pink-600 transition flex items-center">
                        <i class="fas fa-question-circle mr-2"></i>Need Help?
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Security Notice -->
        <div class="text-center space-y-2">
            <p class="text-sm text-gray-700 flex items-center justify-center">
                <i class="fas fa-shield-alt text-pink-600 mr-2"></i>
                Secure Admin Area - Authorized Access Only
            </p>
            <p class="text-xs text-gray-600">
                All login attempts are monitored and logged
            </p>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 text-xs text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> Girls Trip. All rights reserved.</p>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto-hide alert messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-500, .bg-red-500');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
        });
        
        // Prevent multiple submissions
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...';
        });
        
        // Add Enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>
