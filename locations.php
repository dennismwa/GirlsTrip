<?php
// locations.php
require_once 'config.php';

$page_title = 'Locations - Girls Trip';

$locations_query = "SELECT l.*, 
                    (SELECT COUNT(*) FROM tours t WHERE t.location_id = l.id AND t.status = 'published') as tour_count
                    FROM locations l
                    WHERE l.status = 'active'
                    ORDER BY l.is_popular DESC, l.name ASC";
$locations = $conn->query($locations_query);

include 'includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Explore Destinations</h1>
        <p class="text-pink-100">Discover amazing places to visit</p>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($locations && $locations->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($location = $locations->fetch_assoc()): ?>
                    <a href="/location/<?php echo $location['slug']; ?>" 
                       class="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="relative h-56">
                            <?php if ($location['image']): ?>
                                <img src="/<?php echo htmlspecialchars($location['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($location['name']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <?php else: ?>
                                <div class="w-full h-full bg-pink-200 flex items-center justify-center">
                                    <i class="fas fa-map-marker-alt text-6xl text-pink-400"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="absolute inset-0 bg-black bg-opacity-30 group-hover:bg-opacity-40 transition"></div>
                            
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-1">
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </h3>
                                <?php if ($location['county']): ?>
                                    <p class="text-sm opacity-90">
                                        <i class="fas fa-map-pin mr-1"></i>
                                        <?php echo htmlspecialchars($location['county']); ?>, <?php echo htmlspecialchars($location['country']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="text-sm mt-2">
                                    <?php echo $location['tour_count']; ?> <?php echo $location['tour_count'] == 1 ? 'tour' : 'tours'; ?> available
                                </p>
                            </div>
                            
                            <?php if ($location['is_popular']): ?>
                                <span class="absolute top-3 right-3 bg-yellow-500 text-white text-xs px-3 py-1 rounded-full font-semibold">
                                    Popular
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-lg shadow-md">
                <i class="fas fa-map-marked-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Locations Available</h3>
                <p class="text-gray-600">Check back soon for amazing destinations</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<!-- END locations.php -->
