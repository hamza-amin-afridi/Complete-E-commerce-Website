<?php
$pageTitle = 'Forgot Password';
require_once 'includes/db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($conn, $_POST['email']);
    
    if (empty($email)) {
        $message = 'Please enter your email address';
        $type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
        $type = 'danger';
    } else {
        // Check if email exists
        $result = $conn->query("SELECT id, name FROM users WHERE email = '$email'");
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $conn->query("UPDATE users SET reset_token = '$token', reset_expires = '$expires' WHERE id = {$user['id']}");
            
            // In a real application, you would send an email here
            // For demo purposes, we'll just show the reset link
            $resetLink = "reset_password.php?token=$token";
            
            $message = "Password reset instructions have been sent to your email. <br><small class='text-muted'>Demo: <a href='$resetLink'>Click here to reset</a></small>";
            $type = 'success';
        } else {
            $message = 'If this email exists in our system, you will receive reset instructions.';
            $type = 'info';
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
        .auth-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                    <h3>Forgot Password?</h3>
                    <p class="text-muted">Enter your email address and we'll send you instructions to reset your password.</p>
                </div>
                
                <?php if($message): ?>
                <div class="alert alert-<?php echo $type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-outline mb-4">
                        <input type="email" name="email" class="form-control form-control-lg" required>
                        <label class="form-label">Email address</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-4">Send Reset Link</button>
                </form>
                
                <div class="text-center">
                    <p>Remember your password? <a href="login.php" class="text-primary">Sign in</a></p>
                    <a href="index.php" class="text-muted"><i class="fas fa-arrow-left me-2"></i>Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
</body>
</html>
