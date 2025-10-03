<?php
// account/profile.php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('/login');
}

$user_id = getUserId();
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    
    // Check if email is already used by another user
    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $user_id");
    
    if ($check_email && $check_email->num_rows > 0) {
        setMessage('Email is already used by another account', 'error');
    } else {
        $update_query = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone' 
                        WHERE id = $user_id";
        
        if ($conn->query($update_query)) {
            $_SESSION['full_name'] = $full_name;
            
            // Update password if provided
            if (!empty($_POST['new_password'])) {
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                if (password_verify($current_password, $user['password'])) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 6) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $conn->query("UPDATE users SET password = '$hashed_password' WHERE id = $user_id");
                            setMessage('Profile and password updated successfully!', 'success');
                        } else {
                            setMessage('Password must be at least 6 characters', 'error');
                        }
                    } else {
                        setMessage('New passwords do not match', 'error');
                    }
                } else {
                    setMessage('Current password is incorrect', 'error');
                }
            } else {
                setMessage('Profile updated successfully!', 'success');
            }
            
            redirect('/account/profile');
        } else {
            setMessage('Failed to update profile', 'error');
        }
    }
}

$page_title = 'Edit Profile - Girls Trip';

include '../includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Edit Profile</h1>
        <p class="text-pink-100">Update your account information</p>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="POST">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
                    <p class="text-sm text-gray-600 mb-4">Leave blank to keep current password</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <input type="password" name="new_password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" 
                            class="bg-pink-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-pink-700 transition">
                        <i class="fas fa-save mr-2"></i>Update Profile
                    </button>
                    <a href="/account" 
                       class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
<!-- END account/profile.php -->

