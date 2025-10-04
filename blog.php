<?php
// blog.php
require_once 'config.php';

$page_title = 'Blog - Girls Trip';
$meta_description = 'Read travel stories, tips, and inspiration from Girls Trip';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;

$base_query = "SELECT bp.*, u.full_name as author_name
               FROM blog_posts bp
               JOIN users u ON bp.author_id = u.id
               WHERE bp.status = 'published'
               ORDER BY bp.published_at DESC, bp.created_at DESC";

$result = paginate($base_query, $page, $per_page);

include 'includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Blog</h1>
        <p class="text-pink-100">Travel stories, tips, and inspiration</p>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($result['data'] && $result['data']->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($post = $result['data']->fetch_assoc()): ?>
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <?php if ($post['featured_image']): ?>
                            <img src="/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                 class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200"></div>
                        <?php endif; ?>
                        
                        <div class="p-5">
                            <div class="flex items-center text-sm text-gray-600 mb-3">
                                <span><?php echo date('F d, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?></span>
                                <span class="mx-2">•</span>
                                <span><?php echo htmlspecialchars($post['author_name']); ?></span>
                            </div>
                            
                            <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </h2>
                            
                            <?php if ($post['excerpt']): ?>
                                <p class="text-gray-600 mb-4 line-clamp-3">
                                    <?php echo htmlspecialchars($post['excerpt']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <a href="/blog/<?php echo $post['slug']; ?>" 
                               class="text-pink-600 font-medium hover:text-pink-700 transition">
                                Read More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($result['total_pages'] > 1): ?>
                <div class="mt-8 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>" 
                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $result['total_pages']; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="px-4 py-2 bg-pink-600 text-white rounded-lg"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>" 
                                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $result['total_pages']): ?>
                            <a href="?page=<?php echo ($page + 1); ?>" 
                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-lg shadow-md">
                <i class="fas fa-blog text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Blog Posts Yet</h3>
                <p class="text-gray-600">Check back soon for travel stories and tips</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
