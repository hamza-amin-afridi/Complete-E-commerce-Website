<?php
$pageTitle = 'Login';
require_once 'includes/db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Migrate guest cart to user cart if exists
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $productId => $quantity) {
                        $existing = $conn->query("SELECT id, quantity FROM cart WHERE user_id = {$user['id']} AND product_id = $productId");
                        
                        if ($existing->num_rows > 0) {
                            $cartItem = $existing->fetch_assoc();
                            $newQuantity = $cartItem['quantity'] + $quantity;
                            $conn->query("UPDATE cart SET quantity = $newQuantity WHERE id = {$cartItem['id']}");
                        } else {
                            $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ({$user['id']}, $productId, $quantity)");
                        }
                    }
                    unset($_SESSION['cart']);
                }
                
                setFlashMessage('Welcome back, ' . $user['name'] . '!', 'success');
                
                // Redirect based on role or stored redirect
                if ($user['role'] == 'admin') {
                    redirect('admin/index.php');
                } else if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirect);
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Email not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - E-Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/E-Commerce%20Website/assets/css/style.css" rel="stylesheet">
    <style>
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="row g-0">
                <div class="col-md-6 d-none d-md-block">
                    <div class="h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; padding: 40px;">
                        <div class="text-center text-white">
                            <i class="fas fa-shopping-bag fa-5x mb-4"></i>
                            <h2>Welcome Back!</h2>
                            <p>Sign in to access your account and continue shopping.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4 p-md-5">
                        <h3 class="mb-4">Sign In</h3>
                        
                        <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-outline mb-4">
                                <input type="email" name="email" class="form-control form-control-lg" required>
                                <label class="form-label">Email address</label>
                            </div>
                            
                            <div class="form-outline mb-4">
                                <input type="password" name="password" class="form-control form-control-lg" required>
                                <label class="form-label">Password</label>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                <a href="forgot_password.php" class="text-primary">Forgot password?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-4">Sign In</button>
                        </form>
                        
                        <p class="text-center">Don't have an account? <a href="signup.php" class="text-primary">Sign up</a></p>
                        
                        <div class="text-center mt-4">
                            <a href="index.php" class="text-muted"><i class="fas fa-arrow-left me-2"></i>Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
</body>
</html>
