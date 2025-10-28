<?php
session_start();
require_once '../src/db_connect.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header('Location: admin_login.php');
    exit();
}

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adaptive Compliance Engine</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../src/style.css">
</head>
<body>

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
    <a href="users.php" class="nav-item"><i class="fas fa-user-cog"></i><span>User Management</span></a>
    <a href="audit.php" class="nav-item"><i class="fas fa-history"></i><span>Audit Logs</span></a>
    <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
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

<!-- Main Content -->
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

  <!-- Date Filter -->
  <div class="date-filter">
    <label for="dateRange">Filter by Date:</label>
    <select id="dateRange" name="dateRange">
      <option value="today">Today</option>
      <option value="thisWeek">This Week</option>
      <option value="thisMonth">This Month</option>
      <option value="thisYear">This Year</option>
      <option value="custom">Custom Range</option>
    </select>
  </div>

  <main class="content">
    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="card">
        <div class="card-header"><div class="icon-bg"><i class="fas fa-money-bill-wave"></i></div><span class="trend">+14%</span></div>
        <h3>Revenue Impact (Year 1)</h3>
        <p class="value">ZMW 730M</p>
        <p class="target">Target: ZMW 5.93B annual</p>
        <p class="details-link">Click for details →</p>
      </div>
      <div class="card">
        <div class="card-header"><div class="icon-bg"><i class="fas fa-check-circle"></i></div><span class="trend">+20pts</span></div>
        <h3>Compliance Rate</h3>
        <p class="value">85%</p>
        <p class="target">Up from 65%</p>
        <p class="details-link">Click for details →</p>
      </div>
      <div class="card">
        <div class="card-header"><div class="icon-bg"><i class="fas fa-users"></i></div><span class="trend">+60pts</span></div>
        <h3>Population Reach</h3>
        <p class="value">95%</p>
        <p class="target">2M+ rural taxpayers</p>
        <p class="details-link">Click for details →</p>
      </div>
      <div class="card">
        <div class="card-header"><div class="icon-bg"><i class="fas fa-chart-line"></i></div><span class="trend">652x</span></div>
        <h3>Return on Investment</h3>
        <p class="value">$1.12M</p>
        <p class="target">Break-even: 2 weeks</p>
        <p class="details-link">Click for details →</p>
      </div>
    </div>

    <!-- Graphs Section -->
    <div class="graphs-section">
      <div class="graph-card">
        <h3>Revenue Trend (Last 6 Months)</h3>
        <div class="chart-container"><canvas id="revenueChart"></canvas></div>
      </div>
      <div class="graph-card">
        <h3>Risk Distribution</h3>
        <div class="calendar-header">
          <button class="nav-arrow" id="prevWeek">&lt;</button>
          <span id="weekRange">Oct 6 - Oct 12, 2025</span>
          <button class="nav-arrow" id="nextWeek">&gt;</button>
        </div>
        <div class="chart-container"><canvas id="riskChart"></canvas></div>
      </div>
    </div>

    <!-- Risk Assessments Table -->
     <?php
// Build date range based on filter selection (default: thisMonth)
$filter = $_GET['range'] ?? 'thisMonth';
$startDate = null;
$endDate = date('Y-m-d');

switch ($filter) {
    case 'today':
        $startDate = date('Y-m-d');
        break;
    case 'thisWeek':
        $startDate = date('Y-m-d', strtotime('monday this week'));
        break;
    case 'thisMonth':
        $startDate = date('Y-m-01');
        break;
    case 'thisYear':
        $startDate = date('Y-01-01');
        break;
    default:
        // custom: allow ?from=YYYY-MM-DD&to=YYYY-MM-DD
        $startDate = $_GET['from'] ?? date('Y-m-01');
        $endDate   = $_GET['to']   ?? date('Y-m-d');
        break;
}

// Query Risk Assessments (AuditCases) with taxpayer names
$assessments = [];
try {
    $sql = "
        SELECT 
            a.AuditID,
            a.AuditType AS RiskLevel,              -- map AuditType to Risk Level label
            a.RiskScore AS Score,                  -- if you store numeric score
            a.Province,                            -- optional; fallback handled below
            a.StartDate,
            t.TPIN,
            COALESCE(CONCAT(i.FirstName, ' ', i.LastName), bb.BusinessName, 'Unknown') AS TaxpayerName
        FROM AuditCases a
        JOIN Taxpayers t      ON a.TPIN = t.TPIN
        LEFT JOIN Individuals i ON t.TPIN = i.TPIN
        LEFT JOIN biz_businesses bb ON t.TPIN = bb.TPIN
        WHERE a.StartDate BETWEEN :start AND :end
        ORDER BY a.StartDate DESC
        LIMIT 100
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':start' => $startDate,
        ':end'   => $endDate
    ]);
    $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If your schema uses different column names (e.g., a.Score instead of a.RiskScore),
    // you can adjust here or log the error.
    $assessments = [];
}
?>
<div class="table-section">
  <h3>Risk Assessments</h3>

  <!-- Optional date filter controls that sync with the global filter -->
  <form method="get" class="table-filter" style="margin-bottom:10px; display:flex; gap:10px; align-items:center;">
    <label for="range">Range:</label>
    <select id="range" name="range">
      <option value="today"     <?php echo $filter==='today'?'selected':''; ?>>Today</option>
      <option value="thisWeek"  <?php echo $filter==='thisWeek'?'selected':''; ?>>This Week</option>
      <option value="thisMonth" <?php echo $filter==='thisMonth'?'selected':''; ?>>This Month</option>
      <option value="thisYear"  <?php echo $filter==='thisYear'?'selected':''; ?>>This Year</option>
      <option value="custom"    <?php echo ($filter!=='today' && $filter!=='thisWeek' && $filter!=='thisMonth' && $filter!=='thisYear')?'selected':''; ?>>Custom</option>
    </select>
    <input type="date" name="from" value="<?php echo htmlspecialchars($startDate); ?>">
    <input type="date" name="to"   value="<?php echo htmlspecialchars($endDate); ?>">
    <button class="action-btn" type="submit">Apply</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Taxpayer</th>
        <th>Risk Level</th>
        <th>Score</th>
        <th>Province</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($assessments)): ?>
        <?php foreach ($assessments as $row): ?>
          <?php
            $id        = 'AC-' . ($row['AuditID'] ?? 'N/A');
            $name      = $row['TaxpayerName'] ?? 'Unknown';
            $riskLevel = $row['RiskLevel']     ?? '—';
            $score     = $row['Score']         ?? '—';
            $province  = $row['Province']      ?? 'Zambia';
            $date      = !empty($row['StartDate']) ? date('M d, Y', strtotime($row['StartDate'])) : '—';
          ?>
          <tr>
            <td><?php echo htmlspecialchars($id); ?></td>
            <td><?php echo htmlspecialchars($name); ?></td>
            <td><?php echo htmlspecialchars($riskLevel); ?></td>
            <td><?php echo htmlspecialchars($score); ?></td>
            <td><?php echo htmlspecialchars($province); ?></td>
            <td><?php echo htmlspecialchars($date); ?></td>
            <td>
              <button class="action-btn" onclick="location.href='assessment_view.php?id=<?php echo urlencode($row['AuditID']); ?>'">View</button>
              <button class="action-btn" onclick="location.href='assessment_edit.php?id=<?php echo urlencode($row['AuditID']); ?>'">Edit</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7">No risk assessments found for the selected date range.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
  </main>
</div>


<!-- Nudge Management Panel -->

<!-- 🔘 Nudge Toggle Button -->
<button id="toggleNudgePanel" class="nudge-toggle">🚨 Compliance Alerts</button>

<!-- 🧩 Nudge Management Drawer -->
<div class="nudge-panel hidden">
  <div class="nudge-header">
    <h3>Compliance Alerts</h3>
    <button id="closeNudgePanel" class="close-btn">✖</button>
  </div>

  <!-- Active Alerts Section -->
  <div class="nudge-alerts" id="nudgeAlerts">
    <!-- Alerts will be dynamically loaded here -->
  </div>

  <!-- Nudge Editor -->
  <div class="nudge-editor hidden" id="nudgeEditor">
    <div class="nudge-recipient-banner" id="nudgeRecipientBanner"></div>

    <label for="nudgeTemplates">Choose Template:</label>
    <select id="nudgeTemplates">
      <option value="">-- Select a Template --</option>
      <option value="Please review your latest filing for discrepancies.">Review Reminder</option>
      <option value="Your compliance score is below threshold. Schedule a review.">Low Compliance Alert</option>
      <option value="Immediate action required. Contact your compliance officer.">Urgent Follow-Up</option>
    </select>

    <textarea id="nudgeMessage" placeholder="Add a short note or edit the message..."></textarea>

    <div class="nudge-preview">
      <h4>Preview:</h4>
      <div id="nudgePreviewBox" class="preview-box">Your message will appear here...</div>
    </div>

    <button id="sendNudge">Send Nudge</button>
  </div>

  <!-- Log Section -->
  <ul id="nudgeLog" class="nudge-log">
    <li><strong>Oct 11, 2025:</strong> Sent to Officer Sarah Mwanza — “Review your filing”</li>
  </ul>
</div>


        </main>



    <script src="script.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
</body>

</html>


