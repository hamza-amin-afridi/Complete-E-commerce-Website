<?php
// Promo code handler
require_once 'includes/db_connect.php';

$response = ['success' => false, 'message' => '', 'discount' => 0, 'new_total' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code']) && isset($_POST['subtotal'])) {
    $code = sanitize($conn, strtoupper($_POST['code']));
    $subtotal = floatval($_POST['subtotal']);
    $cartItems = json_decode($_POST['cart_items'] ?? '[]', true);
    
    // Check if promo code exists and is valid
    $sql = "SELECT * FROM promo_codes 
            WHERE code = '$code' 
            AND status = 1 
            AND (start_date IS NULL OR start_date <= CURDATE()) 
            AND (end_date IS NULL OR end_date >= CURDATE())
            AND (max_uses IS NULL OR uses_count < max_uses)";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $promo = $result->fetch_assoc();
        
        // Check minimum order amount
        if ($subtotal < $promo['min_order_amount']) {
            $response['message'] = 'Minimum order amount of $' . number_format($promo['min_order_amount'], 2) . ' required';
        } else {
            $discount = 0;
            $appliesTo = $promo['applies_to'];
            
            // Calculate discount based on type
            if ($promo['discount_type'] === 'percentage') {
                if ($appliesTo === 'all') {
                    $discount = $subtotal * ($promo['discount_value'] / 100);
                } elseif ($appliesTo === 'category' && $promo['category_id']) {
                    // Calculate discount for specific category
                    foreach ($cartItems as $item) {
                        if ($item['category_id'] == $promo['category_id']) {
                            $discount += ($item['price'] * $item['quantity']) * ($promo['discount_value'] / 100);
                        }
                    }
                } elseif ($appliesTo === 'product' && $promo['product_id']) {
                    // Calculate discount for specific product
                    foreach ($cartItems as $item) {
                        if ($item['product_id'] == $promo['product_id']) {
                            $discount += ($item['price'] * $item['quantity']) * ($promo['discount_value'] / 100);
                        }
                    }
                }
            } else { // fixed amount
                $discount = $promo['discount_value'];
                if ($discount > $subtotal) {
                    $discount = $subtotal;
                }
            }
            
            // Store promo code in session
            $_SESSION['promo_code'] = [
                'code' => $code,
                'discount' => $discount,
                'id' => $promo['id']
            ];
            
            // Update uses count
            $conn->query("UPDATE promo_codes SET uses_count = uses_count + 1 WHERE id = {$promo['id']}");
            
            $shipping = $subtotal > 50 ? 0 : 10;
            $newTotal = $subtotal + $shipping - $discount;
            
            $response['success'] = true;
            $response['message'] = 'Promo code applied successfully!';
            $response['discount'] = $discount;
            $response['new_total'] = $newTotal;
            $response['code'] = $code;
        }
    } else {
        $response['message'] = 'Invalid or expired promo code';
    }
} else {
    $response['message'] = 'Invalid request';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
