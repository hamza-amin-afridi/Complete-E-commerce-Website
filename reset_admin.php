<?php
// Admin password reset script
// Run this file once to reset admin password, then delete it for security

require_once 'includes/db_connect.php';

// Generate correct hash for 'admin123'
$correctHash = password_hash('admin123', PASSWORD_DEFAULT);

echo "Generated hash for 'admin123': " . $correctHash . "<br><br>";

// Check if admin exists
$result = $conn->query("SELECT * FROM users WHERE email = 'admin@eshop.com'");

if ($result->num_rows > 0) {
    // Update admin password
    $sql = "UPDATE users SET password = '$correctHash' WHERE email = 'admin@eshop.com'";
    if ($conn->query($sql)) {
        echo "✅ Admin password reset successfully!<br>";
        echo "Email: admin@eshop.com<br>";
        echo "Password: admin123<br><br>";
        echo "<strong>Delete this file (reset_admin.php) after use for security!</strong>";
    } else {
        echo "❌ Error updating password: " . $conn->error;
    }
} else {
    // Create new admin
    $sql = "INSERT INTO users (name, email, password, role) VALUES 
            ('Admin', 'admin@eshop.com', '$correctHash', 'admin')";
    if ($conn->query($sql)) {
        echo "✅ Admin user created successfully!<br>";
        echo "Email: admin@eshop.com<br>";
        echo "Password: admin123<br><br>";
        echo "<strong>Delete this file (reset_admin.php) after use for security!</strong>";
    } else {
        echo "❌ Error creating admin: " . $conn->error;
    }
}
?>
