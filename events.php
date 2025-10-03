<?php
// events.php
require_once 'config.php';

$page_title = 'Events - Girls Trip';
$meta_description = 'Browse our collection of exciting events and experiences designed for women.';

// Get filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$location = isset($_GET['location']) ? sanitize($_GET['location']) : '';
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;

// Build query
$where = ["t.status = 'published'", "t.type = 'event'"];

if ($search) {
    $where[] = "(t.title LIKE '%$search%' OR t.description LIKE '%$search%')";
}

if ($location) {
    $where[] = "l.slug = '$location'";
}

if ($date_from) {
    $where[] = "t.start_date >= '$date_from'";
}

$where_clause = implode(' AND ', $where);

$base_query = "SELECT t.*, l.name as location_name, l.slug as location_slug,
               (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id AND status = 'approved') as avg_rating
               FROM tours t 
               LEFT JOIN locations l ON t.location_id = l.id
               WHERE $where_clause
               ORDER BY t.start_date ASC, t.created_at DESC";

$result = paginate($base_query, $page, $per_page);
$locations = $conn->query("SELECT * FROM locations WHERE status = 'active' ORDER BY name ASC");

include 'includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Events & Experiences</h1>
        <p class="text-pink-100">Unique events designed for amazing women</p>
    </div>
</section>

<section class="bg-white border-b sticky top-16 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <form method="GET" action="/events" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search events..." 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            
            <select name="location" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <option value="">All Locations</option>
                <?php while($loc = $locations->fetch_assoc()): ?>
                    <option value="<?php echo $loc['slug']; ?>" <?php echo $location === $loc['slug'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($loc['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-pink-700 transition">
                <i class="fas fa-search mr-2"></i>Search
            </button>
        </form>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($result['data'] && $result['data']->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($event = $result['data']->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="relative">
                            <?php if ($event['featured_image']): ?>
                                <img src="/<?php echo htmlspecialchars($event['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-5xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($event['start_date']): ?>
                                <div class="absolute top-3 left-3 bg-pink-600 text-white px-3 py-2 rounded-lg">
                                    <p class="text-xs font-semibold"><?php echo date('M', strtotime($event['start_date'])); ?></p>
                                    <p class="text-lg font-bold"><?php echo date('d', strtotime($event['start_date'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-5">
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt mr-2 text-pink-600"></i>
                                <span><?php echo htmlspecialchars($event['location_name']); ?></span>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                <?php echo htmlspecialchars($event['title']); ?>
                            </h3>
                            
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars(substr(strip_tags($event['description']), 0, 100)); ?>...
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <?php if ($event['price']): ?>
                                    <span class="text-xl font-bold text-pink-600">
                                        <?php echo formatPrice($event['price']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <a href="/events/<?php echo $event['slug']; ?>" 
                                   class="bg-pink-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-pink-700 transition">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Events Found</h3>
                <p class="text-gray-600">Check back soon for upcoming events</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<!-- END events.php -->

