
// WIDGET NAVIGATION SYSTEM


document.addEventListener('DOMContentLoaded', function() {
  
  
  initializeWidgetNavigation();
  
  
  initializeClock();
  
  
  initializeCharts();
});


// Widget Navigation Function

function initializeWidgetNavigation() {
  const navButtons = document.querySelectorAll('.main-nav .nav-item');
  const widgets = document.querySelectorAll('.widget');

  navButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetWidget = this.getAttribute('data-widget');
      
      
      navButtons.forEach(btn => btn.classList.remove('active'));
      
   
      this.classList.add('active');
      
      
      widgets.forEach(widget => {
        widget.classList.remove('active');
      });
      
      // Show target widget
      const activeWidget = document.querySelector(`.widget[data-widget="${targetWidget}"]`);
      if (activeWidget) {
        activeWidget.classList.add('active');
        
      
        window.dispatchEvent(new Event('resize'));
      }
      
      // Log for debugging
      console.log(`Switched to widget: ${targetWidget}`);
    });
  });
}




// Initialize All Charts

function initializeCharts() {
  // Revenue Trend Chart
  const revenueTrendCtx = document.getElementById('revenueTrendChart');
  if (revenueTrendCtx) {
    new Chart(revenueTrendCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Revenue (ZMW)',
          data: [820000, 930000, 890000, 1100000, 1200000, 1150000],
          borderColor: '#0A4174',
          backgroundColor: 'rgba(10, 65, 116, 0.1)',
          fill: true,
          tension: 0.4,
          borderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true },
          tooltip: { enabled: true }
        },
        scales: {
          y: { 
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return 'ZMW ' + (value / 1000) + 'K';
              }
            }
          }
        }
      }
    });
  }

  // Revenue by Sector Chart
  const revenueSectorCtx = document.getElementById('revenueSectorChart');
  if (revenueSectorCtx) {
    new Chart(revenueSectorCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['VAT', 'Income Tax', 'Corporate Tax', 'Customs', 'Other'],
        datasets: [{
          data: [35, 28, 20, 12, 5],
          backgroundColor: [
            '#0A4174',
            '#28A745',
            '#FFC107',
            '#6C5CE7',
            '#D32F2F'
          ],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { 
            display: true,
            position: 'bottom'
          }
        }
      }
    });
  }

  // Compliance Trend Chart
  const complianceTrendCtx = document.getElementById('complianceTrendChart');
  if (complianceTrendCtx) {
    new Chart(complianceTrendCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Compliance Rate (%)',
          data: [78, 81, 79, 85, 87, 85],
          borderColor: '#28A745',
          backgroundColor: 'rgba(40, 167, 69, 0.1)',
          fill: true,
          tension: 0.4,
          borderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true }
        },
        scales: {
          y: { 
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function(value) {
                return value + '%';
              }
            }
          }
        }
      }
    });
  }

  // Risk Distribution Chart
  const riskDistributionCtx = document.getElementById('riskDistributionChart');
  if (riskDistributionCtx) {
    new Chart(riskDistributionCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: ['Low Risk', 'Medium Risk', 'High Risk', 'Critical'],
        datasets: [{
          label: 'Number of Taxpayers',
          data: [1520, 840, 152, 27],
          backgroundColor: [
            '#28A745',
            '#FFC107',
            '#FF8C00',
            '#D32F2F'
          ],
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { 
            beginAtZero: true
          }
        }
      }
    });
  }
}

// Sidebar Navigation Functionality
document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
  item.addEventListener('click', function() {
    const section = this.getAttribute('data-section');
    console.log(`Navigating to section: ${section}`);
    
   
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(nav => {
      nav.classList.remove('active');
    });
    
  
    this.classList.add('active');
  });
});

// Placeholder for backend database integration
// TODO: You guy to integrate database connection here 😭
// - Fetch data from the backend API (e.g., using fetch() or axios)
// - Example endpoint: '/api/analytics/data'
// - Update charts and tables dynamically (e.g., via Chart.js instances and table DOM manipulation)
// - Suggested structure:
/*
async function fetchAnalyticsData() {
  try {
    const response = await fetch('/api/analytics/data');
    const data = await response.json();
    // Update charts
    updateCharts(data);
    // Update tables
    updateTables(data);
  } catch (error) {
    console.error('Error fetching analytics data:', error);
  }
}

function updateCharts(data) {
  // Example: Update Revenue Trend Chart
  const revenueTrendChart = Chart.getChart('revenueTrendChart');
  if (revenueTrendChart) {
    revenueTrendChart.data.labels = data.revenueTrendLabels;
    revenueTrendChart.data.datasets[0].data = data.revenueTrendData;
    revenueTrendChart.update();
  }

  // Example: Update Compliance Trend Chart
  const complianceTrendChart = Chart.getChart('complianceTrendChart');
  if (complianceTrendChart) {
    complianceTrendChart.data.labels = data.complianceTrendLabels;
    complianceTrendChart.data.datasets[0].data = data.complianceTrendData;
    complianceTrendChart.update();
  }

  // Example: Update Risk Distribution Chart
  const riskDistributionChart = Chart.getChart('riskDistributionChart');
  if (riskDistributionChart) {
    riskDistributionChart.data.labels = data.riskDistributionLabels;
    riskDistributionChart.data.datasets[0].data = data.riskDistributionData;
    riskDistributionChart.update();
  }

  // Example: Update Revenue by Sector Chart
  const revenueSectorChart = Chart.getChart('revenueSectorChart');
  if (revenueSectorChart) {
    revenueSectorChart.data.labels = data.revenueSectorLabels;
    revenueSectorChart.data.datasets[0].data = data.revenueSectorData;
    revenueSectorChart.update();
  }
}

function updateTables(data) {
  // Example: Update Compliance Breakdown Table
  const complianceTable = document.querySelector('.widget[data-widget="compliance"] table tbody');
  if (complianceTable) {
    complianceTable.innerHTML = '';
    data.complianceBreakdown.forEach(row => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${row.category}</td><td>${row.rate}</td><td>${row.status}</td>`;
      complianceTable.appendChild(tr);
    });
  }

  // Example: Update High Risk Taxpayers Table
  const riskTable = document.querySelector('.widget[data-widget="risk-distribution"] table tbody');
  if (riskTable) {
    riskTable.innerHTML = '';
    data.riskTaxpayers.forEach(row => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${row.id}</td><td>${row.name}</td><td>${row.riskScore}</td>`;
      riskTable.appendChild(tr);
    });
  }
}

// Call the function to initialize data
fetchAnalyticsData();
*/