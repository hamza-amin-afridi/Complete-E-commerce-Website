<?php
session_start();
require_once '../includes/db_connect.php';

// Check if admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('admin_login.php');
}

$pageTitle = 'Add Product';
$isEdit = false;
$product = [];

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE status = 1");

// Handle edit mode
if (isset($_GET['id'])) {
    $isEdit = true;
    $pageTitle = 'Edit Product';
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    
    if ($result->num_rows == 0) {
        setFlashMessage('Product not found', 'danger');
        redirect('products.php');
    }
    
    $product = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($conn, $_POST['name']);
    $slug = generateSlug($name);
    $description = sanitize($conn, $_POST['description']);
    $shortDesc = sanitize($conn, $_POST['short_description']);
    $categoryId = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image = sanitize($conn, $_POST['image']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Make slug unique
    $slugCheck = $conn->query("SELECT id FROM products WHERE slug = '$slug'" . ($isEdit ? " AND id != $id" : ""));
    if ($slugCheck->num_rows > 0) {
        $slug .= '-' . time();
    }
    
    if ($isEdit) {
        $sql = "UPDATE products SET name='$name', slug='$slug', description='$description', short_description='$shortDesc', 
                category_id=$categoryId, price=$price, stock=$stock, image='$image', featured=$featured 
                WHERE id=$id";
        $message = 'Product updated successfully';
    } else {
        $sql = "INSERT INTO products (name, slug, description, short_description, category_id, price, stock, image, featured) 
                VALUES ('$name', '$slug', '$description', '$shortDesc', $categoryId, $price, $stock, '$image', $featured)";
        $message = 'Product added successfully';
    }
    
    if ($conn->query($sql)) {
        setFlashMessage($message, 'success');
        redirect('products.php');
    } else {
        setFlashMessage('Error saving product', 'danger');
    }
}
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
                    <h3 class="mb-0"><?php echo $pageTitle; ?></h3>
                    <a href="products.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Products</a>
                </div>
                
                <?php showFlashMessage(); ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo $product['name'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Select Category</option>
                                        <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo $cat['name']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo $product['price'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock *</label>
                                    <input type="number" name="stock" class="form-control" min="0" value="<?php echo $product['stock'] ?? '0'; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Image URL</label>
                                    <input type="url" name="image" class="form-control" value="<?php echo $product['image'] ?? ''; ?>" placeholder="https://example.com/image.jpg">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Short Description</label>
                                    <input type="text" name="short_description" class="form-control" value="<?php echo $product['short_description'] ?? ''; ?>" maxlength="500">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="5"><?php echo $product['description'] ?? ''; ?></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" name="featured" class="form-check-input" id="featured" <?php echo ($product['featured'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="featured">Featured Product</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i><?php echo $isEdit ? 'Update' : 'Add'; ?> Product
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
</body>
</html>
