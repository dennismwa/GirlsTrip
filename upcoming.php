<?php
// upcoming.php - Upcoming Events
require_once 'config.php';

$page_title = 'Upcoming Events - Girls Trip';

$query = "SELECT t.*, l.name as location_name, c.name as category_name,
          (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id AND status = 'approved') as avg_rating
          FROM tours t 
          LEFT JOIN locations l ON t.location_id = l.id
          LEFT JOIN categories c ON t.category_id = c.id
          WHERE t.status = 'published' AND t.start_date >= CURDATE()
          ORDER BY t.start_date ASC";
$upcoming = $conn->query($query);

include 'includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Upcoming Events</h1>
        <p class="text-pink-100">Don't miss out on these amazing experiences</p>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($upcoming && $upcoming->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($event = $upcoming->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="relative">
                            <?php if ($event['featured_image']): ?>
                                <img src="/<?php echo htmlspecialchars($event['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200"></div>
                            <?php endif; ?>
                            
                            <div class="absolute top-3 left-3 bg-pink-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <?php echo date('M d', strtotime($event['start_date'])); ?>
                            </div>
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
                            
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <span><?php echo date('F d, Y', strtotime($event['start_date'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <?php if ($event['price']): ?>
                                    <span class="text-xl font-bold text-pink-600">
                                        <?php echo formatPrice($event['price']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <a href="/tours/<?php echo $event['slug']; ?>" 
                                   class="bg-pink-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-pink-700 transition">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Upcoming Events</h3>
                <p class="text-gray-600 mb-6">Check back soon or explore our other tours</p>
                <a href="/tours" class="btn-primary inline-block">
                    Browse All Tours
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<!-- END upcoming.php -->
