<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

// Get user data
$user = fetch_row("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Set page title
$page_title = 'Profile - Akademi SK';

// Include header
require_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Your Profile</h3>
                        <a href="../auth/update_profile.php" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?= display_messages() ?>
                    
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <?php 
                            $profile_pic = $user['profile_pic'];
                            $profile_pic_path = file_exists('../uploads/profile/' . $profile_pic) 
                                ? '../uploads/profile/' . $profile_pic 
                                : '../assets/img/' . $profile_pic;
                            ?>
                            <img src="<?= $profile_pic_path ?>" alt="<?= htmlspecialchars($user['username']) ?>" 
                                 class="img-thumbnail rounded-circle profile-pic" width="200" height="200">
                        </div>
                        <div class="col-md-8">
                            <h4 class="mb-3"><?= htmlspecialchars($user['username']) ?></h4>
                            
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                            <p><strong>Theme Preference:</strong> <?= ucfirst($user['theme_preference']) ?></p>
                            
                            <div class="mt-4">
                                <a href="../chat/" class="btn btn-primary">
                                    <i class="bi bi-chat-dots"></i> Go to Chat
                                </a>
                                <a href="../auth/logout.php" class="btn btn-outline-danger ms-2">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>