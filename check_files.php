<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check PHP version
echo "PHP Version: " . phpversion() . "<br>";

// Check upload settings
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post max size: " . ini_get('post_max_size') . "<br>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max file uploads: " . ini_get('max_file_uploads') . "<br>";
echo "File uploads enabled: " . (ini_get('file_uploads') ? 'Yes' : 'No') . "<br>";

echo "<hr>";

// Check directory existence and permissions
$dirs_to_check = [
    'uploads',
    'uploads/profile',
    'uploads/chat'
];

echo "<h2>Directory Check</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Directory</th><th>Exists</th><th>Writable</th><th>Permissions</th></tr>";

foreach ($dirs_to_check as $dir) {
    echo "<tr>";
    echo "<td>$dir</td>";
    
    $exists = file_exists($dir);
    echo "<td>" . ($exists ? 'Yes' : 'No') . "</td>";
    
    $writable = $exists ? is_writable($dir) : false;
    echo "<td>" . ($writable ? 'Yes' : 'No') . "</td>";
    
    $perms = $exists ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';
    echo "<td>$perms</td>";
    
    echo "</tr>";
}

echo "</table>";

echo "<hr>";

// Try a test file upload to the upload directory
echo "<h2>Upload Test</h2>";

if (!file_exists('uploads/chat')) {
    mkdir('uploads/chat', 0755, true);
}

$test_file = 'uploads/chat/test_' . time() . '.txt';
$success = file_put_contents($test_file, 'This is a test file.');

if ($success) {
    echo "Successfully created test file: $test_file<br>";
    echo "File size: " . filesize($test_file) . " bytes<br>";
    echo "File permissions: " . substr(sprintf('%o', fileperms($test_file)), -4) . "<br>";
    
    // Try to delete the test file
    if (unlink($test_file)) {
        echo "Successfully deleted test file.<br>";
    } else {
        echo "Failed to delete test file. Check permissions.<br>";
    }
} else {
    echo "Failed to create test file. Check directory permissions.<br>";
}

echo "<hr>";

// Check if current URL matches
echo "<h2>URL Check</h2>";
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . 
               $_SERVER['HTTP_HOST'] . 
               dirname($_SERVER['PHP_SELF']);
               
echo "Current URL: $current_url<br>";
echo "Uploads URL should be: $current_url/uploads/<br>";

echo "<hr>";

// Test image display
echo "<h2>Image Test</h2>";

// Create a small test image
$img_file = 'uploads/chat/test_image_' . time() . '.png';
$img = imagecreatetruecolor(100, 100);
$bg_color = imagecolorallocate($img, 255, 100, 100);
imagefill($img, 0, 0, $bg_color);
$text_color = imagecolorallocate($img, 255, 255, 255);
imagestring($img, 5, 20, 40, 'Test', $text_color);

if (imagepng($img, $img_file)) {
    echo "Test image created: $img_file<br>";
    $img_url = "$current_url/$img_file";
    echo "<img src='$img_file' alt='Test image'><br>";
    echo "Direct URL: <a href='$img_file' target='_blank'>$img_file</a><br>";
    
    // Try to delete the test image
    imagedestroy($img);
    if (unlink($img_file)) {
        echo "Successfully deleted test image.<br>";
    } else {
        echo "Failed to delete test image. Check permissions.<br>";
    }
} else {
    echo "Failed to create test image. Check GD library and directory permissions.<br>";
}
?>