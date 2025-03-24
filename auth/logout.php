<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Only process if user is logged in
if (is_logged_in()) {
    // Remove from online users
    try {
        $stmt = $conn->prepare("DELETE FROM online_users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Silently fail
    }
    
    // Destroy the session
    session_destroy();
}

// Redirect to login page
redirect('../auth/login.php');
?>