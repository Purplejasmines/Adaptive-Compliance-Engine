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

// Filters
$riskLevel = $_GET['risk']   ?? 'all';
$sector    = $_GET['sector'] ?? 'all';
$days      = (int)($_GET['days'] ?? 30);
$status    = $_GET['status']  ?? 'all';

$startDate = date('Y-m-d', strtotime("-{$days} days"));
$endDate   = date('Y-m-d');

$where = [];
$params = [':start' => $startDate, ':end' => $endDate];

if ($riskLevel !== 'all') { $where[] = "LOWER(a.RiskLevel)=:risk"; $params[':risk']=strtolower($riskLevel); }
if ($sector    !== 'all') { $where[] = "LOWER(a.Sector)=:sector"; $params[':sector']=strtolower($sector); }
if ($status    !== 'all') { $where[] = "LOWER(a.Status)=:status"; $params[':status']=strtolower($status); }

$whereSql = $where ? (' AND '.implode(' AND ',$where)) : '';

$sql = "
    SELECT a.AuditID,
           COALESCE(CONCAT(i.FirstName,' ',i.LastName), bb.BusinessName,'Unknown') AS TaxpayerName,
           a.RiskLevel,a.RiskScore,a.Province,a.Sector,a.Status,a.StartDate
    FROM AuditCases a
    JOIN Taxpayers t ON a.TPIN=t.TPIN
    LEFT JOIN Individuals i ON t.TPIN=i.TPIN
    LEFT JOIN biz_businesses bb ON t.TPIN=bb.TPIN
    WHERE a.StartDate BETWEEN :start AND :end
    $whereSql
    ORDER BY a.StartDate DESC
    LIMIT 200
";
$assessments=[];
try {
  $stmt=$pdo->prepare($sql);
  $stmt->execute($params);
  $assessments=$stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){ $assessments=[]; }

function risk_chip_class($level){
  $l=strtolower($level??'');
  if($l==='critical'||$l==='high') return 'prob-chip';
  return 'risk-chip';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Risk Assessment</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="risk.css">
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
    <a href="risk.php" class="nav-item active"><i class="fas fa-exclamation-triangle"></i><span>Risk Assessments</span></a>
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

<!-- Main -->
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

<main class="content">

  <!-- Filters Bar -->
  <div class="filters-bar">
    <div class="filter-group">
      <label>Risk Level</label>
      <select id="riskLevelFilter" class="filter-select">
        <option value="all">All Levels</option>
        <option value="critical">Critical</option>
        <option value="high">High</option>
        <option value="medium">Medium</option>
        <option value="low">Low</option>
      </select>
    </div>

    <div class="filter-group">
      <label>Sector</label>
      <select id="sectorFilter" class="filter-select">
        <option value="all">All Sectors</option>
        <option value="retail">Retail</option>
        <option value="manufacturing">Manufacturing</option>
        <option value="services">Services</option>
        <option value="agriculture">Agriculture</option>
      </select>
    </div>

    <div class="filter-group">
      <label>Time Period</label>
      <select id="timeFilter" class="filter-select">
        <option value="7">Last 7 days</option>
        <option value="30" selected>Last 30 days</option>
        <option value="90">Last 90 days</option>
        <option value="365">Last year</option>
      </select>
    </div>

    <div class="filter-group">
      <label>Status</label>
      <select id="statusFilter" class="filter-select">
        <option value="all">All Status</option>
        <option value="pending">Pending Review</option>
        <option value="nudged">Nudge Sent</option>
        <option value="responded">Responded</option>
        <option value="audit">Audit Scheduled</option>
      </select>
    </div>

    <button class="btn-filter-reset" id="resetFilters">
      <i class="fas fa-redo"></i> Reset
    </button>
  </div>

  <!-- Main Dashboard Grid -->
  <div class="risk-dashboard-grid">
    <!-- LEFT COLUMN -->
    <div class="main-risk-content">
      <!-- Risk Distribution Chart -->
      <div class="chart-card">
        <div class="card-header">
          <h3>Risk Distribution Overview</h3>
          <div class="card-actions"><button class="icon-btn"><i class="fas fa-sync-alt"></i></button></div>
        </div>
        <div class="chart-wrap chart-medium"><canvas id="riskDistributionChart"></canvas></div>
      </div>

      <!-- Risk Queue Table -->
      <div class="table-card">
        <div class="card-header">
          <h3>High-Risk Taxpayer Queue</h3>
          <div class="card-meta"><span class="count-badge"><?php echo count($assessments); ?> taxpayers</span></div>
        </div>
        <div class="table-wrapper">
          <table class="risk-table">
            <thead>
              <tr><th>ID</th><th>Name</th><th>Risk Score</th><th>Risk Level</th><th>Sector</th><th>Last Activity</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php if(!empty($assessments)): foreach($assessments as $row): ?>
                <tr>
                  <td><strong><?php echo 'TP-'.$row['AuditID']; ?></strong></td>
                  <td><?php echo htmlspecialchars($row['TaxpayerName']); ?></td>
                  <td><?php echo htmlspecialchars($row['RiskScore']); ?></td>
                  <td><span class="<?php echo risk_chip_class($row['RiskLevel']); ?>"><?php echo htmlspecialchars($row['RiskLevel']); ?></span></td>
                  <td><?php echo htmlspecialchars($row['Sector']); ?></td>
                  <td><?php echo htmlspecialchars($row['StartDate']); ?></td>
                  <td><span class="status-badge"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                  <td>
                    <button class="action-btn view-btn"><i class="fas fa-eye"></i></button>
                    <button class="action-btn nudge-btn"><i class="fas fa-bell"></i></button>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="8">No records found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <!-- RIGHT PANEL -->
    <aside class="risk-side-panel">
      
      <!-- Quick Actions -->
      <div class="action-card">
        <h4>Quick Actions</h4>
        <div class="action-buttons">
          <button class="action-card-btn primary">
            <i class="fas fa-paper-plane"></i>
            <span>Send Bulk Nudges</span>
          </button>
          <button class="action-card-btn secondary">
            <i class="fas fa-download"></i>
            <span>Export High-Risk List</span>
          </button>
        </div>
      </div>

      <!-- Risk Trend Mini Chart -->
      <div class="stat-card">
        <h4>Risk Trend (7 Days)</h4>
        <div class="chart-wrap chart-small">
          <canvas id="riskTrendMiniChart"></canvas>
        </div>
      </div>

      <!-- Recent Interventions -->
      <div class="stat-card">
        <h4>Recent Interventions</h4>
        <div class="intervention-list">
          <div class="intervention-item">
            <div class="intervention-icon success"><i class="fas fa-check"></i></div>
            <div class="intervention-info">
              <p class="intervention-title">3 Nudges sent today</p>
              <p class="intervention-meta">All delivered successfully</p>
            </div>
          </div>
          <div class="intervention-item">
            <div class="intervention-icon success"><i class="fas fa-chart-line"></i></div>
            <div class="intervention-info">
              <p class="intervention-title">2 Compliance improved</p>
              <p class="intervention-meta">From nudges sent last week</p>
            </div>
          </div>
          <div class="intervention-item">
            <div class="intervention-icon warning"><i class="fas fa-calendar-alt"></i></div>
            <div class="intervention-info">
              <p class="intervention-title">1 Audit scheduled</p>
              <p class="intervention-meta">High-risk case (TP-09435)</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Risk Level Summary -->
      <div class="stat-card">
        <h4>Risk Summary</h4>
        <div class="risk-summary-list">
          <div class="risk-summary-item">
            <span class="risk-dot critical"></span>
            <span class="risk-label">Critical</span>
            <span class="risk-count">27</span>
          </div>
          <div class="risk-summary-item">
            <span class="risk-dot high"></span>
            <span class="risk-label">High</span>
            <span class="risk-count">152</span>
          </div>
          <div class="risk-summary-item">
            <span class="risk-dot medium"></span>
            <span class="risk-label">Medium</span>
            <span class="risk-count">840</span>
          </div>
          <div class="risk-summary-item">
            <span class="risk-dot low"></span>
            <span class="risk-label">Low</span>
            <span class="risk-count">1,520</span>
          </div>
        </div>
      </div>
    </aside>
  </div> <!-- end .risk-dashboard-grid -->
</main>

<!-- Slide-out Detail Panel -->
<div class="detail-panel" id="detailPanel">
  <div class="detail-panel-overlay" id="detailPanelOverlay"></div>
  <div class="detail-panel-content">
    <div class="detail-header">
      <h3 id="detailTaxpayerName">Loading...</h3>
      <button class="close-btn" id="closeDetailPanel"><i class="fas fa-times"></i></button>
    </div>

    <div class="detail-body" id="detailBody">
      <!-- Taxpayer Info -->
      <div class="detail-section">
        <h4>Taxpayer Information</h4>
        <div class="info-grid">
          <div class="info-item"><span class="info-label">Taxpayer ID</span><span class="info-value" id="detailID">-</span></div>
          <div class="info-item"><span class="info-label">Sector</span><span class="info-value" id="detailSector">-</span></div>
          <div class="info-item"><span class="info-label">Last Filing</span><span class="info-value" id="detailLastFiling">-</span></div>
          <div class="info-item"><span class="info-label">Registration Date</span><span class="info-value" id="detailRegDate">-</span></div>
        </div>
      </div>

      <!-- Risk Score Breakdown -->
      <div class="detail-section">
        <h4>Risk Score Breakdown</h4>
        <div class="risk-breakdown">
          <div class="risk-score-large">
            <div class="score-circle"><span class="score-value" id="detailRiskScore">0.82</span></div>
            <div class="confidence-badge"><i class="fas fa-check-circle"></i> 87% Confidence</div>
          </div>
          <div class="risk-factors">
            <h5>Contributing Factors</h5>
            <div class="factor-item"><div class="factor-header"><i class="fas fa-exclamation-triangle"></i><span>Late filing pattern</span></div><div class="factor-impact">+0.25</div></div>
            <div class="factor-item"><div class="factor-header"><i class="fas fa-chart-line"></i><span>Revenue drop (>30%)</span></div><div class="factor-impact">+0.20</div></div>
            <div class="factor-item"><div class="factor-header"><i class="fas fa-redo"></i><span>Inconsistent reporting</span></div><div class="factor-impact">+0.15</div></div>
            <div class="factor-item"><div class="factor-header"><i class="fas fa-building"></i><span>High-risk sector</span></div><div class="factor-impact">+0.12</div></div>
            <div class="factor-item"><div class="factor-header"><i class="fas fa-clock"></i><span>Overdue payment</span></div><div class="factor-impact">+0.10</div></div>
          </div>
        </div>
      </div>

      <!-- Recommended Actions -->
      <div class="detail-section">
        <h4>Recommended Actions</h4>
        <div class="action-recommendations">
          <div class="recommendation-card primary-action">
            <div class="rec-icon"><i class="fas fa-bell"></i></div>
            <div class="rec-content">
              <h5>Send Compliance Nudge</h5>
              <p>AI recommends a personalized reminder about upcoming deadlines</p>
              <button class="rec-btn" data-action="nudge">Send Nudge</button>
            </div>
          </div>
          <div class="recommendation-card">
            <div class="rec-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="rec-content">
              <h5>Schedule Audit</h5>
              <p>If no response within 7 days, schedule formal audit</p>
              <button class="rec-btn secondary">Schedule</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Activity History -->
      <div class="detail-section">
        <h4>Recent Activity</h4>
        <div class="activity-timeline">
          <div class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content"><p class="timeline-title">Nudge sent</p><p class="timeline-meta">October 15, 2025 - No response yet</p></div></div>
          <div class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content"><p class="timeline-title">Risk score increased</p><p class="timeline-meta">October 10, 2025 - From 0.65 to 0.82</p></div></div>
          <div class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content"><p class="timeline-title">Late filing detected</p><p class="timeline-meta">October 5, 2025 - VAT return overdue</p></div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="script.js"></script>
<script src="risk.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
</body>
</html>
