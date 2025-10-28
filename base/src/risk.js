// ===============================================
// RISK ASSESSMENT PAGE JAVASCRIPT
// ===============================================

document.addEventListener('DOMContentLoaded', function() {
  
  // Initialize all components
  initializeRiskCharts();
  initializeDetailPanel();
  initializeFilters();
  initializeTableActions();
  
});

// ===============================================
// CHART INITIALIZATION
// ===============================================

function initializeRiskCharts() {
  
  // Risk Distribution Chart
  const riskDistCtx = document.getElementById('riskDistributionChart');
  if (riskDistCtx) {
    new Chart(riskDistCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Low Risk', 'Medium Risk', 'High Risk', 'Critical'],
        datasets: [{
          data: [1520, 840, 152, 27],
          backgroundColor: [
            '#059669',
            '#D97706',
            '#EA580C',
            '#DC2626'
          ],
          borderWidth: 3,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              font: {
                size: 13,
                weight: '600'
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  }

  // Risk Trend Mini Chart
  const riskTrendMiniCtx = document.getElementById('riskTrendMiniChart');
  if (riskTrendMiniCtx) {
    new Chart(riskTrendMiniCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
          label: 'Average Risk Score',
          data: [0.42, 0.45, 0.43, 0.48, 0.46, 0.44, 0.47],
          borderColor: '#DC2626',
          backgroundColor: 'rgba(220, 38, 38, 0.1)',
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 5
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
            beginAtZero: true,
            max: 1,
            ticks: {
              callback: function(value) {
                return value.toFixed(1);
              }
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }
}

// ===============================================
// DETAIL PANEL FUNCTIONALITY
// ===============================================

function initializeDetailPanel() {
  const detailPanel = document.getElementById('detailPanel');
  const detailOverlay = document.getElementById('detailPanelOverlay');
  const closeBtn = document.getElementById('closeDetailPanel');

  // Placeholder taxpayer data
  const taxpayerData = {
    'TP-09432': {
      name: 'Logistics Inc.',
      id: 'TP-09432',
      sector: 'Manufacturing',
      lastFiling: 'September 15, 2025',
      regDate: 'January 2018',
      riskScore: '0.82',
      factors: [
        { icon: 'fa-exclamation-triangle', text: 'Late filing pattern', impact: '+0.25' },
        { icon: 'fa-chart-line', text: 'Revenue drop (>30%)', impact: '+0.20' },
        { icon: 'fa-redo', text: 'Inconsistent reporting', impact: '+0.15' },
        { icon: 'fa-building', text: 'High-risk sector', impact: '+0.12' },
        { icon: 'fa-clock', text: 'Overdue payment', impact: '+0.10' }
      ]
    },
    'TP-09433': {
      name: 'Retail Corp.',
      id: 'TP-09433',
      sector: 'Retail',
      lastFiling: 'September 20, 2025',
      regDate: 'March 2019',
      riskScore: '0.65',
      factors: [
        { icon: 'fa-chart-line', text: 'Revenue inconsistency', impact: '+0.18' },
        { icon: 'fa-exclamation-triangle', text: 'Missing documentation', impact: '+0.22' },
        { icon: 'fa-redo', text: 'Late payment history', impact: '+0.15' },
        { icon: 'fa-building', text: 'Sector volatility', impact: '+0.10' }
      ]
    },
    'TP-09434': {
      name: 'Services Ltd.',
      id: 'TP-09434',
      sector: 'Services',
      lastFiling: 'September 10, 2025',
      regDate: 'July 2020',
      riskScore: '0.58',
      factors: [
        { icon: 'fa-chart-line', text: 'Cash flow concerns', impact: '+0.20' },
        { icon: 'fa-exclamation-triangle', text: 'Delayed responses', impact: '+0.18' },
        { icon: 'fa-building', text: 'New business risk', impact: '+0.20' }
      ]
    },
    'TP-09435': {
      name: 'AgriCo Farms',
      id: 'TP-09435',
      sector: 'Agriculture',
      lastFiling: 'September 18, 2025',
      regDate: 'May 2017',
      riskScore: '0.72',
      factors: [
        { icon: 'fa-exclamation-triangle', text: 'Seasonal irregularities', impact: '+0.25' },
        { icon: 'fa-chart-line', text: 'Income volatility', impact: '+0.22' },
        { icon: 'fa-clock', text: 'Multiple late filings', impact: '+0.15' },
        { icon: 'fa-building', text: 'Weather impact claims', impact: '+0.10' }
      ]
    },
    'TP-09436': {
      name: 'TechStart Hub',
      id: 'TP-09436',
      sector: 'Services',
      lastFiling: 'September 17, 2025',
      regDate: 'November 2021',
      riskScore: '0.61',
      factors: [
        { icon: 'fa-chart-line', text: 'Rapid growth concerns', impact: '+0.21' },
        { icon: 'fa-exclamation-triangle', text: 'Complex transactions', impact: '+0.18' },
        { icon: 'fa-building', text: 'Startup volatility', impact: '+0.22' }
      ]
    }
  };

  // Open panel
  function openDetailPanel(taxpayerId) {
    const data = taxpayerData[taxpayerId];
    
    if (!data) {
      console.error('Taxpayer data not found:', taxpayerId);
      return;
    }

    // Populate data
    document.getElementById('detailTaxpayerName').textContent = data.name;
    document.getElementById('detailID').textContent = data.id;
    document.getElementById('detailSector').textContent = data.sector;
    document.getElementById('detailLastFiling').textContent = data.lastFiling;
    document.getElementById('detailRegDate').textContent = data.regDate;
    document.getElementById('detailRiskScore').textContent = data.riskScore;

    // Populate risk factors
    const factorsContainer = document.querySelector('.risk-factors');
    const existingFactors = factorsContainer.querySelectorAll('.factor-item');
    
    data.factors.forEach((factor, index) => {
      if (existingFactors[index]) {
        const icon = existingFactors[index].querySelector('i');
        const text = existingFactors[index].querySelector('.factor-header span');
        const impact = existingFactors[index].querySelector('.factor-impact');
        
        icon.className = `fas ${factor.icon}`;
        text.textContent = factor.text;
        impact.textContent = factor.impact;
      }
    });

    // Show panel
    detailPanel.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  // Close panel
  function closeDetailPanel() {
    detailPanel.classList.remove('active');
    document.body.style.overflow = '';
  }

  // Event listeners
  closeBtn.addEventListener('click', closeDetailPanel);
  detailOverlay.addEventListener('click', closeDetailPanel);

  // Escape key to close
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && detailPanel.classList.contains('active')) {
      closeDetailPanel();
    }
  });

  // Expose function globally for table actions
  window.openDetailPanel = openDetailPanel;
}

// ===============================================
// TABLE ACTIONS
// ===============================================

function initializeTableActions() {
  // View buttons
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const taxpayerId = this.getAttribute('data-id');
      window.openDetailPanel(taxpayerId);
    });
  });

  // Nudge buttons
  document.querySelectorAll('.nudge-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const taxpayerId = this.getAttribute('data-id');
      sendNudge(taxpayerId);
    });
  });

  // Row click to open detail
  document.querySelectorAll('.risk-row').forEach(row => {
    row.addEventListener('click', function() {
      const taxpayerId = this.getAttribute('data-taxpayer');
      window.openDetailPanel(taxpayerId);
    });
  });
}

function sendNudge(taxpayerId) {
  // Placeholder nudge function
  alert(`Nudge sent to ${taxpayerId}!\n\nIn production, this would:\n- Open nudge composer\n- Select appropriate template\n- Log intervention\n- Update status`);
  
  // Simulate status update
  const row = document.querySelector(`[data-taxpayer="${taxpayerId}"]`);
  if (row) {
    const statusBadge = row.querySelector('.status-badge');
    statusBadge.textContent = 'Nudge Sent';
    statusBadge.className = 'status-badge nudged';
  }
}

// ===============================================
// FILTER FUNCTIONALITY
// ===============================================

function initializeFilters() {
  const riskLevelFilter = document.getElementById('riskLevelFilter');
  const sectorFilter = document.getElementById('sectorFilter');
  const timeFilter = document.getElementById('timeFilter');
  const statusFilter = document.getElementById('statusFilter');
  const resetBtn = document.getElementById('resetFilters');

  // Apply filters (placeholder - would filter table in production)
  function applyFilters() {
    const riskLevel = riskLevelFilter.value;
    const sector = sectorFilter.value;
    const time = timeFilter.value;
    const status = statusFilter.value;

    console.log('Filters applied:', { riskLevel, sector, time, status });
    
    // In production: filter table rows based on criteria
    // For demo: just log the values
  }

  // Reset filters
  resetBtn.addEventListener('click', function() {
    riskLevelFilter.value = 'all';
    sectorFilter.value = 'all';
    timeFilter.value = '30';
    statusFilter.value = 'all';
    applyFilters();
  });

  // Listen for filter changes
  [riskLevelFilter, sectorFilter, timeFilter, statusFilter].forEach(filter => {
    filter.addEventListener('change', applyFilters);
  });
}

// ===============================================
// EXPORT FUNCTIONALITY
// ===============================================

document.getElementById('exportRiskReport')?.addEventListener('click', function() {
  alert('Exporting risk report...\n\nIn production, this would generate a PDF or CSV report with:\n- Current risk assessments\n- Trend analysis\n- Intervention history\n- Compliance metrics');
});

// ===============================================
// REAL-TIME UPDATES (Placeholder)
// ===============================================

// Simulate real-time risk score updates
function simulateRealTimeUpdates() {
  setInterval(() => {
    // Randomly update a risk score (for demo purposes)
    const rows = document.querySelectorAll('.risk-row');
    if (rows.length > 0) {
      const randomRow = rows[Math.floor(Math.random() * rows.length)];
      const scoreCell = randomRow.querySelector('.score-number');
      const scoreFill = randomRow.querySelector('.score-fill');
      
      if (scoreCell && scoreFill) {
        const currentScore = parseFloat(scoreCell.textContent);
        const newScore = (currentScore + (Math.random() - 0.5) * 0.05).toFixed(2);
        
        // Ensure score stays between 0 and 1
        const clampedScore = Math.max(0, Math.min(1, parseFloat(newScore)));
        
        scoreCell.textContent = clampedScore.toFixed(2);
        scoreFill.style.width = (clampedScore * 100) + '%';
        
        // Add pulse animation
        scoreCell.style.animation = 'pulse 0.5s';
        setTimeout(() => {
          scoreCell.style.animation = '';
        }, 500);
      }
    }
  }, 10000); // Update every 10 seconds
}

// Uncomment to enable real-time updates for demo
// simulateRealTimeUpdates();

// Add CSS for pulse animation
const style = document.createElement('style');
style.textContent = `
  @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
  }
`;
document.head.appendChild(style);



