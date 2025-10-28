 //Vanilla JS for now!! 😭
 
 
 // Navigation
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        const section = this.getAttribute('data-section');
        alert(`Navigating to ${section} section...`);
    });
});

// Update time
function updateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}
updateTime();
setInterval(updateTime, 1000);

document.addEventListener('DOMContentLoaded', () => {
  // Risk Level Coloring
  document.querySelectorAll('tbody tr').forEach(row => {
    const riskCell = row.children[2];
    const level = riskCell.textContent.trim().toLowerCase();

    if (level === 'high') {
      riskCell.style.color = '#e74c3c';
      riskCell.style.fontWeight = 'bold';
    } else if (level === 'medium') {
      riskCell.style.color = '#f39c12';
      riskCell.style.fontWeight = 'bold';
    } else if (level === 'low') {
      riskCell.style.color = '#27ae60';
      riskCell.style.fontWeight = 'bold';
    }
  });
});


// Date filter selection (placeholder for now)
document.getElementById('dateRange').addEventListener('change', function() {
    const selectedDate = this.value;
    console.log('Selected date range:', selectedDate);
    // Placeholder for backend call (e.g., fetch(`/api/risk_assessments?date=${selectedDate}`))
});


// Initialize Charts
window.onload = function() {
    // Revenue Trend Chart (line chart)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [{
                label: 'Revenue (ZMW Million)',
                data: [420, 450, 480, 520, 580, 650],
                borderColor: '#0A4174',
                backgroundColor: 'rgba(10, 65, 116, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // Risk Distribution Chart (doughnut chart with initial data)
    const riskCtx = document.getElementById('riskChart').getContext('2d');
    let riskChart = new Chart(riskCtx, {
        type: 'doughnut',
        data: {
            labels: ['Low Risk', 'Medium Risk', 'High Risk'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: ['#28A745', '#FFC107', '#D32F2F'] 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Calendar Navigation
    let currentDate = new Date();
    function updateWeekRange() {
        const startOfWeek = new Date(currentDate);
        startOfWeek.setDate(currentDate.getDate() - currentDate.getDay());
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        document.getElementById('weekRange').textContent = `${startOfWeek.toLocaleString('en-US', { month: 'short', day: 'numeric' })} - ${endOfWeek.toLocaleString('en-US', { month: 'short', day: 'numeric' })}, ${endOfWeek.getFullYear()}`;
    }

    document.getElementById('prevWeek').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() - 7);
        updateWeekRange();
        // Placeholder for chart update based on week
        console.log('Previous week selected');
    });

    document.getElementById('nextWeek').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() + 7);
        updateWeekRange();
        // Placeholder for chart update based on week
        console.log('Next week selected');
    });

    updateWeekRange(); // Initialize with current week
};


// === Nudge Panel Interactivity ===

// === Nudge Panel Logic ===
const toggleNudgePanel = document.getElementById('toggleNudgePanel');
const closeNudgePanel = document.getElementById('closeNudgePanel');
const nudgePanel = document.querySelector('.nudge-panel');
const alertsContainer = document.getElementById('nudgeAlerts');
const nudgeEditor = document.getElementById('nudgeEditor');
const nudgeRecipientBanner = document.getElementById('nudgeRecipientBanner');
const nudgeTemplates = document.getElementById('nudgeTemplates');
const nudgeMessage = document.getElementById('nudgeMessage');
const nudgePreviewBox = document.getElementById('nudgePreviewBox');
const nudgeLog = document.getElementById('nudgeLog');

toggleNudgePanel.addEventListener('click', () => {
  nudgePanel.classList.toggle('hidden');
  loadComplianceAlerts();
});

closeNudgePanel.addEventListener('click', () => {
  nudgePanel.classList.add('hidden');
});

// Sample compliance alert data
const complianceAlerts = [
  { userId: 'TXP-2034', name: 'Officer Sarah Mwanza', issue: 'Missed Filing Deadline', risk: 'High' },
  { userId: 'TXP-1599', name: 'James Kunda', issue: 'Incomplete Tax Form', risk: 'Medium' },
  { userId: 'TXP-1178', name: 'Linda Chirwa', issue: 'Late Payment Notice', risk: 'High' }
];

// Load alerts dynamically
function loadComplianceAlerts() {
  alertsContainer.innerHTML = '';
  complianceAlerts.forEach(alert => {
    const card = document.createElement('div');
    card.classList.add('alert-card');
    card.innerHTML = `
      <div class="alert-info">
        <strong>${alert.name}</strong>
        <span>${alert.issue} — Risk: ${alert.risk}</span>
      </div>
      <button data-user="${alert.name}" data-id="${alert.userId}">Nudge</button>
    `;
    alertsContainer.appendChild(card);
  });

  // Attach listeners for Nudge buttons
  document.querySelectorAll('.alert-card button').forEach(btn => {
    btn.addEventListener('click', function() {
      const name = this.getAttribute('data-user');
      const id = this.getAttribute('data-id');
      nudgeRecipientBanner.textContent = `Nudging ${name} (User ID: ${id})`;
      nudgeEditor.classList.remove('hidden');
      nudgeMessage.value = '';
      nudgeTemplates.selectedIndex = 0;
      nudgePreviewBox.textContent = 'Your message will appear here...';
    });
  });
}

// Live preview
function updatePreview() {
  const message = nudgeMessage.value.trim() || 'Your message will appear here...';
  const recipient = nudgeRecipientBanner.textContent.replace('Nudging ', '');
  nudgePreviewBox.textContent = `${recipient}: ${message}`;
}
nudgeTemplates.addEventListener('change', () => {
  if (nudgeTemplates.value) {
    nudgeMessage.value = nudgeTemplates.value;
  }
  updatePreview();
});
nudgeMessage.addEventListener('input', updatePreview);

// Send nudge
document.getElementById('sendNudge').addEventListener('click', () => {
  const recipient = nudgeRecipientBanner.textContent.replace('Nudging ', '');
  const message = nudgeMessage.value.trim();

  if (!message) {
    alert('Please add a message or choose a template.');
    return;
  }

  const date = new Date().toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const li = document.createElement('li');
  li.innerHTML = `<strong>${date}:</strong> Sent to ${recipient} — “${message}”`;
  nudgeLog.prepend(li);

  // Reset editor
  nudgeEditor.classList.add('hidden');
  nudgeMessage.value = '';
  nudgePreviewBox.textContent = 'Your message will appear here...';

  // Small visual feedback
  nudgePanel.classList.add('pulse');
  setTimeout(() => nudgePanel.classList.remove('pulse'), 300);
});

// Placeholder for backend database integration
// TODO: You guys to integrate database connection here
// - Fetch data from the backend API (e.g., using fetch() or axios)
// - Example endpoint: '/api/data'
// - Update global state or trigger analytics.js updates (e.g., via custom events)
// - Suggested structure:
/*
async function fetchData() {
  try {
    const response = await fetch('/api/data');
    const data = await response.json();
    // Process data and dispatch to analytics.js (e.g., via CustomEvent)
    const event = new CustomEvent('dataUpdated', { detail: data });
    document.dispatchEvent(event);
  } catch (error) {
    console.error('Error fetching data:', error);
  }
}

// Initialize data fetch
fetchData();
*/