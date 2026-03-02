<?php
session_start();
require_once '../includes/db_connect.php';

// Check if admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('admin_login.php');
}

$pageTitle = 'Dashboard';

// Get statistics
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 1")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'] ?? 0;

// Get recent orders
$recentOrders = $conn->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");

// Get low stock products
$lowStock = $conn->query("SELECT * FROM products WHERE stock < 10 AND status = 1 LIMIT 5");

// Get sales data for chart (last 7 days)
$salesData = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime("-$i days"));
    
    $sales = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = '$date' AND status != 'cancelled'")->fetch_assoc()['total'];
    $salesData[] = floatval($sales);
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1c2e 0%, #16213e 100%);
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 15px 25px;
            border-radius: 0 30px 30px 0;
            margin: 5px 0;
            transition: all 0.3s ease;
            position: relative;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        .admin-sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary-gradient);
            border-radius: 0 4px 4px 0;
        }
        .admin-sidebar .nav-link i {
            width: 30px;
            font-size: 1.1rem;
        }
        .stat-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }
        .stat-card .card-body {
            padding: 25px;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .stat-icon.users { background: var(--primary-gradient); }
        .stat-icon.products { background: var(--success-gradient); }
        .stat-icon.orders { background: var(--warning-gradient); }
        .stat-icon.revenue { background: var(--info-gradient); }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .stat-trend {
            font-size: 0.85rem;
            color: #27ae60;
            font-weight: 500;
        }
        .main-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .main-card .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 25px;
            border-radius: 20px 20px 0 0 !important;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .order-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.2s ease;
        }
        .order-item:hover {
            background: #f8f9fa;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .low-stock-table th {
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #f0f0f0;
        }
        .low-stock-table td {
            vertical-align: middle;
            padding: 15px;
        }
        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: scale(1.1);
        }
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
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
                    <a href="index.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
                    <a href="products.php" class="nav-link"><i class="fas fa-box"></i>Products</a>
                    <a href="categories.php" class="nav-link"><i class="fas fa-tags"></i>Categories</a>
                    <a href="orders.php" class="nav-link"><i class="fas fa-shopping-cart"></i>Orders</a>
                    <a href="users.php" class="nav-link"><i class="fas fa-users"></i>Users</a>
                    <div class="mt-auto">
                        <a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i>View Website</a>
                        <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
                    </div>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1 fw-bold">Dashboard</h3>
                        <p class="text-muted mb-0">Welcome back! Here's what's happening with your store today.</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="text-end me-3">
                            <p class="mb-0 fw-bold"><?php echo $_SESSION['user_name']; ?></p>
                            <small class="text-muted">Administrator</small>
                        </div>
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="stat-label mb-1">Total Users</p>
                                        <h3 class="stat-value mb-2"><?php echo $totalUsers; ?></h3>
                                        <span class="stat-trend"><i class="fas fa-arrow-up me-1"></i>+12% this month</span>
                                    </div>
                                    <div class="stat-icon users">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="stat-label mb-1">Products</p>
                                        <h3 class="stat-value mb-2"><?php echo $totalProducts; ?></h3>
                                        <span class="stat-trend"><i class="fas fa-arrow-up me-1"></i>+5 new this week</span>
                                    </div>
                                    <div class="stat-icon products">
                                        <i class="fas fa-box"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="stat-label mb-1">Orders</p>
                                        <h3 class="stat-value mb-2"><?php echo $totalOrders; ?></h3>
                                        <span class="stat-trend"><i class="fas fa-arrow-up me-1"></i>+8% this week</span>
                                    </div>
                                    <div class="stat-icon orders">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="stat-label mb-1">Revenue</p>
                                        <h3 class="stat-value mb-2">$<?php echo number_format($totalRevenue, 0); ?></h3>
                                        <span class="stat-trend"><i class="fas fa-arrow-up me-1"></i>+15% this month</span>
                                    </div>
                                    <div class="stat-icon revenue">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart and Recent Orders -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card main-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Sales Overview</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-mdb-toggle="dropdown">
                                        Last 7 Days
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                        <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                        <li><a class="dropdown-item" href="#">This Year</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="salesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card main-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Recent Orders</h5>
                                <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <?php while($order = $recentOrders->fetch_assoc()): ?>
                                <div class="order-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0 fw-bold"><?php echo $order['order_number']; ?></p>
                                            <small class="text-muted"><?php echo $order['user_name'] ?? 'Guest'; ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="status-badge bg-<?php echo ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'][$order['status']] ?? 'secondary'; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                            <p class="mb-0 small fw-bold mt-1">$<?php echo number_format($order['total_amount'], 2); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                <div class="card main-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Low Stock Products</h5>
                        <a href="products.php" class="btn btn-sm btn-primary">Manage Products</a>
                    </div>
                    <div class="card-body">
                        <?php if($lowStock->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover low-stock-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Current Stock</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($product = $lowStock->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $product['image']; ?>" alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover; margin-right: 15px;">
                                                <span class="fw-bold"><?php echo $product['name']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock'] < 5 ? 'danger' : 'warning'; ?> px-3 py-2">
                                                <?php echo $product['stock']; ?> units
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($product['stock'] < 5): ?>
                                                <span class="text-danger"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>Critical</span>
                                            <?php else: ?>
                                                <span class="text-warning"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>Low</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary btn-action" title="Restock">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted mb-0">All products have sufficient stock levels.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    <script>
        // Sales Chart with gradient and modern styling
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Create gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
        gradient.addColorStop(1, 'rgba(102, 126, 234, 0.01)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Sales ($)',
                    data: <?php echo json_encode($salesData); ?>,
                    borderColor: '#667eea',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#7f8c8d'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            color: '#7f8c8d',
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
