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

// Quick stats
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
  <title>Adaptive Compliance Engine</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="taxpayer.css">
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
    <a href="taxpayers.php" class="nav-item active"><i class="fas fa-users"></i><span>Taxpayers</span></a>
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


      <!-- Search and Filters -->
  <div class="search-filter-section">
    <div class="search-bar-wrapper">
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input 
          type="text" 
          id="taxpayerSearch" 
          placeholder="Search by ID, Name, TPIN, or Business..."
        >
        <button class="clear-search" id="clearSearch" style="display: none;">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <button class="btn-icon" id="advancedFiltersToggle" title="Advanced Filters">
        <i class="fas fa-filter"></i>
      </button>
    </div>

    <!-- Advanced Filters (Initially Hidden) -->
    <div class="advanced-filters" id="advancedFilters" style="display: none;">
      <div class="filter-row">
        <div class="filter-group">
          <label>Type</label>
          <select id="typeFilter" class="filter-select">
            <option value="all">All Types</option>
            <option value="business">Business</option>
            <option value="individual">Individual</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Status</label>
          <select id="statusFilter" class="filter-select">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="dormant">Dormant</option>
            <option value="suspended">Suspended</option>
            <option value="pending">Pending</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Risk Level</label>
          <select id="riskFilter" class="filter-select">
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
            <option value="technology">Technology</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Compliance</label>
          <select id="complianceFilter" class="filter-select">
            <option value="all">All Scores</option>
            <option value="excellent">Excellent (90+)</option>
            <option value="good">Good (70-89)</option>
            <option value="fair">Fair (50-69)</option>
            <option value="poor">Poor (&lt;50)</option>
          </select>
        </div>
        <button class="btn-filter-reset" id="resetFilters">
          <i class="fas fa-redo"></i> Reset All
        </button>
      </div>
    </div>
  </div>


  <!-- Quick Stats -->
  <div class="quick-stats-grid">

      
    <div class="stat-box"><div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--brand-dark))">
        <i class="fas fa-users"></i>
      </div>
      <div class="stat-info"><p>Total Taxpayers</p><h3><?php echo number_format($total); ?></h3></div>
    </div>
    <div class="stat-box"><div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #1f8b3e)">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-info"><p>Active</p><h3><?php echo number_format($active); ?></h3><p><?php echo $complianceRate; ?>%</p></div>
    </div>
    <div class="stat-box">      <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d39e2a)">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="stat-info"><p>Dormant</p><h3><?php echo number_format($dormant); ?></h3></div>
    </div>
    <div class="stat-box"><div class="stat-icon" style="background: linear-gradient(135deg, var(--error), #b52b27)">
        <i class="fas fa-flag"></i>
      </div>
      <div class="stat-info"><p>Suspended</p><h3><?php echo number_format($suspended); ?></h3></div>
    </div>
  </div>

  <!-- Bulk Actions -->
  <div class="bulk-actions-bar" id="bulkActionsBar" style="display:none;">
    <!-- same markup -->
  </div>





  
  <!-- Taxpayers Table -->
  <div class="table-card">
    <div class="card-header"><h3>Taxpayer Directory ~ With Database Stored Details</h3></div>
    <div class="table-wrapper">
      <table class="taxpayers-table">
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>TPIN</th>
            <th>Email / Phone</th>
            <th>Type</th>
            <th>Registered</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $stmt = $pdo->query("SELECT tpin, taxpayertype, registrationdate, status, primaryemail, primaryphone FROM taxpayers LIMIT 20");
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
              <td><input type="checkbox"></td>
              <td><?php echo htmlspecialchars($row['tpin']); ?></td>
              <td><?php echo htmlspecialchars($row['primaryemail']); ?><br><small><?php echo htmlspecialchars($row['primaryphone']); ?></small></td>
              <td><?php echo ucfirst($row['taxpayertype']); ?></td>
              <td><?php echo htmlspecialchars($row['registrationdate']); ?></td>
              <td><?php echo ucfirst($row['status']); ?></td>
              <td>
                <button><i class="fas fa-eye"></i></button>
                <button><i class="fas fa-bell"></i></button>
                <button><i class="fas fa-ellipsis-v"></i></button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

          </div>


<div class="table-card">
    <div class="card-header"><h3>Taxpayer Directory ~ With Data for Visual Explanation</h3></div>
    <div class="table-wrapper">








<!------------------------------------------------------------------------------------------------------------------>

  <!-- Quick Stats -->
  <div class="quick-stats-grid">
    <div class="stat-box">
      <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--brand-dark))">
        <i class="fas fa-users"></i>
      </div>
      <div class="stat-info">
        <p class="stat-label">Total Taxpayers</p>
        <h3 class="stat-value">2,539</h3>
        <p class="stat-change positive">+12 this week</p>
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #1f8b3e)">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-info">
        <p class="stat-label">Compliant</p>
        <h3 class="stat-value">2,340</h3>
        <p class="stat-meta">92.2%</p>
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d39e2a)">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="stat-info">
        <p class="stat-label">At Risk</p>
        <h3 class="stat-value">152</h3>
        <p class="stat-meta">6.0%</p>
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-icon" style="background: linear-gradient(135deg, var(--error), #b52b27)">
        <i class="fas fa-flag"></i>
      </div>
      <div class="stat-info">
        <p class="stat-label">Flagged</p>
        <h3 class="stat-value">27</h3>
        <p class="stat-meta">1.1%</p>
      </div>
    </div>
  </div>




<!-- Detail Slide-out Panel -->
<div class="detail-panel" id="taxpayerDetailPanel">
  <div class="detail-panel-overlay" id="taxpayerDetailOverlay"></div>
  <div class="detail-panel-content">
    <div class="detail-header">
      <h3 id="taxpayerDetailName">Loading...</h3>
      <button class="close-btn" id="closeTaxpayerDetail">
        <i class="fas fa-times"></i>
      </button>
    </div>
    </div>
    </div>
    </div>



      <!--div class="table-card">
    <div class="card-header"><h3>Taxpayer Directory ~ With Data for Visual Expression</h3></div>
    <div class="table-wrapper"-->

      <div class="table-wrapper">
      <table class="taxpayers-table">
        <thead>
          <tr>
            <th style="width: 40px;">
              <input type="checkbox" id="selectAll">
            </th>
            <th class="sortable" data-sort="id">
              ID <i class="fas fa-sort"></i>
            </th>
            <th class="sortable" data-sort="name">
              Name/Business <i class="fas fa-sort"></i>
            </th>
            <th class="sortable" data-sort="type">
              Type <i class="fas fa-sort"></i>
            </th>
            <th>TPIN</th>
            <th class="sortable" data-sort="sector">
              Sector <i class="fas fa-sort"></i>
            </th>
            <th class="sortable" data-sort="compliance">
              Compliance <i class="fas fa-sort"></i>
            </th>
            <th class="sortable" data-sort="risk">
              Risk <i class="fas fa-sort"></i>
            </th>
            <th class="sortable" data-sort="lastFiling">
              Last Filing <i class="fas fa-sort"></i>
            </th>
            <th>Status</th>
            <th style="width: 120px;">Actions</th>
          </tr>
        </thead>
        <tbody id="taxpayersTableBody">
          <!-- Row 1: Business - Critical Risk -->
          <tr class="taxpayer-row" data-id="TP-09432" data-type="business">
            <td><input type="checkbox" class="row-checkbox"></td>
            <td><strong>TP-09432</strong></td>
            <td>
              <div class="taxpayer-cell">
                <div class="taxpayer-avatar business">
                  <i class="fas fa-industry"></i>
                </div>
                <div class="taxpayer-name">
                  <span class="name">Logistics Inc.</span>
                  <span class="meta">Manufacturing</span>
                </div>
              </div>
            </td>
            <td><span class="type-badge business">Business</span></td>
            <td>1234567890</td>
            <td>Manufacturing</td>
            <td>
              <div class="compliance-cell">
                <div class="compliance-bar">
                  <div class="compliance-fill" style="width: 73%; background: #FFC107;"></div>
                </div>
                <span class="compliance-score">73</span>
              </div>
            </td>
            <td><span class="risk-chip critical">Critical</span></td>
            <td>
              <div class="date-cell">
                <span class="date">Sept 15, 2025</span>
                <span class="relative">5 days ago</span>
              </div>
            </td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <div class="action-btns">
                <button class="action-btn view-btn" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="action-btn nudge-btn" title="Send Nudge">
                  <i class="fas fa-bell"></i>
                </button>
                <button class="action-btn more-btn" title="More">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Row 2: Individual - Medium Risk -->
          <tr class="taxpayer-row" data-id="TP-10234" data-type="individual">
            <td><input type="checkbox" class="row-checkbox"></td>
            <td><strong>TP-10234</strong></td>
            <td>
              <div class="taxpayer-cell">
                <div class="taxpayer-avatar individual">
                  <span>JM</span>
                </div>
                <div class="taxpayer-name">
                  <span class="name">John Mwanza</span>
                  <span class="meta">Self-Employed</span>
                </div>
              </div>
            </td>
            <td><span class="type-badge individual">Individual</span></td>
            <td>9876543210</td>
            <td>Services</td>
            <td>
              <div class="compliance-cell">
                <div class="compliance-bar">
                  <div class="compliance-fill" style="width: 85%; background: #28A745;"></div>
                </div>
                <span class="compliance-score">85</span>
              </div>
            </td>
            <td><span class="risk-chip medium">Medium</span></td>
            <td>
              <div class="date-cell">
                <span class="date">Sept 18, 2025</span>
                <span class="relative">2 days ago</span>
              </div>
            </td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <div class="action-btns">
                <button class="action-btn view-btn" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="action-btn nudge-btn" title="Send Nudge">
                  <i class="fas fa-bell"></i>
                </button>
                <button class="action-btn more-btn" title="More">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Row 3: Business - High Risk -->
          <tr class="taxpayer-row" data-id="TP-09433" data-type="business">
            <td><input type="checkbox" class="row-checkbox"></td>
            <td><strong>TP-09433</strong></td>
            <td>
              <div class="taxpayer-cell">
                <div class="taxpayer-avatar business">
                  <i class="fas fa-store"></i>
                </div>
                <div class="taxpayer-name">
                  <span class="name">Retail Corp.</span>
                  <span class="meta">Retail Trade</span>
                </div>
              </div>
            </td>
            <td><span class="type-badge business">Business</span></td>
            <td>1112223330</td>
            <td>Retail</td>
            <td>
              <div class="compliance-cell">
                <div class="compliance-bar">
                  <div class="compliance-fill" style="width: 65%; background: #FFC107;"></div>
                </div>
                <span class="compliance-score">65</span>
              </div>
            </td>
            <td><span class="risk-chip high">High</span></td>
            <td>
              <div class="date-cell">
                <span class="date">Sept 10, 2025</span>
                <span class="relative">10 days ago</span>
              </div>
            </td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <div class="action-btns">
                <button class="action-btn view-btn" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="action-btn nudge-btn" title="Send Nudge">
                  <i class="fas fa-bell"></i>
                </button>
                <button class="action-btn more-btn" title="More">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Row 4: Individual - Low Risk -->
          <tr class="taxpayer-row" data-id="TP-10567" data-type="individual">
            <td><input type="checkbox" class="row-checkbox"></td>
            <td><strong>TP-10567</strong></td>
            <td>
              <div class="taxpayer-cell">
                <div class="taxpayer-avatar individual">
                  <span>SK</span>
                </div>
                <div class="taxpayer-name">
                  <span class="name">Sarah Kabwe</span>
                  <span class="meta">Professional</span>
                </div>
              </div>
            </td>
            <td><span class="type-badge individual">Individual</span></td>
            <td>5554443330</td>
            <td>Services</td>
            <td>
              <div class="compliance-cell">
                <div class="compliance-bar">
                  <div class="compliance-fill" style="width: 94%; background: #28A745;"></div>
                </div>
                <span class="compliance-score">94</span>
              </div>
            </td>
            <td><span class="risk-chip low">Low</span></td>
            <td>
              <div class="date-cell">
                <span class="date">Sept 19, 2025</span>
                <span class="relative">1 day ago</span>
              </div>
            </td>
            <td><span class="status-badge active">Active</span></td>
            <td>
              <div class="action-btns">
                <button class="action-btn view-btn" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="action-btn nudge-btn" title="Send Nudge">
                  <i class="fas fa-bell"></i>
                </button>
                <button class="action-btn more-btn" title="More">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Row 5: Business - Dormant -->
          <tr class="taxpayer-row" data-id="TP-08765" data-type="business">
            <td><input type="checkbox" class="row-checkbox"></td>
            <td><strong>TP-08765</strong></td>
            <td>
              <div class="taxpayer-cell">
                <div class="taxpayer-avatar business">
                  <i class="fas fa-seedling"></i>
                </div>
                <div class="taxpayer-name">
                  <span class="name">AgriCo Farms</span>
                  <span class="meta">Agriculture</span>
                </div>
              </div>
            </td>
            <td><span class="type-badge business">Business</span></td>
            <td>7778889990</td>
            <td>Agriculture</td>
            <td>
              <div class="compliance-cell">
                <div class="compliance-bar">
                  <div class="compliance-fill" style="width: 58%; background: #FFC107;"></div>
                </div>
                <span class="compliance-score">58</span>
              </div>
            </td>
            <td><span class="risk-chip high">High</span></td>
            <td>
              <div class="date-cell">
                <span class="date">June 5, 2025</span>
                <span class="relative">4 months ago</span>
              </div>
            </td>
            <td><span class="status-badge dormant">Dormant</span></td>
            <td>
              <div class="action-btns">
                <button class="action-btn view-btn" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="action-btn nudge-btn" title="Send Nudge">
                  <i class="fas fa-bell"></i>
                </button>
                <button class="action-btn more-btn" title="More">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>




    <!-- Pagination -->
    <div class="pagination-wrapper">
      <div class="pagination-info">
        Showing <strong>1-5</strong> of <strong>2,539</strong> taxpayers
      </div>
      <div class="pagination-controls">
        <button class="page-btn" disabled>
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">3</button>
        <span class="page-dots">...</span>
        <button class="page-btn">508</button>
        <button class="page-btn">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      <div class="per-page-selector">
        <label>Per page:</label>
        <select id="perPage">
          <option value="5" selected>5</option>
          <option value="20">20</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>
    </div>
  </div>
</main>

<!-- Detail Slide-out Panel -->
<div class="detail-panel" id="taxpayerDetailPanel">
  <div class="detail-panel-overlay" id="taxpayerDetailOverlay"></div>
  <div class="detail-panel-content">
    <div class="detail-header">
      <h3 id="taxpayerDetailName">Loading...</h3>
      <button class="close-btn" id="closeTaxpayerDetail">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="detail-body" id="taxpayerDetailBody">
      <!-- Taxpayer Type Indicator -->
      <div class="detail-type-badge" id="detailTypeBadge"></div>

      <!-- Overview Section -->
      <div class="detail-section">
        <h4><i class="fas fa-info-circle"></i> Overview</h4>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Taxpayer ID</span>
            <span class="info-value" id="detailID">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">TPIN</span>
            <span class="info-value" id="detailTIN">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Type</span>
            <span class="info-value" id="detailType">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Sector</span>
            <span class="info-value" id="detailSector">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Registered</span>
            <span class="info-value" id="detailRegistered">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value" id="detailStatus">-</span>
          </div>
        </div>
      </div>

      <!-- Compliance Score -->
      <div class="detail-section">
        <h4><i class="fas fa-chart-line"></i> Compliance Performance</h4>
        <div class="compliance-display">
          <div class="compliance-circle">
            <svg width="140" height="140" viewBox="0 0 140 140">
              <circle cx="70" cy="70" r="60" fill="none" stroke="#E6EDF4" stroke-width="12"/>
              <circle id="complianceCircle" cx="70" cy="70" r="60" fill="none" stroke="#28A745" 
                      stroke-width="12" stroke-dasharray="377" stroke-dashoffset="94" 
                      transform="rotate(-90 70 70)" stroke-linecap="round"/>
            </svg>
            <div class="compliance-value" id="detailCompliance">73</div>
          </div>
          <div class="compliance-stats">
            <div class="stat-item">
              <span class="stat-icon">📊</span>
              <div>
                <p class="stat-text">On-time Filing Rate</p>
                <p class="stat-number" id="detailOnTime">87%</p>
              </div>
            </div>
            <div class="stat-item">
              <span class="stat-icon">📅</span>
              <div>
                <p class="stat-text">Average Days Late</p>
                <p class="stat-number" id="detailAvgLate">4 days</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="detail-section">
        <h4><i class="fas fa-dollar-sign"></i> Financial Summary (YTD)</h4>
        <div class="financial-grid">
          <div class="financial-item">
            <span class="financial-label">Revenue</span>
            <span class="financial-value" id="detailRevenue">ZMW 5.2M</span>
          </div>
          <div class="financial-item">
            <span class="financial-label">Tax Paid</span>
            <span class="financial-value" id="detailTaxPaid">ZMW 890K</span>
          </div>
          <div class="financial-item">
            <span class="financial-label">Outstanding</span>
            <span class="financial-value" id="detailOutstanding">ZMW 0</span>
          </div>
        </div>
      </div>

      <!-- Risk Factors -->
      <div class="detail-section">
        <h4><i class="fas fa-exclamation-triangle"></i> Risk Factors</h4>
        <div class="risk-factors" id="detailRiskFactors">
          <div class="factor-item">
            <div class="factor-header">
              <i class="fas fa-clock"></i>
              <span>Late filing pattern</span>
            </div>
            <div class="factor-impact">+0.25</div>
          </div>
          <div class="factor-item">
            <div class="factor-header">
              <i class="fas fa-chart-line"></i>
              <span>Revenue volatility</span>
            </div>
            <div class="factor-impact">+0.15</div>
          </div>
        </div>
      </div>

      <!-- Contact Information -->
      <div class="detail-section">
        <h4><i class="fas fa-address-book"></i> Contact Information</h4>
        <div class="contact-grid">
          <div class="contact-item">
            <i class="fas fa-phone"></i>
            <div>
              <p class="contact-label">Phone</p>
              <p class="contact-value" id="detailPhone">+260-xxx-xxx</p>
            </div>
          </div>
          <div class="contact-item">
            <i class="fas fa-envelope"></i>
            <div>
              <p class="contact-label">Email</p>
              <p class="contact-value" id="detailEmail">contact@example.zm</p>
            </div>
          </div>
          <div class="contact-item full-width">
            <i class="fas fa-map-marker-alt"></i>
            <div>
              <p class="contact-label">Address</p>
              <p class="contact-value" id="detailAddress">Lusaka, Zambia</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="detail-section">
        <h4><i class="fas fa-history"></i> Recent Activity</h4>
        <div class="activity-timeline" id="detailActivity">
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <p class="timeline-title">Tax return filed</p>
              <p class="timeline-meta">September 15, 2025</p>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <p class="timeline-title">Nudge sent - Filing reminder</p>
              <p class="timeline-meta">September 1, 2025</p>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <p class="timeline-title">Payment received</p>
              <p class="timeline-meta">August 28, 2025</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="detail-actions">
        <button class="detail-action-btn primary">
          <i class="fas fa-bell"></i> Send Nudge
        </button>
        <button class="detail-action-btn secondary">
          <i class="fas fa-calendar-alt"></i> Schedule Audit
        </button>
        <button class="detail-action-btn secondary">
          <i class="fas fa-flag"></i> Flag Account
        </button>
      </div>
    </div>
  </div>
</div>


    </div>
  </div>
</div>


<!-- Detail Panel -->
<div class="detail-panel" id="taxpayerDetailPanel">
  <!-- same structure as your HTML file -->
</div>

<script src="script.js"></script>
<script src="taxpayers.js"></script>
</body>
</html>



