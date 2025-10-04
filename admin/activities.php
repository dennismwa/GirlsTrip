<?php
// admin/activities.php - Activity Management
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/login');
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = createSlug($_POST['slug'] ?: $name);
    $description = sanitize($_POST['description']);
    $icon = sanitize($_POST['icon']);
    $status = sanitize($_POST['status']);
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_result = uploadImage($_FILES['image'], 'activities');
        if ($upload_result['success']) {
            $image = $upload_result['path'];
        }
    }
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = "UPDATE activities SET name = '$name', slug = '$slug', description = '$description',
                  icon = '$icon', status = '$status'";
        if ($image) {
            $query .= ", image = '$image'";
        }
        $query .= " WHERE id = $id";
        $conn->query($query);
        setMessage('Activity updated successfully', 'success');
    } else {
        $query = "INSERT INTO activities (name, slug, description, image, icon, status)
                  VALUES ('$name', '$slug', '$description', " . ($image ? "'$image'" : "NULL") . ", '$icon', '$status')";
        $conn->query($query);
        setMessage('Activity added successfully', 'success');
    }
    
    redirect('/admin/activities');
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM activities WHERE id = $id");
    setMessage('Activity deleted successfully', 'success');
    redirect('/admin/activities');
}

$activities = $conn->query("SELECT * FROM activities ORDER BY name ASC");
$edit_activity = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM activities WHERE id = $edit_id");
    $edit_activity = $edit_result->fetch_assoc();
}

$page_title = 'Manage Activities - Admin';
$page_heading = 'Manage Activities';

include 'includes/admin-header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <?php echo $edit_activity ? 'Edit Activity' : 'Add New Activity'; ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_activity): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_activity['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Activity Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="<?php echo $edit_activity['name'] ?? ''; ?>" required 
                           placeholder="e.g., Hiking, Swimming"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                    <input type="text" name="slug" value="<?php echo $edit_activity['slug'] ?? ''; ?>" 
                           placeholder="Auto-generated from name"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" 
                              placeholder="Brief description of this activity..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"><?php echo $edit_activity['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon Class</label>
                    <input type="text" name="icon" value="<?php echo $edit_activity['icon'] ?? 'fas fa-hiking'; ?>" 
                           placeholder="fas fa-hiking"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">
                        FontAwesome icon class 
                        <a href="https://fontawesome.com/icons" target="_blank" class="text-pink-600 hover:underline">Browse icons</a>
                    </p>
                    <?php if ($edit_activity && $edit_activity['icon']): ?>
                        <div class="mt-2 p-3 bg-gray-50 rounded text-center">
                            <i class="<?php echo $edit_activity['icon']; ?> text-4xl text-pink-600"></i>
                            <p class="text-xs text-gray-600 mt-1">Current Icon</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                    <?php if ($edit_activity && $edit_activity['image']): ?>
                        <div class="mb-2">
                            <img src="/<?php echo $edit_activity['image']; ?>" alt="Current image" class="w-full h-32 object-cover rounded">
                            <p class="text-xs text-gray-500 mt-1">Current image</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF (Max 5MB)</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="active" <?php echo ($edit_activity['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_activity['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" 
                            class="flex-1 bg-pink-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-pink-700 transition">
                        <i class="fas fa-save mr-2"></i><?php echo $edit_activity ? 'Update' : 'Add'; ?>
                    </button>
                    <?php if ($edit_activity): ?>
                        <a href="/admin/activities" 
                           class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-50 border-b">
                <h3 class="font-semibold text-gray-900">All Activities</h3>
            </div>
            
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Icon</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Name</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Slug</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($activities && $activities->num_rows > 0): ?>
                        <?php while($activity = $activities->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <?php if ($activity['icon']): ?>
                                        <i class="<?php echo $activity['icon']; ?> text-2xl text-pink-600"></i>
                                    <?php else: ?>
                                        <i class="fas fa-question text-2xl text-gray-300"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <?php if ($activity['image']): ?>
                                            <img src="/<?php echo $activity['image']; ?>" alt="" class="w-10 h-10 rounded object-cover mr-3">
                                        <?php endif; ?>
                                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($activity['name']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-700">
                                    <code class="bg-gray-100 px-2 py-1 rounded text-xs"><?php echo $activity['slug']; ?></code>
                                </td>
                                <td class="py-3 px-4">
                                    <?php
                                    $color = $activity['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                        <?php echo ucfirst($activity['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="/admin/activities?edit=<?php echo $activity['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-700" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/activities?delete=<?php echo $activity['id']; ?>" 
                                           onclick="return confirm('Delete this activity? This will remove it from all tours.')"
                                           class="text-red-600 hover:text-red-700" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-500">
                                <i class="fas fa-hiking text-4xl text-gray-300 mb-3"></i>
                                <p>No activities found. Add your first activity above.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
