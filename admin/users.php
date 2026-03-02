<?php
session_start();
require_once '../includes/db_connect.php';

// Check if admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('admin_login.php');
}

$pageTitle = 'Users';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $id AND role != 'admin'");
        setFlashMessage('User deleted successfully', 'success');
    } else {
        setFlashMessage('Cannot delete your own account', 'danger');
    }
    redirect('users.php');
}

// Handle role toggle
if (isset($_GET['toggle_role']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user = $conn->query("SELECT role FROM users WHERE id = $id")->fetch_assoc();
    
    if ($user && $id != $_SESSION['user_id']) {
        $newRole = $user['role'] == 'admin' ? 'user' : 'admin';
        $conn->query("UPDATE users SET role = '$newRole' WHERE id = $id");
        setFlashMessage('User role updated', 'success');
    }
    redirect('users.php');
}

// Get users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// Get statistics
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$totalAdmins = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
$newUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-sidebar { min-height: 100vh; background: #2c3e50; }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 15px 20px; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .admin-sidebar .nav-link i { width: 25px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 admin-sidebar">
                <div class="d-flex align-items-center justify-content-center py-4 border-bottom border-secondary">
                    <h4 class="text-white mb-0"><i class="fas fa-shopping-bag me-2"></i>E-Shop</h4>
                </div>
                <nav class="nav flex-column">
                    <a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
                    <a href="products.php" class="nav-link"><i class="fas fa-box"></i>Products</a>
                    <a href="categories.php" class="nav-link"><i class="fas fa-tags"></i>Categories</a>
                    <a href="orders.php" class="nav-link"><i class="fas fa-shopping-cart"></i>Orders</a>
                    <a href="users.php" class="nav-link active"><i class="fas fa-users"></i>Users</a>
                    <a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i>View Website</a>
                    <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Manage Users</h3>
                </div>
                
                <?php showFlashMessage(); ?>
                
                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h4 class="mb-1"><?php echo $totalUsers; ?></h4>
                                <small class="text-muted">Regular Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-user-shield fa-2x text-warning mb-2"></i>
                                <h4 class="mb-1"><?php echo $totalAdmins; ?></h4>
                                <small class="text-muted">Admins</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-user-plus fa-2x text-success mb-2"></i>
                                <h4 class="mb-1"><?php echo $newUsers; ?></h4>
                                <small class="text-muted">New Users (30 days)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Users</h5>
                        <span class="text-muted">Total: <?php echo $users->num_rows; ?> users</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Orders</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $users->data_seek(0);
                                    while($user = $users->fetch_assoc()): 
                                        $orderCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = {$user['id']}")->fetch_assoc()['count'];
                                        $totalSpent = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE user_id = {$user['id']} AND status != 'cancelled'")->fetch_assoc()['total'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar bg-primary text-white me-2">
                                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                </div>
                                                <span class="fw-bold"><?php echo $user['name']; ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td><?php echo $user['phone'] ?: '-'; ?></td>
                                        <td><?php echo $user['address'] ? substr($user['address'], 0, 30) . '...' : '-'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'warning' : 'primary'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td><span class="badge bg-info"><?php echo $orderCount; ?></span></td>
                                        <td>
                                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info me-1" data-mdb-toggle="modal" data-mdb-target="#userModal<?php echo $user['id']; ?>" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="users.php?toggle_role=1&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Toggle Role">
                                                <i class="fas fa-exchange-alt"></i>
                                            </a>
                                            <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <span class="badge bg-success">You</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- User Details Modal -->
                                    <div class="modal fade" id="userModal<?php echo $user['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>User Details</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-mdb-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted text-uppercase mb-2">Personal Information</h6>
                                                            <table class="table table-sm">
                                                                <tr><td><strong>Name:</strong></td><td><?php echo $user['name']; ?></td></tr>
                                                                <tr><td><strong>Email:</strong></td><td><?php echo $user['email']; ?></td></tr>
                                                                <tr><td><strong>Phone:</strong></td><td><?php echo $user['phone'] ?: 'Not provided'; ?></td></tr>
                                                                <tr><td><strong>Role:</strong></td><td><span class="badge bg-<?php echo $user['role'] == 'admin' ? 'warning' : 'primary'; ?>"><?php echo ucfirst($user['role']); ?></span></td></tr>
                                                                <tr><td><strong>Joined:</strong></td><td><?php echo date('F j, Y g:i A', strtotime($user['created_at'])); ?></td></tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted text-uppercase mb-2">Address</h6>
                                                            <p class="mb-0"><?php echo nl2br($user['address'] ?: 'No address provided'); ?></p>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-4 text-center">
                                                            <h3 class="text-primary mb-1"><?php echo $orderCount; ?></h3>
                                                            <small class="text-muted">Total Orders</small>
                                                        </div>
                                                        <div class="col-md-4 text-center">
                                                            <h3 class="text-success mb-1">$<?php echo number_format($totalSpent, 2); ?></h3>
                                                            <small class="text-muted">Total Spent</small>
                                                        </div>
                                                        <div class="col-md-4 text-center">
                                                            <h3 class="text-info mb-1"><?php echo $orderCount > 0 ? round($totalSpent / $orderCount, 2) : '0.00'; ?></h3>
                                                            <small class="text-muted">Avg. Order Value</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Close</button>
                                                    <a href="../profile.php" class="btn btn-primary" target="_blank">View Full Profile</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
</body>
</html>
