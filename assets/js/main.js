document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    // Clear any sensitive information from username fields
    const usernameFields = document.querySelectorAll('input[name="username"]');
    usernameFields.forEach(field => {
        // Clear field if it contains database-like values
        if (field.value.includes('_sk') || field.value.startsWith('u45') || 
            field.value.includes('429') || field.value.includes('db_')) {
            field.value = '';
        }
        
        // Add event to prevent pasting sensitive information
        field.addEventListener('paste', function(e) {
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            if (pastedText.includes('_sk') || pastedText.startsWith('u45') || 
                pastedText.includes('429') || pastedText.includes('db_')) {
                e.preventDefault();
                alert('Please do not paste sensitive information');
            }
        });
    });
});