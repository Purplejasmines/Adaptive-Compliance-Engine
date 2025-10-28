<?php
if (!isset($currentPage)) {
    $currentPage = basename($_SERVER['PHP_SELF']);
}
?>
<div class="sidebar">
  <div class="sidebar-header">
    <h2 class="sidebar-title"><i class="fas fa-shield-alt"></i> ZRA Admin</h2>
    <p class="sidebar-subtitle">Compliance Engine</p>
  </div>

  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item <?php if($currentPage=='dashboard.php') echo 'active'; ?>">
      <i class="fas fa-home"></i><span>Overview</span>
    </a>
    <a href="analysis.php" class="nav-item <?php if($currentPage=='analysis.php') echo 'active'; ?>">
      <i class="fas fa-chart-bar"></i><span>Analytics</span>
    </a>
    <a href="assessments.php" class="nav-item <?php if($currentPage=='assessments.php') echo 'active'; ?>">
      <i class="fas fa-exclamation-triangle"></i><span>Risk Assessments</span>
    </a>
    <a href="taxpayers.php" class="nav-item <?php if($currentPage=='taxpayers.php') echo 'active'; ?>">
      <i class="fas fa-users"></i><span>Taxpayers</span>
    </a>
    <a href="compliance.php" class="nav-item <?php if($currentPage=='compliance.php') echo 'active'; ?>">
      <i class="fas fa-balance-scale"></i><span>Compliance</span>
    </a>
    <a href="alerts.php" class="nav-item <?php if($currentPage=='alerts.php') echo 'active'; ?>">
      <i class="fas fa-bell"></i><span>Alerts</span>
    </a>
    <a href="reports.php" class="nav-item <?php if($currentPage=='reports.php') echo 'active'; ?>">
      <i class="fas fa-file-download"></i><span>Reports</span>
    </a>
    <a href="users.php" class="nav-item <?php if($currentPage=='users.php') echo 'active'; ?>">
      <i class="fas fa-user-cog"></i><span>User Management</span>
    </a>
    <a href="audit.php" class="nav-item <?php if($currentPage=='audit.php') echo 'active'; ?>">
      <i class="fas fa-history"></i><span>Audit Logs</span>
    </a>
    <a href="settings.php" class="nav-item <?php if($currentPage=='settings.php') echo 'active'; ?>">
      <i class="fas fa-cog"></i><span>Settings</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"></div>
      <div>
        <p class="user-name"><?php echo htmlspecialchars($adminName ?? 'Admin User'); ?></p>
        <p class="user-email"><?php echo htmlspecialchars($adminEmail ?? 'admin@example.com'); ?></p>
        <a href="logout.php" class="btn btn-outline-light btn-sm mt-2 logout-btn">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>
  </div>
</div>
