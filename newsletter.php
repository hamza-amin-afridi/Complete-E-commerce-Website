<?php
// Newsletter subscription handler
require_once 'includes/db_connect.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = sanitize($conn, $_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
    } else {
        // Check if email already exists
        $check = $conn->query("SELECT id FROM newsletter WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $response['message'] = 'You are already subscribed!';
        } else {
            // Insert into newsletter table
            $sql = "INSERT INTO newsletter (email, subscribed_at, status) VALUES ('$email', NOW(), 1)";
            if ($conn->query($sql)) {
                $response['success'] = true;
                $response['message'] = 'Thank you for subscribing to our newsletter!';
            } else {
                $response['message'] = 'Error subscribing. Please try again.';
            }
        }
    }
} else {
    $response['message'] = 'Invalid request';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
