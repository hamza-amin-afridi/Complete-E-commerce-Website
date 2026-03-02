<?php
$pageTitle = 'Shop';
require_once 'includes/header.php';

// Get filter parameters
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($conn, $_GET['sort']) : 'newest';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 1000;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$whereClause = "WHERE p.status = 1";
if ($categoryId > 0) {
    $whereClause .= " AND p.category_id = $categoryId";
}
if (!empty($search)) {
    $whereClause .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if ($minPrice > 0 || $maxPrice < 1000) {
    $whereClause .= " AND p.price BETWEEN $minPrice AND $maxPrice";
}

// Sort options
$orderClause = "ORDER BY ";
switch ($sort) {
    case 'price_low':
        $orderClause .= "p.price ASC";
        break;
    case 'price_high':
        $orderClause .= "p.price DESC";
        break;
    case 'popularity':
        $orderClause .= "p.popularity DESC";
        break;
    default:
        $orderClause .= "p.created_at DESC";
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM products p $whereClause";
$totalResult = $conn->query($countSql);
$totalProducts = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $whereClause 
        $orderClause 
        LIMIT $offset, $perPage";
$products = $conn->query($sql);

// Get all categories for filter
$categories = $conn->query("SELECT * FROM categories WHERE status = 1");

// Get current category name if filtering
$currentCategory = null;
if ($categoryId > 0) {
    $catResult = $conn->query("SELECT name FROM categories WHERE id = $categoryId");
    $currentCategory = $catResult->fetch_assoc()['name'] ?? null;
}
?>

<!-- Shop Header -->
<section class="bg-light py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Shop</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-0 border">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Filters</h5>
                        
                        <!-- Categories -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Categories</h6>
                            <div class="list-group list-group-flush">
                                <a href="shop.php" class="list-group-item list-group-item-action <?php echo $categoryId == 0 ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                                <?php 
                                $categories->data_seek(0);
                                while($cat = $categories->fetch_assoc()): 
                                ?>
                                <a href="shop.php?category=<?php echo $cat['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $categoryId == $cat['id'] ? 'active' : ''; ?>">
                                    <?php echo $cat['name']; ?>
                                </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Price Range</h6>
                            <form method="GET" action="shop.php">
                                <?php if($categoryId > 0): ?>
                                <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                                <?php endif; ?>
                                <?php if(!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo $maxPrice < 1000 ? $maxPrice : ''; ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Apply</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Sort & Results Info -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="mb-0 text-muted">
                        Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $perPage, $totalProducts); ?> of <?php echo $totalProducts; ?> results
                        <?php if($currentCategory): ?>
                        in <strong><?php echo $currentCategory; ?></strong>
                        <?php endif; ?>
                        <?php if(!empty($search)): ?>
                        for <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                        <?php endif; ?>
                    </p>
                    
                    <form method="GET" class="d-flex align-items-center">
                        <?php if($categoryId > 0): ?>
                        <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        <?php if(!empty($search)): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        <?php if($minPrice > 0): ?>
                        <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                        <?php endif; ?>
                        <?php if($maxPrice < 1000): ?>
                        <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                        <?php endif; ?>
                        <label class="me-2">Sort by:</label>
                        <select name="sort" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="popularity" <?php echo $sort == 'popularity' ? 'selected' : ''; ?>>Popularity</option>
                        </select>
                    </form>
                </div>
                
                <?php if($products->num_rows > 0): ?>
                <div class="row g-4">
                    <?php while($product = $products->fetch_assoc()): ?>
                    <div class="col-6 col-md-4">
                        <div class="card product-card h-100">
                            <div class="product-image-wrapper">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                                <?php if($product['featured']): ?>
                                <span class="product-badge bg-warning">Featured</span>
                                <?php endif; ?>
                                <?php if($product['stock'] < 10 && $product['stock'] > 0): ?>
                                <span class="product-badge bg-danger">Low Stock</span>
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
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $categoryId > 0 ? '&category='.$categoryId : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo '&sort='.$sort; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $categoryId > 0 ? '&category='.$categoryId : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo '&sort='.$sort; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $categoryId > 0 ? '&category='.$categoryId : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo '&sort='.$sort; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-4"></i>
                    <h4>No products found</h4>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                    <a href="shop.php" class="btn btn-primary">View All Products</a>
                </div>
                <?php endif; ?>
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
