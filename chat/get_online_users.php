<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Only allow authenticated users
if (!is_logged_in()) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Update user activity
update_user_activity();

try {
    // Get online users
    $online_users = get_online_users();
    
    // Return success
    echo json_encode([
        'success' => true,
        'data' => $online_users
    ]);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to get online users']);
}
?>