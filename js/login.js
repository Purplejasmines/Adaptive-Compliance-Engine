$(document).ready(function() {
    const $loginForm = $('#loginForm');
    const $errorAlert = $('#errorAlert');
    const $loginBtn = $('#loginBtn');
    const $btnText = $('.btn-text');
    const $spinner = $('.spinner-border');

    $loginForm.on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        $loginBtn.prop('disabled', true);
        $btnText.text('Signing in...');
        $spinner.removeClass('d-none');
        $errorAlert.addClass('d-none');
        
        // Get form data
        const username = $('#username').val().trim();
        const password = $('#password').val();
        
        // Simulate API call (replace with actual API call)
        setTimeout(() => {
            // Mock authentication
            if (username && password) {
                // Mock user data - in a real app, this would come from your backend
                const mockUser = {
                    id: '12345',
                    username: username,
                    firstName: 'John',
                    lastName: 'Doe',
                    email: `${username}@example.com`,
                    tpin: 'TPIN12345678'
                };
                
                // Store user data and token in localStorage
                localStorage.setItem('authToken', 'mock-jwt-token');
                localStorage.setItem('currentUser', JSON.stringify(mockUser));
                
                // Redirect to dashboard
                window.location.href = 'dashboard.html';
            } else {
                showError('Please enter both username and password');
            }
        }, 1000);
    });
    
    function showError(message) {
        $errorAlert.text(message).removeClass('d-none');
        $loginBtn.prop('disabled', false);
        $btnText.text('Sign In');
        $spinner.addClass('d-none');
    }
});
