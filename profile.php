<?php
$pageTitle = 'My Profile';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('Please login to view your profile', 'warning');
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($conn, $_POST['name']);
    $phone = sanitize($conn, $_POST['phone']);
    $address = sanitize($conn, $_POST['address']);
    
    // Handle profile image upload
    $profileImage = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            // Create uploads directory if not exists
            $uploadDir = 'uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $newFilename = 'user_' . $userId . '_' . time() . '.' . $filetype;
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                // Delete old image if exists
                if ($profileImage && file_exists($profileImage)) {
                    unlink($profileImage);
                }
                $profileImage = $uploadPath;
            }
        }
    }
    
    // Update password if provided
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        
        if (password_verify($currentPassword, $user['password'])) {
            if (strlen($newPassword) >= 6) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET name='$name', phone='$phone', address='$address', profile_image='$profileImage', password='$hashedPassword' WHERE id=$userId");
                setFlashMessage('Profile updated successfully', 'success');
            } else {
                setFlashMessage('New password must be at least 6 characters', 'danger');
            }
        } else {
            setFlashMessage('Current password is incorrect', 'danger');
        }
    } else {
        $conn->query("UPDATE users SET name='$name', phone='$phone', address='$address', profile_image='$profileImage' WHERE id=$userId");
        setFlashMessage('Profile updated successfully', 'success');
        // Refresh user data
        $user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();
    }
    
    // Update session name
    $_SESSION['user_name'] = $name;
}

require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <h2 class="mb-4">My Profile</h2>
        
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="position-relative d-inline-block mb-3">
                            <?php if($user['profile_image']): ?>
                            <img src="<?php echo $user['profile_image']; ?>" alt="Profile" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <h5 class="mb-1"><?php echo $user['name']; ?></h5>
                        <p class="text-muted mb-3"><?php echo $user['email']; ?></p>
                        <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="orders.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag me-2"></i>My Orders
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Profile Form -->
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <!-- Profile Image -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label">Profile Picture</label>
                                    <div class="input-group">
                                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                                        <small class="text-muted d-block mt-1">Max size: 2MB. Allowed: JPG, PNG, GIF</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="3"><?php echo $user['address']; ?></textarea>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="mb-3">Change Password</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control">
                                    <small class="text-muted">Leave blank to keep current password</small>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
