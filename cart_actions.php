<?php
session_start();
require_once 'includes/db_connect.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addToCart();
        break;
    case 'update':
        updateCart();
        break;
    case 'remove':
        removeFromCart();
        break;
    case 'clear':
        clearCart();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addToCart() {
    global $conn;
    
    $productId = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        return;
    }
    
    // Check product availability
    $product = $conn->query("SELECT stock FROM products WHERE id = $productId AND status = 1");
    if ($product->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    $stock = $product->fetch_assoc()['stock'];
    
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        
        // Check if product already in cart
        $existing = $conn->query("SELECT id, quantity FROM cart WHERE user_id = $userId AND product_id = $productId");
        
        if ($existing->num_rows > 0) {
            $cartItem = $existing->fetch_assoc();
            $newQuantity = $cartItem['quantity'] + $quantity;
            
            if ($newQuantity > $stock) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                return;
            }
            
            $conn->query("UPDATE cart SET quantity = $newQuantity WHERE id = {$cartItem['id']}");
        } else {
            if ($quantity > $stock) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                return;
            }
            
            $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($userId, $productId, $quantity)");
        }
        
        // Update popularity
        $conn->query("UPDATE products SET popularity = popularity + 1 WHERE id = $productId");
        
        // Get updated cart count
        $cartCount = getCartCount($userId);
        echo json_encode(['success' => true, 'cart_count' => $cartCount]);
    } else {
        // Guest cart using session
        if (isset($_SESSION['cart'][$productId])) {
            $newQuantity = $_SESSION['cart'][$productId] + $quantity;
            if ($newQuantity > $stock) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                return;
            }
            $_SESSION['cart'][$productId] = $newQuantity;
        } else {
            if ($quantity > $stock) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                return;
            }
            $_SESSION['cart'][$productId] = $quantity;
        }
        
        $cartCount = array_sum($_SESSION['cart']);
        echo json_encode(['success' => true, 'cart_count' => $cartCount]);
    }
}

function updateCart() {
    global $conn;
    
    $cartId = intval($_POST['cart_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($cartId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item or quantity']);
        return;
    }
    
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        
        // Get product stock
        $result = $conn->query("SELECT p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = $cartId AND c.user_id = $userId");
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            return;
        }
        
        $stock = $result->fetch_assoc()['stock'];
        
        if ($quantity > $stock) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            return;
        }
        
        $conn->query("UPDATE cart SET quantity = $quantity WHERE id = $cartId AND user_id = $userId");
        
        $cartData = calculateCartTotals($userId);
        echo json_encode([
            'success' => true, 
            'cart_count' => $cartData['count'],
            'subtotal' => $cartData['subtotal'],
            'total' => $cartData['total']
        ]);
    } else {
        // Guest cart
        if (isset($_SESSION['cart'][$cartId])) {
            $_SESSION['cart'][$cartId] = $quantity;
        }
        
        $cartData = calculateGuestCartTotals();
        echo json_encode([
            'success' => true, 
            'cart_count' => $cartData['count'],
            'subtotal' => $cartData['subtotal'],
            'total' => $cartData['total']
        ]);
    }
}

function removeFromCart() {
    global $conn;
    
    $cartId = intval($_POST['cart_id'] ?? 0);
    
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $conn->query("DELETE FROM cart WHERE id = $cartId AND user_id = $userId");
        
        $cartData = calculateCartTotals($userId);
        echo json_encode([
            'success' => true, 
            'cart_count' => $cartData['count'],
            'subtotal' => $cartData['subtotal'],
            'total' => $cartData['total']
        ]);
    } else {
        if (isset($_SESSION['cart'][$cartId])) {
            unset($_SESSION['cart'][$cartId]);
        }
        
        $cartData = calculateGuestCartTotals();
        echo json_encode([
            'success' => true, 
            'cart_count' => $cartData['count'],
            'subtotal' => $cartData['subtotal'],
            'total' => $cartData['total']
        ]);
    }
}

function clearCart() {
    global $conn;
    
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $conn->query("DELETE FROM cart WHERE user_id = $userId");
    } else {
        $_SESSION['cart'] = [];
    }
    
    echo json_encode(['success' => true, 'cart_count' => 0]);
}

function getCartCount($userId) {
    global $conn;
    $result = $conn->query("SELECT SUM(quantity) as count FROM cart WHERE user_id = $userId");
    return $result->fetch_assoc()['count'] ?? 0;
}

function calculateCartTotals($userId) {
    global $conn;
    
    $result = $conn->query("SELECT SUM(c.quantity * p.price) as subtotal, SUM(c.quantity) as count FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $userId");
    $data = $result->fetch_assoc();
    
    $subtotal = $data['subtotal'] ?? 0;
    $count = $data['count'] ?? 0;
    $shipping = $subtotal > 50 ? 0 : 10;
    $total = $subtotal + $shipping;
    
    return ['subtotal' => $subtotal, 'total' => $total, 'count' => $count];
}

function calculateGuestCartTotals() {
    global $conn;
    
    $subtotal = 0;
    $count = 0;
    
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $result = $conn->query("SELECT price FROM products WHERE id = $productId");
            if ($row = $result->fetch_assoc()) {
                $subtotal += $row['price'] * $quantity;
                $count += $quantity;
            }
        }
    }
    
    $shipping = $subtotal > 50 ? 0 : 10;
    $total = $subtotal + $shipping;
    
    return ['subtotal' => $subtotal, 'total' => $total, 'count' => $count];
}
?>
