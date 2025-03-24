<?php
// Create upload directories if they don't exist
$upload_dirs = [
    '../uploads',
    '../uploads/profile',
    '../uploads/chat'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log("Failed to create directory: $dir");
        } else {
            // Set proper permissions
            chmod($dir, 0755);
        }
    } else if (!is_writable($dir)) {
        // Try to make the directory writable
        chmod($dir, 0755);
        if (!is_writable($dir)) {
            error_log("Directory exists but is not writable: $dir");
        }
    }
}
?>