<?php
session_start();
require_once '../includes/db_connect.php';

// Check if admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('admin_login.php');
}

$pageTitle = 'Products';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("UPDATE products SET status = 0 WHERE id = $id");
    setFlashMessage('Product deleted successfully', 'success');
    redirect('products.php');
}

// Get products with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$where = "WHERE p.status = 1";
if ($search) {
    $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products p $where")->fetch_assoc()['count'];
$totalPages = ceil($totalProducts / $perPage);

$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC LIMIT $offset, $perPage");

$categories = $conn->query("SELECT * FROM categories WHERE status = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-sidebar { min-height: 100vh; background: #2c3e50; }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 15px 20px; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .admin-sidebar .nav-link i { width: 25px; }
        .product-image { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
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
                    <a href="products.php" class="nav-link active"><i class="fas fa-box"></i>Products</a>
                    <a href="categories.php" class="nav-link"><i class="fas fa-tags"></i>Categories</a>
                    <a href="orders.php" class="nav-link"><i class="fas fa-shopping-cart"></i>Orders</a>
                    <a href="users.php" class="nav-link"><i class="fas fa-users"></i>Users</a>
                    <a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i>View Website</a>
                    <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Manage Products</h3>
                    <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Product</a>
                </div>
                
                <?php showFlashMessage(); ?>
                
                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo $search; ?>">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="text-muted">Total: <?php echo $totalProducts; ?> products</span>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Products Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($product = $products->fetch_assoc()): ?>
                                    <tr>
                                        <td><img src="<?php echo $product['image']; ?>" alt="" class="product-image"></td>
                                        <td>
                                            <h6 class="mb-0"><?php echo $product['name']; ?></h6>
                                            <small class="text-muted"><?php echo substr($product['short_description'], 0, 50); ?>...</small>
                                        </td>
                                        <td><?php echo $product['category_name'] ?? 'Uncategorized'; ?></td>
                                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock'] < 10 ? 'danger' : ($product['stock'] < 50 ? 'warning' : 'success'); ?>">
                                                <?php echo $product['stock']; ?> in stock
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($product['featured']): ?>
                                            <span class="badge bg-primary">Yes</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                            <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
</body>
</html>
