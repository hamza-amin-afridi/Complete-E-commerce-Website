<?php
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('Please login to submit a review', 'warning');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = sanitize($conn, $_POST['comment'] ?? '');
    $userId = $_SESSION['user_id'];
    
    // Validation
    if ($productId <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
        setFlashMessage('Please provide a rating and review comment', 'danger');
        redirect('product.php?id=' . $productId);
    }
    
    // Check if user has already reviewed this product
    $existing = $conn->query("SELECT id FROM reviews WHERE product_id = $productId AND user_id = $userId");
    
    if ($existing->num_rows > 0) {
        // Update existing review
        $conn->query("UPDATE reviews SET rating = $rating, comment = '$comment', created_at = NOW() WHERE product_id = $productId AND user_id = $userId");
        setFlashMessage('Your review has been updated!', 'success');
    } else {
        // Insert new review
        $conn->query("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES ($productId, $userId, $rating, '$comment')");
        setFlashMessage('Thank you for your review!', 'success');
    }
    
    // Get product slug for redirect
    $product = $conn->query("SELECT slug FROM products WHERE id = $productId")->fetch_assoc();
    redirect('product.php?slug=' . $product['slug']);
} else {
    redirect('index.php');
}
?>
