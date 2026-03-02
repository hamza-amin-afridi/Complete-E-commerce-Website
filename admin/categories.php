<?php
session_start();
require_once '../includes/db_connect.php';

// Check if admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('admin_login.php');
}

$pageTitle = 'Categories';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("UPDATE categories SET status = 0 WHERE id = $id");
    setFlashMessage('Category deleted successfully', 'success');
    redirect('categories.php');
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($conn, $_POST['name']);
    $slug = generateSlug($name);
    $description = sanitize($conn, $_POST['description']);
    $id = intval($_POST['id'] ?? 0);
    
    if ($id > 0) {
        // Update
        $conn->query("UPDATE categories SET name='$name', slug='$slug', description='$description' WHERE id=$id");
        setFlashMessage('Category updated successfully', 'success');
    } else {
        // Add new
        $conn->query("INSERT INTO categories (name, slug, description) VALUES ('$name', '$slug', '$description')");
        setFlashMessage('Category added successfully', 'success');
    }
    redirect('categories.php');
}

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name");

// Get category for edit
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM categories WHERE id = $editId");
    $editCategory = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin Panel</title>
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
                    <a href="products.php" class="nav-link"><i class="fas fa-box"></i>Products</a>
                    <a href="categories.php" class="nav-link active"><i class="fas fa-tags"></i>Categories</a>
                    <a href="orders.php" class="nav-link"><i class="fas fa-shopping-cart"></i>Orders</a>
                    <a href="users.php" class="nav-link"><i class="fas fa-users"></i>Users</a>
                    <a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i>View Website</a>
                    <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Manage Categories</h3>
                    <button type="button" class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#categoryModal">
                        <i class="fas fa-plus me-2"></i>Add Category
                    </button>
                </div>
                
                <?php showFlashMessage(); ?>
                
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Products</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($category = $categories->fetch_assoc()): 
                                        $productCount = $conn->query("SELECT COUNT(*) as count FROM products WHERE category_id = {$category['id']} AND status = 1")->fetch_assoc()['count'];
                                    ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td><strong><?php echo $category['name']; ?></strong></td>
                                        <td><?php echo $category['slug']; ?></td>
                                        <td><?php echo substr($category['description'], 0, 50); ?>...</td>
                                        <td><span class="badge bg-primary"><?php echo $productCount; ?> products</span></td>
                                        <td>
                                            <a href="categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                            <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure? This will not delete associated products.')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $editCategory ? 'Edit' : 'Add'; ?> Category</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $editCategory['id'] ?? ''; ?>">
                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $editCategory['name'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo $editCategory['description'] ?? ''; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><?php echo $editCategory ? 'Update' : 'Add'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    <?php if($editCategory): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new mdb.Modal(document.getElementById('categoryModal'));
            modal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>
