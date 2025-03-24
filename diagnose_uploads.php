<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Upload Diagnostics</h1>";

// Check PHP settings
echo "<h2>PHP Configuration</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "file_uploads enabled: " . (ini_get('file_uploads') ? 'Yes' : 'No') . "\n";
echo "</pre>";

// Check upload directories
$upload_paths = [
    'uploads',
    'uploads/profile',
    'uploads/chat'
];

echo "<h2>Directory Status</h2>";
echo "<ul>";

foreach ($upload_paths as $path) {
    echo "<li>$path: ";
    
    if (!file_exists($path)) {
        echo "<span style='color:red'>Does not exist</span>";
        echo " - Attempting to create...";
        
        if (mkdir($path, 0777, true)) {
            echo "<span style='color:green'>Created successfully</span>";
        } else {
            echo "<span style='color:red'>Failed to create</span>";
        }
    } else {
        echo "<span style='color:green'>Exists</span>";
        
        // Check if directory is writable
        if (is_writable($path)) {
            echo ", <span style='color:green'>Writable</span>";
        } else {
            echo ", <span style='color:red'>Not writable</span>";
            echo " - Attempting to set permissions...";
            
            if (chmod($path, 0777)) {
                echo "<span style='color:green'>Permissions set</span>";
            } else {
                echo "<span style='color:red'>Failed to set permissions</span>";
            }
        }
        
        // Show directory permissions
        echo ", Permissions: " . substr(sprintf('%o', fileperms($path)), -4);
    }
    
    echo "</li>";
}

echo "</ul>";

// Test file creation
echo "<h2>File Creation Test</h2>";

foreach ($upload_paths as $path) {
    $test_file = "$path/test_" . time() . ".txt";
    echo "<p>Testing in $path: ";
    
    $content = "Test file created on " . date('Y-m-d H:i:s');
    
    if (file_put_contents($test_file, $content)) {
        echo "<span style='color:green'>File created successfully</span>";
        
        // Clean up test file
        if (unlink($test_file)) {
            echo ", Test file removed";
        } else {
            echo ", <span style='color:red'>Failed to remove test file</span>";
        }
    } else {
        echo "<span style='color:red'>Failed to create test file</span>";
    }
    
    echo "</p>";
}

// Test file upload
echo "<h2>File Upload Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_upload'])) {
    echo "<pre>";
    echo "Upload data: ";
    print_r($_FILES['test_upload']);
    echo "</pre>";
    
    if ($_FILES['test_upload']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['test_upload']['tmp_name'];
        $name = $_FILES['test_upload']['name'];
        $dest = "uploads/test_" . time() . "_" . $name;
        
        if (move_uploaded_file($tmp_name, $dest)) {
            echo "<p style='color:green'>File uploaded successfully to $dest</p>";
            echo "<p>File size: " . filesize($dest) . " bytes</p>";
        } else {
            echo "<p style='color:red'>Failed to move uploaded file!</p>";
            $error = error_get_last();
            echo "<p>Error: " . ($error ? $error['message'] : 'Unknown error') . "</p>";
        }
    } else {
        $error_codes = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $error_message = isset($error_codes[$_FILES['test_upload']['error']]) 
            ? $error_codes[$_FILES['test_upload']['error']] 
            : 'Unknown error';
            
        echo "<p style='color:red'>Upload error: $error_message (code: {$_FILES['test_upload']['error']})</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <h3>Test Upload</h3>
    <p>
        <input type="file" name="test_upload">
    </p>
    <p>
        <button type="submit">Upload Test File</button>
    </p>
</form>