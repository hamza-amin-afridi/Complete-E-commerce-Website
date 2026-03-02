<?php
// Logout functionality
session_start();
require_once 'includes/db_connect.php';

// Clear all session data
session_unset();
session_destroy();

// Start a new session for flash message
session_start();
setFlashMessage('You have been logged out successfully', 'success');

redirect('index.php');
?>
