<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Girls Trip - Adventures & Tours for Women'; ?></title>
    <meta name="description" content="<?php echo $meta_description ?? 'Discover amazing tours, trips, and adventures designed for women across Kenya and beyond'; ?>">
    <?php if (isset($meta_keywords)): ?>
        <meta name="keywords" content="<?php echo $meta_keywords; ?>">
    <?php endif; ?>
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $page_title ?? 'Girls Trip'; ?>">
    <meta property="og:description" content="<?php echo $meta_description ?? 'Adventures for Women'; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/images/og-image.jpg">
    <meta property="og:url" content="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $page_title ?? 'Girls Trip'; ?>">
    <meta name="twitter:description" content="<?php echo $meta_description ?? 'Adventures for Women'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
    
    <!-- Stylesheets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .btn-primary {
            @apply bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-pink-700 transition shadow-md;
        }
        
        .slide {
            display: none;
            animation: fadeIn 1s;
        }
        
        .slide.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        body {
            padding-bottom: 70px;
        }
        
        @media (min-width: 768px) {
            body {
                padding-bottom: 0;
            }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #ec4899;
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #db2777;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Header -->
    <header class="bg-white border-b sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Desktop Header -->
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="/" class="flex items-center group">
                        <i class="fas fa-plane text-2xl text-pink-600 mr-2 group-hover:rotate-12 transition-transform"></i>
                        <span class="text-2xl font-bold text-pink-600">Girls Trip</span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-pink-600 transition font-medium">Home</a>
                    <a href="/tours" class="text-gray-700 hover:text-pink-600 transition font-medium">Tours</a>
                    <a href="/trips" class="text-gray-700 hover:text-pink-600 transition font-medium">Trips</a>
                    <a href="/events" class="text-gray-700 hover:text-pink-600 transition font-medium">Events</a>
                    <a href="/locations" class="text-gray-700 hover:text-pink-600 transition font-medium">Destinations</a>
                    <a href="/blog" class="text-gray-700 hover:text-pink-600 transition font-medium">Blog</a>
                    <a href="/contact" class="text-gray-700 hover:text-pink-600 transition font-medium">Contact</a>
                </nav>
                
                <!-- User Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="/account" class="text-gray-700 hover:text-pink-600 transition flex items-center">
                            <i class="fas fa-user-circle mr-2 text-xl"></i>
                            <span class="font-medium"><?php echo $_SESSION['full_name']; ?></span>
                        </a>
                        <a href="/logout" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="/login" class="text-gray-700 hover:text-pink-600 transition font-medium">Login</a>
                        <a href="/register" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition">
                            Sign Up
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-gray-700 hover:text-pink-600 transition">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t bg-white">
            <nav class="px-4 py-4 space-y-2">
                <a href="/" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                    <i class="fas fa-home mr-3 w-5"></i>Home
                </a>
                <a href="/tours" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                    <i class="fas fa-map-marked-alt mr-3 w-5"></i>Tours
                </a>
                <a href="/trips" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                    <i class="fas fa-route mr-3 w-5"></i>Trips
                </a>
                <a href="/events" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                    <i class="fas fa-calendar-alt mr-3 w-5"></i>Events
                </a>
                <a href="/locations" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                    <i class="fas fa-map-pin mr-3 w-5"></i>Destinations
                </a>
                <a href="/blog" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                    <i class="fas fa-blog mr-3 w-5"></i>Blog
                </a>
                <a href="/contact" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                    <i class="fas fa-envelope mr-3 w-5"></i>Contact
                </a>
                
                <div class="border-t pt-4 mt-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="/account" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                            <i class="fas fa-user mr-3 w-5"></i>My Account
                        </a>
                        <a href="/logout" class="block py-2 text-pink-600 hover:text-pink-700 hover:bg-pink-50 rounded px-3 transition">
                            <i class="fas fa-sign-out-alt mr-3 w-5"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="/login" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                            <i class="fas fa-sign-in-alt mr-3 w-5"></i>Login
                        </a>
                        <a href="/register" class="block py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded px-3 transition">
                            <i class="fas fa-user-plus mr-3 w-5"></i>Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Alert Messages -->
    <?php 
    $msg = getMessage();
    if ($msg): 
        $bg_color = $msg['type'] === 'success' ? 'bg-green-500' : 'bg-red-500';
        $icon = $msg['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    ?>
    <div class="<?php echo $bg_color; ?> text-white px-4 py-3 fixed top-16 left-0 right-0 z-40 shadow-lg animate-pulse">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas <?php echo $icon; ?> mr-3"></i>
                <span><?php echo htmlspecialchars($msg['message']); ?></span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Mobile Bottom Navigation -->
    <?php if (isLoggedIn()): ?>
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-50 bottom-nav">
        <div class="grid grid-cols-5 h-16">
            <a href="/" class="flex flex-col items-center justify-center text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs">Home</span>
            </a>
            <a href="/tours" class="flex flex-col items-center justify-center text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition">
                <i class="fas fa-map-marked-alt text-xl mb-1"></i>
                <span class="text-xs">Tours</span>
            </a>
            <a href="/account/bookings" class="flex flex-col items-center justify-center text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition">
                <i class="fas fa-ticket-alt text-xl mb-1"></i>
                <span class="text-xs">Bookings</span>
            </a>
            <a href="/tickets" class="flex flex-col items-center justify-center text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition">
                <i class="fas fa-qrcode text-xl mb-1"></i>
                <span class="text-xs">Tickets</span>
            </a>
            <a href="/account" class="flex flex-col items-center justify-center text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs">Account</span>
            </a>
        </div>
    </nav>
    <?php endif; ?>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
        
        // Auto-hide alerts after 5 seconds
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
    </script>
