document.addEventListener('DOMContentLoaded', function() {
    // Get all theme toggle buttons
    const themeToggles = document.querySelectorAll('.theme-toggle');
    
    // Current theme
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    
    // Function to toggle theme
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Update HTML attribute
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        
        // Save preference to localStorage for guests
        localStorage.setItem('theme', newTheme);
        
        // If user is logged in, save preference to server
        if (typeof currentUser !== 'undefined') {
            saveThemePreference(newTheme);
        }
    }
    
    // Save theme preference to server
    function saveThemePreference(theme) {
        const formData = new FormData();
        formData.append('theme', theme);
        
        fetch('../auth/save_theme.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log('Theme preference saved:', data);
            })
            .catch(error => {
                console.error('Error saving theme preference:', error);
            });
    }
    
    // Add click event to all toggle buttons
    themeToggles.forEach(button => {
        button.addEventListener('click', toggleTheme);
    });
});