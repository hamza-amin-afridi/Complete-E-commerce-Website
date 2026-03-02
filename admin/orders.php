<?php
session_start();
require_once '../includes/db_connect.php';

// Check if admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('admin_login.php');
}

$pageTitle = 'Orders';

// Handle status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    $orderId = intval($_GET['id']);
    $status = sanitize($conn, $_GET['status']);
    $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($status, $allowedStatuses)) {
        $conn->query("UPDATE orders SET status='$status' WHERE id=$orderId");
        setFlashMessage('Order status updated', 'success');
    }
    redirect('orders.php');
}

// Get filter
$statusFilter = isset($_GET['status_filter']) ? sanitize($conn, $_GET['status_filter']) : '';
$whereClause = '';
if ($statusFilter && in_array($statusFilter, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
    $whereClause = "WHERE o.status = '$statusFilter'";
}

// Get orders
$orders = $conn->query("SELECT o.*, u.name as user_name, u.email as user_email 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       $whereClause 
                       ORDER BY o.created_at DESC");

// Calculate totals
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'] ?? 0;
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$completedOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-sidebar { min-height: 100vh; background: #2c3e50; }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 15px 20px; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .admin-sidebar .nav-link i { width: 25px; }
        .order-card { border-left: 4px solid; }
        .order-card.pending { border-color: #ffc107; }
        .order-card.processing { border-color: #54b4d3; }
        .order-card.shipped { border-color: #3b71ca; }
        .order-card.delivered { border-color: #14a44d; }
        .order-card.cancelled { border-color: #dc4c64; }
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
                    <a href="orders.php" class="nav-link active"><i class="fas fa-shopping-cart"></i>Orders</a>
                    <a href="users.php" class="nav-link"><i class="fas fa-users"></i>Users</a>
                    <a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i>View Website</a>
                    <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Manage Orders</h3>
                    <span class="text-muted">Total Revenue: <strong>$<?php echo number_format($totalRevenue, 2); ?></strong></span>
                </div>
                
                <?php showFlashMessage(); ?>
                
                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-warning mb-1"><?php echo $pendingOrders; ?></h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-info mb-1"><?php echo $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")->fetch_assoc()['count']; ?></h4>
                                <small class="text-muted">Processing</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-primary mb-1"><?php echo $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'shipped'")->fetch_assoc()['count']; ?></h4>
                                <small class="text-muted">Shipped</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-success mb-1"><?php echo $completedOrders; ?></h4>
                                <small class="text-muted">Delivered</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label">Filter by Status</label>
                                <select name="status_filter" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Orders</option>
                                    <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $statusFilter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $statusFilter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $statusFilter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $statusFilter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <label class="d-block">&nbsp;</label>
                                <span class="text-muted">Showing <?php echo $orders->num_rows; ?> orders</span>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Orders -->
                <?php while($order = $orders->fetch_assoc()): 
                    $statusColors = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $color = $statusColors[$order['status']] ?? 'secondary';
                    
                    // Get order items
                    $orderItems = $conn->query("SELECT * FROM order_items WHERE order_id = {$order['id']}");
                ?>
                <div class="card mb-3 order-card <?php echo $order['status']; ?>">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold"><?php echo $order['order_number']; ?></span>
                            <span class="text-muted mx-2">|</span>
                            <span class="text-muted"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <h6 class="mb-1">Customer</h6>
                                <p class="mb-0"><?php echo $order['shipping_name']; ?></p>
                                <small class="text-muted"><?php echo $order['shipping_email']; ?></small>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1">Shipping Address</h6>
                                <p class="mb-0 small"><?php echo $order['shipping_address']; ?>, <?php echo $order['shipping_city']; ?> <?php echo $order['shipping_zip']; ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h6 class="mb-1">Total Amount</h6>
                                <h4 class="text-primary mb-0">$<?php echo number_format($order['total_amount'], 2); ?></h4>
                                <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></small>
                            </div>
                        </div>
                        
                        <h6>Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($item = $orderItems->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $item['product_name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td>$<?php echo number_format($item['total'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-mdb-toggle="dropdown">
                                    Update Status
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="orders.php?status=pending&id=<?php echo $order['id']; ?>">Pending</a></li>
                                    <li><a class="dropdown-item" href="orders.php?status=processing&id=<?php echo $order['id']; ?>">Processing</a></li>
                                    <li><a class="dropdown-item" href="orders.php?status=shipped&id=<?php echo $order['id']; ?>">Shipped</a></li>
                                    <li><a class="dropdown-item" href="orders.php?status=delivered&id=<?php echo $order['id']; ?>">Delivered</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="orders.php?status=cancelled&id=<?php echo $order['id']; ?>">Cancelled</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
</body>
</html>
