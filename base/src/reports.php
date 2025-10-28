<?php
session_start();
require_once '../src/db_connect.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Admin info
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, email FROM tax_admins WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userName = $user ? htmlspecialchars($user['full_name']) : 'Admin User';
$userEmail = $user ? htmlspecialchars($user['email']) : 'admin@zra.gov.zm';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports - Adaptive Compliance Engine</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="reports.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <h2 class="sidebar-title"><i class="fas fa-shield-alt"></i> ZRA Admin</h2>
    <p class="sidebar-subtitle">Compliance Engine</p>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Overview</span></a>
    <a href="analysis.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
    <a href="risk.php" class="nav-item"><i class="fas fa-exclamation-triangle"></i><span>Risk Assessments</span></a>
    <a href="taxpayers.php" class="nav-item"><i class="fas fa-users"></i><span>Taxpayers</span></a>
    <a href="alerts.php" class="nav-item"><i class="fas fa-bell"></i><span>Alerts</span></a>
    <a href="reports.php" class="nav-item active"><i class="fas fa-file-download"></i><span>Reports</span></a>
    <a href="#users" class="nav-item"><i class="fas fa-user-cog"></i><span>User Management</span></a>
    <a href="#audit" class="nav-item"><i class="fas fa-history"></i><span>Audit Logs</span></a>
    <a href="#settings" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"></div>
      <div>
        <p class="user-name"><?php echo $userName; ?></p>
        <p class="user-email"><?php echo $userEmail; ?></p>
      </div>
    </div>
  </div>
</div>

<!-- Main -->
<div class="main-content">
<header>
  <div class="header-content">
    <div class="header-text">
      <h1>Report Generator</h1>
      <p class="subtitle">Generate and export compliance & revenue reports</p>
    </div>
    <div class="header-right">
      <div class="clock" id="current-time"></div>
      <div class="profile-badge">Officer: <?php echo $userName; ?></div>
    </div>
  </div>
</header>

<main class="content">

  <!-- Quick Stats -->
  <div class="report-stats-grid">
    <div class="stat-card"><div class="stat-icon"><i class="fas fa-file-alt"></i></div>
      <div class="stat-info"><p class="stat-label">Reports Generated</p><h3 class="stat-value">1,247</h3><p class="stat-meta">This year</p></div>
    </div>
    <div class="stat-card"><div class="stat-icon"><i class="fas fa-download"></i></div>
      <div class="stat-info"><p class="stat-label">Downloads</p><h3 class="stat-value">3,892</h3><p class="stat-meta">Last 30 days</p></div>
    </div>
    <div class="stat-card"><div class="stat-icon"><i class="fas fa-clock"></i></div>
      <div class="stat-info"><p class="stat-label">Scheduled Reports</p><h3 class="stat-value">12</h3><p class="stat-meta">Active</p></div>
    </div>
    <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div>
      <div class="stat-info"><p class="stat-label">Shared Reports</p><h3 class="stat-value">284</h3><p class="stat-meta">With team</p></div>
    </div>
  </div>
<?php
// Example: these could come from queries or calculations
$revenueCompliance = "ZMW 8.3M";
$revenueTarget     = "ZMW 5.93B";

$nudgesSent        = 4230;
$nudgesPeriod      = "Last 7 days";

$fraudAlerts       = 27;
$fraudStatus       = "Active";

$highRiskCount     = 152;
$highRiskNote      = "Review queue";

$complianceRate    = 85;
$complianceChange  = "+3%";

$fraudDetection    = "12%";
$fraudNote         = "View cases";
?>
<aside class="side-panel">
  <div class="summary-cards">
    <div class="card" data-target="revenue">
      <div class="icon-bg"><i class="fas fa-coins"></i></div>
      <div class="card-info">
        <h4>Revenue Compliance</h4>
        <p><?php echo htmlspecialchars($revenueCompliance); ?></p>
        <div class="muted">Target: <?php echo htmlspecialchars($revenueTarget); ?></div>
      </div>
    </div>

    <div class="card" data-target="nudges">
      <div class="icon-bg"><i class="fas fa-bell"></i></div>
      <div class="card-info">
        <h4>Nudges Sent</h4>
        <p><?php echo number_format($nudgesSent); ?></p>
        <div class="muted"><?php echo htmlspecialchars($nudgesPeriod); ?></div>
      </div>
    </div>

    <div class="card" data-target="fraud">
      <div class="icon-bg"><i class="fas fa-shield-alt"></i></div>
      <div class="card-info">
        <h4>Fraud Alerts</h4>
        <p><?php echo (int)$fraudAlerts; ?></p>
        <div class="muted"><?php echo htmlspecialchars($fraudStatus); ?></div>
      </div>
    </div>

    <div class="card" data-target="risk">
      <div class="icon-bg"><i class="fas fa-exclamation-triangle"></i></div>
      <div class="card-info">
        <h4>High Risk Taxpayers</h4>
        <p><?php echo (int)$highRiskCount; ?></p>
        <div class="muted"><?php echo htmlspecialchars($highRiskNote); ?></div>
      </div>
    </div>
  </div>

  <div class="quick-stats">
    <div class="quick-stat-card">
      <div class="icon-bg" style="background:linear-gradient(135deg,var(--success),#1f8b3e)">
        <i class="fas fa-chart-line"></i>
      </div>
      <div>
        <p class="muted">Compliance Rate</p>
        <strong><?php echo $complianceRate; ?>% 
          <small style="color:var(--success)">▲ <?php echo $complianceChange; ?></small>
        </strong>
      </div>
    </div>
    <div class="quick-stat-card">
      <div class="icon-bg" style="background:linear-gradient(135deg,var(--error),#b52b27)">
        <i class="fas fa-bug"></i>
      </div>
      <div>
        <p class="muted">Fraud Detection</p>
        <strong><?php echo htmlspecialchars($fraudDetection); ?> 
          <small class="muted"><?php echo htmlspecialchars($fraudNote); ?></small>
        </strong>
      </div>
    </div>
  </div>
</aside>

<?php
// Define report templates as an array
$reportTemplates = [
  [
    'id' => 'compliance',
    'icon' => 'fa-check-circle',
    'class' => 'compliance',
    'title' => 'Compliance Summary Report',
    'description' => 'Comprehensive overview of taxpayer compliance rates, trends, and sector breakdowns.',
    'meta' => [
      ['icon' => 'fa-chart-line', 'text' => 'Trends'],
      ['icon' => 'fa-building', 'text' => 'By Sector'],
      ['icon' => 'fa-calendar', 'text' => 'Time-based']
    ]
  ],
  [
    'id' => 'risk',
    'icon' => 'fa-exclamation-triangle',
    'class' => 'risk',
    'title' => 'Risk Assessment Report',
    'description' => 'Detailed analysis of high-risk taxpayers, risk factors, and recommended interventions.',
    'meta' => [
      ['icon' => 'fa-shield-alt', 'text' => 'Risk Scores'],
      ['icon' => 'fa-flag', 'text' => 'Flagged Cases'],
      ['icon' => 'fa-lightbulb', 'text' => 'AI Insights']
    ]
  ],
  [
    'id' => 'revenue',
    'icon' => 'fa-dollar-sign',
    'class' => 'revenue',
    'title' => 'Revenue Collection Report',
    'description' => 'Revenue performance vs targets, collection efficiency, and sector contributions.',
    'meta' => [
      ['icon' => 'fa-target', 'text' => 'Targets'],
      ['icon' => 'fa-coins', 'text' => 'Collections'],
      ['icon' => 'fa-percentage', 'text' => 'Efficiency']
    ]
  ],
  [
    'id' => 'nudge',
    'icon' => 'fa-paper-plane',
    'class' => 'nudge',
    'title' => 'Nudge Campaign Effectiveness',
    'description' => 'Track nudge delivery, response rates, and impact on compliance behavior.',
    'meta' => [
      ['icon' => 'fa-bell', 'text' => 'Sent'],
      ['icon' => 'fa-reply', 'text' => 'Responses'],
      ['icon' => 'fa-chart-line', 'text' => 'Impact']
    ]
  ],
  [
    'id' => 'taxpayer',
    'icon' => 'fa-users',
    'class' => 'taxpayer',
    'title' => 'Taxpayer Directory Export',
    'description' => 'Complete taxpayer list with contact information, status, and compliance data.',
    'meta' => [
      ['icon' => 'fa-address-book', 'text' => 'Contacts'],
      ['icon' => 'fa-filter', 'text' => 'Filterable'],
      ['icon' => 'fa-file-excel', 'text' => 'Excel Ready']
    ]
  ],
  [
    'id' => 'fraud',
    'icon' => 'fa-shield-alt',
    'class' => 'fraud',
    'title' => 'Fraud Detection Summary',
    'description' => 'AI-flagged cases, investigation status, and fraud prevention metrics.',
    'meta' => [
      ['icon' => 'fa-search', 'text' => 'Detected'],
      ['icon' => 'fa-gavel', 'text' => 'Cases'],
      ['icon' => 'fa-lock', 'text' => 'Confidential']
    ]
  ],
  [
    'id' => 'sector',
    'icon' => 'fa-industry',
    'class' => 'sector',
    'title' => 'Sector Performance Analysis',
    'description' => 'Compare compliance and revenue across different economic sectors.',
    'meta' => [
      ['icon' => 'fa-chart-bar', 'text' => 'Comparison'],
      ['icon' => 'fa-arrows-alt-h', 'text' => 'Benchmarks'],
      ['icon' => 'fa-trophy', 'text' => 'Rankings']
    ]
  ],
  [
    'id' => 'audit',
    'icon' => 'fa-history',
    'class' => 'audit',
    'title' => 'Audit Trail & Activity Log',
    'description' => 'Complete system activity log for compliance audits and security reviews.',
    'meta' => [
      ['icon' => 'fa-user-shield', 'text' => 'Secure'],
      ['icon' => 'fa-clock', 'text' => 'Timestamped'],
      ['icon' => 'fa-lock', 'text' => 'Encrypted']
    ]
  ]
];
?>

<!-- Report Templates Grid -->
<div class="reports-grid">
  <?php foreach ($reportTemplates as $report): ?>
    <div class="report-card">
      <div class="report-icon <?php echo $report['class']; ?>">
        <i class="fas <?php echo $report['icon']; ?>"></i>
      </div>
      <div class="report-content">
        <h3><?php echo htmlspecialchars($report['title']); ?></h3>
        <p><?php echo htmlspecialchars($report['description']); ?></p>
        <div class="report-meta">
          <?php foreach ($report['meta'] as $meta): ?>
            <span class="meta-tag"><i class="fas <?php echo $meta['icon']; ?>"></i> <?php echo htmlspecialchars($meta['text']); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="report-actions">
        <button class="report-btn primary" data-report="<?php echo $report['id']; ?>">
          <i class="fas fa-file-pdf"></i> Generate
        </button>
        <button class="report-btn secondary" data-report="<?php echo $report['id']; ?>-csv">
          <i class="fas fa-file-csv"></i> CSV
        </button>
      </div>
    </div>
  <?php endforeach; ?>
</div>


<!-- Custom Report Builder Section -->
<div class="custom-report-section">
  <div class="section-header">
    <h3><i class="fas fa-magic"></i> Custom Report Builder</h3>
    <p>Create a tailored report with specific data points and date ranges</p>
  </div>
  
  <form method="post" action="generate_custom_report.php" class="custom-report-form">
    <div class="form-row">
      <div class="form-group">
        <label for="customReportName">Report Name</label>
        <input type="text" class="form-input" 
               placeholder="e.g., Q3 Compliance Review" 
               id="customReportName" 
               name="report_name" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="dateRange">Date Range</label>
        <select class="form-select" id="dateRange" name="date_range">
          <option value="6">Last 6 Months</option>
          <option value="9">Last 9 Months</option>
          <option value="12" selected>Last 12 Months</option>
          <option value="custom">Custom Range</option>
        </select>
      </div>

      <div class="form-group">
        <label for="reportFormat">Report Format</label>
        <select class="form-select" id="reportFormat" name="report_format">
          <option value="pdf">PDF Document</option>
          <option value="csv">CSV Spreadsheet</option>
          <option value="excel">Excel Workbook</option>
          <option value="json">JSON Data</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group full-width">
        <label>Include Data Points</label>
        <div class="checkbox-grid">
          <label class="checkbox-label">
            <input type="checkbox" name="data_points[]" value="taxpayer_info" checked> Taxpayer Information
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="data_points[]" value="compliance_scores" checked> Compliance Scores
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="data_points[]" value="risk_assessments" checked> Risk Assessments
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="data_points[]" value="payment_history"> Payment History
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="data_points[]" value="nudge_history"> Nudge History
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="data_points[]" value="revenue_data"> Revenue Data
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="data_points[]" value="sector_analysis"> Sector Analysis
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="data_points[]" value="fraud_alerts"> Fraud Alerts
          </label>
        </div>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group full-width">
        <label>Filter by Sector (Optional)</label>
        <div class="checkbox-grid">
          <label class="checkbox-label">
            <input type="checkbox" name="sectors[]" value="retail"> Retail
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="sectors[]" value="manufacturing"> Manufacturing
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="sectors[]" value="services"> Services
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="sectors[]" value="agriculture"> Agriculture
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="sectors[]" value="technology"> Technology
          </label>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-primary" id="generateCustomReport">
        <i class="fas fa-magic"></i> Generate Custom Report
      </button>
      <button type="reset" class="btn-secondary" id="resetForm">
        <i class="fas fa-redo"></i> Reset
      </button>
    </div>
  </form>
</div>



<script src="https://kit.fontawesome.com/a2e0e6a7b8.js" crossorigin="anonymous"></script>
<script src="/assets/analytics.js"></script>
<script src="/assets/script.js"></script>
</body>
</html>
