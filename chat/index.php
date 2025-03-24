<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

// Update user activity
update_user_activity();

// Get online users
$online_users = get_online_users();

// Set page title
$page_title = 'Chat - Akademi SK';

// Include header
require_once '../includes/header.php';
?>

<div class="container-fluid h-100">
    <div class="row h-100">
        <!-- Sidebar with online users -->
        <div class="col-md-3 col-lg-2 d-md-block bg-body-tertiary sidebar collapse">
            <div class="sidebar-sticky pt-3">
                <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                    <h5>Online Users</h5>
                    <span class="badge bg-success"><?= count($online_users) ?></span>
                </div>
                <ul class="list-group" id="online-users-list">
                    <?php foreach ($online_users as $user): ?>
                    <li class="list-group-item d-flex align-items-center">
                        <img src="../assets/img/<?= htmlspecialchars($user['profile_pic']) ?>" 
                             alt="<?= htmlspecialchars($user['username']) ?>" 
                             class="rounded-circle me-2" 
                             width="32" height="32">
                        <span><?= htmlspecialchars($user['username']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Main chat area -->
        <div class="col-md-9 col-lg-10 ms-sm-auto">
            <div class="chat-container">
                <div class="chat-container">
    <div class="card shadow h-100">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Akademi SK Chat</h4>
                <div>
                    <button class="btn btn-sm btn-light theme-toggle me-1" title="Toggle Theme">
                        <i class="bi bi-moon-fill dark-icon"></i>
                        <i class="bi bi-sun-fill light-icon"></i>
                    </button>
                    <button class="btn btn-sm btn-light d-md-none" id="toggle-sidebar" title="Show Users">
                        <i class="bi bi-people-fill"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="chat-messages" id="chat-messages">
                <!-- Messages will be loaded here via AJAX -->
                <div class="text-center p-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer chat-footer">
            <!-- File preview area -->
            <div id="file-preview" class="file-preview mb-2">
                <div class="d-flex align-items-center">
                    <img id="file-preview-image" class="me-2" style="max-width: 50px; max-height: 50px; display: none;">
                    <div class="flex-grow-1">
                        <div id="file-preview-name" class="fw-bold"></div>
                        <div id="file-preview-size" class="small text-muted"></div>
                    </div>
                    <button id="file-preview-clear" type="button" class="btn btn-sm btn-danger">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            
            <form id="message-form" class="d-flex">
                <div class="input-group w-100">
                    <input type="text" id="message-input" class="form-control" 
                        placeholder="Type your message here..." autocomplete="off">
                        
                    <button type="button" class="btn btn-outline-secondary" id="attach-file-btn" title="Attach File">
                        <i class="bi bi-paperclip"></i>
                    </button>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                <input type="file" id="file-input" style="display: none;">
            </form>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<!-- Templates for message rendering -->
<template id="message-template">
    <div class="message">
        <div class="message-header">
            <img src="" alt="" class="avatar rounded-circle">
            <span class="username"></span>
            <small class="timestamp text-muted"></small>
        </div>
        <div class="message-body"></div>
    </div>
</template>

<script>
    // Set user information for JavaScript
    const currentUser = {
        id: <?= $_SESSION['user_id'] ?>,
        username: "<?= htmlspecialchars($_SESSION['username']) ?>"
    };
</script>

<?php require_once '../includes/footer.php'; ?>