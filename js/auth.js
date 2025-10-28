// Authentication functions
const Auth = {
    // Check if user is logged in
    checkAuth: function() {
        const token = localStorage.getItem('authToken');
        if (!token) {
            window.location.href = 'login.html';
        }
        return token;
    },

    // Get current user info
    getCurrentUser: function() {
        const user = localStorage.getItem('currentUser');
        return user ? JSON.parse(user) : null;
    },

    // Logout function
    logout: function() {
        localStorage.removeItem('authToken');
        localStorage.removeItem('currentUser');
        window.location.href = 'login.html';
    }
};

// Initialize auth check when page loads
$(document).ready(function() {
    Auth.checkAuth();
    
    // Set up logout button
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault();
        Auth.logout();
    });
    
    // Display user info
    const user = Auth.getCurrentUser();
    if (user) {
        $('#userName').text(`${user.firstName} ${user.lastName}`);
    }
});
