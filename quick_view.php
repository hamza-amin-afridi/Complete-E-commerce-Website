<?php
require_once 'includes/db_connect.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    echo '<div class="text-center p-4">Product not found</div>';
    exit;
}

$product = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = $productId AND p.status = 1")->fetch_assoc();

if (!$product) {
    echo '<div class="text-center p-4">Product not found</div>';
    exit;
}
?>
<div class="row g-4">
    <div class="col-md-6">
        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid rounded">
    </div>
    <div class="col-md-6">
        <span class="badge bg-primary mb-2"><?php echo $product['category_name'] ?? 'Uncategorized'; ?></span>
        <h4><?php echo $product['name']; ?></h4>
        <h5 class="text-primary mb-3"><?php echo formatPrice($product['price']); ?></h5>
        <p class="text-muted"><?php echo $product['short_description']; ?></p>
        
        <div class="d-flex align-items-center mb-3">
            <div class="product-rating me-2">
                <i class="fas fa-star star"></i>
                <i class="fas fa-star star"></i>
                <i class="fas fa-star star"></i>
                <i class="fas fa-star star"></i>
                <i class="fas fa-star-half-alt star"></i>
            </div>
            <span class="text-muted">(<?php echo $product['popularity']; ?>)</span>
        </div>
        
        <div class="mb-3">
            <span class="badge bg-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?>">
                <?php echo $product['stock'] > 0 ? $product['stock'] . ' in stock' : 'Out of stock'; ?>
            </span>
        </div>
        
        <div class="d-flex gap-2">
            <a href="product.php?slug=<?php echo $product['slug']; ?>" class="btn btn-outline-primary">View Details</a>
            <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
            </button>
        </div>
    </div>
</div>
