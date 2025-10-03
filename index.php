<?php
require_once 'config.php';

$page_title = 'Girls Trip - Adventures & Tours for Women';
$meta_description = 'Discover amazing tours, trips, and adventures designed for women. Book with flexible payment options including M-Pesa and installments.';

// Fetch active sliders
$sliders_query = "SELECT * FROM sliders WHERE status = 'active' ORDER BY display_order ASC LIMIT 5";
$sliders = $conn->query($sliders_query);

// Fetch featured tours
$featured_query = "SELECT t.*, l.name as location_name, c.name as category_name,
                   (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id AND status = 'approved') as avg_rating
                   FROM tours t 
                   LEFT JOIN locations l ON t.location_id = l.id
                   LEFT JOIN categories c ON t.category_id = c.id
                   WHERE t.status = 'published' AND t.is_featured = 1 
                   ORDER BY t.created_at DESC LIMIT 6";
$featured_tours = $conn->query($featured_query);

// Fetch popular locations
$locations_query = "SELECT * FROM locations WHERE status = 'active' AND is_popular = 1 ORDER BY name ASC LIMIT 6";
$popular_locations = $conn->query($locations_query);

// Fetch categories
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC LIMIT 8";
$categories = $conn->query($categories_query);

// Fetch activities
$activities_query = "SELECT * FROM activities WHERE status = 'active' ORDER BY name ASC LIMIT 8";
$activities = $conn->query($activities_query);

// Fetch upcoming tours
$upcoming_query = "SELECT t.*, l.name as location_name 
                   FROM tours t 
                   LEFT JOIN locations l ON t.location_id = l.id
                   WHERE t.status = 'published' AND t.start_date >= CURDATE() 
                   ORDER BY t.start_date ASC LIMIT 4";
$upcoming_tours = $conn->query($upcoming_query);

include 'includes/header.php';
?>

<!-- Hero Section with Slider -->
<section class="hero-slider relative h-screen md:h-[600px]">
    <?php endif; ?>
</section>

<!-- Categories Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-900 mb-3">Explore by Category</h2>
            <p class="text-gray-600">Choose your perfect adventure</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php if ($categories && $categories->num_rows > 0): ?>
                <?php while($category = $categories->fetch_assoc()): ?>
                    <a href="/category/<?php echo $category['slug']; ?>" 
                       class="group relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition">
                        <?php if ($category['image']): ?>
                            <img src="/<?php echo htmlspecialchars($category['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                 class="w-full h-32 object-cover group-hover:scale-110 transition duration-300">
                        <?php else: ?>
                            <div class="w-full h-32 bg-pink-100 flex items-center justify-center">
                                <i class="fas <?php echo $category['icon'] ?? 'fa-map-marked-alt'; ?> text-4xl text-pink-500"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-50 transition flex items-center justify-center">
                            <h3 class="text-white font-semibold text-sm md:text-base">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Tours Section -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Featured Tours & Trips</h2>
                <p class="text-gray-600">Hand-picked adventures just for you</p>
            </div>
            <a href="/tours" class="hidden md:inline-block text-pink-600 font-semibold hover:text-pink-700 transition">
                View All <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($featured_tours && $featured_tours->num_rows > 0): ?>
                <?php while($tour = $featured_tours->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="relative">
                            <?php if ($tour['featured_image']): ?>
                                <img src="/<?php echo htmlspecialchars($tour['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($tour['title']); ?>" 
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-4xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($tour['is_exclusive']): ?>
                                <span class="absolute top-3 right-3 bg-pink-600 text-white text-xs px-3 py-1 rounded-full">
                                    Exclusive
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-5">
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt mr-2 text-pink-600"></i>
                                <span><?php echo htmlspecialchars($tour['location_name']); ?></span>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                <?php echo htmlspecialchars($tour['title']); ?>
                            </h3>
                            
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars(substr(strip_tags($tour['description']), 0, 100)); ?>...
                            </p>
                            
                            <div class="flex items-center justify-between mb-4">
                                <?php if ($tour['avg_rating']): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm font-semibold"><?php echo number_format($tour['avg_rating'], 1); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($tour['duration']): ?>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-clock mr-2"></i>
                                        <span><?php echo htmlspecialchars($tour['duration']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <?php if ($tour['price']): ?>
                                        <span class="text-xl font-bold text-pink-600">
                                            <?php echo formatPrice($tour['price']); ?>
                                        </span>
                                        <span class="text-sm text-gray-600">/person</span>
                                    <?php elseif ($tour['price_min'] && $tour['price_max']): ?>
                                        <span class="text-lg font-bold text-pink-600">
                                            <?php echo formatPrice($tour['price_min']); ?> - <?php echo formatPrice($tour['price_max']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="/tours/<?php echo $tour['slug']; ?>" 
                                   class="bg-pink-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-pink-700 transition">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-3 text-center py-12">
                    <p class="text-gray-600">No featured tours available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-8 md:hidden">
            <a href="/tours" class="btn-primary inline-block">
                View All Tours
            </a>
        </div>
    </div>
</section>

<!-- Activities Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-900 mb-3">Popular Activities</h2>
            <p class="text-gray-600">What would you like to do?</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php if ($activities && $activities->num_rows > 0): ?>
                <?php while($activity = $activities->fetch_assoc()): ?>
                    <a href="/activity/<?php echo $activity['slug']; ?>" 
                       class="group bg-white rounded-lg border border-gray-200 p-4 hover:border-pink-600 hover:shadow-md transition">
                        <?php if ($activity['image']): ?>
                            <img src="/<?php echo htmlspecialchars($activity['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($activity['name']); ?>" 
                                 class="w-full h-24 object-cover rounded-md mb-3">
                        <?php else: ?>
                            <div class="w-full h-24 bg-pink-50 rounded-md mb-3 flex items-center justify-center">
                                <i class="fas <?php echo $activity['icon'] ?? 'fa-hiking'; ?> text-3xl text-pink-600"></i>
                            </div>
                        <?php endif; ?>
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-pink-600 transition text-center">
                            <?php echo htmlspecialchars($activity['name']); ?>
                        </h3>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Popular Locations Section -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-900 mb-3">Popular Destinations</h2>
            <p class="text-gray-600">Explore amazing places</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if ($popular_locations && $popular_locations->num_rows > 0): ?>
                <?php while($location = $popular_locations->fetch_assoc()): ?>
                    <a href="/location/<?php echo $location['slug']; ?>" 
                       class="group relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition h-64">
                        <?php if ($location['image']): ?>
                            <img src="/<?php echo htmlspecialchars($location['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($location['name']); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php else: ?>
                            <div class="w-full h-full bg-pink-200"></div>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-black bg-opacity-30 group-hover:bg-opacity-40 transition flex items-end">
                            <div class="p-6 w-full">
                                <h3 class="text-white text-2xl font-bold mb-2">
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </h3>
                                <?php if ($location['county']): ?>
                                    <p class="text-white text-sm">
                                        <i class="fas fa-map-pin mr-2"></i><?php echo htmlspecialchars($location['county']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Upcoming Events -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Upcoming Events</h2>
                <p class="text-gray-600">Don't miss out on these amazing experiences</p>
            </div>
            <a href="/upcoming" class="hidden md:inline-block text-pink-600 font-semibold hover:text-pink-700 transition">
                View All <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if ($upcoming_tours && $upcoming_tours->num_rows > 0): ?>
                <?php while($upcoming = $upcoming_tours->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition">
                        <?php if ($upcoming['featured_image']): ?>
                            <img src="/<?php echo htmlspecialchars($upcoming['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($upcoming['title']); ?>" 
                                 class="w-full h-40 object-cover">
                        <?php else: ?>
                            <div class="w-full h-40 bg-gray-200"></div>
                        <?php endif; ?>
                        
                        <div class="p-4">
                            <div class="bg-pink-100 text-pink-700 text-xs font-semibold px-3 py-1 rounded-full inline-block mb-3">
                                <?php echo date('M d, Y', strtotime($upcoming['start_date'])); ?>
                            </div>
                            
                            <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                                <?php echo htmlspecialchars($upcoming['title']); ?>
                            </h3>
                            
                            <p class="text-sm text-gray-600 mb-3">
                                <i class="fas fa-map-marker-alt text-pink-600 mr-1"></i>
                                <?php echo htmlspecialchars($upcoming['location_name']); ?>
                            </p>
                            
                            <a href="/tours/<?php echo $upcoming['slug']; ?>" 
                               class="text-pink-600 font-medium text-sm hover:text-pink-700 transition">
                                Learn More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-pink-600">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
            Ready for Your Next Adventure?
        </h2>
        <p class="text-pink-100 text-lg mb-8">
            Join thousands of women who have experienced unforgettable journeys with us
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/tours" class="bg-white text-pink-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Browse Tours
            </a>
            <a href="/register" class="bg-pink-700 text-white px-8 py-3 rounded-lg font-semibold hover:bg-pink-800 transition">
                Join Now
            </a>
        </div>
    </div>
</section>

<script>
let slideIndex = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.slide-dot');

function showSlides() {
    slides.forEach((slide, index) => {
        slide.classList.remove('active');
        if (dots[index]) dots[index].classList.remove('bg-opacity-100');
    });
    
    slideIndex++;
    if (slideIndex > slides.length) slideIndex = 1;
    
    if (slides[slideIndex - 1]) {
        slides[slideIndex - 1].classList.add('active');
        if (dots[slideIndex - 1]) dots[slideIndex - 1].classList.add('bg-opacity-100');
    }
    
    setTimeout(showSlides, 5000);
}

function changeSlide(n) {
    slideIndex += n;
    if (slideIndex > slides.length) slideIndex = 1;
    if (slideIndex < 1) slideIndex = slides.length;
    currentSlide(slideIndex - 1);
}

function currentSlide(n) {
    slideIndex = n;
    slides.forEach((slide, index) => {
        slide.classList.remove('active');
        if (dots[index]) dots[index].classList.remove('bg-opacity-100');
    });
    
    if (slides[n]) {
        slides[n].classList.add('active');
        if (dots[n]) dots[n].classList.add('bg-opacity-100');
    }
}

if (slides.length > 0) {
    showSlides();
}
</script>

<?php include 'includes/footer.php'; ?> if ($sliders && $sliders->num_rows > 0): ?>
        <?php $slide_num = 0; ?>
        <?php while($slider = $sliders->fetch_assoc()): ?>
            <div class="slide <?php echo $slide_num === 0 ? 'active' : ''; ?> absolute inset-0">
                <div class="relative h-full">
                    <img src="/<?php echo htmlspecialchars($slider['image']); ?>" 
                         alt="<?php echo htmlspecialchars($slider['title']); ?>" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <div class="text-center text-white px-4 max-w-4xl">
                            <h1 class="text-4xl md:text-6xl font-bold mb-4">
                                <?php echo htmlspecialchars($slider['title']); ?>
                            </h1>
                            <?php if ($slider['description']): ?>
                                <p class="text-lg md:text-xl mb-8">
                                    <?php echo htmlspecialchars($slider['description']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($slider['link_url']): ?>
                                <a href="<?php echo htmlspecialchars($slider['link_url']); ?>" 
                                   class="btn-primary inline-block">
                                    <?php echo htmlspecialchars($slider['link_text'] ?? 'Explore Now'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php $slide_num++; ?>
        <?php endwhile; ?>
        
        <!-- Slider Controls -->
        <?php if ($sliders->num_rows > 1): ?>
            <button onclick="changeSlide(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-75 rounded-full p-3 transition">
                <i class="fas fa-chevron-left text-gray-800"></i>
            </button>
            <button onclick="changeSlide(1)" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-75 rounded-full p-3 transition">
                <i class="fas fa-chevron-right text-gray-800"></i>
            </button>
            
            <!-- Dots -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex space-x-2">
                <?php for($i = 0; $i < $sliders->num_rows; $i++): ?>
                    <button onclick="currentSlide(<?php echo $i; ?>)" 
                            class="slide-dot w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100 transition"></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Default Hero -->
        <div class="relative h-full bg-pink-600">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center text-white px-4 max-w-4xl">
                    <h1 class="text-4xl md:text-6xl font-bold mb-4">
                        Welcome to Girls Trip
                    </h1>
                    <p class="text-lg md:text-xl mb-8">
                        Unforgettable Adventures Designed for Women
                    </p>
                    <a href="/tours" class="btn-primary inline-block">
                        Explore Tours
                    </a>
                </div>
            </div>
        </div>
    <?php