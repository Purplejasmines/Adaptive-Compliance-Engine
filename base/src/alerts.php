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

$userName  = $user ? htmlspecialchars($user['full_name']) : 'Admin User';
$userEmail = $user ? htmlspecialchars($user['email']) : 'admin@zra.gov.zm';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaptive Complinance Engine</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="alerts.css">
</head>
<body>

     <!-- Sidebar Navigation -->
      
<!-- Sidebar -->
<!-- Sidebar Navigation -->
<div class="sidebar">
  <div class="sidebar-header">
    <h2 class="sidebar-title">
      <i class="fas fa-shield-alt"></i>
      ZRA Admin
    </h2>
    <p class="sidebar-subtitle">Compliance Engine</p>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item active"><i class="fas fa-home"></i><span>Overview</span></a>
    <a href="analysis.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
    <a href="risk.php" class="nav-item"><i class="fas fa-exclamation-triangle"></i><span>Risk Assessments</span></a>
    <a href="taxpayers.php" class="nav-item"><i class="fas fa-users"></i><span>Taxpayers</span></a>
    <a href="alerts.php" class="nav-item"><i class="fas fa-bell"></i><span>Alerts <span class="alert-count">5</span></span></a>
    <a href="reports.php" class="nav-item"><i class="fas fa-file-download"></i><span>Reports</span></a>
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
        <a href="logout.php" class="btn btn-outline-light btn-sm mt-2 logout-btn">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>
  </div>
</div>


    <!-- Header -->
    <div class="main-content">
        <header>
            <div class="header-content">
                <div class="header-text">
                    <h1>Alerts & Notifications</h1>
                    <p class="subtitle">Real-time monitoring and system notifications</p>
                </div>
                <div class="header-right">
                    <div class="clock" id="current-time"></div>
                    <div class="profile-badge">Officer: Sarah Mwanza</div>
                </div>
            </div>
        </header>

<!-- Main content area -->
<main class="content" id="Content">

  

  <!-- Filters -->
  <div class="alerts-filters">
    <div class="filter-group">
      <label>Alert Type</label>
      <select id="typeFilter" class="filter-select">
        <option value="all">All Alerts</option>
        <option value="fraud">Fraud Detection</option>
        <option value="compliance">Compliance</option>
        <option value="system">System</option>
        <option value="nudge">Nudge Response</option>
      </select>
    </div>

    <div class="filter-group">
      <label>Severity</label>
      <select id="severityFilter" class="filter-select">
        <option value="all">All Levels</option>
        <option value="critical">Critical</option>
        <option value="high">High</option>
        <option value="medium">Medium</option>
        <option value="info">Info</option>
      </select>
    </div>

    <div class="filter-group">
      <label>Time Period</label>
      <select id="timeFilter" class="filter-select">
        <option value="today">Today</option>
        <option value="week">This Week</option>
        <option value="month">This Month</option>
        <option value="all">All Time</option>
      </select>
    </div>

    <div class="filter-group">
      <label>Status</label>
      <select id="statusFilter" class="filter-select">
        <option value="all">All Status</option>
        <option value="unread">Unread</option>
        <option value="read">Read</option>
        <option value="actioned">Actioned</option>
      </select>
    </div>

    <button class="btn-filter-reset" id="resetAlertFilters">
      <i class="fas fa-redo"></i> Reset
    </button>
  </div>

  <!-- Alert Stats -->
  <div class="alert-stats-grid">
    <div class="alert-stat-box critical">
      <div class="stat-icon">
        <i class="fas fa-exclamation-circle"></i>
      </div>
      <div class="stat-content">
        <h3>5</h3>
        <p>Critical</p>
      </div>
    </div>

    <div class="alert-stat-box high">
      <div class="stat-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="stat-content">
        <h3>12</h3>
        <p>High Priority</p>
      </div>
    </div>

    <div class="alert-stat-box medium">
      <div class="stat-icon">
        <i class="fas fa-info-circle"></i>
      </div>
      <div class="stat-content">
        <h3>34</h3>
        <p>Medium</p>
      </div>
    </div>

    <div class="alert-stat-box info">
      <div class="stat-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-content">
        <h3>89</h3>
        <p>Informational</p>
      </div>
    </div>
  </div>

  <!-- Alerts Feed -->
  <div class="alerts-feed">
    <div class="feed-header">
      <h3>Alert Feed</h3>
      <div class="feed-controls">
        <button class="control-btn active" data-view="all">
          <i class="fas fa-list"></i> All
        </button>
        <button class="control-btn" data-view="unread">
          <i class="fas fa-envelope"></i> Unread
        </button>
      </div>
    </div>

    <div class="alerts-list" id="alertsList">
      
      <!-- Alert 1: Critical - Fraud Detection -->
      <div class="alert-card critical unread" data-id="alert-001">
        <div class="alert-indicator"></div>
        <div class="alert-icon">
          <i class="fas fa-shield-alt"></i>
        </div>
        <div class="alert-content">
          <div class="alert-header">
            <span class="alert-badge critical">Critical</span>
            <span class="alert-type">Fraud Detection</span>
            <span class="alert-time">2 minutes ago</span>
          </div>
          <h4 class="alert-title">Potential Fraud Pattern Detected</h4>
          <p class="alert-description">
            Taxpayer <strong>TP-09876</strong> (Manufacturing Corp) shows unusual transaction patterns. 
            Multiple invoices with identical amounts to different vendors flagged by AI.
          </p>
          <div class="alert-meta">
            <span class="meta-item"><i class="fas fa-user"></i> TP-09876</span>
            <span class="meta-item"><i class="fas fa-dollar-sign"></i> ZMW 2.4M affected</span>
            <span class="meta-item"><i class="fas fa-flag"></i> Auto-flagged</span>
          </div>
          <div class="alert-actions">
            <button class="alert-btn primary">
              <i class="fas fa-eye"></i> Investigate
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-user-plus"></i> Assign Officer
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-times"></i> Dismiss
            </button>
          </div>
        </div>
        <button class="mark-read-btn" title="Mark as read">
          <i class="fas fa-check"></i>
        </button>
      </div>

      <!-- Alert 2: High - Compliance Warning -->
      <div class="alert-card high unread" data-id="alert-002">
        <div class="alert-indicator"></div>
        <div class="alert-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="alert-content">
          <div class="alert-header">
            <span class="alert-badge high">High</span>
            <span class="alert-type">Compliance</span>
            <span class="alert-time">15 minutes ago</span>
          </div>
          <h4 class="alert-title">Multiple Late Filings Detected</h4>
          <p class="alert-description">
            <strong>15 taxpayers</strong> in the Retail sector missed the Q3 filing deadline. 
            Bulk nudge campaign recommended to improve compliance.
          </p>
          <div class="alert-meta">
            <span class="meta-item"><i class="fas fa-building"></i> Retail Sector</span>
            <span class="meta-item"><i class="fas fa-calendar"></i> Q3 Deadline</span>
            <span class="meta-item"><i class="fas fa-users"></i> 15 taxpayers</span>
          </div>
          <div class="alert-actions">
            <button class="alert-btn primary">
              <i class="fas fa-paper-plane"></i> Send Bulk Nudges
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-list"></i> View List
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-times"></i> Dismiss
            </button>
          </div>
        </div>
        <button class="mark-read-btn" title="Mark as read">
          <i class="fas fa-check"></i>
        </button>
      </div>

      <!-- Alert 3: High - Risk Score Increase -->
      <div class="alert-card high unread" data-id="alert-003">
        <div class="alert-indicator"></div>
        <div class="alert-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="alert-content">
          <div class="alert-header">
            <span class="alert-badge high">High</span>
            <span class="alert-type">Risk Assessment</span>
            <span class="alert-time">1 hour ago</span>
          </div>
          <h4 class="alert-title">Risk Score Spike Detected</h4>
          <p class="alert-description">
            Taxpayer <strong>TP-09432</strong> (Logistics Inc.) risk score increased from 0.65 to 0.82. 
            Factors: late payment, revenue drop >30%, inconsistent reporting.
          </p>
          <div class="alert-meta">
            <span class="meta-item"><i class="fas fa-user"></i> TP-09432</span>
            <span class="meta-item"><i class="fas fa-arrow-up"></i> +0.17 increase</span>
            <span class="meta-item"><i class="fas fa-exclamation"></i> Now Critical</span>
          </div>
          <div class="alert-actions">
            <button class="alert-btn primary">
              <i class="fas fa-eye"></i> View Profile
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-bell"></i> Send Nudge
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-times"></i> Dismiss
            </button>
          </div>
        </div>
        <button class="mark-read-btn" title="Mark as read">
          <i class="fas fa-check"></i>
        </button>
      </div>

      <!-- Alert 4: Medium - System Notification -->
      <div class="alert-card medium read" data-id="alert-004">
        <div class="alert-indicator"></div>
        <div class="alert-icon">
          <i class="fas fa-server"></i>
        </div>
        <div class="alert-content">
          <div class="alert-header">
            <span class="alert-badge medium">Medium</span>
            <span class="alert-type">System</span>
            <span class="alert-time">2 hours ago</span>
          </div>
          <h4 class="alert-title">AI Model Update Completed</h4>
          <p class="alert-description">
            Risk assessment model updated with new data. Performance metrics improved: 
            accuracy +2.3%, false positive rate -1.8%.
          </p>
          <div class="alert-meta">
            <span class="meta-item"><i class="fas fa-robot"></i> AI System</span>
            <span class="meta-item"><i class="fas fa-check-circle"></i> Success</span>
            <span class="meta-item"><i class="fas fa-chart-line"></i> Performance +2.3%</span>
          </div>
          <div class="alert-actions">
            <button class="alert-btn secondary">
              <i class="fas fa-file-alt"></i> View Report
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-times"></i> Dismiss
            </button>
          </div>
        </div>
        <button class="mark-read-btn" title="Mark as read">
          <i class="fas fa-check"></i>
        </button>
      </div>

      <!-- Alert 5: Info - Nudge Response -->
      <div class="alert-card info read" data-id="alert-005">
        <div class="alert-indicator"></div>
        <div class="alert-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="alert-content">
          <div class="alert-header">
            <span class="alert-badge info">Info</span>
            <span class="alert-type">Nudge Response</span>
            <span class="alert-time">3 hours ago</span>
          </div>
          <h4 class="alert-title">Positive Nudge Response</h4>
          <p class="alert-description">
            Taxpayer <strong>TP-10234</strong> (John Mwanza) filed tax return on time after receiving 
            reminder nudge. Compliance score improved from 82 to 85.
          </p>
          <div class="alert-meta">
            <span class="meta-item"><i class="fas fa-user"></i> TP-10234</span>
            <span class="meta-item"><i class="fas fa-arrow-up"></i> Score +3</span>
            <span class="meta-item"><i class="fas fa-thumbs-up"></i> Success</span>
          </div>
          <div class="alert-actions">
            <button class="alert-btn secondary">
              <i class="fas fa-eye"></i> View Profile
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-times"></i> Dismiss
            </button>
          </div>
        </div>
        <button class="mark-read-btn" title="Mark as read">
          <i class="fas fa-check"></i>
        </button>
      </div>

      <!-- Alert 6: Info - System Success -->
      <div class="alert-card info read" data-id="alert-006">
        <div class="alert-indicator"></div>
        <div class="alert-icon">
          <i class="fas fa-paper-plane"></i>
        </div>
        <div class="alert-content">
          <div class="alert-header">
            <span class="alert-badge info">Info</span>
            <span class="alert-type">System</span>
            <span class="alert-time">5 hours ago</span>
          </div>
          <h4 class="alert-title">Bulk Nudge Campaign Completed</h4>
          <p class="alert-description">
            Successfully sent 450 compliance reminders to Manufacturing sector taxpayers. 
            Delivery rate: 98.7%, estimated response time: 48-72 hours.
          </p>
          <div class="alert-meta">
            <span class="meta-item"><i class="fas fa-building"></i> Manufacturing</span>
            <span class="meta-item"><i class="fas fa-envelope"></i> 450 sent</span>
            <span class="meta-item"><i class="fas fa-percentage"></i> 98.7% delivered</span>
          </div>
          <div class="alert-actions">
            <button class="alert-btn secondary">
              <i class="fas fa-chart-bar"></i> View Campaign
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-times"></i> Dismiss
            </button>
          </div>
        </div>
        <button class="mark-read-btn" title="Mark as read">
          <i class="fas fa-check"></i>
        </button>
      </div>

      <!-- Alert 7: Critical - System Alert -->
      <div class="alert-card critical unread" data-id="alert-007">
        <div class="alert-indicator"></div>
        <div class="alert-icon">
          <i class="fas fa-database"></i>
        </div>
        <div class="alert-content">
          <div class="alert-header">
            <span class="alert-badge critical">Critical</span>
            <span class="alert-type">System</span>
            <span class="alert-time">6 hours ago</span>
          </div>
          <h4 class="alert-title">Data Anomaly Detected</h4>
          <p class="alert-description">
            Unusual data pattern in Agriculture sector filings. 23 taxpayers reported identical 
            revenue figures. Possible data entry error or coordination.
          </p>
          <div class="alert-meta">
            <span class="meta-item"><i class="fas fa-seedling"></i> Agriculture</span>
            <span class="meta-item"><i class="fas fa-users"></i> 23 taxpayers</span>
            <span class="meta-item"><i class="fas fa-exclamation"></i> Investigation needed</span>
          </div>
          <div class="alert-actions">
            <button class="alert-btn primary">
              <i class="fas fa-search"></i> Investigate
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-file-export"></i> Export Data
            </button>
            <button class="alert-btn secondary">
              <i class="fas fa-times"></i> Dismiss
            </button>
          </div>
        </div>
        <button class="mark-read-btn" title="Mark as read">
          <i class="fas fa-check"></i>
        </button>
      </div>

    </div>

    <!-- Empty State (hidden by default) -->
    <div class="empty-state" id="emptyState" style="display: none;">
      <div class="empty-icon">
        <i class="fas fa-bell-slash"></i>
      </div>
      <h3>No Alerts Found</h3>
      <p>All caught up! No alerts match your current filters.</p>
      <button class="btn-primary" onclick="document.getElementById('resetAlertFilters').click()">
        Reset Filters
      </button>
    </div>

    <!-- Load More -->
    <div class="load-more-section">
      <button class="btn-secondary" id="loadMore">
        <i class="fas fa-chevron-down"></i> Load More Alerts
      </button>
    </div>
  </div>
</main>










   
   

    <script src="script.js"></script>
       <script src="alerts.js"></script>

</body>

</html-->
























<!--?php
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

$userName  = $user ? htmlspecialchars($user['full_name']) : 'Admin User';
$userEmail = $user ? htmlspecialchars($user['email']) : 'admin@zra.gov.zm';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alerts & Notifications - Adaptive Compliance Engine</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="alerts.css">
</head>
<body>

<!-- Sidebar Navigation ->
<div class="sidebar">
  <div class="sidebar-header">
    <h2 class="sidebar-title"><i class="fas fa-shield-alt"></i> ZRA Admin</h2>
    <p class="sidebar-subtitle">Compliance Engine</p>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Overview</span></a>
    <a href="analysis.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
    <a href="assessments.php" class="nav-item"><i class="fas fa-exclamation-triangle"></i><span>Risk Assessments</span></a>
    <a href="taxpayers.php" class="nav-item"><i class="fas fa-users"></i><span>Taxpayers</span></a>
    <a href="alerts.php" class="nav-item active"><i class="fas fa-bell"></i><span>Alerts <span class="alert-count">5</span></span></a>
    <a href="reports.php" class="nav-item"><i class="fas fa-file-download"></i><span>Reports</span></a>
    <a href="users.php" class="nav-item"><i class="fas fa-user-cog"></i><span>User Management</span></a>
    <a href="audit.php" class="nav-item"><i class="fas fa-history"></i><span>Audit Logs</span></a>
    <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"></div>
      <div>
        <p class="user-name"><!?php echo $userName; ?></p>
<p class="user-email"><?php echo $userEmail; ?></p>

<div class="profile-badge">Officer: <!?php echo $userName; ?></div>

      </div>
    </div>
  </div>
</div>

<!-- Main Content ->
<div class="main-content">
  <header>
    <div class="header-content">
      <div class="header-text">
        <h1>Alerts & Notifications</h1>
        <p class="subtitle">Real-time monitoring and system notifications</p>
      </div>
      <div class="header-right">
        <div class="clock" id="current-time"></div>
        <div class="profile-badge">Officer: Sarah Mwanza</div>
      </div>
    </div>
  </header>

  <main class="content" id="Content">

    <!-- Filters ->
    <div class="alerts-filters">
      <div class="filter-group">
        <label>Alert Type</label>
        <select id="typeFilter" class="filter-select">
          <option value="all">All Alerts</option>
          <option value="fraud">Fraud Detection</option>
          <option value="compliance">Compliance</option>
          <option value="system">System</option>
          <option value="nudge">Nudge Response</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Severity</label>
        <select id="severityFilter" class="filter-select">
          <option value="all">All Levels</option>
          <option value="critical">Critical</option>
          <option value="high">High</option>
          <option value="medium">Medium</option>
          <option value="info">Info</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Time Period</label>
        <select id="timeFilter" class="filter-select">
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="all">All Time</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Status</label>
        <select id="statusFilter" class="filter-select">
          <option value="all">All Status</option>
          <option value="unread">Unread</option>
          <option value="read">Read</option>
          <option value="actioned">Actioned</option>
        </select>
      </div>
      <button class="btn-filter-reset" id="resetAlertFilters"><i class="fas fa-redo"></i> Reset</button>
    </div>

    <!-- Alert Stats ->
    <div class="alert-stats-grid">
      <div class="alert-stat-box critical"><div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div><div class="stat-content"><h3>5</h3><p>Critical</p></div></div>
      <div class="alert-stat-box high"><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div><div class="stat-content"><h3>12</h3><p>High Priority</p></div></div>
      <div class="alert-stat-box medium"><div class="stat-icon"><i class="fas fa-info-circle"></i></div><div class="stat-content"><h3>34</h3><p>Medium</p></div></div>
      <div class="alert-stat-box info"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-content"><h3>89</h3><p>Informational</p></div></div>
    </div>

    <!-- Alerts Feed ->
    <div class="alerts-feed">
      <div class="feed-header">
        <h3>Alert Feed</h3>
        <div class="feed-controls">
          <button class="control-btn active" data-view="all"><i class="fas fa-list"></i> All</button>
          <button class="control-btn" data-view="unread"><i class="fas fa-envelope"></i> Unread</button>
        </div>
      </div>

      <div class="alerts-list" id="alertsList">


<!-- Example Alert Items ->
      <div class="alert-item critical unread">
        <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="alert-content">
          <h4>Fraudulent Filing Detected</h4>
          <p>System flagged suspicious VAT return from TP-09432.</p>
          <span class="alert-meta">Oct 22, 2025 • Critical • Fraud Detection</span>
        </div>
        <div class="alert-actions">
          <button class="action-btn mark-read"><i class="fas fa-envelope-open"></i></button>
          <button class="action-btn view-detail"><i class="fas fa-eye"></i></button>
        </div>
      </div>

      <div class="alert-item high unread">
        <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="alert-content">
          <h4>Compliance Deadline Approaching</h4>
          <p>Corporate Income Tax filing due in 3 days for TP-08321.</p>
          <span class="alert-meta">Oct 21, 2025 • High • Compliance</span>
        </div>
        <div class="alert-actions">
          <button class="action-btn mark-read"><i class="fas fa-envelope-open"></i></button>
          <button class="action-btn view-detail"><i class="fas fa-eye"></i></button>
        </div>
      </div>

      <div class="alert-item medium read">
        <div class="alert-icon"><i class="fas fa-info-circle"></i></div>
        <div class="alert-content">
          <h4>System Update Completed</h4>
          <p>Security patch applied successfully to compliance engine.</p>
          <span class="alert-meta">Oct 20, 2025 • Medium • System</span>
        </div>
        <div class="alert-actions">
          <button class="action-btn mark-unread"><i class="fas fa-envelope"></i></button>
          <button class="action-btn view-detail"><i class="fas fa-eye"></i></button>
        </div>
      </div>

      <div class="alert-item info read">
        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
        <div class="alert-content">
          <h4>Nudge Response Received</h4>
          <p>TP-07219 acknowledged compliance nudge and updated filing.</p>
          <span class="alert-meta">Oct 19, 2025 • Info • Nudge Response</span>
        </div>
        <div class="alert-actions">
          <button class="action-btn mark-unread"><i class="fas fa-envelope"></i></button>
          <button class="action-btn view-detail"><i class="fas fa-eye"></i></button>
        </div>
      </div>
    </div> <!-- end .alerts-list -->
  </div> <!-- end .alerts-feed -->

</main>
</div> <!-- end .main-content -->

<!-- Alert Detail Slide-out Panel ->
<div class="alert-detail-panel" id="alertDetailPanel">
  <div class="detail-overlay" id="detailOverlay"></div>
  <div class="detail-content">
    <div class="detail-header">
      <h3 id="alertDetailTitle">Alert Details</h3>
      <button class="close-btn" id="closeDetail"><i class="fas fa-times"></i></button>
    </div>
    <div class="detail-body" id="alertDetailBody">
      <p><strong>Type:</strong> <span id="alertType">-</span></p>
      <p><strong>Severity:</strong> <span id="alertSeverity">-</span></p>
      <p><strong>Date:</strong> <span id="alertDate">-</span></p>
      <p><strong>Description:</strong></p>
      <p id="alertDescription">Select an alert to view details.</p>
    </div>
    <div class="detail-footer">
      <button class="btn-primary" id="markActioned"><i class="fas fa-check"></i> Mark Actioned</button>
      <button class="btn-secondary" id="closeDetailBtn"><i class="fas fa-times"></i> Close</button>
    </div>
  </div>
</div>

<script src="assets/script.js"></script>
<script src="assets/alerts.js"></script>
<script>
  // Simple clock
  function updateClock() {
    const el = document.getElementById('current-time');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleString();
  }
  updateClock();
  setInterval(updateClock, 60000);
</script>

<!-- Alert 1: Critical - Fraud Detection ->
<div class="alert-card critical unread" data-id="alert-001">
  <div class="alert-indicator"></div>
  <div class="alert-icon"><i class="fas fa-shield-alt"></i></div>
  <div class="alert-content">
    <div class="alert-header">
      <span class="alert-badge critical">Critical</span>
      <span class="alert-type">Fraud Detection</span>
      <span class="alert-time">2 minutes ago</span>
    </div>
    <h4 class="alert-title">Potential Fraud Pattern Detected</h4>
    <p class="alert-description">
      Taxpayer <strong>TP-09876</strong> (Manufacturing Corp) shows unusual transaction patterns. 
      Multiple invoices with identical amounts to different vendors flagged by AI.
    </p>
    <div class="alert-meta">
      <span class="meta-item"><i class="fas fa-user"></i> TP-09876</span>
      <span class="meta-item"><i class="fas fa-dollar-sign"></i> ZMW 2.4M affected</span>
      <span class="meta-item"><i class="fas fa-flag"></i> Auto-flagged</span>
    </div>
    <div class="alert-actions">
      <button class="alert-btn primary"><i class="fas fa-eye"></i> Investigate</button>
      <button class="alert-btn secondary"><i class="fas fa-user-plus"></i> Assign Officer</button>
      <button class="alert-btn secondary"><i class="fas fa-times"></i> Dismiss</button>
    </div>
  </div>
  <button class="mark-read-btn" title="Mark as read"><i class="fas fa-check"></i></button>
</div>

<!-- Alert 2: High - Compliance Warning ->
<div class="alert-card high unread" data-id="alert-002">
  <div class="alert-indicator"></div>
  <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
  <div class="alert-content">
    <div class="alert-header">
      <span class="alert-badge high">High</span>
      <span class="alert-type">Compliance</span>
      <span class="alert-time">15 minutes ago</span>
    </div>
    <h4 class="alert-title">Multiple Late Filings Detected</h4>
    <p class="alert-description">
      <strong>15 taxpayers</strong> in the Retail sector missed the Q3 filing deadline. 
      Bulk nudge campaign recommended to improve compliance.
    </p>
    <div class="alert-meta">
      <span class="meta-item"><i class="fas fa-building"></i> Retail Sector</span>
      <span class="meta-item"><i class="fas fa-calendar"></i> Q3 Deadline</span>
      <span class="meta-item"><i class="fas fa-users"></i> 15 taxpayers</span>
    </div>
    <div class="alert-actions">
      <button class="alert-btn primary"><i class="fas fa-paper-plane"></i> Send Bulk Nudges</button>
      <button class="alert-btn secondary"><i class="fas fa-list"></i> View List</button>
      <button class="alert-btn secondary"><i class="fas fa-times"></i> Dismiss</button>
    </div>
  </div>
  <button class="mark-read-btn" title="Mark as read"><i class="fas fa-check"></i></button>
</div>

<!-- Alert 3: High - Risk Score Increase ->
<div class="alert-card high unread" data-id="alert-003">
  <div class="alert-indicator"></div>
  <div class="alert-icon"><i class="fas fa-chart-line"></i></div>
  <div class="alert-content">
    <div class="alert-header">
      <span class="alert-badge high">High</span>
      <span class="alert-type">Risk Assessment</span>
      <span class="alert-time">1 hour ago</span>
    </div>
    <h4 class="alert-title">Risk Score Spike Detected</h4>
    <p class="alert-description">
      Taxpayer <strong>TP-09432</strong> (Logistics Inc.) risk score increased from 0.65 to 0.82. 
      Factors: late payment, revenue drop >30%, inconsistent reporting.
    </p>
    <div class="alert-meta">
      <span class="meta-item"><i class="fas fa-user"></i> TP-09432</span>
      <span class="meta-item"><i class="fas fa-arrow-up"></i> +0.17 increase</span>
      <span class="meta-item"><i class="fas fa-exclamation"></i> Now Critical</span>
    </div>
    <div class="alert-actions">
      <button class="alert-btn primary"><i class="fas fa-eye"></i> View Profile</button>
      <button class="alert-btn secondary"><i class="fas fa-bell"></i> Send Nudge</button>
      <button class="alert-btn secondary"><i class="fas fa-times"></i> Dismiss</button>
    </div>
  </div>
  <button class="mark-read-btn" title="Mark as read"><i class="fas fa-check"></i></button>
</div>

<!-- Alert 4: Medium - System Notification ->
<div class="alert-card medium read" data-id="alert-004">
  <div class="alert-indicator"></div>
  <div class="alert-icon"><i class="fas fa-server"></i></div>
  <div class="alert-content">
    <div class="alert-header">
      <span class="alert-badge medium">Medium</span>
      <span class="alert-type">System</span>
      <span class="alert-time">2 hours ago</span>
    </div>
    <h4 class="alert-title">AI Model Update Completed</h4>
    <p class="alert-description">
      Risk assessment model updated with new data. Performance metrics improved: 
      accuracy +2.3%, false positive rate -1.8%.
    </p>
    <div class="alert-meta">
      <span class="meta-item"><i class="fas fa-robot"></i> AI System</span>
      <span class="meta-item"><i class="fas fa-check-circle"></i> Success</span>
      <span class="meta-item"><i class="fas fa-chart-line"></i> Performance +2.3%</span>
    </div>
    <div class="alert-actions">
      <button class="alert-btn secondary"><i class="fas fa-file-alt"></i> View Report</button>
      <button class="alert-btn secondary"><i class="fas fa-times"></i> Dismiss</button>
    </div>
  </div>
  <button class="mark-read-btn" title="Mark as read"><i class="fas fa-check"></i></button>
</div>

<!-- Alert 5: Info - Nudge Response ->
<div class="alert-card info read" data-id="alert-005">
  <div class="alert-indicator"></div>
  <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
  <div class="alert-content">
    <div class="alert-header">
      <span class="alert-badge info">Info</span>
      <span class="alert-type">Nudge Response</span>
      <span class="alert-time">3 hours ago</span>
    </div>
    <h4 class="alert-title">Positive Nudge Response</h4>
    <p class="alert-description">
      Taxpayer <strong>TP-10234</strong> (John Mwanza) filed tax return on time after receiving 
      reminder nudge. Compliance score improved from 82 to 85.
    </p>
    <div class="alert-meta">
      <span class="meta-item"><i class="fas fa-user"></i> TP-10234</span>
      <span class="meta-item"><i class="fas fa-arrow-up"></i> Score +3</span>
      <span class="meta-item"><i class="fas fa-thumbs-up"></i> Success</span>
    </div>
    <div class="alert-actions">
      <button class="alert-btn secondary"><i class="fas fa-eye"></i> View Profile</button>
      <button class="alert-btn secondary"><i class="fas fa-times"></i> Dismiss</button>
    </div>
  </div>
  <button class="mark-read-btn" title="Mark as read"><i class="fas fa-check"></i></button>
</div>

<!-- Alert 6: Info - System Success --
<div class="alert-card info read" data-id="alert-006">
  <div class="alert-indicator"></div>
  <div class="alert-icon"><i class="fas fa-paper-plane"></i></div>
  <div class="alert-content">
    <div class="alert-header">
      <span class="alert-badge info">Info</span>
      <span class="alert-type">System</span>
      <span class="alert-time">5 hours ago</span>
    </div>
    <h4 class="alert-title">Bulk Nudge Campaign Completed</h4>
    <p class="alert-description">
      Successfully sent 450 compliance reminders to Manufacturing sector taxpayers. 
      Delivery rate: 98.7%, estimated response time: 48–72 hours.
    </p>
    <div class="alert-meta">
      <span class="meta-item"><i class="fas fa-building"></i> Manufacturing</span>
      <span class="meta-item"><i class="fas fa-envelope"></i> 450 sent</span>
      <span class="meta-item"><i class="fas fa-percentage"></i> 98.7% delivered</span>
    </div>
    <div class="alert-actions">
      <button class="alert-btn secondary"><i class="fas fa-chart-bar"></i> View Campaign</button>
      <button class="alert-btn secondary"><i class="fas fa-times"></i> Dismiss</button>
    </div>
  </div>
  <button class="mark-read-btn" title="Mark as read"><i class="fas fa-check"></i></button>
</div>

<!-- Alert 7: Critical - System Alert ->
<div class="alert-card critical unread" data-id="alert-007">
  <div class="alert-indicator"></div>
  <div class="alert-icon"><i class="fas fa-database"></i></div>
  <div class="alert-content">
    <div class="alert-header">
      <span class="alert-badge critical">Critical</span>
      <span class="alert-type">System</span>
      <span class="alert-time">6 hours ago</span>
    </div>
    <h4 class="alert-title">Data Anomaly Detected</h4>
    <p class="alert-description">
      Unusual data pattern in Agriculture sector filings. 23 taxpayers reported identical 
      revenue figures. Possible data entry error or coordinated reporting.
    </p>
    <div class="alert-meta">
      <span class="meta-item"><i class="fas fa-seedling"></i> Agriculture</span>
      <span class="meta-item"><i class="fas fa-users"></i> 23 taxpayers</span>
      <span class="meta-item"><i class="fas fa-exclamation"></i> Investigation needed</span>
    </div>
    <div class="alert-actions">
      <button class="alert-btn primary"><i class="fas fa-search"></i> Investigate</button>
      <button class="alert-btn secondary"><i class="fas fa-file-export"></i> Export Data</button>
      <button class="alert-btn secondary"><i class="fas fa-times"></i> Dismiss</button>
    </div>
  </div>
  <button class="mark-read-btn" title="Mark as read"><i class="fas fa-check"></i></button>
</div>

</div> <!-- end .alerts-list -->

<!-- Empty State (hidden by default) ->
<div class="empty-state" id="emptyState" style="display: none;">
  <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
  <h3>No Alerts Found</h3>
  <p>All caught up! No alerts match your current filters.</p>
  <button class="btn-primary" onclick="document.getElementById('resetAlertFilters').click()">
    Reset Filters
  </button>
</div>

<!-- Load More ->
<div class="load-more-section">
  <button class="btn-secondary" id="loadMore">
    <i class="fas fa-chevron-down"></i> Load More Alerts
  </button>
</div>
</div> <!-- end .alerts-feed ->
</main>
</div> <!-- end .main-content --

<script src="script.js"></script>
<script src="alerts.js"></script>
<script>
  // Simple clock
  function updateClock() {
    const el = document.getElementById('current-time');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleString();
  }
  updateClock();
  setInterval(updateClock, 60000);
</script>
</body>
</html-->
