<?php
$pageTitle = 'Product Details';
require_once 'includes/db_connect.php';

// Get product slug from URL
$slug = isset($_GET['slug']) ? sanitize($conn, $_GET['slug']) : '';

if (empty($slug)) {
    setFlashMessage('Product not found', 'danger');
    redirect('shop.php');
}

// Get product details
$sql = "SELECT p.*, c.name as category_name, c.id as category_id 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.slug = '$slug' AND p.status = 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    setFlashMessage('Product not found', 'danger');
    redirect('shop.php');
}

$product = $result->fetch_assoc();
$pageTitle = $product['name'];

// Get related products
$categoryId = $product['category_id'];
$productId = $product['id'];
$relatedProducts = $conn->query("SELECT * FROM products WHERE category_id = $categoryId AND id != $productId AND status = 1 LIMIT 4");

// Get reviews
$reviews = $conn->query("SELECT r.*, u.name as user_name FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = $productId AND r.status = 1 ORDER BY r.created_at DESC");

// Calculate average rating
$avgRating = $conn->query("SELECT AVG(rating) as avg FROM reviews WHERE product_id = $productId AND status = 1");
$avgRating = round($avgRating->fetch_assoc()['avg'] ?? 0, 1);

// Parse additional images
$additionalImages = [];
if ($product['additional_images']) {
    $additionalImages = explode(',', $product['additional_images']);
}

require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<section class="bg-light py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                <li class="breadcrumb-item"><a href="shop.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name'] ?? 'Category'; ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Product Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="position-relative">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="main-image" id="mainImage">
                        <?php if($product['featured']): ?>
                        <span class="position-absolute top-0 start-0 badge bg-warning m-3">Featured</span>
                        <?php endif; ?>
                    </div>
                    <?php if(count($additionalImages) > 0): ?>
                    <div class="thumbnail-images">
                        <img src="<?php echo $product['image']; ?>" class="thumbnail active" onclick="changeImage(this.src)">
                        <?php foreach($additionalImages as $img): ?>
                        <img src="<?php echo trim($img); ?>" class="thumbnail" onclick="changeImage(this.src)">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-details">
                    <span class="badge bg-primary mb-3"><?php echo $product['category_name'] ?? 'Uncategorized'; ?></span>
                    <h1 class="h2 mb-3"><?php echo $product['name']; ?></h1>
                    
                    <!-- Rating -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="product-rating me-3">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $avgRating ? 'star' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-muted"><?php echo $avgRating; ?> out of 5</span>
                        <span class="mx-2">|</span>
                        <a href="#reviews" class="text-decoration-none"><?php echo $reviews->num_rows; ?> Reviews</a>
                    </div>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <span class="h3 text-primary fw-bold"><?php echo formatPrice($product['price']); ?></span>
                        <?php if($product['stock'] > 0): ?>
                        <span class="badge bg-success ms-2">In Stock (<?php echo $product['stock']; ?> available)</span>
                        <?php else: ?>
                        <span class="badge bg-danger ms-2">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Short Description -->
                    <p class="lead text-muted mb-4"><?php echo $product['short_description']; ?></p>
                    
                    <!-- Product Meta -->
                    <div class="product-meta mb-4">
                        <div class="product-meta-item">
                            <i class="fas fa-truck"></i>
                            <span>Free Shipping</span>
                        </div>
                        <div class="product-meta-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>1 Year Warranty</span>
                        </div>
                        <div class="product-meta-item">
                            <i class="fas fa-undo"></i>
                            <span>30-Day Returns</span>
                        </div>
                    </div>
                    
                    <!-- Add to Cart Form -->
                    <?php if($product['stock'] > 0): ?>
                    <form class="mb-4" id="addToCartForm">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="form-label fw-bold">Quantity:</label>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn qty-minus">-</button>
                                    <input type="number" name="quantity" class="quantity-input qty-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button type="button" class="quantity-btn qty-plus">+</button>
                                </div>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mb-4">
                        <button class="btn btn-outline-primary">
                            <i class="far fa-heart me-2"></i>Add to Wishlist
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-share-alt me-2"></i>Share
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-mdb-toggle="tab" data-mdb-target="#description" type="button">
                            Description
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-mdb-toggle="tab" data-mdb-target="#reviews" type="button">
                            Reviews (<?php echo $reviews->num_rows; ?>)
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="productTabContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="card border-0 shadow-0">
                            <div class="card-body p-0">
                                <h5 class="mb-3">Product Description</h5>
                                <div class="product-description">
                                    <?php echo nl2br($product['description']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <h5 class="mb-4">Customer Reviews</h5>
                                
                                <?php if($reviews->num_rows > 0): ?>
                                <?php while($review = $reviews->fetch_assoc()): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar bg-primary text-white d-flex align-items-center justify-content-center">
                                                <?php echo strtoupper(substr($review['user_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo $review['user_name']; ?></h6>
                                                <small class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="product-rating">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'star' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br($review['comment']); ?></p>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="far fa-comment-dots fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Write Review -->
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-3">Write a Review</h6>
                                        <?php if(isLoggedIn()): ?>
                                        <form action="submit_review.php" method="POST">
                                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Rating</label>
                                                <select name="rating" class="form-select" required>
                                                    <option value="5">5 Stars - Excellent</option>
                                                    <option value="4">4 Stars - Very Good</option>
                                                    <option value="3">3 Stars - Good</option>
                                                    <option value="2">2 Stars - Fair</option>
                                                    <option value="1">1 Star - Poor</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Your Review</label>
                                                <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience with this product..." required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">Submit Review</button>
                                        </form>
                                        <?php else: ?>
                                        <p class="text-muted">Please <a href="login.php">login</a> to write a review.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if($relatedProducts->num_rows > 0): ?>
        <div class="mt-5">
            <h3 class="mb-4">Related Products</h3>
            <div class="row g-4">
                <?php while($related = $relatedProducts->fetch_assoc()): ?>
                <div class="col-6 col-md-3">
                    <div class="card product-card h-100">
                        <div class="product-image-wrapper">
                            <img src="<?php echo $related['image']; ?>" alt="<?php echo $related['name']; ?>" class="product-image">
                            <div class="product-actions">
                                <button class="product-action-btn quick-view" data-product-id="<?php echo $related['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="product-action-btn add-to-cart" data-product-id="<?php echo $related['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body product-info">
                            <h5 class="product-title">
                                <a href="product.php?slug=<?php echo $related['slug']; ?>"><?php echo $related['name']; ?></a>
                            </h5>
                            <div class="product-price">
                                <span class="current-price"><?php echo formatPrice($related['price']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function changeImage(src) {
    document.getElementById('mainImage').src = src;
}

// Add to cart form submission
document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const quantity = this.querySelector('input[name="quantity"]').value;
    addToCart(<?php echo $productId; ?>, quantity);
});
</script>

<?php require_once 'includes/footer.php'; ?>
