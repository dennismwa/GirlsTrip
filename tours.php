<?php
require_once 'config.php';

$page_title = 'Tours - Girls Trip';
$meta_description = 'Browse our collection of amazing tours and adventures designed for women.';

// Get filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$location = isset($_GET['location']) ? sanitize($_GET['location']) : '';
$activity = isset($_GET['activity']) ? sanitize($_GET['activity']) : '';
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
$price_min = isset($_GET['price_min']) ? intval($_GET['price_min']) : 0;
$price_max = isset($_GET['price_max']) ? intval($_GET['price_max']) : 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;

// Build query
$where = ["t.status = 'published'", "t.type IN ('tour', 'trip')"];

if ($search) {
    $where[] = "(t.title LIKE '%$search%' OR t.description LIKE '%$search%')";
}

if ($category) {
    $where[] = "c.slug = '$category'";
}

if ($location) {
    $where[] = "l.slug = '$location'";
}

if ($activity) {
    $where[] = "EXISTS (SELECT 1 FROM tour_activities ta 
                        JOIN activities a ON ta.activity_id = a.id 
                        WHERE ta.tour_id = t.id AND a.slug = '$activity')";
}

if ($date_from) {
    $where[] = "t.start_date >= '$date_from'";
}

if ($date_to) {
    $where[] = "t.start_date <= '$date_to'";
}

if ($price_min > 0) {
    $where[] = "(t.price >= $price_min OR t.price_min >= $price_min)";
}

if ($price_max > 0) {
    $where[] = "(t.price <= $price_max OR t.price_max <= $price_max)";
}

$where_clause = implode(' AND ', $where);

$base_query = "SELECT t.*, l.name as location_name, l.slug as location_slug, 
               c.name as category_name, c.slug as category_slug,
               (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id AND status = 'approved') as avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE tour_id = t.id AND status = 'approved') as review_count
               FROM tours t 
               LEFT JOIN locations l ON t.location_id = l.id
               LEFT JOIN categories c ON t.category_id = c.id
               WHERE $where_clause
               ORDER BY t.is_featured DESC, t.created_at DESC";

$result = paginate($base_query, $page, $per_page);

// Get filter options
$categories = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
$locations = $conn->query("SELECT * FROM locations WHERE status = 'active' ORDER BY name ASC");
$activities = $conn->query("SELECT * FROM activities WHERE status = 'active' ORDER BY name ASC");

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Tours & Trips</h1>
        <p class="text-pink-100">Discover your next adventure</p>
    </div>
</section>

<!-- Filters Section -->
<section class="bg-white border-b sticky top-16 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <form method="GET" action="/tours" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search tours..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                
                <!-- Category -->
                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['slug']; ?>" <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <!-- Location -->
                <select name="location" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <option value="">All Locations</option>
                    <?php while($loc = $locations->fetch_assoc()): ?>
                        <option value="<?php echo $loc['slug']; ?>" <?php echo $location === $loc['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loc['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <!-- Activity -->
                <select name="activity" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <option value="">All Activities</option>
                    <?php while($act = $activities->fetch_assoc()): ?>
                        <option value="<?php echo $act['slug']; ?>" <?php echo $activity === $act['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($act['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Additional Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" 
                       placeholder="From Date" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" 
                       placeholder="To Date" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                
                <input type="number" name="price_min" value="<?php echo $price_min > 0 ? $price_min : ''; ?>" 
                       placeholder="Min Price (KES)" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                
                <input type="number" name="price_max" value="<?php echo $price_max > 0 ? $price_max : ''; ?>" 
                       placeholder="Max Price (KES)" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-pink-700 transition">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <a href="/tours" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-300 transition">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</section>

<!-- Tours Grid -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($result['data'] && $result['data']->num_rows > 0): ?>
            <div class="mb-6 flex justify-between items-center">
                <p class="text-gray-600">
                    Showing <?php echo (($page - 1) * $per_page) + 1; ?> - 
                    <?php echo min($page * $per_page, $result['total']); ?> 
                    of <?php echo $result['total']; ?> tours
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($tour = $result['data']->fetch_assoc()): ?>
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
                            
                            <?php if ($tour['is_featured']): ?>
                                <span class="absolute top-3 left-3 bg-yellow-500 text-white text-xs px-3 py-1 rounded-full">
                                    <i class="fas fa-star mr-1"></i>Featured
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
                                        <span class="text-xs text-gray-500 ml-1">(<?php echo $tour['review_count']; ?>)</span>
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
            </div>
            
            <!-- Pagination -->
            <?php if ($result['total_pages'] > 1): ?>
                <div class="mt-8 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $location ? '&location=' . $location : ''; ?>" 
                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $result['total_pages']; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="px-4 py-2 bg-pink-600 text-white rounded-lg font-medium">
                                    <?php echo $i; ?>
                                </span>
                            <?php elseif ($i == 1 || $i == $result['total_pages'] || abs($i - $page) <= 2): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $location ? '&location=' . $location : ''; ?>" 
                                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif (abs($i - $page) == 3): ?>
                                <span class="px-2">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $result['total_pages']): ?>
                            <a href="?page=<?php echo ($page + 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $location ? '&location=' . $location : ''; ?>" 
                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Tours Found</h3>
                <p class="text-gray-600 mb-6">Try adjusting your filters or search criteria</p>
                <a href="/tours" class="btn-primary inline-block">
                    View All Tours
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>