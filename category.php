<?php
// category.php
require_once 'config.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    redirect('/tours');
}

$category_query = "SELECT * FROM categories WHERE slug = '$slug' AND status = 'active'";
$category_result = $conn->query($category_query);

if (!$category_result || $category_result->num_rows === 0) {
    redirect('/tours');
}

$category = $category_result->fetch_assoc();

$tours_query = "SELECT t.*, l.name as location_name,
                (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id AND status = 'approved') as avg_rating
                FROM tours t
                LEFT JOIN locations l ON t.location_id = l.id
                WHERE t.category_id = " . $category['id'] . " AND t.status = 'published'
                ORDER BY t.is_featured DESC, t.created_at DESC";
$tours = $conn->query($tours_query);

$page_title = $category['name'] . ' - Categories - Girls Trip';
$meta_description = $category['description'] ?? 'Explore ' . $category['name'] . ' tours and experiences';

include 'includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 mb-4">
            <?php if ($category['icon']): ?>
                <i class="<?php echo $category['icon']; ?> text-5xl text-white"></i>
            <?php endif; ?>
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                    <?php echo htmlspecialchars($category['name']); ?>
                </h1>
                <?php if ($category['description']): ?>
                    <p class="text-pink-100"><?php echo htmlspecialchars($category['description']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                <i class="fas fa-th-large text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Tours in This Category</h3>
                <p class="text-gray-600 mb-6">Check back soon or explore other categories</p>
                <a href="/tours" class="btn-primary inline-block">
                    Browse All Tours
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>