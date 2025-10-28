
document.addEventListener('DOMContentLoaded', function() {
  
  // Initialize all components
  initializeReportButtons();
  initializeCustomReportForm();
  initializeModal();
  
});

// ===============================================
// REPORT GENERATION
// ===============================================

function initializeReportButtons() {
  const reportButtons = document.querySelectorAll('.report-btn');
  
  reportButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      const reportType = this.getAttribute('data-report');
      const format = reportType.includes('csv') ? 'CSV' : 'PDF';
      const cleanType = reportType.replace('-csv', '');
      
      generateReport(cleanType, format);
    });
  });
}

function generateReport(type, format) {
  const reportNames = {
    'compliance': 'Compliance Summary Report',
    'risk': 'Risk Assessment Report',
    'revenue': 'Revenue Collection Report',
    'nudge': 'Nudge Campaign Effectiveness',
    'taxpayer': 'Taxpayer Directory Export',
    'fraud': 'Fraud Detection Summary',
    'sector': 'Sector Performance Analysis',
    'audit': 'Audit Trail & Activity Log'
  };
  
  const reportName = reportNames[type] || 'Report';
  showGenerationModal(reportName, format);
}

// ===============================================
// CUSTOM REPORT FORM
// ===============================================

function initializeCustomReportForm() {
  const generateBtn = document.getElementById('generateCustomReport');
  const resetBtn = document.getElementById('resetForm');
  
  generateBtn.addEventListener('click', function() {
    const reportName = document.getElementById('customReportName').value || 'Custom Report';
    const dateRange = document.getElementById('dateRange').value;
    const format = document.getElementById('reportFormat').value.toUpperCase();
    
    // Get selected data points
    const dataPoints = [];
    document.querySelectorAll('.checkbox-grid input[type="checkbox"]:checked').forEach(cb => {
      dataPoints.push(cb.parentElement.textContent.trim());
    });
    
    if (dataPoints.length === 0) {
      alert('Please select at least one data point to include in your report.');
      return;
    }
    
    console.log('Generating custom report:', {
      name: reportName,
      dateRange: dateRange + ' months',
      format: format,
      dataPoints: dataPoints
    });
    
    showGenerationModal(reportName, format, dateRange);
  });
  
  resetBtn.addEventListener('click', function() {
    document.getElementById('customReportName').value = '';
    document.getElementById('dateRange').value = '12';
    document.getElementById('reportFormat').value = 'pdf';
    document.querySelectorAll('.checkbox-grid input[type="checkbox"]').forEach(cb => {
      cb.checked = false;
    });
    // Check first 3 by default
    const checkboxes = document.querySelectorAll('.checkbox-grid input[type="checkbox"]');
    if (checkboxes[0]) checkboxes[0].checked = true;
    if (checkboxes[1]) checkboxes[1].checked = true;
    if (checkboxes[2]) checkboxes[2].checked = true;
  });
}

// ===============================================
// MODAL FUNCTIONALITY
// ===============================================

function initializeModal() {
  const modal = document.getElementById('reportModal');
  const closeBtn = document.getElementById('closeModal');
  const overlay = document.getElementById('modalOverlay');
  
  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', closeModal);
  
  // Escape key to close
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });
}

function showGenerationModal(reportName, format, dateRange) {
  const modal = document.getElementById('reportModal');
  const modalTitle = document.getElementById('modalTitle');
  const modalBody = document.getElementById('modalBody');
  const modalFooter = document.getElementById('modalFooter');
  
  // Reset modal
  modalTitle.textContent = 'Generating Report...';
  modalFooter.style.display = 'none';
  
  // Show loading state
  modalBody.innerHTML = `
    <div class="loading-spinner">
      <div class="spinner"></div>
      <p>Processing data and generating your report...</p>
      <div class="progress-bar">
        <div class="progress-fill" id="progressFill"></div>
      </div>
      <p class="progress-text" id="progressText">0%</p>
    </div>
  `;
  
  modal.classList.add('active');
  
  // Simulate progress
  simulateProgress(reportName, format, dateRange);
}

function simulateProgress(reportName, format, dateRange) {
  const progressFill = document.getElementById('progressFill');
  const progressText = document.getElementById('progressText');
  let progress = 0;
  
  const interval = setInterval(() => {
    progress += Math.random() * 15;
    if (progress > 100) progress = 100;
    
    progressFill.style.width = progress + '%';
    progressText.textContent = Math.round(progress) + '%';
    
    if (progress >= 100) {
      clearInterval(interval);
      setTimeout(() => {
        showSuccessState(reportName, format, dateRange);
      }, 500);
    }
  }, 200);
}

function showSuccessState(reportName, format, dateRange) {
  const modalTitle = document.getElementById('modalTitle');
  const modalBody = document.getElementById('modalBody');
  const modalFooter = document.getElementById('modalFooter');
  
  modalTitle.textContent = 'Report Generated Successfully!';
  
  const rangeText = dateRange ? ` (${dateRange} months)` : '';
  const fileSize = (Math.random() * 2 + 0.5).toFixed(2);
  const timestamp = new Date().toLocaleString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
  
  modalBody.innerHTML = `
    <div class="modal-success">
      <div class="success-icon">
        <i class="fas fa-check"></i>
      </div>
      <h4>Your report is ready!</h4>
      <p>The report has been generated and is ready for download.</p>
      
      <div class="report-info">
        <div class="report-info-item">
          <span class="report-info-label">Report Name</span>
          <span class="report-info-value">${reportName}${rangeText}</span>
        </div>
        <div class="report-info-item">
          <span class="report-info-label">Format</span>
          <span class="report-info-value">${format}</span>
        </div>
        <div class="report-info-item">
          <span class="report-info-label">File Size</span>
          <span class="report-info-value">${fileSize} MB</span>
        </div>
        <div class="report-info-item">
          <span class="report-info-label">Generated</span>
          <span class="report-info-value">${timestamp}</span>
        </div>
      </div>
    </div>
  `;
  
  modalFooter.style.display = 'flex';
  
  // Add download functionality
  document.getElementById('downloadReport').onclick = function() {
    downloadReport(reportName, format);
  };
  
  document.getElementById('viewReport').onclick = function() {
    alert(`Opening ${reportName} preview...\n\nIn production, this would open a preview of the ${format} file.`);
  };
}

function downloadReport(reportName, format) {
  const fileName = reportName.replace(/\s+/g, '_') + '.' + format.toLowerCase();
  
  alert(`Downloading: ${fileName}\n\nIn production, this would trigger an actual file download of the generated report.`);
  
  console.log('Download initiated:', {
    fileName: fileName,
    timestamp: new Date().toISOString()
  });
  
  // Close modal after download
  setTimeout(() => {
    closeModal();
  }, 1000);
}

function closeModal() {
  const modal = document.getElementById('reportModal');
  modal.classList.remove('active');
}

// ===============================================
// REPORT HISTORY
// ===============================================

document.getElementById('viewHistory')?.addEventListener('click', function() {
  alert('Report History\n\nIn production, this would show:\n- Previously generated reports\n- Download history\n- Scheduled reports\n- Report templates');
});

// ===============================================
// ALERT SETTINGS
// ===============================================

document.getElementById('alertSettings')?.addEventListener('click', function() {
  alert('Report Settings\n\nIn production, this would allow:\n- Configure auto-generated reports\n- Set up email delivery\n- Manage report templates\n- Schedule recurring reports');
});




// ===============================================
// UTILITY FUNCTIONS
// ===============================================

// Format file size
function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
  return (bytes / 1048576).toFixed(2) + ' MB';
}

// Generate random file size for demo
function getRandomFileSize() {
  return Math.floor(Math.random() * 2000000) + 500000; // 500KB - 2.5MB
}

console.log('Reports page initialized successfully!');