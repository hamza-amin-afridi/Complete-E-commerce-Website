<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Get featured products
$featuredProducts = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.status = 1 LIMIT 8");

// Get trending products (by popularity)
$trendingProducts = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1 ORDER BY p.popularity DESC LIMIT 4");

// Get all categories
$categories = $conn->query("SELECT * FROM categories WHERE status = 1 LIMIT 6");
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="hero-title">Discover Amazing Products</h1>
                <p class="hero-subtitle">Shop the latest trends in electronics, fashion, home & living, and more. Quality products at unbeatable prices.</p>
                <div class="d-flex gap-3">
                    <a href="shop.php" class="btn btn-light btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="#categories" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-th-large me-2"></i>Browse Categories
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600" alt="Shopping" class="img-fluid rounded-4 shadow-4" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5" id="categories">
    <div class="container">
        <div class="section-title">
            <h2>Shop by Category</h2>
            <p>Find exactly what you're looking for in our wide range of categories</p>
        </div>
        <div class="row g-4">
            <?php while($category = $categories->fetch_assoc()): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="shop.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="category-card">
                        <img src="<?php echo $category['image'] ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=300'; ?>" alt="<?php echo $category['name']; ?>" class="category-image">
                        <div class="category-overlay">
                            <h5 class="category-name"><?php echo $category['name']; ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="section-title">
            <h2>Featured Products</h2>
            <p>Handpicked selection of our best products just for you</p>
        </div>
        <div class="row g-4">
            <?php while($product = $featuredProducts->fetch_assoc()): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card h-100">
                    <div class="product-image-wrapper">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                        <?php if($product['featured']): ?>
                        <span class="product-badge bg-warning">Featured</span>
                        <?php endif; ?>
                        <div class="product-actions">
                            <button class="product-action-btn quick-view" data-product-id="<?php echo $product['id']; ?>" title="Quick View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="product-action-btn add-to-cart" data-product-id="<?php echo $product['id']; ?>" title="Add to Cart">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body product-info">
                        <div class="product-category"><?php echo $product['category_name'] ?? 'Uncategorized'; ?></div>
                        <h5 class="product-title">
                            <a href="product.php?slug=<?php echo $product['slug']; ?>"><?php echo $product['name']; ?></a>
                        </h5>
                        <div class="product-price">
                            <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                        </div>
                        <div class="product-rating">
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star-half-alt star"></i>
                            <span class="ms-1 text-muted small">(<?php echo $product['popularity']; ?>)</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-5">
            <a href="shop.php" class="btn btn-primary btn-lg">
                View All Products <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Promo Banner -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 text-white overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <span class="badge bg-white text-primary mb-2">New Arrivals</span>
                                <h3 class="card-title">Summer Collection 2024</h3>
                                <p class="card-text">Get up to 50% off on selected items. Limited time offer!</p>
                                <a href="shop.php" class="btn btn-light">Shop Now</a>
                            </div>
                            <div class="col-4 text-center">
                                <i class="fas fa-sun fa-5x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 text-white overflow-hidden" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <span class="badge bg-white text-danger mb-2">Free Shipping</span>
                                <h3 class="card-title">On Orders Over $50</h3>
                                <p class="card-text">Enjoy free shipping on all orders. No code needed!</p>
                                <a href="shop.php" class="btn btn-light">Shop Now</a>
                            </div>
                            <div class="col-4 text-center">
                                <i class="fas fa-shipping-fast fa-5x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trending Products -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Trending Products</h2>
            <p>Most popular items this week</p>
        </div>
        <div class="row g-4">
            <?php while($product = $trendingProducts->fetch_assoc()): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card h-100">
                    <div class="product-image-wrapper">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                        <span class="product-badge bg-danger">Hot</span>
                        <div class="product-actions">
                            <button class="product-action-btn quick-view" data-product-id="<?php echo $product['id']; ?>" title="Quick View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="product-action-btn add-to-cart" data-product-id="<?php echo $product['id']; ?>" title="Add to Cart">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body product-info">
                        <div class="product-category"><?php echo $product['category_name'] ?? 'Uncategorized'; ?></div>
                        <h5 class="product-title">
                            <a href="product.php?slug=<?php echo $product['slug']; ?>"><?php echo $product['name']; ?></a>
                        </h5>
                        <div class="product-price">
                            <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                        </div>
                        <div class="product-rating">
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <span class="ms-1 text-muted small">(<?php echo $product['popularity']; ?>)</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="text-center p-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-4 d-inline-block mb-3">
                        <i class="fas fa-truck fa-2x text-primary"></i>
                    </div>
                    <h5>Free Shipping</h5>
                    <p class="text-muted mb-0">On orders over $50</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-4">
                    <div class="rounded-circle bg-success bg-opacity-10 p-4 d-inline-block mb-3">
                        <i class="fas fa-shield-alt fa-2x text-success"></i>
                    </div>
                    <h5>Secure Payment</h5>
                    <p class="text-muted mb-0">100% secure checkout</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-4">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-4 d-inline-block mb-3">
                        <i class="fas fa-undo fa-2x text-warning"></i>
                    </div>
                    <h5>Easy Returns</h5>
                    <p class="text-muted mb-0">30-day return policy</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-4">
                    <div class="rounded-circle bg-info bg-opacity-10 p-4 d-inline-block mb-3">
                        <i class="fas fa-headset fa-2x text-info"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted mb-0">Dedicated support team</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick View</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
