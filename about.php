<?php
// about.php
require_once 'config.php';

$page_title = 'About Us - Girls Trip';
$meta_description = 'Learn about Girls Trip - creating unforgettable adventures for women across Kenya and beyond.';

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-pink-600 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">About Girls Trip</h1>
        <p class="text-xl text-pink-100 max-w-3xl mx-auto">
            Empowering women through unforgettable travel experiences
        </p>
    </div>
</section>

<!-- Our Story -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Story</h2>
            <div class="w-20 h-1 bg-pink-600 mx-auto"></div>
        </div>
        
        <div class="prose prose-lg max-w-none">
            <p class="text-gray-700 leading-relaxed mb-6">
                Girls Trip was born from a simple idea: women deserve spaces where they can explore, 
                connect, and create memories without limitations. Founded in 2020, we recognized that 
                travel experiences specifically designed for women were rare in Kenya and across Africa.
            </p>
            
            <p class="text-gray-700 leading-relaxed mb-6">
                What started as small weekend getaways has grown into a vibrant community of adventurous 
                women exploring destinations across Kenya and beyond. We've organized over 200 trips, 
                welcomed thousands of travelers, and created countless lifelong friendships along the way.
            </p>
            
            <p class="text-gray-700 leading-relaxed">
                Today, Girls Trip stands as Kenya's premier women-focused travel company, offering 
                everything from day trips and weekend adventures to international expeditions. Our 
                commitment remains unchanged: providing safe, fun, and empowering travel experiences 
                that celebrate womanhood.
            </p>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-bullseye text-3xl text-pink-600"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h3>
                <p class="text-gray-700 leading-relaxed">
                    To create safe, empowering, and unforgettable travel experiences that inspire women 
                    to explore the world, build connections, and discover their adventurous spirit.
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-eye text-3xl text-pink-600"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Vision</h3>
                <p class="text-gray-700 leading-relaxed">
                    To be Africa's leading women-focused travel company, recognized for creating 
                    transformative experiences that empower women to travel fearlessly and live fully.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Core Values -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Core Values</h2>
            <div class="w-20 h-1 bg-pink-600 mx-auto"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-20 h-20 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-3xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-3">Safety First</h4>
                <p class="text-gray-600">
                    We prioritize the safety and security of every traveler, ensuring peace of mind 
                    throughout your journey.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-3xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-3">Community</h4>
                <p class="text-gray-600">
                    We foster a supportive community where women connect, share experiences, and 
                    build lasting friendships.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-star text-3xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-3">Excellence</h4>
                <p class="text-gray-600">
                    We deliver exceptional experiences through meticulous planning, quality service, 
                    and attention to detail.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-3xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-3">Inclusivity</h4>
                <p class="text-gray-600">
                    We welcome all women, regardless of age, background, or experience level, 
                    creating spaces for everyone.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-leaf text-3xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-3">Sustainability</h4>
                <p class="text-gray-600">
                    We promote responsible tourism, respecting local cultures and protecting our 
                    beautiful destinations.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-smile text-3xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-3">Fun & Adventure</h4>
                <p class="text-gray-600">
                    We believe travel should be exciting and joyful, creating memories that bring 
                    smiles for years to come.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose Girls Trip?</h2>
            <div class="w-20 h-1 bg-pink-600 mx-auto"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg p-6 shadow-md">
                <i class="fas fa-calendar-check text-4xl text-pink-600 mb-4"></i>
                <h4 class="text-lg font-bold text-gray-900 mb-3">Flexible Booking</h4>
                <p class="text-gray-600">
                    Book with ease using our flexible payment plans including M-Pesa, deposits, 
                    and installment options.
                </p>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <i class="fas fa-user-check text-4xl text-pink-600 mb-4"></i>
                <h4 class="text-lg font-bold text-gray-900 mb-3">Experienced Guides</h4>
                <p class="text-gray-600">
                    Our professional female guides ensure your safety while sharing insider 
                    knowledge and local insights.
                </p>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <i class="fas fa-map-marked-alt text-4xl text-pink-600 mb-4"></i>
                <h4 class="text-lg font-bold text-gray-900 mb-3">Curated Experiences</h4>
                <p class="text-gray-600">
                    Every trip is carefully planned with unique experiences you won't find anywhere else.
                </p>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <i class="fas fa-handshake text-4xl text-pink-600 mb-4"></i>
                <h4 class="text-lg font-bold text-gray-900 mb-3">Small Groups</h4>
                <p class="text-gray-600">
                    Intimate group sizes ensure personalized attention and meaningful connections.
                </p>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <i class="fas fa-mobile-alt text-4xl text-pink-600 mb-4"></i>
                <h4 class="text-lg font-bold text-gray-900 mb-3">Easy Digital Access</h4>
                <p class="text-gray-600">
                    Manage bookings, access tickets, and stay updated through our mobile-friendly platform.
                </p>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <i class="fas fa-headset text-4xl text-pink-600 mb-4"></i>
                <h4 class="text-lg font-bold text-gray-900 mb-3">24/7 Support</h4>
                <p class="text-gray-600">
                    Our dedicated support team is always available to assist you before, during, 
                    and after your trip.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="py-16 bg-pink-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <p class="text-5xl font-bold text-white mb-2">200+</p>
                <p class="text-pink-100">Trips Organized</p>
            </div>
            <div>
                <p class="text-5xl font-bold text-white mb-2">5000+</p>
                <p class="text-pink-100">Happy Travelers</p>
            </div>
            <div>
                <p class="text-5xl font-bold text-white mb-2">50+</p>
                <p class="text-pink-100">Destinations</p>
            </div>
            <div>
                <p class="text-5xl font-bold text-white mb-2">4.9★</p>
                <p class="text-pink-100">Average Rating</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Meet Our Team</h2>
            <div class="w-20 h-1 bg-pink-600 mx-auto mb-4"></div>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Our passionate team of travel experts and guides are dedicated to making your 
                experience unforgettable.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-40 h-40 bg-pink-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-user text-6xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-2">Sarah Mwangi</h4>
                <p class="text-pink-600 font-medium mb-3">Founder & CEO</p>
                <p class="text-gray-600 text-sm">
                    Passionate about empowering women through travel with 10+ years of experience 
                    in the tourism industry.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-40 h-40 bg-pink-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-user text-6xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-2">Grace Wanjiru</h4>
                <p class="text-pink-600 font-medium mb-3">Operations Manager</p>
                <p class="text-gray-600 text-sm">
                    Ensures every detail is perfect, from bookings to on-ground experiences, 
                    with meticulous attention.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-40 h-40 bg-pink-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-user text-6xl text-pink-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-2">Linda Akinyi</h4>
                <p class="text-pink-600 font-medium mb-3">Lead Tour Guide</p>
                <p class="text-gray-600 text-sm">
                    Brings destinations to life with engaging stories and insider knowledge of 
                    Kenya's hidden gems.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to Start Your Adventure?</h2>
        <p class="text-xl text-gray-600 mb-8">
            Join thousands of women who have discovered the joy of traveling with Girls Trip
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/tours" class="bg-pink-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-pink-700 transition">
                Browse Tours
            </a>
            <a href="/register" class="bg-white border-2 border-pink-600 text-pink-600 px-8 py-3 rounded-lg font-semibold hover:bg-pink-50 transition">
                Join Our Community
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
