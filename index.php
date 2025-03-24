<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Redirect to chat if logged in
if (is_logged_in()) {
    redirect('chat/');
}

// Set page title
$page_title = 'Welcome to Akademi SK';

// Include header
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-6 text-center">
            <img src="assets/img/logo.png" alt="Akademi SK" class="img-fluid mb-4" style="max-width: 150px;">
            <h1 class="display-4">Welcome to Akademi SK</h1>
            <p class="lead">A simple chat application for students and teachers.</p>
            <div class="mt-4">
                <a href="auth/login.php" class="btn btn-primary btn-lg me-2">Login</a>
                <a href="auth/register.php" class="btn btn-outline-primary btn-lg">Register</a>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-chat-dots display-4 text-primary mb-3"></i>
                    <h3>Real-time Chat</h3>
                    <p>Connect with others in real-time through our easy-to-use chat interface.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-moon-stars display-4 text-primary mb-3"></i>
                    <h3>Dark Mode</h3>
                    <p>Choose between light and dark themes for comfortable chatting day or night.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-shield-lock display-4 text-primary mb-3"></i>
                    <h3>Secure</h3>
                    <p>Your data is protected with secure authentication and data encryption.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>