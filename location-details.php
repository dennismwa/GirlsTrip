<?php
// location-details.php
require_once 'config.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    redirect('/locations');
}

$location_query = "SELECT * FROM locations WHERE slug = '$slug' AND status = 'active'";
$location_result = $conn->query($location_query);

if (!$location_result || $location_result->num_rows === 0) {
    redirect('/locations');
}

$location = $location_result->fetch_assoc();

$tours_query = "SELECT t.*, c.name as category_name,
                (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id AND status = 'approved') as avg_rating
                FROM tours t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.location_id = " . $location['id'] . " AND t.status = 'published'
                ORDER BY t.is_featured DESC, t.created_at DESC";
$tours = $conn->query($tours_query);

$page_title = $location['name'] . ' - Locations - Girls Trip';
$meta_description = $location['description'] ?? 'Explore tours and experiences in ' . $location['name'];

include 'includes/header.php';
?>

<section class="relative h-96">
    <?php if ($location['image']): ?>
        <img src="/<?php echo htmlspecialchars($location['image']); ?>" 
             alt="<?php echo htmlspecialchars($location['name']); ?>" 
             class="w-full h-full object-cover">
    <?php else: ?>
        <div class="w-full h-full bg-pink-600"></div>
    <?php endif; ?>
    
    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
        <div class="text-center text-white px-4">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <?php echo htmlspecialchars($location['name']); ?>
            </h1>
            <?php if ($location['county']): ?>
                <p class="text-xl text-pink-100">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    <?php echo htmlspecialchars($location['county']); ?>, <?php echo htmlspecialchars($location['country']); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($location['description']): ?>
<section class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose max-w-none">
            <?php echo nl2br(htmlspecialchars($location['description'])); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Available Tours in <?php echo htmlspecialchars($location['name']); ?></h2>
        
        <?php if ($tours && $tours->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($tour = $tours->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="relative">
                            <?php if ($tour['featured_image']): ?>
                                <img src="/<?php echo htmlspecialchars($tour['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($tour['title']); ?>" 
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200"></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                <?php echo htmlspecialchars($tour['title']); ?>
                            </h3>
                            
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars(substr(strip_tags($tour['description']), 0, 100)); ?>...
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <?php if ($tour['price']): ?>
                                    <span class="text-xl font-bold text-pink-600">
                                        <?php echo formatPrice($tour['price']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <a href="/tours/<?php echo $tour['slug']; ?>" 
                                   class="bg-pink-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-pink-700 transition">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-lg shadow-md">
                <i class="fas fa-map-marked-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Tours Available</h3>
                <p class="text-gray-600 mb-6">Check back soon for tours in this location</p>
                <a href="/tours" class="btn-primary inline-block">
                    Browse All Tours
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<!-- END location-details.php -->
