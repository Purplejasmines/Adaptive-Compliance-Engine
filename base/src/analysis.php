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

// Quick stats (example queries)
$total = $pdo->query("SELECT COUNT(*) AS c FROM taxpayers")->fetch(PDO::FETCH_ASSOC)['c'];
$active = $pdo->query("SELECT COUNT(*) AS c FROM taxpayers WHERE status='Active'")->fetch(PDO::FETCH_ASSOC)['c'];
$dormant = $pdo->query("SELECT COUNT(*) AS c FROM taxpayers WHERE status='Dormant'")->fetch(PDO::FETCH_ASSOC)['c'];
$suspended = $pdo->query("SELECT COUNT(*) AS c FROM taxpayers WHERE status='Suspended'")->fetch(PDO::FETCH_ASSOC)['c'];
$complianceRate = $total ? round(($active/$total)*100,1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Adaptive Compliance Engine - Analytics</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="assets/analytics.css">
</head>
<body>

<!-- Sidebar Navigation -->
<div class="sidebar">
  <div class="sidebar-header">
    <h2 class="sidebar-title"><i class="fas fa-shield-alt"></i> ZRA Admin</h2>
    <p class="sidebar-subtitle">Compliance Engine</p>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Overview</span></a>
    <a href="analytics.php" class="nav-item active"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
    <a href="risk.php" class="nav-item"><i class="fas fa-exclamation-triangle"></i><span>Risk Assessments</span></a>
    <a href="taxpayers.php" class="nav-item"><i class="fas fa-users"></i><span>Taxpayers</span></a>
    <a href="alerts.php" class="nav-item"><i class="fas fa-bell"></i><span>Alerts</span></a>
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
      </div>
    </div>
  </div>
</div>

<!-- Header -->
<div class="main-content">
  <header>
    <div class="header-content">
      <div class="header-text">
        <h1>Admin Dashboard</h1>
        <p class="subtitle">Zambia Revenue Authority</p>
      </div>
      <div class="header-right">
        <div class="clock" id="current-time"></div>
        <div class="profile-badge">Officer: <?php echo $userName; ?></div>
      </div>
    </div>
  </header>

  <!-- Main content area -->
  <main class="content" id="Content">
    <div class="dashboard-grid">

      <!-- LEFT: Main Analytics Area -->
      <div class="main-analytics">
<!-- Widget Navigation -->
        <nav class="main-nav">
          <button class="nav-item active" data-widget="revenue-trends">Revenue Trends</button>
          <button class="nav-item" data-widget="compliance">Compliance</button>
          <button class="nav-item" data-widget="risk-distribution">Risk Distribution</button>
        </nav>

        <!-- Widget Container -->
        <div class="widget-container">
          <!-- Revenue Trends Widget -->
          <div class="widget active" data-widget="revenue-trends">
            <div class="chart-card">
              <h3>Revenue Trends Over Time</h3>
              <div class="chart-wrap">
                <canvas id="revenueTrendChart"></canvas>
              </div>
            </div>
            <div class="chart-card">
              <h3>Revenue by Sector</h3>
              <div class="chart-wrap small">
                <canvas id="revenueSectorChart"></canvas>
              </div>
            </div>
          </div>

          <!-- Compliance Widget -->
          <div class="widget" data-widget="compliance">
            <div class="chart-card">
              <h3>Compliance Rate Trends</h3>
              <div class="chart-wrap">
                <canvas id="complianceTrendChart"></canvas>
              </div>
            </div>
            <div class="table-section">
              <h3>Compliance Breakdown</h3>
              <table>
                <thead>
                  <tr>
                    <th>Category</th>
                    <th>Rate</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Tax Filing</td>
                    <td>87%</td>
                    <td>Stable</td>
                  </tr>
                  <tr>
                    <td>VAT Returns</td>
                    <td>82%</td>
                    <td>Improving</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
<!-- Risk Distribution Widget -->
          <div class="widget" data-widget="risk-distribution">
            <div class="chart-card">
              <h3>Risk Distribution</h3>
              <div class="chart-wrap">
                <canvas id="riskDistributionChart"></canvas>
              </div>
            </div>
            <div class="table-section">
              <h3>High Risk Taxpayers</h3>
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Risk Score</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>TP-09432</td>
                    <td>Logistics Inc.</td>
                    <td>0.82</td>
                  </tr>
                  <tr>
                    <td>TP-09433</td>
                    <td>Retail Corp.</td>
                    <td>0.65</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Side Panel -->
      <aside class="side-panel">
        <div class="summary-cards">
          <div class="card" data-target="revenue">
            <div class="icon-bg"><i class="fas fa-coins"></i></div>
            <div class="card-info">
              <h4>Revenue Compliance</h4>
              <p>$8.3M</p>
              <div class="muted">Target: ZMW 5.93B</div>
            </div>
          </div>

          <div class="card" data-target="nudges">
            <div class="icon-bg"><i class="fas fa-bell"></i></div>
            <div class="card-info">
              <h4>Nudges Sent</h4>
              <p>4,230</p>
              <div class="muted">Last 7 days</div>
            </div>
          </div>

          <div class="card" data-target="fraud">
            <div class="icon-bg"><i class="fas fa-shield-alt"></i></div>
            <div class="card-info">
              <h4>Fraud Alerts</h4>
              <p>27</p>
              <div class="muted">Active</div>
            </div>
          </div>

          <div class="card" data-target="risk">
            <div class="icon-bg"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="card-info">
              <h4>High Risk Taxpayers</h4>
              <p>152</p>
              <div class="muted">Review queue</div>
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
              <strong>85% <small style="color:var(--success)">▲ 3%</small></strong>
            </div>
          </div>
          <div class="quick-stat-card">
            <div class="icon-bg" style="background:linear-gradient(135deg,var(--error),#b52b27)">
              <i class="fas fa-bug"></i>
            </div>
            <div>
              <p class="muted">Fraud Detection</p>
              <strong>12% <small class="muted">View cases</small></strong>
            </div>
          </div>
        </div>
      </aside>
      
    </div>
  </main>
</div>

<script src="https://kit.fontawesome.com/a2e0e6a7b8.js" crossorigin="anonymous"></script>
<script src="assets/analytics.js"></script>
<script src="script.js"></script>
</body>
</html>
