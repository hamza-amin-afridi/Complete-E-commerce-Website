<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart items
$cartItems = [];
$subtotal = 0;

if (isLoggedIn()) {
    // Get cart from database for logged in users
    $userId = $_SESSION['user_id'];
    $sql = "SELECT c.id as cart_id, c.quantity, p.* 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = $userId";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $subtotal += $row['price'] * $row['quantity'];
    }
} else {
    // Get cart from session for guests
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $result = $conn->query("SELECT * FROM products WHERE id = $productId");
        if ($row = $result->fetch_assoc()) {
            $row['cart_id'] = $productId;
            $row['quantity'] = $quantity;
            $cartItems[] = $row;
            $subtotal += $row['price'] * $quantity;
        }
    }
}

$shipping = $subtotal > 50 ? 0 : 10;
$total = $subtotal + $shipping;
?>

<section class="py-5">
    <div class="container">
        <h2 class="mb-4">Shopping Cart</h2>
        
        <?php if(count($cartItems) > 0): ?>
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <?php foreach($cartItems as $item): ?>
                <div class="cart-item" data-cart-item="<?php echo $item['cart_id']; ?>">
                    <div class="row align-items-center">
                        <div class="col-md-2 mb-3 mb-md-0">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="cart-item-image">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <h5 class="mb-1"><a href="product.php?slug=<?php echo $item['slug']; ?>" class="text-decoration-none"><?php echo $item['name']; ?></a></h5>
                            <p class="text-muted mb-0 small"><?php echo $item['short_description']; ?></p>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small">Quantity:</label>
                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn qty-minus">-</button>
                                <input type="number" class="quantity-input cart-qty-input" 
                                       data-cart-id="<?php echo $item['cart_id']; ?>" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['stock']; ?>">
                                <button type="button" class="quantity-btn qty-plus">+</button>
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end mb-3 mb-md-0">
                            <h6 class="mb-0"><?php echo formatPrice($item['price'] * $item['quantity']); ?></h6>
                            <small class="text-muted"><?php echo formatPrice($item['price']); ?> each</small>
                        </div>
                        <div class="col-md-1 text-md-end">
                            <button class="btn btn-link text-danger remove-from-cart" data-cart-id="<?php echo $item['cart_id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="shop.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                    <button class="btn btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash me-2"></i>Clear Cart
                    </button>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="cart-subtotal"><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span><?php echo $shipping == 0 ? 'Free' : formatPrice($shipping); ?></span>
                        </div>
                        <?php if($shipping > 0): ?>
                        <div class="alert alert-info py-2 mt-2 mb-2">
                            <small>Add <?php echo formatPrice(50 - $subtotal); ?> more for free shipping!</small>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Total</span>
                            <span class="h5 text-primary" id="cart-total"><?php echo formatPrice($total); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary w-100 btn-lg">
                            Proceed to Checkout<i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Promo Code -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="mb-3">Promo Code</h6>
                        <div class="input-group">
                            <input type="text" id="promoCodeInput" class="form-control" placeholder="Enter code">
                            <button class="btn btn-outline-primary" type="button" id="applyPromoBtn" onclick="applyPromoCode()">Apply</button>
                        </div>
                        <div id="promoMessage" class="mt-2 small"></div>
                        <div id="promoDiscountRow" class="d-flex justify-content-between mb-2 mt-2" style="display: none !important;">
                            <span>Discount</span>
                            <span class="text-success" id="discountAmount">-$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
            <h4>Your cart is empty</h4>
            <p class="text-muted">Looks like you haven't added any items to your cart yet.</p>
            <a href="shop.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function clearCart() {
    if (confirm('Are you sure you want to clear your cart?')) {
        $.ajax({
            url: 'cart_actions.php',
            type: 'POST',
            data: { action: 'clear' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    }
}

function applyPromoCode() {
    const code = $('#promoCodeInput').val().trim();
    const subtotal = parseFloat($('#cart-subtotal').text().replace('$', '').replace(',', ''));
    
    if (!code) {
        $('#promoMessage').html('<span class="text-warning">Please enter a promo code</span>');
        return;
    }
    
    // Get cart items data
    const cartItems = [];
    $('.cart-item').each(function() {
        const productId = $(this).find('.cart-qty-input').data('cart-id');
        const price = parseFloat($(this).find('small.text-muted').text().replace('$', ''));
        const quantity = parseInt($(this).find('.cart-qty-input').val());
        cartItems.push({ product_id: productId, price: price, quantity: quantity });
    });
    
    $('#applyPromoBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: 'apply_promo.php',
        type: 'POST',
        data: {
            code: code,
            subtotal: subtotal,
            cart_items: JSON.stringify(cartItems)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#promoMessage').html('<span class="text-success">' + response.message + '</span>');
                $('#promoDiscountRow').show().css('display', 'flex !important');
                $('#discountAmount').text('-$' + response.discount.toFixed(2));
                $('#cart-total').text('$' + response.new_total.toFixed(2));
                $('#promoCodeInput').prop('readonly', true);
                $('#applyPromoBtn').text('Applied').removeClass('btn-outline-primary').addClass('btn-success');
            } else {
                $('#promoMessage').html('<span class="text-danger">' + response.message + '</span>');
            }
        },
        error: function() {
            $('#promoMessage').html('<span class="text-danger">Error applying promo code</span>');
        },
        complete: function() {
            $('#applyPromoBtn').prop('disabled', false);
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
