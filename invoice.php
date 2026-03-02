<?php
$pageTitle = 'Order Invoice';
require_once 'includes/db_connect.php';

// Check if user is logged in or has order access
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get order ID from URL
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$download = isset($_GET['download']) ? true : false;

if ($orderId <= 0) {
    setFlashMessage('Invalid order ID', 'danger');
    redirect('orders.php');
}

// Get order details
$order = $conn->query("SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      WHERE o.id = $orderId AND o.user_id = {$_SESSION['user_id']}")->fetch_assoc();

if (!$order) {
    setFlashMessage('Order not found', 'danger');
    redirect('orders.php');
}

// Get order items
$items = $conn->query("SELECT * FROM order_items WHERE order_id = $orderId");

require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <a href="orders.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Orders
                    </a>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary me-2">
                            <i class="fas fa-print me-2"></i>Print Invoice
                        </button>
                        <a href="?id=<?php echo $orderId; ?>&download=1" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </a>
                    </div>
                </div>

                <!-- Invoice -->
                <div class="card invoice-card" id="invoice">
                    <div class="card-body p-5">
                        <!-- Invoice Header -->
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-shopping-bag fa-2x text-primary me-3"></i>
                                    <h3 class="mb-0 fw-bold">E-Shop</h3>
                                </div>
                                <p class="text-muted mb-1">123 Commerce Street</p>
                                <p class="text-muted mb-1">New York, NY 10001</p>
                                <p class="text-muted mb-1">support@eshop.com</p>
                                <p class="text-muted">+1 (555) 123-4567</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h4 class="text-primary fw-bold mb-3">INVOICE</h4>
                                <p class="mb-1"><strong>Order #:</strong> <?php echo $order['order_number']; ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                <p class="mb-1"><strong>Status:</strong> 
                                    <span class="badge bg-<?php echo ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'][$order['status']] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Payment:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                            </div>
                        </div>

                        <!-- Bill To / Ship To -->
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="text-muted text-uppercase mb-3">Bill To</h6>
                                    <p class="fw-bold mb-1"><?php echo $order['shipping_name']; ?></p>
                                    <p class="text-muted mb-1"><?php echo $order['shipping_email']; ?></p>
                                    <p class="text-muted"><?php echo $order['shipping_phone']; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="text-muted text-uppercase mb-3">Ship To</h6>
                                    <p class="text-muted mb-1"><?php echo $order['shipping_address']; ?></p>
                                    <p class="text-muted mb-1"><?php echo $order['shipping_city']; ?><?php echo $order['shipping_zip'] ? ', ' . $order['shipping_zip'] : ''; ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $subtotal = 0;
                                    while($item = $items->fetch_assoc()): 
                                        $subtotal += $item['total'];
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $item['product_name']; ?></strong>
                                        </td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end"><?php echo formatPrice($item['price']); ?></td>
                                        <td class="text-end fw-bold"><?php echo formatPrice($item['total']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-end">Subtotal:</td>
                                        <td class="text-end fw-bold"><?php echo formatPrice($subtotal); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-end">Shipping:</td>
                                        <td class="text-end">
                                            <?php 
                                            $shipping = $order['total_amount'] - $subtotal;
                                            echo $shipping > 0 ? formatPrice($shipping) : 'Free'; 
                                            ?>
                                        </td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="text-end h5">Total:</td>
                                        <td class="text-end h4 text-primary fw-bold"><?php echo formatPrice($order['total_amount']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Notes -->
                        <?php if($order['notes']): ?>
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="text-muted">Order Notes:</h6>
                            <p class="mb-0"><?php echo nl2br($order['notes']); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Footer -->
                        <div class="mt-5 pt-4 border-top text-center">
                            <p class="text-muted mb-1">Thank you for shopping with E-Shop!</p>
                            <p class="text-muted small">For any questions about this invoice, please contact us at support@eshop.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@media print {
    .no-print { display: none !important; }
    .navbar { display: none !important; }
    footer { display: none !important; }
    .invoice-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    body { background: white !important; }
}
.invoice-card {
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border: none;
}
</style>

<?php require_once 'includes/footer.php'; ?>
