<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// Get message from POST data
$message_text = trim($_POST['message'] ?? '');
$has_file = isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE;

// Validate message or file
if (empty($message_text) && !$has_file) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Message or file required']);
    exit;
}

// Handle file upload if present
$file_data = null;
if ($has_file) {
    // Log file details for debugging
    error_log("File upload detected: " . json_encode($_FILES['file']));
    
    // Ensure uploads directory exists
    if (!file_exists('../uploads/chat')) {
        mkdir('../uploads/chat', 0755, true);
    }
    
    $allowed_extensions = get_allowed_file_extensions();
    $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    
    // Log file extension
    error_log("File extension: " . $file_ext);
    
    if (!in_array($file_ext, $allowed_extensions)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'File type not allowed: ' . $file_ext]);
        exit;
    }
    
    // Upload file
    $upload = handle_file_upload($_FILES['file'], '../uploads/chat');
    
    // Log upload result
    error_log("Upload result: " . json_encode($upload));
    
    if (!$upload['success']) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => $upload['error']]);
        exit;
    }
    
    $file_data = $upload;
}

// Update user activity
update_user_activity();

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Insert message into database
    if ($has_file) {
        $stmt = $conn->prepare("
            INSERT INTO messages (user_id, message, has_file, file_name, file_type, file_size)
            VALUES (?, ?, 1, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'], 
            $message_text, 
            $file_data['file_name'],
            $file_data['file_type'],
            $file_data['file_size']
        ]);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO messages (user_id, message)
            VALUES (?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $message_text]);
    }
    
    // Get the ID of the inserted message
    $message_id = $conn->lastInsertId();
    
    // Store file info in the files table if a file was uploaded
    if ($has_file) {
        $stmt = $conn->prepare("
            INSERT INTO files (message_id, file_name, original_name, file_type, file_size)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $message_id,
            $file_data['file_name'],
            $file_data['original_name'],
            $file_data['file_type'],
            $file_data['file_size']
        ]);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Get the message with user information
    $message = fetch_row("
        SELECT m.id, m.message, m.created_at, m.has_file, m.file_name, m.file_type, m.file_size,
               u.id as user_id, u.username, u.profile_pic
        FROM messages m
        JOIN users u ON m.user_id = u.id
        WHERE m.id = ?
    ", [$message_id]);
    
    // Format the created_at timestamp
    $message['created_at'] = date('M j, Y g:i A', strtotime($message['created_at']));
    
    // Add file info if needed
    if ($has_file) {
        // Use absolute URLs for file paths
        $file_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . 
                    $_SERVER['HTTP_HOST'] . 
                    dirname(dirname($_SERVER['PHP_SELF'])) . '/uploads/chat/' . $file_data['file_name'];
                    
        $message['file_path'] = '../uploads/chat/' . $file_data['file_name'];
        $message['file_url'] = $file_url;
        $message['original_file_name'] = $file_data['original_name'];
        $message['formatted_file_size'] = format_file_size($file_data['file_size']);
    }
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'data' => $message
    ]);
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()]);
}
?>