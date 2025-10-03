<?php
// blog-post.php
require_once 'config.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    redirect('/blog');
}

$post_query = "SELECT bp.*, u.full_name as author_name
               FROM blog_posts bp
               JOIN users u ON bp.author_id = u.id
               WHERE bp.slug = '$slug' AND bp.status = 'published'";
$result = $conn->query($post_query);

if (!$result || $result->num_rows === 0) {
    redirect('/blog');
}

$post = $result->fetch_assoc();

// Update views
$conn->query("UPDATE blog_posts SET views = views + 1 WHERE id = " . $post['id']);

// Get related posts
$related_query = "SELECT * FROM blog_posts 
                  WHERE id != " . $post['id'] . " AND status = 'published'
                  ORDER BY RAND() LIMIT 3";
$related_posts = $conn->query($related_query);

$page_title = $post['meta_title'] ?: $post['title'] . ' - Blog - Girls Trip';
$meta_description = $post['meta_description'] ?: $post['excerpt'];
$meta_keywords = $post['meta_keywords'] ?: '';

include 'includes/header.php';
?>

<article class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Post Header -->
        <header class="mb-8">
            <div class="flex items-center gap-4 text-sm text-gray-600 mb-4">
                <span><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($post['author_name']); ?></span>
                <span><i class="fas fa-calendar mr-2"></i><?php echo date('F d, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?></span>
                <span><i class="fas fa-eye mr-2"></i><?php echo $post['views']; ?> views</span>
            </div>
            
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
            
            <?php if ($post['excerpt']): ?>
                <p class="text-xl text-gray-600">
                    <?php echo htmlspecialchars($post['excerpt']); ?>
                </p>
            <?php endif; ?>
        </header>
        
        <!-- Featured Image -->
        <?php if ($post['featured_image']): ?>
            <div class="mb-8 rounded-lg overflow-hidden">
                <img src="/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($post['title']); ?>" 
                     class="w-full h-auto object-cover">
            </div>
        <?php endif; ?>
        
        <!-- Post Content -->
        <div class="prose prose-lg max-w-none mb-12">
            <?php echo nl2br($post['content']); ?>
        </div>
        
        <!-- Share Buttons -->
        <div class="border-t border-b border-gray-200 py-6 mb-12">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this post</h3>
            <div class="flex gap-3">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/blog/' . $post['slug']); ?>" 
                   target="_blank"
                   class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fab fa-facebook-f mr-2"></i>Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/blog/' . $post['slug']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                   target="_blank"
                   class="bg-blue-400 text-white px-6 py-2 rounded-lg hover:bg-blue-500 transition">
                    <i class="fab fa-twitter mr-2"></i>Twitter
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' ' . SITE_URL . '/blog/' . $post['slug']); ?>" 
                   target="_blank"
                   class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fab fa-whatsapp mr-2"></i>WhatsApp
                </a>
            </div>
        </div>
    </div>
</article>

<!-- Related Posts -->
<?php if ($related_posts && $related_posts->num_rows > 0): ?>
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Posts</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php while($related = $related_posts->fetch_assoc()): ?>
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <?php if ($related['featured_image']): ?>
                            <img src="/<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                 class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200"></div>
                        <?php endif; ?>
                        
                        <div class="p-5">
                            <p class="text-sm text-gray-600 mb-2">
                                <?php echo date('F d, Y', strtotime($related['published_at'] ?? $related['created_at'])); ?>
                            </p>
                            
                            <h3 class="text-lg font-bold text-gray-900 mb-3 line-clamp-2">
                                <?php echo htmlspecialchars($related['title']); ?>
                            </h3>
                            
                            <a href="/blog/<?php echo $related['slug']; ?>" 
                               class="text-pink-600 font-medium hover:text-pink-700 transition">
                                Read More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>