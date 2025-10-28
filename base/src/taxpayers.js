
document.addEventListener('DOMContentLoaded', function() {
  
  // Initialize all components
  initializeSearch();
  initializeFilters();
  initializeBulkActions();
  initializeTableSorting();
  initializePagination();
  initializeTaxpayerDetail();
  
});

// ===============================================
// SEARCH FUNCTIONALITY
// ===============================================

function initializeSearch() {
  const searchInput = document.getElementById('taxpayerSearch');
  const clearBtn = document.getElementById('clearSearch');

  searchInput.addEventListener('input', function() {
    const query = this.value.trim();
    
    if (query.length > 0) {
      clearBtn.style.display = 'block';
      performSearch(query);
    } else {
      clearBtn.style.display = 'none';
      resetSearch();
    }
  });

  clearBtn.addEventListener('click', function() {
    searchInput.value = '';
    clearBtn.style.display = 'none';
    resetSearch();
  });
}

function performSearch(query) {
  const rows = document.querySelectorAll('.taxpayer-row');
  let visibleCount = 0;

  rows.forEach(row => {
    const id = row.querySelector('strong').textContent.toLowerCase();
    const name = row.querySelector('.taxpayer-name .name').textContent.toLowerCase();
    const tin = row.querySelectorAll('td')[4].textContent.toLowerCase();
    
    if (id.includes(query.toLowerCase()) || 
        name.includes(query.toLowerCase()) || 
        tin.includes(query.toLowerCase())) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });

  console.log(`Search results: ${visibleCount} taxpayers found for "${query}"`);
}

function resetSearch() {
  const rows = document.querySelectorAll('.taxpayer-row');
  rows.forEach(row => row.style.display = '');
}

// ===============================================
// FILTERS FUNCTIONALITY
// ===============================================

function initializeFilters() {
  const advancedToggle = document.getElementById('advancedFiltersToggle');
  const advancedFilters = document.getElementById('advancedFilters');
  const resetBtn = document.getElementById('resetFilters');
  
  // Toggle advanced filters
  advancedToggle.addEventListener('click', function() {
    if (advancedFilters.style.display === 'none') {
      advancedFilters.style.display = 'block';
      this.style.background = 'var(--primary)';
      this.style.color = 'white';
    } else {
      advancedFilters.style.display = 'none';
      this.style.background = '';
      this.style.color = '';
    }
  });

  // Apply filters
  const filters = ['typeFilter', 'statusFilter', 'riskFilter', 'sectorFilter', 'complianceFilter'];
  filters.forEach(filterId => {
    document.getElementById(filterId).addEventListener('change', applyFilters);
  });

  // Reset filters
  resetBtn.addEventListener('click', function() {
    filters.forEach(filterId => {
      document.getElementById(filterId).value = 'all';
    });
    applyFilters();
  });
}

function applyFilters() {
  const typeFilter = document.getElementById('typeFilter').value;
  const statusFilter = document.getElementById('statusFilter').value;
  const riskFilter = document.getElementById('riskFilter').value;
  const sectorFilter = document.getElementById('sectorFilter').value;
  const complianceFilter = document.getElementById('complianceFilter').value;

  const rows = document.querySelectorAll('.taxpayer-row');
  let visibleCount = 0;

  rows.forEach(row => {
    let show = true;

    // Type filter
    if (typeFilter !== 'all') {
      const rowType = row.getAttribute('data-type');
      if (rowType !== typeFilter) show = false;
    }

    // Status filter
    if (statusFilter !== 'all') {
      const statusBadge = row.querySelector('.status-badge');
      if (!statusBadge.classList.contains(statusFilter)) show = false;
    }

    // Risk filter
    if (riskFilter !== 'all') {
      const riskChip = row.querySelector('.risk-chip');
      if (!riskChip.classList.contains(riskFilter)) show = false;
    }

    // Sector filter (case-insensitive match)
    if (sectorFilter !== 'all') {
      const sectorCell = row.querySelectorAll('td')[5].textContent.toLowerCase();
      if (!sectorCell.includes(sectorFilter.toLowerCase())) show = false;
    }

    // Compliance filter
    if (complianceFilter !== 'all') {
      const complianceScore = parseInt(row.querySelector('.compliance-score').textContent);
      switch(complianceFilter) {
        case 'excellent': if (complianceScore < 90) show = false; break;
        case 'good': if (complianceScore < 70 || complianceScore >= 90) show = false; break;
        case 'fair': if (complianceScore < 50 || complianceScore >= 70) show = false; break;
        case 'poor': if (complianceScore >= 50) show = false; break;
      }
    }

    row.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });

  console.log(`Filters applied: ${visibleCount} taxpayers visible`);
}

// ===============================================
// BULK ACTIONS
// ===============================================

function initializeBulkActions() {
  const selectAll = document.getElementById('selectAll');
  const checkboxes = document.querySelectorAll('.row-checkbox');
  const bulkBar = document.getElementById('bulkActionsBar');
  const selectedCount = document.getElementById('selectedCount');
  const deselectBtn = document.getElementById('bulkDeselect');

  // Select all checkbox
  selectAll.addEventListener('change', function() {
    checkboxes.forEach(cb => {
      // Only check visible rows
      const row = cb.closest('tr');
      if (row.style.display !== 'none') {
        cb.checked = this.checked;
      }
    });
    updateBulkBar();
  });

  // Individual checkboxes
  checkboxes.forEach(cb => {
    cb.addEventListener('change', function() {
      updateBulkBar();
      
      // Update select all state
      const visibleCheckboxes = Array.from(checkboxes).filter(checkbox => {
        return checkbox.closest('tr').style.display !== 'none';
      });
      const allChecked = visibleCheckboxes.every(checkbox => checkbox.checked);
      selectAll.checked = allChecked && visibleCheckboxes.length > 0;
    });
  });

  // Deselect all
  deselectBtn.addEventListener('click', function() {
    checkboxes.forEach(cb => cb.checked = false);
    selectAll.checked = false;
    updateBulkBar();
  });

  // Bulk actions
  document.getElementById('bulkNudge').addEventListener('click', function() {
    const selected = getSelectedTaxpayers();
    alert(`Sending nudges to ${selected.length} taxpayers:\n${selected.join(', ')}`);
  });

  document.getElementById('bulkExport').addEventListener('click', function() {
    const selected = getSelectedTaxpayers();
    alert(`Exporting data for ${selected.length} taxpayers:\n${selected.join(', ')}`);
  });

  document.getElementById('bulkFlag').addEventListener('click', function() {
    const selected = getSelectedTaxpayers();
    alert(`Flagging ${selected.length} taxpayers for review:\n${selected.join(', ')}`);
  });

  function updateBulkBar() {
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    if (checkedCount > 0) {
      bulkBar.style.display = 'flex';
      selectedCount.textContent = checkedCount;
    } else {
      bulkBar.style.display = 'none';
    }
  }

  function getSelectedTaxpayers() {
    const selected = [];
    checkboxes.forEach(cb => {
      if (cb.checked) {
        const row = cb.closest('tr');
        const id = row.querySelector('strong').textContent;
        selected.push(id);
      }
    });
    return selected;
  }
}

// ===============================================
// TABLE SORTING
// ===============================================

function initializeTableSorting() {
  const sortableHeaders = document.querySelectorAll('.sortable');
  
  sortableHeaders.forEach(header => {
    header.addEventListener('click', function() {
      const column = this.getAttribute('data-sort');
      const tbody = document.getElementById('taxpayersTableBody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      
      // Determine sort direction
      const currentSort = this.getAttribute('data-direction') || 'asc';
      const newSort = currentSort === 'asc' ? 'desc' : 'asc';
      
      // Reset all headers
      sortableHeaders.forEach(h => {
        h.setAttribute('data-direction', '');
        h.querySelector('i').className = 'fas fa-sort';
      });
      
      // Set current header
      this.setAttribute('data-direction', newSort);
      this.querySelector('i').className = newSort === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
      
      // Sort rows
      rows.sort((a, b) => {
        let aVal, bVal;
        
        switch(column) {
          case 'id':
            aVal = a.querySelector('strong').textContent;
            bVal = b.querySelector('strong').textContent;
            break;
          case 'name':
            aVal = a.querySelector('.taxpayer-name .name').textContent;
            bVal = b.querySelector('.taxpayer-name .name').textContent;
            break;
          case 'type':
            aVal = a.getAttribute('data-type');
            bVal = b.getAttribute('data-type');
            break;
          case 'sector':
            aVal = a.querySelectorAll('td')[5].textContent;
            bVal = b.querySelectorAll('td')[5].textContent;
            break;
          case 'compliance':
            aVal = parseInt(a.querySelector('.compliance-score').textContent);
            bVal = parseInt(b.querySelector('.compliance-score').textContent);
            break;
          case 'risk':
            const riskOrder = { 'critical': 4, 'high': 3, 'medium': 2, 'low': 1 };
            const aRisk = a.querySelector('.risk-chip').className.split(' ').pop();
            const bRisk = b.querySelector('.risk-chip').className.split(' ').pop();
            aVal = riskOrder[aRisk] || 0;
            bVal = riskOrder[bRisk] || 0;
            break;
          case 'lastFiling':
            aVal = new Date(a.querySelector('.date-cell .date').textContent);
            bVal = new Date(b.querySelector('.date-cell .date').textContent);
            break;
          default:
            return 0;
        }
        
        if (typeof aVal === 'string') {
          aVal = aVal.toLowerCase();
          bVal = bVal.toLowerCase();
        }
        
        if (newSort === 'asc') {
          return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
        } else {
          return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
        }
      });
      
      // Re-append sorted rows
      rows.forEach(row => tbody.appendChild(row));
      
      console.log(`Sorted by ${column} (${newSort})`);
    });
  });
}

// ===============================================
// PAGINATION
// ===============================================

function initializePagination() {
  const pageButtons = document.querySelectorAll('.page-btn:not([disabled])');
  const perPageSelect = document.getElementById('perPage');
  
  pageButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      if (this.classList.contains('active')) return;
      
      pageButtons.forEach(b => b.classList.remove('active'));
      
      // Only add active class if it's a number button
      if (!this.querySelector('i')) {
        this.classList.add('active');
      }
      
      console.log(`Navigating to page ${this.textContent || 'Next/Prev'}`);
    });
  });
  
  perPageSelect.addEventListener('change', function() {
    console.log(`Changed items per page to: ${this.value}`);
    // In production: reload table with new pagination
  });
}

// ===============================================
// TAXPAYER DETAIL PANEL
// ===============================================

function initializeTaxpayerDetail() {
  const detailPanel = document.getElementById('taxpayerDetailPanel');
  const overlay = document.getElementById('taxpayerDetailOverlay');
  const closeBtn = document.getElementById('closeTaxpayerDetail');
  
  // Placeholder data for detail panel
  const taxpayerData = {
    'TP-09432': {
      name: 'Logistics Inc.',
      type: 'business',
      id: 'TP-09432',
      tin: '1234567890',
      sector: 'Manufacturing',
      registered: 'January 2018',
      status: 'Active',
      compliance: 73,
      onTime: 87,
      avgLate: 4,
      revenue: 'ZMW 5.2M',
      taxPaid: 'ZMW 890K',
      outstanding: 'ZMW 0',
      phone: '+260-211-234567',
      email: 'contact@logistics.zm',
      address: 'Plot 1234, Industrial Area, Lusaka, Zambia',
      riskFactors: [
        { icon: 'fa-clock', text: 'Late filing pattern', impact: '+0.25' },
        { icon: 'fa-chart-line', text: 'Revenue volatility', impact: '+0.15' }
      ],
      activity: [
        { title: 'Tax return filed', date: 'September 15, 2025' },
        { title: 'Nudge sent - Filing reminder', date: 'September 1, 2025' },
        { title: 'Payment received', date: 'August 28, 2025' }
      ]
    },
    'TP-10234': {
      name: 'John Mwanza',
      type: 'individual',
      id: 'TP-10234',
      tin: '9876543210',
      sector: 'Services',
      registered: 'March 2020',
      status: 'Active',
      compliance: 85,
      onTime: 92,
      avgLate: 2,
      revenue: 'ZMW 840K',
      taxPaid: 'ZMW 168K',
      outstanding: 'ZMW 0',
      phone: '+260-977-123456',
      email: 'john.mwanza@email.zm',
      address: 'Kabwe Road, Lusaka, Zambia',
      riskFactors: [
        { icon: 'fa-chart-line', text: 'Income fluctuation', impact: '+0.12' }
      ],
      activity: [
        { title: 'Tax return filed', date: 'September 18, 2025' },
        { title: 'Payment received', date: 'September 10, 2025' }
      ]
    },
    'TP-09433': {
      name: 'Retail Corp.',
      type: 'business',
      id: 'TP-09433',
      tin: '1112223330',
      sector: 'Retail',
      registered: 'June 2019',
      status: 'Active',
      compliance: 65,
      onTime: 78,
      avgLate: 7,
      revenue: 'ZMW 3.1M',
      taxPaid: 'ZMW 465K',
      outstanding: 'ZMW 45K',
      phone: '+260-211-987654',
      email: 'info@retailcorp.zm',
      address: 'Cairo Road, Lusaka, Zambia',
      riskFactors: [
        { icon: 'fa-exclamation-triangle', text: 'Missing documentation', impact: '+0.22' },
        { icon: 'fa-clock', text: 'Late payment history', impact: '+0.15' }
      ],
      activity: [
        { title: 'Tax return filed (late)', date: 'September 10, 2025' },
        { title: 'Nudge sent - Payment reminder', date: 'August 25, 2025' },
        { title: 'Partial payment received', date: 'August 15, 2025' }
      ]
    },
    'TP-10567': {
      name: 'Sarah Kabwe',
      type: 'individual',
      id: 'TP-10567',
      tin: '5554443330',
      sector: 'Services',
      registered: 'October 2021',
      status: 'Active',
      compliance: 94,
      onTime: 98,
      avgLate: 0,
      revenue: 'ZMW 1.2M',
      taxPaid: 'ZMW 240K',
      outstanding: 'ZMW 0',
      phone: '+260-966-789012',
      email: 'sarah.kabwe@professional.zm',
      address: 'Kabulonga, Lusaka, Zambia',
      riskFactors: [],
      activity: [
        { title: 'Tax return filed (early)', date: 'September 19, 2025' },
        { title: 'Payment received', date: 'September 19, 2025' },
        { title: 'Voluntary compliance update', date: 'September 5, 2025' }
      ]
    },
    'TP-08765': {
      name: 'AgriCo Farms',
      type: 'business',
      id: 'TP-08765',
      tin: '7778889990',
      sector: 'Agriculture',
      registered: 'May 2017',
      status: 'Dormant',
      compliance: 58,
      onTime: 65,
      avgLate: 12,
      revenue: 'ZMW 2.8M',
      taxPaid: 'ZMW 280K',
      outstanding: 'ZMW 120K',
      phone: '+260-211-456789',
      email: 'info@agricofarms.zm',
      address: 'Chisamba, Central Province, Zambia',
      riskFactors: [
        { icon: 'fa-exclamation-triangle', text: 'Seasonal irregularities', impact: '+0.25' },
        { icon: 'fa-clock', text: 'Multiple late filings', impact: '+0.15' }
      ],
      activity: [
        { title: 'No activity (dormant status)', date: 'June 5, 2025' },
        { title: 'Audit scheduled', date: 'May 20, 2025' },
        { title: 'Nudge sent - Filing overdue', date: 'May 1, 2025' }
      ]
    }
  };
  
  // Open detail panel
  function openTaxpayerDetail(taxpayerId) {
    const data = taxpayerData[taxpayerId];
    
    if (!data) {
      console.error('Taxpayer data not found:', taxpayerId);
      return;
    }
    
    // Populate basic info
    document.getElementById('taxpayerDetailName').textContent = data.name;
    document.getElementById('detailID').textContent = data.id;
    document.getElementById('detailTIN').textContent = data.tin;
    document.getElementById('detailType').textContent = data.type === 'business' ? 'Business Entity' : 'Individual';
    document.getElementById('detailSector').textContent = data.sector;
    document.getElementById('detailRegistered').textContent = data.registered;
    document.getElementById('detailStatus').textContent = data.status;
    
    // Type badge
    const typeBadge = document.getElementById('detailTypeBadge');
    typeBadge.textContent = data.type === 'business' ? 'Business Entity' : 'Individual Taxpayer';
    typeBadge.className = `detail-type-badge ${data.type}`;
    
    // Compliance score
    document.getElementById('detailCompliance').textContent = data.compliance;
    document.getElementById('detailOnTime').textContent = data.onTime + '%';
    document.getElementById('detailAvgLate').textContent = data.avgLate + ' days';
    
    // Update compliance circle
    const circle = document.getElementById('complianceCircle');
    const circumference = 2 * Math.PI * 60; // radius = 60
    const offset = circumference - (data.compliance / 100) * circumference;
    circle.style.strokeDashoffset = offset;
    
    // Set circle color based on score
    if (data.compliance >= 90) {
      circle.style.stroke = '#28A745';
    } else if (data.compliance >= 70) {
      circle.style.stroke = '#FFC107';
    } else {
      circle.style.stroke = '#D32F2F';
    }
    
    // Financial info
    document.getElementById('detailRevenue').textContent = data.revenue;
    document.getElementById('detailTaxPaid').textContent = data.taxPaid;
    document.getElementById('detailOutstanding').textContent = data.outstanding;
    
    // Risk factors
    const riskFactorsContainer = document.getElementById('detailRiskFactors');
    riskFactorsContainer.innerHTML = '';
    
    if (data.riskFactors.length === 0) {
      riskFactorsContainer.innerHTML = '<p style="color: #6b7280; font-size: 14px;">No significant risk factors detected</p>';
    } else {
      data.riskFactors.forEach(factor => {
        const factorEl = document.createElement('div');
        factorEl.className = 'factor-item';
        factorEl.innerHTML = `
          <div class="factor-header">
            <i class="fas ${factor.icon}"></i>
            <span>${factor.text}</span>
          </div>
          <div class="factor-impact">${factor.impact}</div>
        `;
        riskFactorsContainer.appendChild(factorEl);
      });
    }
    
    // Contact info
    document.getElementById('detailPhone').textContent = data.phone;
    document.getElementById('detailEmail').textContent = data.email;
    document.getElementById('detailAddress').textContent = data.address;
    
    // Activity timeline
    const activityContainer = document.getElementById('detailActivity');
    activityContainer.innerHTML = '';
    
    data.activity.forEach(item => {
      const activityEl = document.createElement('div');
      activityEl.className = 'timeline-item';
      activityEl.innerHTML = `
        <div class="timeline-dot"></div>
        <div class="timeline-content">
          <p class="timeline-title">${item.title}</p>
          <p class="timeline-meta">${item.date}</p>
        </div>
      `;
      activityContainer.appendChild(activityEl);
    });
    
    // Show panel
    detailPanel.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  
  // Close panel
  function closeTaxpayerDetail() {
    detailPanel.classList.remove('active');
    document.body.style.overflow = '';
  }
  
  // Event listeners
  closeBtn.addEventListener('click', closeTaxpayerDetail);
  overlay.addEventListener('click', closeTaxpayerDetail);
  
  // Escape key to close
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && detailPanel.classList.contains('active')) {
      closeTaxpayerDetail();
    }
  });
  
  // View buttons
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const row = this.closest('tr');
      const taxpayerId = row.getAttribute('data-id');
      openTaxpayerDetail(taxpayerId);
    });
  });
  
  // Row click
  document.querySelectorAll('.taxpayer-row').forEach(row => {
    row.addEventListener('click', function(e) {
      // Don't trigger if clicking checkbox or action buttons
      if (e.target.type === 'checkbox' || e.target.closest('.action-btns')) {
        return;
      }
      const taxpayerId = this.getAttribute('data-id');
      openTaxpayerDetail(taxpayerId);
    });
  });
  
  // Nudge buttons
  document.querySelectorAll('.nudge-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const row = this.closest('tr');
      const taxpayerId = row.getAttribute('data-id');
      const name = row.querySelector('.taxpayer-name .name').textContent;
      alert(`Sending nudge to ${name} (${taxpayerId})...\n\nIn production, this would open the nudge composer.`);
    });
  });
  
  // More buttons
  document.querySelectorAll('.more-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const row = this.closest('tr');
      const taxpayerId = row.getAttribute('data-id');
      console.log('More options for:', taxpayerId);
      // In production: show dropdown menu with more actions
    });
  });
  
  // Detail panel action buttons
  document.querySelectorAll('.detail-action-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const action = this.textContent.trim();
      const taxpayerName = document.getElementById('taxpayerDetailName').textContent;
      alert(`Action: ${action}\nFor: ${taxpayerName}\n\nThis would trigger the appropriate action in production.`);
    });
  });
}

// ===============================================
// EXPORT FUNCTIONALITY
// ===============================================

document.getElementById('exportTaxpayers')?.addEventListener('click', function() {
  alert('Exporting taxpayer list...\n\nIn production, this would generate a CSV/Excel file with:\n- All visible taxpayers\n- Applied filters\n- Selected columns');
});

// ===============================================
// ADD TAXPAYER
// ===============================================

document.getElementById('addTaxpayer')?.addEventListener('click', function() {
  alert('Add New Taxpayer\n\nIn production, this would open a form to register:\n- Business entities\n- Individual taxpayers\n\nWith fields for:\n- Name/Business Name\n- TIN\n- Sector\n- Contact info\n- Initial compliance data');
});

// ===============================================
// UTILITY FUNCTIONS
// ===============================================

// Format currency
function formatCurrency(amount) {
  return 'ZMW ' + amount.toLocaleString('en-ZM');
}

// Format date
function formatDate(date) {
  return new Date(date).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
}

// Get relative time
function getRelativeTime(date) {
  const now = new Date();
  const past = new Date(date);
  const diff = Math.floor((now - past) / 1000);
  
  if (diff < 60) return 'just now';
  if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
  if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
  if (diff < 2592000) return Math.floor(diff / 86400) + ' days ago';
  return Math.floor(diff / 2592000) + ' months ago';
}

console.log('Taxpayers page initialized successfully!');