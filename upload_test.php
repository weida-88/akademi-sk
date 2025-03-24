<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h2>FILES Data</h2>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    // Check upload directory
    $upload_dir = 'uploads/chat';
    if (!file_exists($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            echo "<p>Created upload directory: $upload_dir</p>";
        } else {
            echo "<p style='color: red;'>Failed to create upload directory: $upload_dir</p>";
        }
    } else {
        echo "<p>Upload directory exists: $upload_dir</p>";
        echo "<p>Is writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "</p>";
        echo "<p>Permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "</p>";
    }
    
    // Try to upload the file
    if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['test_file']['tmp_name'];
        $name = $_FILES['test_file']['name'];
        $dest = $upload_dir . '/' . uniqid() . '_' . $name;
        
        if (move_uploaded_file($tmp_name, $dest)) {
            echo "<p style='color: green;'>File successfully uploaded to: $dest</p>";
        } else {
            echo "<p style='color: red;'>Failed to move uploaded file.</p>";
            echo "<p>PHP Error: " . error_get_last()['message'] . "</p>";
        }
    } else if (isset($_FILES['test_file'])) {
        $error_codes = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $error_code = $_FILES['test_file']['error'];
        $error_msg = isset($error_codes[$error_code]) ? $error_codes[$error_code] : 'Unknown error';
        
        echo "<p style='color: red;'>Upload error: $error_msg (code: $error_code)</p>";
    }
}

// PHP Configuration
echo "<h2>PHP Configuration</h2>";
echo "<ul>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>max_file_uploads: " . ini_get('max_file_uploads') . "</li>";
echo "<li>memory_limit: " . ini_get('memory_limit') . "</li>";
echo "<li>file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "</li>";
echo "<li>max_execution_time: " . ini_get('max_execution_time') . "</li>";
echo "</ul>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-form { padding: 20px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>File Upload Debug</h1>
    
    <div class="test-form">
        <h2>Test File Upload</h2>
        <form method="POST" enctype="multipart/form-data">
            <p>
                <label for="test_file">Select a file:</label>
                <input type="file" id="test_file" name="test_file">
            </p>
            <p>
                <label for="test_text">Test text field:</label>
                <input type="text" id="test_text" name="test_text" value="Test message">
            </p>
            <p>
                <button type="submit">Upload File</button>
            </p>
        </form>
    </div>
    
    <div class="test-form">
        <h2>JavaScript File Upload Test</h2>
        <p>
            <label for="js_test_file">Select a file:</label>
            <input type="file" id="js_test_file" name="js_test_file">
        </p>
        <p>
            <button type="button" id="js_upload_btn">Upload with JS</button>
        </p>
        <div id="js_result"></div>
    </div>
    
    <script>
        document.getElementById('js_upload_btn').addEventListener('click', function() {
            const fileInput = document.getElementById('js_test_file');
            const resultDiv = document.getElementById('js_result');
            
            if (!fileInput.files.length) {
                resultDiv.innerHTML = '<p style="color: red;">Please select a file first</p>';
                return;
            }
            
            const file = fileInput.files[0];
            resultDiv.innerHTML = '<p>Uploading file: ' + file.name + ' (' + file.size + ' bytes)</p>';
            
            const formData = new FormData();
            formData.append('test_file', file);
            formData.append('test_text', 'Sent via JavaScript');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                resultDiv.innerHTML += '<p>Response received: ' + response.status + '</p>';
                return response.text();
            })
            .then(html => {
                resultDiv.innerHTML += '<p>Upload complete!</p>';
                // Create an iframe to show the response
                const iframe = document.createElement('iframe');
                iframe.style.width = '100%';
                iframe.style.height = '300px';
                iframe.style.border = '1px solid #ccc';
                iframe.srcdoc = html;
                resultDiv.appendChild(iframe);
            })
            .catch(error => {
                resultDiv.innerHTML += '<p style="color: red;">Error: ' + error.message + '</p>';
            });
        });
    </script>
</body>
</html>