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

// Optional parameter to get messages after a certain ID
$after_id = isset($_GET['after_id']) ? (int)$_GET['after_id'] : 0;

// Update user activity
update_user_activity();

try {
    // Get messages with user information
    $query = "
        SELECT m.id, m.message, m.created_at, m.has_file, m.file_name, m.file_type, m.file_size,
               u.id as user_id, u.username, u.profile_pic
        FROM messages m
        JOIN users u ON m.user_id = u.id
    ";
    
    // Add condition if after_id is provided
    if ($after_id > 0) {
        $query .= " WHERE m.id > ?";
        $params = [$after_id];
    } else {
        // Limit to last 50 messages if getting all
        $query .= " ORDER BY m.id DESC LIMIT 50";
        $params = [];
    }
    
    // Execute query
    $messages = fetch_all($query, $params);
    
    // Reverse the order if we got the most recent messages
    if ($after_id === 0) {
        $messages = array_reverse($messages);
    }
    
    // Format the messages
    foreach ($messages as &$message) {
        // Format the created_at timestamp
        $message['created_at'] = date('M j, Y g:i A', strtotime($message['created_at']));
        
        // Add file information if message has a file
        if ($message['has_file']) {
            // Get the original filename from files table
            $file = fetch_row("
                SELECT original_name FROM files 
                WHERE message_id = ? LIMIT 1
            ", [$message['id']]);
            
            if ($file) {
                $message['original_file_name'] = $file['original_name'];
            } else {
                $message['original_file_name'] = $message['file_name'];
            }
            
            $message['file_path'] = '../uploads/chat/' . $message['file_name'];
            $message['formatted_file_size'] = format_file_size($message['file_size']);
        }
    }
    
    // Return success
    echo json_encode([
        'success' => true,
        'data' => $messages
    ]);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to get messages']);
}
?>