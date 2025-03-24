<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success = false;
    
    // Get current user data
    $user = fetch_row("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    
    // Check if profile picture is being updated
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['profile_pic']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors['profile_pic'] = 'Only JPG, PNG, GIF, and WEBP files are allowed.';
        } else {
            // Handle profile picture upload
            $upload = handle_file_upload($_FILES['profile_pic'], '../uploads/profile');
            
            if ($upload['success']) {
                // Update profile picture in database
                try {
                    $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                    $stmt->execute([$upload['file_name'], $_SESSION['user_id']]);
                    
                    // Delete old profile pic if it's not the default
                    if ($user['profile_pic'] !== 'default-avatar.png' && file_exists('../uploads/profile/' . $user['profile_pic'])) {
                        unlink('../uploads/profile/' . $user['profile_pic']);
                    }
                    
                    $success = true;
                } catch (PDOException $e) {
                    $errors['db'] = 'Failed to update profile picture.';
                }
            } else {
                $errors['profile_pic'] = $upload['error'];
            }
        }
    }
    
    // Update username if provided
    if (isset($_POST['username']) && !empty($_POST['username'])) {
        $username = sanitize($_POST['username']);
        
        // Validate username
        if ($username !== $user['username']) {
            if (strlen($username) < 3 || strlen($username) > 20) {
                $errors['username'] = 'Username must be between 3 and 20 characters.';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
            } else {
                // Check if username is already taken
                $existing_user = fetch_row("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $_SESSION['user_id']]);
                
                if ($existing_user) {
                    $errors['username'] = 'Username is already taken.';
                } else {
                    // Update username
                    try {
                        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                        $stmt->execute([$username, $_SESSION['user_id']]);
                        
                        // Update session
                        $_SESSION['username'] = $username;
                        
                        $success = true;
                    } catch (PDOException $e) {
                        $errors['db'] = 'Failed to update username.';
                    }
                }
            }
        }
    }
    
    // Update email if provided
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = sanitize($_POST['email']);
        
        // Validate email
        if ($email !== $user['email']) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address.';
            } else {
                // Check if email is already taken
                $existing_user = fetch_row("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']]);
                
                if ($existing_user) {
                    $errors['email'] = 'Email is already taken.';
                } else {
                    // Update email
                    try {
                        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                        $stmt->execute([$email, $_SESSION['user_id']]);
                        
                        $success = true;
                    } catch (PDOException $e) {
                        $errors['db'] = 'Failed to update email.';
                    }
                }
            }
        }
    }
    
    // Update password if provided
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate password
        if (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match.';
        } else {
            // Update password
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $success = true;
            } catch (PDOException $e) {
                $errors['db'] = 'Failed to update password.';
            }
        }
    }
    
    // Set messages
    if ($success && empty($errors)) {
        success_message('Profile updated successfully!');
    } elseif (!empty($errors)) {
        $error_msg = 'Failed to update profile: ';
        if (isset($errors['db'])) {
            $error_msg .= $errors['db'];
        } else {
            $error_msg .= 'Please fix the errors below.';
        }
        error_message($error_msg);
    }
}

// Get user data
$user = fetch_row("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Set page title
$page_title = 'Update Profile - Akademi SK';

// Include header
require_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Update Profile</h3>
                </div>
                <div class="card-body">
                    <?= display_messages() ?>
                    
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data">
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <?php 
                                    $profile_pic = $user['profile_pic'];
                                    $profile_pic_path = file_exists('../uploads/profile/' . $profile_pic) 
                                        ? '../uploads/profile/' . $profile_pic 
                                        : '../assets/img/' . $profile_pic;
                                    ?>
                                    <img src="<?= $profile_pic_path ?>" alt="<?= htmlspecialchars($user['username']) ?>" 
                                         class="img-thumbnail rounded-circle profile-pic" width="150" height="150">
                                </div>
                                <div class="mb-3">
                                    <label for="profile_pic" class="form-label">Change Profile Picture</label>
                                    <input type="file" class="form-control <?= isset($errors['profile_pic']) ? 'is-invalid' : '' ?>" 
                                           id="profile_pic" name="profile_pic" accept="image/*">
                                    <?php if (isset($errors['profile_pic'])): ?>
                                        <div class="invalid-feedback"><?= $errors['profile_pic'] ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Recommended size: 500x500 pixels</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                           id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                    <?php if (isset($errors['username'])): ?>
                                        <div class="invalid-feedback"><?= $errors['username'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                           id="password" name="password">
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Leave blank to keep current password</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                           id="confirm_password" name="confirm_password">
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="../chat/" class="btn btn-secondary">Back to Chat</a>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>