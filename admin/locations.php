<?php
// admin/locations.php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/login');
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = createSlug($_POST['slug'] ?: $name);
    $description = sanitize($_POST['description']);
    $county = sanitize($_POST['county']);
    $country = sanitize($_POST['country']);
    $latitude = sanitize($_POST['latitude']);
    $longitude = sanitize($_POST['longitude']);
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $status = sanitize($_POST['status']);
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_result = uploadImage($_FILES['image'], 'locations');
        if ($upload_result['success']) {
            $image = $upload_result['path'];
        }
    }
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = "UPDATE locations SET name = '$name', slug = '$slug', description = '$description',
                  county = '$county', country = '$country', latitude = " . ($latitude ? "'$latitude'" : "NULL") . ",
                  longitude = " . ($longitude ? "'$longitude'" : "NULL") . ", is_popular = $is_popular, status = '$status'";
        if ($image) {
            $query .= ", image = '$image'";
        }
        $query .= " WHERE id = $id";
        $conn->query($query);
        setMessage('Location updated successfully', 'success');
    } else {
        $query = "INSERT INTO locations (name, slug, description, image, county, country, latitude, longitude, is_popular, status)
                  VALUES ('$name', '$slug', '$description', " . ($image ? "'$image'" : "NULL") . ", 
                  '$county', '$country', " . ($latitude ? "'$latitude'" : "NULL") . ", 
                  " . ($longitude ? "'$longitude'" : "NULL") . ", $is_popular, '$status')";
        $conn->query($query);
        setMessage('Location added successfully', 'success');
    }
    
    redirect('/admin/locations');
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM locations WHERE id = $id");
    setMessage('Location deleted successfully', 'success');
    redirect('/admin/locations');
}

$locations = $conn->query("SELECT * FROM locations ORDER BY name ASC");
$edit_location = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM locations WHERE id = $edit_id");
    $edit_location = $edit_result->fetch_assoc();
}

$page_title = 'Manage Locations - Admin';
$page_heading = 'Manage Locations';

include 'includes/admin-header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <?php echo $edit_location ? 'Edit Location' : 'Add New Location'; ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_location): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_location['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" value="<?php echo $edit_location['name'] ?? ''; ?>" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                    <input type="text" name="slug" value="<?php echo $edit_location['slug'] ?? ''; ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"><?php echo $edit_location['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                    <input type="text" name="county" value="<?php echo $edit_location['county'] ?? ''; ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <input type="text" name="country" value="<?php echo $edit_location['country'] ?? 'Kenya'; ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                    <input type="file" name="image" accept="image/*" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_popular" value="1" 
                               <?php echo ($edit_location['is_popular'] ?? 0) ? 'checked' : ''; ?>
                               class="mr-2 text-pink-600 focus:ring-pink-500">
                        <span class="text-sm">Popular Location</span>
                    </label>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="active" <?php echo ($edit_location['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_location['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" 
                            class="flex-1 bg-pink-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-pink-700 transition">
                        <?php echo $edit_location ? 'Update' : 'Add'; ?>
                    </button>
                    <?php if ($edit_location): ?>
                        <a href="/admin/locations" 
                           class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Name</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">County</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Popular</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($locations && $locations->num_rows > 0): ?>
                        <?php while($location = $locations->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-900">
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-700">
                                    <?php echo htmlspecialchars($location['county']); ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($location['is_popular']): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Popular
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php
                                    $color = $location['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                        <?php echo ucfirst($location['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="/admin/locations?edit=<?php echo $location['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/locations?delete=<?php echo $location['id']; ?>" 
                                           onclick="return confirm('Delete this location?')"
                                           class="text-red-600 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-500">
                                No locations found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
<!-- END admin/locations.php -->

<?php
// admin/activities.php
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
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <?php echo $edit_activity ? 'Edit Activity' : 'Add New Activity'; ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_activity): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_activity['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" value="<?php echo $edit_activity['name'] ?? ''; ?>" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                    <input type="text" name="slug" value="<?php echo $edit_activity['slug'] ?? ''; ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"><?php echo $edit_activity['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon Class</label>
                    <input type="text" name="icon" value="<?php echo $edit_activity['icon'] ?? 'fas fa-hiking'; ?>" 
                           placeholder="fas fa-hiking"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Font Awesome icon class</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                    <input type="file" name="image" accept="image/*" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="active" <?php echo ($edit_activity['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_activity['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" 
                            class="flex-1 bg-pink-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-pink-700 transition">
                        <?php echo $edit_activity ? 'Update' : 'Add'; ?>
                    </button>
                    <?php if ($edit_activity): ?>
                        <a href="/admin/activities" 
                           class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
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
                                    <div class="flex items-center">
                                        <?php if ($activity['icon']): ?>
                                            <i class="<?php echo $activity['icon']; ?> text-pink-600 mr-3"></i>
                                        <?php endif; ?>
                                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($activity['name']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-700">
                                    <?php echo $activity['slug']; ?>
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
                                           class="text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/activities?delete=<?php echo $activity['id']; ?>" 
                                           onclick="return confirm('Delete this activity?')"
                                           class="text-red-600 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-12 text-center text-gray-500">
                                No activities found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>