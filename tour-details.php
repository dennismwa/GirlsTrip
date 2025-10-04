<?php
// tour-details.php
require_once 'config.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    redirect('/tours');
}

// Fetch tour details
$query = "SELECT t.*, l.name as location_name, l.slug as location_slug, 
          c.name as category_name, c.slug as category_slug,
          u.full_name as vendor_name,
          (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id AND status = 'approved') as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE tour_id = t.id AND status = 'approved') as review_count
          FROM tours t 
          LEFT JOIN locations l ON t.location_id = l.id
          LEFT JOIN categories c ON t.category_id = c.id
          LEFT JOIN users u ON t.vendor_id = u.id
          WHERE t.slug = '$slug' AND t.status = 'published'";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    redirect('/tours');
}

$tour = $result->fetch_assoc();

// Update views
$conn->query("UPDATE tours SET views = views + 1 WHERE id = " . $tour['id']);

// Get tour images
$images_query = "SELECT * FROM tour_images WHERE tour_id = " . $tour['id'] . " ORDER BY display_order ASC";
$images = $conn->query($images_query);

// Get tour activities
$activities_query = "SELECT a.* FROM activities a
                     JOIN tour_activities ta ON a.id = ta.activity_id
                     WHERE ta.tour_id = " . $tour['id'];
$activities = $conn->query($activities_query);

// Get reviews
$reviews_query = "SELECT r.*, u.full_name FROM reviews r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.tour_id = " . $tour['id'] . " AND r.status = 'approved'
                  ORDER BY r.created_at DESC LIMIT 10";
$reviews = $conn->query($reviews_query);

$page_title = $tour['meta_title'] ?: $tour['title'] . ' - Girls Trip';
$meta_description = $tour['meta_description'] ?: substr(strip_tags($tour['description']), 0, 160);

include 'includes/header.php';
?>

<!-- Tour Details -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Image Gallery -->
                <div class="mb-8">
                    <?php if ($tour['featured_image']): ?>
                        <img src="/<?php echo htmlspecialchars($tour['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($tour['title']); ?>" 
                             class="w-full h-96 object-cover rounded-lg shadow-md mb-4">
                    <?php endif; ?>
                    
                    <?php if ($images && $images->num_rows > 0): ?>
                        <div class="grid grid-cols-4 gap-2">
                            <?php while($image = $images->fetch_assoc()): ?>
                                <img src="/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($image['caption']); ?>" 
                                     class="w-full h-24 object-cover rounded cursor-pointer hover:opacity-75 transition">
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Title and Basic Info -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="bg-pink-100 text-pink-700 px-3 py-1 rounded-full text-sm font-semibold">
                            <?php echo ucfirst($tour['type']); ?>
                        </span>
                        <?php if ($tour['is_featured']): ?>
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-star mr-1"></i>Featured
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo htmlspecialchars($tour['title']); ?>
                    </h1>
                    
                    <div class="flex flex-wrap items-center gap-4 text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-pink-600 mr-2"></i>
                            <a href="/location/<?php echo $tour['location_slug']; ?>" class="hover:text-pink-600">
                                <?php echo htmlspecialchars($tour['location_name']); ?>
                            </a>
                        </div>
                        
                        <?php if ($tour['avg_rating']): ?>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 mr-2"></i>
                                <span class="font-semibold"><?php echo number_format($tour['avg_rating'], 1); ?></span>
                                <span class="text-sm ml-1">(<?php echo $tour['review_count']; ?> reviews)</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($tour['duration']): ?>
                            <div class="flex items-center">
                                <i class="fas fa-clock text-pink-600 mr-2"></i>
                                <span><?php echo htmlspecialchars($tour['duration']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="prose max-w-none mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Experience</h2>
                    <?php echo nl2br($tour['description']); ?>
                </div>
                
                <!-- Activities -->
                <?php if ($activities && $activities->num_rows > 0): ?>
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Activities Included</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php while($activity = $activities->fetch_assoc()): ?>
                                <div class="flex items-center bg-gray-50 p-3 rounded-lg">
                                    <i class="<?php echo $activity['icon'] ?? 'fas fa-check'; ?> text-pink-600 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($activity['name']); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Reviews -->
                <?php if ($reviews && $reviews->num_rows > 0): ?>
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Reviews</h2>
                        <div class="space-y-4">
                            <?php while($review = $reviews->fetch_assoc()): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($review['full_name']); ?></p>
                                            <div class="flex items-center">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?> text-sm"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo timeAgo($review['created_at']); ?></span>
                                    </div>
                                    <?php if ($review['title']): ?>
                                        <p class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($review['title']); ?></p>
                                    <?php endif; ?>
                                    <p class="text-gray-700"><?php echo htmlspecialchars($review['comment']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Booking Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white border-2 border-gray-200 rounded-lg p-6 sticky top-24">
                    <!-- Price -->
                    <div class="mb-6">
                        <?php if ($tour['price']): ?>
                            <div class="flex items-baseline">
                                <span class="text-3xl font-bold text-pink-600"><?php echo formatPrice($tour['price']); ?></span>
                                <span class="text-gray-600 ml-2">/person</span>
                            </div>
                        <?php elseif ($tour['price_min'] && $tour['price_max']): ?>
                            <div class="flex items-baseline">
                                <span class="text-2xl font-bold text-pink-600">
                                    <?php echo formatPrice($tour['price_min']); ?> - <?php echo formatPrice($tour['price_max']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($tour['child_price']): ?>
                            <p class="text-sm text-gray-600 mt-2">
                                Children: <?php echo formatPrice($tour['child_price']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Info -->
                    <div class="space-y-3 mb-6 pb-6 border-b">
                        <?php if ($tour['start_date']): ?>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-calendar text-pink-600 w-6"></i>
                                <span class="text-gray-700">
                                    <?php echo date('F d, Y', strtotime($tour['start_date'])); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($tour['max_participants']): ?>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-users text-pink-600 w-6"></i>
                                <span class="text-gray-700">Max <?php echo $tour['max_participants']; ?> people</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($tour['allow_installments']): ?>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-credit-card text-pink-600 w-6"></i>
                                <span class="text-gray-700">Flexible payment options</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Booking Button -->
                    <a href="/booking?id=<?php echo $tour['id']; ?>" 
                       class="w-full bg-pink-600 text-white text-center px-6 py-3 rounded-lg font-semibold hover:bg-pink-700 transition block mb-3">
                        <i class="fas fa-ticket-alt mr-2"></i>Book Now
                    </a>
                    
                    <!-- Share -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-2">Share this tour</p>
                        <div class="flex justify-center gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/tours/' . $tour['slug']); ?>" 
                               target="_blank" class="text-blue-600 hover:text-blue-700">
                                <i class="fab fa-facebook text-2xl"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/tours/' . $tour['slug']); ?>&text=<?php echo urlencode($tour['title']); ?>" 
                               target="_blank" class="text-blue-400 hover:text-blue-500">
                                <i class="fab fa-twitter text-2xl"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($tour['title'] . ' ' . SITE_URL . '/tours/' . $tour['slug']); ?>" 
                               target="_blank" class="text-green-600 hover:text-green-700">
                                <i class="fab fa-whatsapp text-2xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
