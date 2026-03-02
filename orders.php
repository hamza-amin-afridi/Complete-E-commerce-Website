<?php
$pageTitle = 'My Orders';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('Please login to view your orders', 'warning');
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

// Get user's orders
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC");

require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <h2 class="mb-4">My Orders</h2>
        
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <h5 class="mb-1"><?php echo $_SESSION['user_name']; ?></h5>
                        <span class="badge bg-primary">Customer</span>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="orders.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-shopping-bag me-2"></i>My Orders
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Orders List -->
            <div class="col-lg-9">
                <?php if($orders->num_rows > 0): ?>
                    <?php while($order = $orders->fetch_assoc()): 
                        // Get order items
                        $orderId = $order['id'];
                        $items = $conn->query("SELECT * FROM order_items WHERE order_id = $orderId");
                        
                        $statusClass = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'shipped' => 'primary',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ][$order['status']] ?? 'secondary';
                    ?>
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <span class="text-muted">Order #</span>
                                <strong><?php echo $order['order_number']; ?></strong>
                                <span class="mx-2">|</span>
                                <span class="text-muted"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                            </div>
                            <span class="badge bg-<?php echo $statusClass; ?> mt-2 mt-sm-0">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <?php while($item = $items->fetch_assoc()): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo $item['product_name']; ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?> x <?php echo formatPrice($item['price']); ?></small>
                                </div>
                                <div class="text-end">
                                    <strong><?php echo formatPrice($item['total']); ?></strong>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Shipping Address</h6>
                                    <p class="text-muted mb-0">
                                        <?php echo $order['shipping_name']; ?><br>
                                        <?php echo $order['shipping_address']; ?><br>
                                        <?php echo $order['shipping_city']; ?>, <?php echo $order['shipping_zip']; ?><br>
                                        Phone: <?php echo $order['shipping_phone']; ?>
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="mb-2">
                                        <span class="text-muted">Subtotal:</span>
                                        <span><?php echo formatPrice($order['total_amount'] - ($order['total_amount'] > 50 ? 0 : 10)); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-muted">Shipping:</span>
                                        <span><?php echo $order['total_amount'] > 50 ? 'Free' : formatPrice(10); ?></span>
                                    </div>
                                    <div>
                                        <span class="h5">Total:</span>
                                        <span class="h5 text-primary"><?php echo formatPrice($order['total_amount']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                        <h4>No orders yet</h4>
                        <p class="text-muted">You haven't placed any orders yet.</p>
                        <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
