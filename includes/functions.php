<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check and create upload directories if needed
require_once __DIR__ . '/check_uploads.php';

// Include profile helper functions
require_once __DIR__ . '/profile_helper.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect to a URL
function redirect($url) {
    header("Location: $url");
    exit;
}

// Sanitize user input
function sanitize($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Generate a success message
function success_message($message) {
    $_SESSION['success_message'] = $message;
}

// Generate an error message
function error_message($message) {
    $_SESSION['error_message'] = $message;
}

// Display messages and clear from session
function display_messages() {
    $output = '';
    
    if (isset($_SESSION['success_message'])) {
        $output .= '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        $output .= '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    
    return $output;
}

// Update user's last activity
function update_user_activity() {
    if (is_logged_in()) {
        try {
            global $conn;
            $stmt = $conn->prepare("
                INSERT INTO online_users (user_id, last_activity) 
                VALUES (:user_id, NOW()) 
                ON DUPLICATE KEY UPDATE last_activity = NOW()
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
        } catch (PDOException $e) {
            // Silently fail
        }
    }
}

// Clean up inactive users (called periodically)
function clean_inactive_users() {
    try {
        global $conn;
        // Remove users inactive for more than 5 minutes
        $stmt = $conn->prepare("DELETE FROM online_users WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        $stmt->execute();
    } catch (PDOException $e) {
        // Silently fail
    }
}

// Get user theme preference
function get_user_theme() {
    if (is_logged_in()) {
        try {
            $user = fetch_row("SELECT theme_preference FROM users WHERE id = ?", [$_SESSION['user_id']]);
            return $user['theme_preference'] ?? 'light';
        } catch (PDOException $e) {
            return 'light';
        }
    }
    
    // Default to light theme for guests or if error occurs
    return isset($_COOKIE['theme_preference']) ? $_COOKIE['theme_preference'] : 'light';
}

// Get current online users
function get_online_users() {
    try {
        clean_inactive_users(); // Clean up first
        
        return fetch_all("
            SELECT u.id, u.username, u.profile_pic
            FROM users u
            JOIN online_users o ON u.id = o.user_id
            ORDER BY u.username
        ");
    } catch (PDOException $e) {
        return [];
    }
}

// Function to handle file uploads
function handle_file_upload($file, $destination_folder) {
    // Log the file info
    error_log("File upload - Name: " . $file['name'] . ", Size: " . $file['size'] . ", Error: " . $file['error']);
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $error_message = isset($error_messages[$file['error']]) 
            ? $error_messages[$file['error']] 
            : 'Unknown upload error';
            
        error_log("Upload error: " . $error_message);
        
        return [
            'success' => false,
            'error' => $error_message
        ];
    }
    
    // Create the directory if it doesn't exist
    if (!file_exists($destination_folder)) {
        if (!mkdir($destination_folder, 0755, true)) {
            error_log("Failed to create directory: " . $destination_folder);
            return [
                'success' => false,
                'error' => 'Failed to create upload directory'
            ];
        }
    }
    
    // Check if directory is writable
    if (!is_writable($destination_folder)) {
        chmod($destination_folder, 0755); // Try to make it writable
        
        if (!is_writable($destination_folder)) {
            error_log("Directory is not writable: " . $destination_folder);
            return [
                'success' => false,
                'error' => 'Upload directory is not writable'
            ];
        }
    }
    
    // Get file info
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_type = $file['type'];
    
    // Validate file size (max 10MB)
    $max_size = 10 * 1024 * 1024; // 10MB
    if ($file_size > $max_size) {
        error_log("File too large: " . $file_size . " bytes");
        return [
            'success' => false,
            'error' => 'File size too large. Maximum allowed size is 10MB.'
        ];
    }
    
    // Check if file was actually uploaded
    if (!is_uploaded_file($file_tmp)) {
        error_log("Not a valid uploaded file: " . $file_tmp);
        return [
            'success' => false,
            'error' => 'Invalid upload attempt'
        ];
    }
    
    // Generate unique filename
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_filename = uniqid() . '_' . time() . '.' . $ext;
    
    // Set the destination path
    $destination = $destination_folder . '/' . $new_filename;
    
    // Move the uploaded file
    if (move_uploaded_file($file_tmp, $destination)) {
        error_log("File successfully uploaded to: " . $destination);
        
        // Set proper permissions
        chmod($destination, 0644);
        
        return [
            'success' => true,
            'original_name' => $file_name,
            'file_name' => $new_filename,
            'file_size' => $file_size,
            'file_type' => $file_type,
            'file_path' => $destination
        ];
    } else {
        $upload_error = error_get_last();
        error_log("Failed to move uploaded file. Error: " . ($upload_error ? $upload_error['message'] : 'Unknown error'));
        
        return [
            'success' => false,
            'error' => 'Failed to move uploaded file. Please check server permissions.'
        ];
    }
}

// Function to get allowed file extensions
function get_allowed_file_extensions() {
    return [
        // Images
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp',
        // Documents
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt',
        // Audio
        'mp3', 'wav', 'ogg',
        // Video
        'mp4', 'webm', 'avi', 'mov',
        // Archives
        'zip', 'rar'
    ];
}

// Function to check if file is an image
function is_image_file($file_type) {
    return in_array($file_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp']);
}

// Function to check if file is a video
function is_video_file($file_type) {
    return in_array($file_type, ['video/mp4', 'video/webm', 'video/avi', 'video/quicktime', 'video/x-msvideo']);
}

// Function to check if file is a document
function is_document_file($file_type) {
    return in_array($file_type, [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain'
    ]);
}

// Function to get file icon based on file type
function get_file_icon($file_type) {
    if (is_image_file($file_type)) {
        return 'bi-file-image';
    } elseif (is_video_file($file_type)) {
        return 'bi-file-play';
    } elseif (is_document_file($file_type)) {
        return 'bi-file-text';
    } elseif (strpos($file_type, 'audio/') === 0) {
        return 'bi-file-music';
    } elseif (strpos($file_type, 'application/zip') === 0 || strpos($file_type, 'application/x-rar') === 0) {
        return 'bi-file-zip';
    } else {
        return 'bi-file-earmark';
    }
}

// Function to format file size
function format_file_size($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}
?>