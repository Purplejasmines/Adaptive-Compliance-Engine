// Dashboard functionality
const Dashboard = {
    init: function() {
        this.loadDashboardData();
        this.setupEventListeners();
    },
    
    loadDashboardData: function() {
        // Simulate API call to get dashboard data
        // In a real app, this would be an AJAX call to your backend
        setTimeout(() => {
            const mockData = {
                totalPayments: 12500.00,
                outstandingReturns: 3,
                pendingLiabilities: 3500.00
            };
            
            $('#totalPayments').text(`ZMW ${mockData.totalPayments.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`);
            
            $('#outstandingReturns').text(mockData.outstandingReturns);
            
            $('#pendingLiabilities').text(`ZMW ${mockData.pendingLiabilities.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`);
        }, 500);
    },
    
    setupEventListeners: function() {
        // Toggle sidebar
        $('#sidebarCollapse').on('click', function() {
            $('#sidebar').toggleClass('active');
        });
        
        // Handle navigation
        $('.dashboard-link').on('click', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            Dashboard.loadPage(page);
        });
    },
    
    loadPage: function(page) {
        // Update active link
        $('.dashboard-link').parent().removeClass('active');
        $(`.dashboard-link[data-page="${page}"]`).parent().addClass('active');
        
        // In a real app, this would load the appropriate content
        // For now, we'll just update the title
        $('h2').text(page.charAt(0).toUpperCase() + page.slice(1));
    }
};

// Initialize dashboard when document is ready
$(document).ready(function() {
    Dashboard.init();
});
