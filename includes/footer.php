<!-- Footer -->
    <footer class="bg-gray-900 text-white pt-12 pb-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- About -->
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-plane text-2xl text-pink-500 mr-2"></i>
                        <h3 class="text-xl font-bold">Girls Trip</h3>
                    </div>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Creating unforgettable adventures and experiences for women across Kenya and beyond. 
                        Empowering women through travel since 2020.
                    </p>
                    <div class="flex space-x-4">
                        <a href="<?php echo getSetting('facebook_url', '#'); ?>" target="_blank" 
                           class="text-gray-400 hover:text-white transition transform hover:scale-110">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="<?php echo getSetting('instagram_url', '#'); ?>" target="_blank" 
                           class="text-gray-400 hover:text-white transition transform hover:scale-110">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="<?php echo getSetting('twitter_url', '#'); ?>" target="_blank" 
                           class="text-gray-400 hover:text-white transition transform hover:scale-110">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="https://wa.me/<?php echo getSetting('whatsapp_number', '254700000000'); ?>" target="_blank" 
                           class="text-gray-400 hover:text-white transition transform hover:scale-110">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/tours" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Tours
                            </a>
                        </li>
                        <li>
                            <a href="/trips" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Trips
                            </a>
                        </li>
                        <li>
                            <a href="/events" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Events
                            </a>
                        </li>
                        <li>
                            <a href="/locations" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Destinations
                            </a>
                        </li>
                        <li>
                            <a href="/blog" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Blog
                            </a>
                        </li>
                        <li>
                            <a href="/about" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>About Us
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/contact" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Contact Us
                            </a>
                        </li>
                        <li>
                            <a href="/faq" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>FAQ
                            </a>
                        </li>
                        <li>
                            <a href="/terms" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Terms & Conditions
                            </a>
                        </li>
                        <li>
                            <a href="/privacy" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Privacy Policy
                            </a>
                        </li>
                        <li>
                            <a href="/cancellation" class="text-gray-400 hover:text-white transition flex items-center">
                                <i class="fas fa-chevron-right text-pink-500 mr-2 text-xs"></i>Cancellation Policy
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-3 text-pink-500"></i>
                            <span>Nairobi, Kenya</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone mt-1 mr-3 text-pink-500"></i>
                            <a href="tel:<?php echo getSetting('site_phone', '+254700000000'); ?>" class="hover:text-white transition">
                                <?php echo getSetting('site_phone', '+254 700 000 000'); ?>
                            </a>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope mt-1 mr-3 text-pink-500"></i>
                            <a href="mailto:<?php echo getSetting('site_email', 'info@girlstrip.co.ke'); ?>" class="hover:text-white transition">
                                <?php echo getSetting('site_email', 'info@girlstrip.co.ke'); ?>
                            </a>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-clock mt-1 mr-3 text-pink-500"></i>
                            <div>
                                <p>Mon - Sat: 8am - 6pm</p>
                                <p>Sunday: Closed</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="border-t border-gray-800 pt-6 mb-6">
                <p class="text-sm text-gray-400 mb-3">We Accept:</p>
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="bg-white px-4 py-2 rounded shadow-md">
                        <span class="text-green-600 font-bold text-sm">M-PESA</span>
                    </div>
                    <div class="bg-white px-4 py-2 rounded shadow-md">
                        <i class="fab fa-cc-visa text-2xl text-blue-600"></i>
                    </div>
                    <div class="bg-white px-4 py-2 rounded shadow-md">
                        <i class="fab fa-cc-mastercard text-2xl text-red-600"></i>
                    </div>
                    <div class="bg-white px-4 py-2 rounded shadow-md flex items-center">
                        <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                        <span class="text-gray-700 text-sm font-semibold">Cash</span>
                    </div>
                </div>
            </div>
            
            <!-- Newsletter Signup -->
            <div class="border-t border-gray-800 pt-6 mb-6">
                <div class="max-w-md">
                    <h4 class="text-lg font-semibold mb-3">Subscribe to Our Newsletter</h4>
                    <p class="text-gray-400 text-sm mb-4">Get travel tips and exclusive deals delivered to your inbox!</p>
                    <form class="flex gap-2">
                        <input type="email" placeholder="Enter your email" required
                               class="flex-1 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-pink-700 transition">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="border-t border-gray-800 pt-6 text-center">
                <p class="text-sm text-gray-400">
                    &copy; <?php echo date('Y'); ?> Girls Trip. All rights reserved. 
                    <?php if (isAdmin()): ?>
                        | <a href="/admin" class="text-pink-500 hover:text-pink-400">Admin Panel</a>
                    <?php endif; ?>
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    Made with <i class="fas fa-heart text-pink-500"></i> in Kenya
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" 
            class="fixed bottom-24 right-6 bg-pink-600 text-white w-12 h-12 rounded-full shadow-lg hover:bg-pink-700 transition transform hover:scale-110 hidden z-40">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script>
        // Scroll to top functionality
        const scrollToTopBtn = document.getElementById('scroll-to-top');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.remove('hidden');
            } else {
                scrollToTopBtn.classList.add('hidden');
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Add active class to current page in navigation
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('nav a');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('text-pink-600');
            }
        });
    </script>
</body>
</html>
