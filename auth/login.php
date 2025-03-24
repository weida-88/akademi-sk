<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('../chat/');
}

$errors = [];

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Prevent database credentials from being used as username
    if ($username === $dbname || $username === $host || $username === $db_username) {
        $username = '';
    }
    // Validate input
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // Attempt login if no validation errors
    if (empty($errors)) {
        try {
            // Check if username exists
            $user = fetch_row("SELECT id, username, password, theme_preference, profile_pic FROM users WHERE username = ?", [$username]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['theme'] = $user['theme_preference'];
                $_SESSION['profile_pic'] = $user['profile_pic'];
                
                // Update user activity
                update_user_activity();
                
                // Redirect to chat
                redirect('../chat/');
            } else {
                $errors['login'] = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            error_message('Login failed. Please try again later.');
        }
    }
}

// Set page title
$page_title = 'Login - Akademi SK';

// Include header
require_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Login to Akademi SK</h3>
                </div>
                <div class="card-body">
                    <?= display_messages() ?>
                    
                    <?php if (isset($errors['login'])): ?>
                        <div class="alert alert-danger"><?= $errors['login'] ?></div>
                    <?php endif; ?>
                    
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                id="username" name="username" placeholder="Enter your username" autocomplete="off"
                                value="<?= isset($username) && $username != $dbname && $username != $host && $username != $db_username ? $username : '' ?>">
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?= $errors['username'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                id="password" name="password">
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= $errors['password'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    Don't have an account? <a href="../auth/register.php">Register</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>