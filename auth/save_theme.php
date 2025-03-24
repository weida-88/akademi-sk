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
    // For non-authenticated users, just set a cookie
    $theme = $_POST['theme'] ?? 'light';
    setcookie('theme_preference', $theme, time() + (86400 * 365), '/'); // 1 year
    
    echo json_encode(['success' => true, 'message' => 'Theme preference saved to cookie']);
    exit;
}

// Get theme from POST data
$theme = $_POST['theme'] ?? 'light';

// Validate theme
if ($theme !== 'light' && $theme !== 'dark') {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid theme']);
    exit;
}

try {
    // Update user's theme preference
    $stmt = $conn->prepare("
        UPDATE users
        SET theme_preference = ?
        WHERE id = ?
    ");
    $stmt->execute([$theme, $_SESSION['user_id']]);
    
    // Update session
    $_SESSION['theme'] = $theme;
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Theme preference saved'
    ]);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to save theme preference']);
}
?>