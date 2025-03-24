<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('../chat/');
}

$errors = [];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate username
    $username = sanitize($_POST['username'] ?? '');
    
    // Prevent database credentials from being used as username
    if ($username === $dbname || $username === $host || $username === $db_username) {
        $username = '';
        $errors['username'] = 'Please choose a different username';
    }
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors['username'] = 'Username must be between 3 and 20 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    } else {
        // Check if username already exists
        $user = fetch_row("SELECT id FROM users WHERE username = ?", [$username]);
        if ($user) {
            $errors['username'] = 'Username already exists';
        }
    }
    
    // Validate email
    $email = sanitize($_POST['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
        // Check if email already exists
        $user = fetch_row("SELECT id FROM users WHERE email = ?", [$email]);
        if ($user) {
            $errors['email'] = 'Email already exists';
        }
    }
    
    // Validate password
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // If no errors, create user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hashed_password]);
            
            success_message('Registration successful! You can now log in.');
            redirect('../auth/login.php');
        } catch (PDOException $e) {
            error_message('Registration failed. Please try again later.');
        }
    }
}

// Set page title
$page_title = 'Register - Akademi SK';

// Include header
require_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Create an Account</h3>
                </div>
                <div class="card-body">
                    <?= display_messages() ?>
                    
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                id="username" name="username" placeholder="Choose a username" autocomplete="off" 
                                value="<?= isset($username) && $username != $dbname && $username != $host && $username != $db_username ? $username : '' ?>">
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?= $errors['username'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                id="email" name="email" value="<?= $email ?? '' ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
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
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                id="confirm_password" name="confirm_password">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    Already have an account? <a href="../auth/login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>