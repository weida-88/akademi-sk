</main>
    
    <!-- Footer -->
    <footer class="bg-body-tertiary py-3 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> Akademi SK. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Switcher JS -->
    <script src="../assets/js/theme-switcher.js"></script>
    
    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>
    
    <!-- Chat JS (only load on chat page) -->
    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php' && dirname($_SERVER['PHP_SELF']) === '/chat'): ?>
    <script src="../assets/js/chat.js"></script>
    <?php endif; ?>
</body>
</html>