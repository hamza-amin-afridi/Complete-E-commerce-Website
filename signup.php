<?php
$pageTitle = 'Sign Up';
require_once 'includes/db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = 'Email address already registered';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$hashedPassword')";
            
            if ($conn->query($sql)) {
                $success = 'Account created successfully! Please sign in.';
            } else {
                $error = 'Error creating account. Please try again.';
            }
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
        .signup-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .signup-card {
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
    <div class="signup-page">
        <div class="signup-card">
            <div class="row g-0">
                <div class="col-md-6 d-none d-md-block">
                    <div class="h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; padding: 40px;">
                        <div class="text-center text-white">
                            <i class="fas fa-user-plus fa-5x mb-4"></i>
                            <h2>Join Us Today!</h2>
                            <p>Create an account to start shopping and enjoy exclusive benefits.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4 p-md-5">
                        <h3 class="mb-4">Create Account</h3>
                        
                        <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">Sign In Now</a>
                        </div>
                        <?php else: ?>
                        
                        <form method="POST" action="">
                            <div class="form-outline mb-4">
                                <input type="text" name="name" class="form-control form-control-lg" required>
                                <label class="form-label">Full Name</label>
                            </div>
                            
                            <div class="form-outline mb-4">
                                <input type="email" name="email" class="form-control form-control-lg" required>
                                <label class="form-label">Email address</label>
                            </div>
                            
                            <div class="form-outline mb-4">
                                <input type="tel" name="phone" class="form-control form-control-lg">
                                <label class="form-label">Phone (Optional)</label>
                            </div>
                            
                            <div class="form-outline mb-4">
                                <input type="password" name="password" class="form-control form-control-lg" id="password" required>
                                <label class="form-label">Password</label>
                                <div id="password-strength" class="form-text"></div>
                            </div>
                            
                            <div class="form-outline mb-4">
                                <input type="password" name="confirm_password" class="form-control form-control-lg" required>
                                <label class="form-label">Confirm Password</label>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-4">Create Account</button>
                        </form>
                        
                        <p class="text-center">Already have an account? <a href="login.php" class="text-primary">Sign in</a></p>
                        
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="index.php" class="text-muted"><i class="fas fa-arrow-left me-2"></i>Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/E-Commerce%20Website/assets/js/main.js"></script>
</body>
</html>
