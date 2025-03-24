<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get theme preference
$theme = get_user_theme();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= $page_title ?? 'Akademi SK' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/png">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../assets/img/logo.png" alt="Akademi SK" width="30" height="30" class="me-2">
                <span>Akademi SK</span>
            </a>
            
            <?php 
            // Show special controls for chat page on mobile
            $is_chat_page = (basename($_SERVER['PHP_SELF']) === 'index.php' && dirname($_SERVER['PHP_SELF']) === '/chat');
            if ($is_chat_page): 
            ?>
            <div class="d-flex d-md-none align-items-center">
                <button class="btn btn-light btn-sm me-2 theme-toggle" title="Toggle Theme">
                    <i class="bi bi-moon-fill dark-icon"></i>
                    <i class="bi bi-sun-fill light-icon"></i>
                </button>
                <button class="btn btn-light btn-sm" id="toggle-sidebar" title="Show Users">
                    <i class="bi bi-people-fill"></i>
                </button>
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <?php else: ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <?php endif; ?>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../chat/">Chat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item d-none d-md-block">
                        <button class="btn btn-sm btn-light ms-2 theme-toggle" title="Toggle Theme">
                            <i class="bi bi-moon-fill dark-icon"></i>
                            <i class="bi bi-sun-fill light-icon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main>